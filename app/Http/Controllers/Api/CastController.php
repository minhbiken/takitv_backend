<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\HelperService;
use Illuminate\Support\Facades\Cache;
use App\Services\MovieService;
use App\Services\TvshowService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;
class CastController extends Controller
{
    protected $imageUrlUpload;
    protected $tvshowService;
    protected $helperService;
    protected $movieService;    
    public function __construct(HelperService $helperService, MovieService $movieService, TvshowService $tvshowService)
    {
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $this->helperService = $helperService;
        $this->movieService = $movieService;
        $this->tvshowService = $tvshowService;
    }

    public function index(Request $request) {
        $page = $request->get('page', 1);
        $perPage = $request->get('limit', env('PAGE_LIMIT'));
        $orderBy = $request->get('orderBy', '');
        $search = $request->get('search', '');

        $select = "SELECT p.ID as id, p.post_name as slug, p.post_title as name, wp.meta_value as src FROM wp_posts p 
        LEFT JOIN wp_postmeta wp ON wp.post_id = p.ID AND wp.meta_key = '_person_image_custom' ";
        $where = " WHERE p.post_status = 'publish' AND p.post_type='person' AND wp.meta_value != '' ";

        if( $orderBy == '' ) {
            $order = "ORDER BY p.post_title DESC ";
        } else if( $orderBy == 'nameAsc' ) {
            $order = "ORDER BY p.post_title ASC ";
        } else if( $orderBy == 'nameDesc' ) {
            $order = "ORDER BY p.post_title DESC ";
        } else {
            $order = "ORDER BY p.post_title DESC ";
        }
        
        $whereSearch = '';
        if( $search != '' ) {
            $whereSearch = " AND p.post_title LIKE '%". $search ."%' ";
            $where = $where . $whereSearch;
        }

        //query all
        $query = $select . $where . $order;
        $queryTotal = "SELECT count(p.ID) as total, p.post_title
        FROM wp_posts p 
        LEFT JOIN wp_postmeta wp ON wp.post_id = p.ID AND wp.meta_key = '_person_image_custom' 
        WHERE p.post_status = 'publish' AND p.post_type='person' AND wp.meta_value != '' " . $whereSearch;
        if( Cache::has('person_query_total') && Cache::get('person_query_total') === $queryTotal && Cache::has('person_data_total')) {
            $total = Cache::get('person_data_total');
        } else {
            $dataTotal = DB::select($queryTotal);
            $total = $dataTotal[0]->total;
            Cache::forever('person_query_total', $queryTotal);
            Cache::forever('person_data_total', $total);
        }

        //query limit
        $limit = "LIMIT " . ( ( $page - 1 ) * $perPage ) . ", $perPage ;";
        $query = $query . $limit;
        $casts = [];

        $items = DB::select($query);

        //clear cast dupplicate
        $this->helperService->clearCastDupplicate($items);
        $items = DB::select($query);
        foreach ($items as $item) {
            $newSlug = (preg_match("@^[a-zA-Z0-9%+-_]*$@", $item->slug)) ? urldecode($item->slug) : $item->slug;
            $newSrc = str_replace('w66_and_h66_face', 'w300_and_h450_bestv2', $item->src);
            $newSrc = str_replace('w300_and_h450_bestv2e', '/w300_and_h450_bestv2', $newSrc);
            $casts[] = [
                'id' => $item->id,
                'slug' => $newSlug,
                'name' => $item->name,
                'src' =>  $newSrc
            ];
        }
        $topWeeks = $this->topWeek();
        $data = [
            "total" => $total,
            "perPage" => $perPage,
            "data" => [
                'topWeeks' => $topWeeks,
                'items' => $casts
            ]
        ];

        return response()->json($data, Response::HTTP_OK);
    }

    public function show(Request $request) 
    {
        $slug = $request->get('slug', '');
        $newSlug = urlencode($slug);
        $queryCast = "SELECT p.ID as id, p.post_name as slug, p.post_title as name, wp.meta_value as src, wp_tv_show.meta_value as tv_show, wp_movie.meta_value as movie
        FROM wp_posts p 
        LEFT JOIN wp_postmeta wp ON wp.post_id = p.ID AND wp.meta_key = '_person_image_custom' 
        LEFT JOIN wp_postmeta wp_tv_show ON wp_tv_show.post_id = p.ID AND wp_tv_show.meta_key = '_tv_show_cast'
        LEFT JOIN wp_postmeta wp_movie ON wp_movie.post_id = p.ID AND wp_movie.meta_key = '_movie_cast'
        WHERE ( p.post_name= '" . $slug .  "' OR p.post_name= '". $newSlug ."' )";
        $dataCast = DB::select($queryCast);
        $cast = [];
        if( count($dataCast) > 0 ) {
            $data = $dataCast[0];
            $newSlug = (preg_match("@^[a-zA-Z0-9%+-_]*$@", $data->slug)) ? urldecode($data->slug) : $data->slug;
            $newSrc = str_replace('w66_and_h66_face', 'w300_and_h450_bestv2', $data->src);
            $newSrc = str_replace('w300_and_h450_bestv2e', '/w300_and_h450_bestv2', $newSrc);
            $cast = [
                'id' => $data->id,
                'slug' => $newSlug,
                'name' => $data->name,
                'src' => $newSrc,
                'tv_show' => $data->tv_show,
                'movie' => $data->movie,
            ];
            //get tv-show
            $tvShow = unserialize($cast['tv_show']);
            $tvShowData = [];
            if( $tvShow != '' && count($tvShow) > 0 ) {
                foreach( $tvShow as  $tvShowId) {
                    $select = "SELECT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt, p.post_date, p.post_modified 
                    FROM wp_posts p 
                    WHERE  ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) AND p.ID=". $tvShowId;
                    if( count($this->tvshowService->getItems($select)) > 0 ) {
                        $tvShowData[] = $this->tvshowService->getItems($select)[0];
                    }
                }
            }
            $cast['tv_show'] = $tvShowData;
            
            //get movie
            $movies = unserialize($cast['movie']);
            $movie = [];
            if( $movies != '' && count($movies) > 0 ) {
                foreach( $movies as  $movieId) {
                    $select = "SELECT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt, p.post_date, p.post_modified 
                    FROM wp_posts p 
                    WHERE  ((p.post_type = 'movie' AND (p.post_status = 'publish'))) AND p.ID=". $movieId;
                    if( count($this->movieService->getItems($select)) > 0 ) {
                        $movie[] = $this->movieService->getItems($select)[0];
                    }
                }
            }
            $cast['movie'] = $movie;
        }
        return response()->json($cast, Response::HTTP_OK);
    }

    public function topWeek() {
        $queryTopWeek = "SELECT p.ID, p.post_name, p.post_title, p.original_title, p.post_content, p.post_type, p.post_date_gmt, p.post_date FROM `wp_most_popular` mp
                            LEFT JOIN wp_posts p ON p.ID = mp.post_id
                            WHERE p.post_title != '' AND mp.post_id != '' AND p.ID != '' AND p.post_status = 'publish'
                            ORDER BY mp.7_day_stats DESC
                            LIMIT 5";
        return $this->getItems($queryTopWeek);
    }

    public function getItems($query) {
        $sliders = [];
        $slug = '';
        $sliderDatas = DB::select($query);
        foreach ( $sliderDatas as $sliderData ) {
            $titleSlider = $sliderData->post_title; 
            $linkSlider = 'movie/' . $sliderData->post_title;
            $year = '';
            $queryMeta = "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ". $sliderData->ID .";";
            $dataMetas = DB::select($queryMeta);
            if( count($dataMetas) > 0 ) {
                foreach ( $dataMetas as $dataMeta ) {
                    if( $dataMeta->meta_key == '_movie_release_date' || $dataMeta->meta_key == '_episode_release_date' ) {
                        if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMeta->meta_value)) {
                            $newDataReleaseDate = explode('-', $dataMeta->meta_value);
                            $year = $newDataReleaseDate[0];
                        } else {
                            $year = $dataMeta->meta_value > 0 ? date('Y', $dataMeta->meta_value) : date('Y');
                        }
                    }
                }
            }
            $queryTaxonomy = "SELECT t.name, t.slug FROM `wp_posts` p
                                    left join wp_term_relationships t_r on t_r.object_id = p.ID
                                    left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'movie_genre'
                                    left join wp_terms t on tx.term_id = t.term_id
                where t.name != 'featured' AND t.name != '' AND p.ID = ". $sliderData->ID .";";
            
            if( $sliderData->post_type == 'tv_show' ) {
                $queryEpisode = "SELECT meta_key, meta_value FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $sliderData->ID . " LIMIT 1;";
                $dataEpisode = DB::select($queryEpisode);
                $episodeData = $dataEpisode[0]->meta_value;
                $episodeData = unserialize($episodeData);
                $lastSeason = end($episodeData);
                $episodeId = end($lastSeason['episodes']);
                
                $select = "SELECT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt FROM wp_posts p ";
                $where = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
                $whereTitle = " AND p.ID='". $episodeId ."' ";
    
                $where = $where . $whereTitle;
                $query = $select . $where;
                $dataEpisoSlider = DB::select($query);
                
                if( count($dataEpisoSlider) > 0 ) {
                    $linkSlider = 'episode/' . $dataEpisoSlider[0]->post_title;
                    $slug = $dataEpisoSlider[0]->post_name;
                    $titleSlider = $dataEpisoSlider[0]->post_title;
                }
                
                $queryTaxonomy = "SELECT t.name, t.slug FROM `wp_posts` p
                left join wp_term_relationships t_r on t_r.object_id = p.ID
                left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'tv_show_genre'
                left join wp_terms t on tx.term_id = t.term_id
                where t.name != 'featured' AND t.name != '' AND p.ID = ". $sliderData->ID .";";
            }
            $dataTaxonomys = DB::select($queryTaxonomy);
            $genres = [];
            if ( count($dataTaxonomys) > 0 ) {
                foreach( $dataTaxonomys as $dataTaxonomy ) {
                    $genres[] = [
                        'name' => $dataTaxonomy->name,
                        'link' =>  $dataTaxonomy->slug,
                        'slug' =>  $dataTaxonomy->slug
                    ];
                }
            }
            $sliders[] = [
                'id' => $sliderData->ID,
                'year' => $year,
                'genres' => $genres,
                'title' => $titleSlider,
                'link' => $linkSlider,
                'slug' => $slug,
                'postType' => $sliderData->post_type,
                'postDate' => $sliderData->post_date
            ];
        }
        return $sliders;
    }

    public function makeCacheCast(Request $request) {
        $from = $request->get('from', 0);
        $to = $request->get('to', 0);
        for($from; $from < $to; $from++) {
            Http::get(route('casts',  ['page' => $from]));
        }
    }

    public function checkCastOfMovie(Request $request) {
        $limitFrom = $request->get('limit_from', 0);
        $limitTo = $request->get('limit_to', 30);
        $queryMovie = "SELECT p.ID, p.post_title, pm.meta_value as tmdb_id, pm2.meta_value as casts
        FROM wp_posts p
        LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_tmdb_id' AND pm.meta_value != ''
        LEFT JOIN wp_postmeta pm2 ON pm2.post_id = p.ID AND pm2.meta_key = '_cast' AND pm2.meta_value != ''
        WHERE ((p.post_type = 'movie' AND (p.post_status = 'publish'))) 
        ORDER BY p.post_date DESC 
        LIMIT " . $limitFrom . ", " . $limitTo . " ;";
        $dataMovies =  DB::select($queryMovie);
        //check cast right or wrong
        $dataWrong = [];
        foreach ( $dataMovies as $dataMovie ) {
            //get first cast 
            if( $dataMovie->casts != '' || $dataMovie->tmdb_id != '' ) {
                try {
                    $castsOfMovie = unserialize($dataMovie->casts);
                    $firstCast = $castsOfMovie[0]['id'];
                    //get title of first cast
                    $queryCast = "SELECT p.post_title FROM wp_posts p
                    WHERE ((p.post_type = 'person' AND (p.post_status = 'publish'))) AND p.ID=".$firstCast;
                    $dataCast =  DB::select($queryCast);
                    if( count($dataCast) > 0 ) {
                        //check tmdb movie
                        $urlTmdb = "https://www.themoviedb.org/movie/" . $dataMovie->tmdb_id . "/cast";
                        $contentTmdb = @file_get_contents($urlTmdb);
                        preg_match_all("/\">(.*)<\/a><p>/", $contentTmdb, $result);
                        $name = str_replace("<p>", "", $result[0]);
                        $name = str_replace("\">", "", $name);
                        
                        if ( $dataCast[0]->post_title != strip_tags(html_entity_decode($name[0])) ) {
                            $wrong = [
                                'movie_id' => $dataMovie->ID,
                                'tmdb_id' => $dataMovie->tmdb_id,
                            ];
                            array_push($dataWrong, $wrong);
                        }
                    }
                } catch (Throwable $e) {
                    continue;
                }
            }
        }
        Storage::disk('local')->put($limitFrom.'_'.$limitTo.'movie_wrong_person.json', json_encode($dataWrong));  
        return ("Ok!");
    }
    public function checkCastOfTvShow(Request $request) {
        $limitFrom = $request->get('limit_from', 0);
        $limitTo = $request->get('limit_to', 30);
        $queryMovie = "SELECT p.ID, p.post_title, pm.meta_value as tmdb_id, pm2.meta_value as casts
        FROM wp_posts p
        LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_tmdb_id' AND pm.meta_value != ''
        LEFT JOIN wp_postmeta pm2 ON pm2.post_id = p.ID AND pm2.meta_key = '_cast' AND pm2.meta_value != ''
        WHERE ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) 
        ORDER BY p.post_date DESC 
        LIMIT " . $limitFrom . ", " . $limitTo . " ;";
        $dataMovies =  DB::select($queryMovie);
        //check cast right or wrong
        $dataWrong = [];
        foreach ( $dataMovies as $dataMovie ) {
            //get first cast 
            if( $dataMovie->casts != '' || $dataMovie->tmdb_id != '' ) {
                try {
                    $castsOfMovie = unserialize($dataMovie->casts);
                    $firstCast = $castsOfMovie[0]['id'];
                    //get title of first cast
                    $queryCast = "SELECT p.post_title FROM wp_posts p
                    WHERE ((p.post_type = 'person' AND (p.post_status = 'publish'))) AND p.ID=".$firstCast;
                    $dataCast =  DB::select($queryCast);
                    if( count($dataCast) > 0 ) {
                        //check tmdb movie
                        $urlTmdb = "https://www.themoviedb.org/tv/" . $dataMovie->tmdb_id . "/cast";
                        $contentTmdb = @file_get_contents($urlTmdb);
                        
                        preg_match_all("/\">(.*)<\/a>/", $contentTmdb, $result);
                        $name = str_replace("\">", "", $result[0][41]);
                        if ( $dataCast[0]->post_title != strip_tags(html_entity_decode($name)) ) {
                            $wrong = [
                                'movie_id' => $dataMovie->ID,
                                'tmdb_id' => $dataMovie->tmdb_id
                            ];
                            array_push($dataWrong, $wrong);
                        }
                    }
                } catch (Throwable $e) {
                    continue;
                }
            }
        }
        Storage::disk('local')->put($limitFrom.'_'.$limitTo.'tv_show_wrong_person.json', json_encode($dataWrong));  
        return ("Ok!");
    }

    public function updateCastOfMovie(Request $request) {
        $file = $request->get('file', '');
        $movieList = json_decode(Storage::disk('local')->get($file), true);
        foreach( $movieList as $movie) {
            Http::get(route('cast.import',  ['movieId' => $movie['movie_id'], 'tmdbId' => $movie['tmdb_id'], 'postType' => 'movie']));
        }
        return "Ok!";
    }

    public function updateCastOfTvShow(Request $request) {
        $file = $request->get('file', '');
        $movieList = json_decode(Storage::disk('local')->get($file), true);
        //print_r(count($movieList)); die;        
        foreach( $movieList as $movie) {
            Http::get(route('cast.import',  ['movieId' => $movie['movie_id'], 'tmdbId' => $movie['tmdb_id'], 'postType' => 'tv_show']));
        }
        return "Ok!";
    }
}
