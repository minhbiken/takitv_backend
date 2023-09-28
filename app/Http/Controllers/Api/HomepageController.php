<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\MovieService;
use App\Services\TvshowService;
use App\Services\SearchService;
use App\Services\HelperService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
class HomepageController extends Controller
{

    protected $movieService;
    protected $tvshowService;
    protected $searchService;
    protected $helperService;
    protected $imageUrlUpload;
    public function __construct(MovieService $movieService, TvshowService $tvshowService, SearchService $searchService, HelperService $helperService)
    {
        $this->movieService = $movieService;
        $this->tvshowService = $tvshowService;
        $this->searchService = $searchService;
        $this->helperService = $helperService;
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if( Cache::has('homepage')) {
            $homepage = Cache::get('homepage');
            $sliderRandoms = $this->tvshowService->getTvShowRandom();
            $homepage['otts']['ottTitle'] = $sliderRandoms['title'];
            $homepage['otts']['ottSliders'] = $sliderRandoms['items'];
        } else {
            //Get header slider
            $sliderQuery = "SELECT meta_key, ID, post_title, post_name, post_type, post_date, meta_value, IF(pm.meta_value IS NOT NULL , CAST( pm.meta_value AS UNSIGNED ) , 0 ) as sort_order
            FROM wp_posts as p
            LEFT JOIN wp_postmeta as pm ON p.ID = pm.post_id and pm.meta_key= '_sort_order'
            WHERE ID IN ( SELECT object_id FROM `wp_term_relationships` WHERE term_taxonomy_id IN (17 , 43) ) 
                AND p.post_status = 'publish'
            ORDER BY sort_order ASC, post_date DESC;";

            $sliders = $this->helperService->getSliderItems($sliderQuery);
            
            //Get Chanel slider random between USA and Korea
            $sliderRandoms = $this->tvshowService->getTvShowRandom();

            //get 12 tv-show
            $queryTvshow = "SELECT DISTINCT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM `wp_posts` p 
                                LEFT JOIN wp_term_relationships t_r ON t_r.object_id = p.ID 
                                LEFT JOIN wp_term_taxonomy tx ON t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'tv_show_genre'
                                LEFT JOIN wp_terms t ON tx.term_id = t.term_id 
                                WHERE ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) ORDER BY p.post_date DESC LIMIT 12;";
            $dataTvshow = $this->tvshowService->getItems($queryTvshow);

            $categories = [
                'menu' => [
                    [
                        'title' => '전체',
                        'link' => 'tv-show'
                    ],
                    [
                        'title' => '드라마',
                        'link' => 'k-drama'
                    ],
                    [
                        'title' => '예능',
                        'link' => 'k-show'
                    ],
                    [
                        'title' => '시사/교양',
                        'link' => 'k-sisa'
                    ],
                    [
                        'title' => '미드',
                        'link' => 'u-drama'
                    ]
                ],
                'items' => [
                    'tv-show' => $dataTvshow,
                ]
            ];
            
            //Get 12 movies 
            $movies = [];
            $queryMovie = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM wp_posts p WHERE  ((p.post_type = 'movie' AND (p.post_status = 'publish'))) ORDER BY p.post_date DESC LIMIT 12";
            $dataMovies = DB::select($queryMovie);

            $movieNewests = [];
            $srcSet = [];
            $originalTitleMovie = '';
            foreach ( $dataMovies as $key => $dataMovie ) {
                $queryMetaMovie = "SELECT meta_value, meta_key FROM wp_postmeta WHERE post_id = ". $dataMovie->ID .";";

                $querySrcMetaMovie = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                                LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $dataMovie->ID .";";
                $dataSrcMetaMovie = DB::select($querySrcMetaMovie);

                $srcMovie = $this->imageUrlUpload.$dataSrcMetaMovie[0]->meta_value;

                $srcSet = $this->helperService->getAttachmentsByPostId($dataMovie->ID);
                
                $releaseDate = '';
                $dataMetaMovies = DB::select($queryMetaMovie);
                foreach($dataMetaMovies as $dataMetaMovie) {
                    if( $dataMetaMovie->meta_key == '_movie_release_date' ) {
                        if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMetaMovie->meta_value)) {
                            $newDataReleaseDate = explode('-', $dataMetaMovie->meta_value);
                            $releaseDate = $newDataReleaseDate[0];
                        } else {
                            $releaseDate = $dataMetaMovie->meta_value > 0 ? date('Y', $dataMetaMovie->meta_value) : '';
                        }
                    }
                    
                    if( $dataMetaMovie->meta_key == '_movie_run_time' ) {
                        $movieRunTime = $dataMetaMovie->meta_value;
                    }

                    if( $dataMetaMovie->meta_key == '_movie_original_title' ) {
                        $originalTitleMovie = $dataMetaMovie->meta_value;
                    }
                }
                
                $queryTaxonomyMovie = "SELECT t.name, t.slug FROM `wp_posts` p
                            left join wp_term_relationships t_r on t_r.object_id = p.ID
                            left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'movie_genre'
                            left join wp_terms t on tx.term_id = t.term_id
                            where t.name != 'featured' AND t.name != '' AND p.ID = ". $dataMovie->ID .";";

                $dataTaxonomyMovies = DB::select($queryTaxonomyMovie);

                $genreMovies = [];
                foreach( $dataTaxonomyMovies as $dataTaxonomyMovie ) {
                    $genreMovies[] = [
                        'name' => $dataTaxonomyMovie->name,
                        'link' =>  $dataTaxonomyMovie->slug
                    ];
                }

                //Get 8 movies newlest
                if( $key < 8 ) {
                    $movieNewests[] = [
                        'id' => $dataMovie->ID,
                        'year' => $releaseDate,
                        'genres' => $genreMovies,
                        'title' => $dataMovie->post_title,
                        'originalTitle' => $originalTitleMovie,
                        'description' => $dataMovie->post_content,
                        'src' => $srcMovie,
                        'srcSet' => $srcSet,
                        'duration' => $movieRunTime
                    ];
                }
                
                $movies[] = [
                    'id' => $dataMovie->ID,
                    'year' => $releaseDate,
                    'genres' => $genreMovies,
                    'title' => $dataMovie->post_title,
                    'originalTitle' => $originalTitleMovie,
                    'description' => $dataMovie->post_content,
                    'src' => $srcMovie,
                    'srcSet' => $srcSet,
                    'duration' => $movieRunTime
                ];
            }

            $topWeeks = $this->movieService->getTopWeeks();

            //Get movies newest of Korea for slider in bottom
            $queryKoreaMovie = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM `wp_posts` p
            LEFT JOIN wp_term_relationships t_r on t_r.object_id = p.ID
            LEFT JOIN wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'movie_genre'
            LEFT JOIN wp_terms t on tx.term_id = t.term_id AND t.slug = 'kmovie'
            WHERE t.name != 'featured' AND t.name != ''
            ORDER BY p.post_date DESC
            LIMIT 8;";

            $movieKoreas = $this->movieService->getItems($queryKoreaMovie);
            
            $homepage = [
                'sliders' => $sliders,
                'otts' => [
                    'ottChanels' => config('constants.ottChanels'),
                    'ottTitle' => $sliderRandoms['title'],
                    'ottSliders' => $sliderRandoms['items']
                ],
                'tvshows' => [
                    'title' => '최신등록 방송',
                    'categories' => $categories
                ],
                'movies' => [
                    'title' => '최신등록영화',
                    'items' => $movies
                ],
                'moviesCarousel' => $movieKoreas,
                'movieNewests' => [
                    'topWeeks' => $topWeeks,
                    'movieNewests' => $movieNewests
                ],
            ];
            Cache::forever('homepage', $homepage);
        }
        
        return response()->json($homepage, Response::HTTP_OK);
    }

    public function search(Request $request) {
        $title = $request->get('title', '');
        $page = $request->get('page', 1);
        $perPage = $request->get('limit', env('PAGE_LIMIT'));
        $orderBy = $request->get('orderBy', '');

        $select = "SELECT p.ID, p.post_name, p.post_title, p.post_type, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM wp_posts p ";
        $where = " WHERE p.post_status = 'publish' AND p.post_type IN ('tv_show', 'movie') ";

        if( $title != '' ) {
            $s_rp = str_replace(" ","", $title);
            $whereTitle = " AND ( p.post_title LIKE '%".$title."%' OR  
            REPLACE(p.post_title, ' ', '') like '%".$s_rp."%' OR
            p.original_title LIKE '%".$title."%' OR
            REPLACE(p.original_title, ' ', '') like '%".$s_rp."%'
        ) ";

            $where = $where . $whereTitle;
        }

        if( $orderBy == '' ) {
            $order = "ORDER BY p.post_date DESC ";
        } else if( $orderBy == 'titleAsc' ) {
            $order = "ORDER BY p.post_title ASC ";
         }else if( $orderBy == 'titleDesc' ) {
            $order = "ORDER BY p.post_title DESC ";
        } else if($orderBy == 'date' ) {
            $order = "ORDER BY p.post_date DESC ";
        } else if($orderBy == 'rating') {
            $selectRating = "LEFT JOIN wp_most_popular mp ON mp.post_id = p.ID";
            $select = $select . $selectRating;
            $order = "ORDER BY mp.all_time_stats DESC ";
        } else if($orderBy == 'menuOrder') {
            $order = "ORDER BY p.menu_order DESC ";
        } else {
            $order = "ORDER BY p.post_date DESC ";
        }

        //query all
        $query = $select . $where . $order;

        $selectTotal = "SELECT COUNT(p.ID) as total FROM wp_posts p ";
        $queryTotal = $selectTotal . $where;
        $dataTotal = DB::select($queryTotal);
        $total = $dataTotal[0]->total;

        //query limit
        $limit = "LIMIT " . ( ( $page - 1 ) * $perPage ) . ", $perPage ;";
        $query = $query . $limit;
        $items = $this->searchService->getItems($query);
        $topWeeks = $this->tvshowService->getTopWeeks();
        $data = [
            "total" => $total,
            "perPage" => $perPage,
            "data" => [
                'topWeeks' => $topWeeks,
                'items' => $items
            ]
        ];
        
        return response()->json($data, Response::HTTP_OK);
    }

    public function tvShowHomepage(Request $request) {
        $type = $request->get('type', '');
        if( Cache::has('tvshow_homepage_' . $type) ) {
            $tvShow = Cache::get('tvshow_homepage_' . $type);
        } else {
            $select = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt, p.post_date, p.post_modified FROM wp_posts p ";
            $where = " WHERE  ((p.post_type = 'tv_show' AND (p.post_status = 'publish')))";
            $tvShow = [];
            if( $type != '' ) {
                $categoryTvShowKorea = config('constants.categoryTvshowKoreas');
                if( in_array($type, $categoryTvShowKorea) ) {
                    $idType = "SELECT wr.object_id
                                FROM wp_terms t
                                LEFT JOIN wp_term_taxonomy wt ON t.term_id = wt.term_id
                                LEFT JOIN wp_term_relationships wr ON wr.term_taxonomy_id = wt.term_taxonomy_id
                                WHERE slug = '". $type ."'";
                    $whereType = " AND p.ID IN ( ". $idType ." ) ";
                } else {
                    $whereType = $this->tvshowService->getWhereByType($type);
                }
                $where = $where . $whereType;
            }
            $queryTvshow = $select . $where . " ORDER BY p.post_date DESC LIMIT 12";
            $dataTvshow = $this->tvshowService->getItems($queryTvshow);
            $tvShow = $dataTvshow;
            Cache::forever('tvshow_homepage_' . $type, $tvShow);
        }
        
        return response()->json($tvShow, Response::HTTP_OK);
    }

    public function clearCache() {
        Artisan::call('cache:clear');
        $this->helperService->makeCacheFirst();
    }

    public function clearCacheByKey($key='') {
        Cache::forget($key);
        return "OK!";
    }

    public function timeCheck() {
        return "OK!";
    }

    public function makeCacheFirst() {
        $this->helperService->makeCacheFirst();
        return "OK!";
    }

    public function putGmtTime() {
        $this->clearCache();
        Storage::disk('public')->put('gmtTime.txt', date('Y-m-d H:i:s'));
    }

    public function getGmtTime() {
        return Storage::disk('public')->get('gmtTime.txt');
    }

    public function getMovieTMDBId(Request $request) {
        $limitFrom = $request->get('limit_from', 0);
        $limitTo = $request->get('limit_to', 30);
        $queryMovie = "SELECT p.ID, pm.meta_value as tmdb_id
        FROM wp_posts p
        LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_tmdb_id' AND pm.meta_value != ''
        WHERE ((p.post_type = 'movie' AND (p.post_status = 'publish'))) 
        ORDER BY p.post_date DESC 
        LIMIT " . $limitFrom . ", " . $limitTo . " ;";
        $dataMovie =  DB::select($queryMovie);
        Storage::disk('local')->put($limitFrom.'_'.$limitTo.'_tmdb.json', response()->json($dataMovie));
    }

    public function getMovieLimit() {
        for($i=0; $i <= 6987; $i=$i+100) {
            Http::get(route('movie.tmdb',  ['limit_from' => $i, 'limit_to' => 100]));
        }
        Http::get(route('movie.tmdb',  ['limit_from' => 6900, 'limit_to' => 100]));
    }

    public function insertPerson() {
        $queryPostPerson = "INSERT INTO wp_posts(post_title, post_content, post_status, guid,post_type, post_excerpt, to_ping, pinged, post_content_filtered, post_date, post_date_gmt, post_modified, post_modified_gmt) VALUES ( 'Mars Shelley 1', 'Mars Shelley 1', 'publish', 'https://try.chethemes.com/vodi/?post_type=person&amp;p=5346', 'person', 'test', ' ', ' ', '', now(), now(), now(), now() );";
        DB::insert($queryPostPerson);
        $queryNew = "SELECT ID FROM wp_posts where post_title = 'Mars Shelley 1'";
        $idnewPerson = DB::select($queryNew);
        print_r($idnewPerson[0]->ID); die;
    }

}