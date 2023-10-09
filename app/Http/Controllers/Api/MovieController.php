<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\MovieService;
use App\Services\HelperService;
use Illuminate\Support\Facades\Cache;
class MovieController extends Controller
{
    protected $movieService;
    protected $helperService;
    protected $imageUrlUpload;
    public function __construct(MovieService $movieService, HelperService $helperService)
    {
        $this->movieService = $movieService;
        $this->helperService = $helperService;        
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('limit', env('PAGE_LIMIT'));
        if( $perPage > env('PAGE_LIMIT') ) {
            $perPage = env('PAGE_LIMIT');
        }
        $releaseYear = $request->get('year', '');
        $genre = $request->get('genre', '');
        $orderBy = $request->get('orderBy', '');
        $title = $request->get('title', '');
        
        if( $page == 1 &&  $orderBy == 'date' && $releaseYear == '' && $genre == '' && Cache::has('movie_first') ) {
            $data = Cache::get('movie_first');
        } else {
            $imageUrlUpload = env('IMAGE_URL_UPLOAD');

            $select = "SELECT p.ID, p.post_name, p.post_title, p.post_content, p.original_title FROM wp_posts p ";
            $where = " WHERE ((p.post_type = 'movie' AND (p.post_status = 'publish'))) ";

            if( $releaseYear != '' ) {
                $queryReleaseYear = "SELECT post_id
                FROM wp_postmeta
                WHERE
                meta_key = '_movie_release_date'
                and DATE_FORMAT(FROM_UNIXTIME(meta_value), '%Y') = '". $releaseYear. "'";
                $where = $where . "AND p.ID IN ( ". $queryReleaseYear ." ) ";    
            }

            if( $genre != '' ) {
                $genre = explode(',', $genre);
                foreach($genre as $key => $g) {
                    $genre[$key] = "'" . "$g" . "'";
                }
                $genre = join(",", $genre);
                $queryGenre = "SELECT tr.object_id FROM wp_terms t
                left join wp_term_taxonomy tx on tx.term_id = t.term_id
                left join wp_term_relationships tr on tr.term_taxonomy_id = tx.term_taxonomy_id
                WHERE t.slug IN (". $genre .") OR t.name IN (". $genre .") ";
                $where = $where . "AND p.ID IN ( ". $queryGenre ." ) ";    
            }

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

            //query all movie
            $query = $select . $where . $order;
            $selectTotal = "SELECT COUNT(p.ID) as total FROM wp_posts p ";
            $queryTotal = $selectTotal . $where;

            if( Cache::has('movie_query_total') && Cache::get('movie_query_total') === $queryTotal && Cache::has('movie_data_total')) {
                $total = Cache::get('movie_data_total');
            } else {
                $dataTotal = DB::select($queryTotal);
                $total = $dataTotal[0]->total;
                Cache::forever('movie_query_total', $queryTotal);
                Cache::forever('movie_data_total', $total);
            }
            //query limit movie
            $limit = "LIMIT " . ( ( $page - 1 ) * $perPage ) . ", $perPage ;";
            $query = $query . $limit;
            $datas = DB::select($query);

            $movies = [];
            $src = '';
            $srcSet = [];
            $originalTitle = '';
            $link = '';
            $releaseDate = '';
            foreach( $datas as $key => $data ) {
                if (Cache::has($data->ID)) {
                    $movie = Cache::get($data->ID);
                } else {
                    $queryMeta = "SELECT meta_value, meta_key FROM wp_postmeta WHERE post_id = ". $data->ID .";";
                    $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                                    LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $data->ID .";";
                    $dataSrcMeta = DB::select($querySrcMeta);
                    $src = $imageUrlUpload.$dataSrcMeta[0]->meta_value;
                    $dataMetas = DB::select($queryMeta);
                    $movieRunTime = '';
                    foreach($dataMetas as $dataMeta) {
                        if( $releaseYear == '' ) {
                            if( $dataMeta->meta_key == '_movie_release_date' ) {
                                if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMeta->meta_value)) {
                                    $newDataReleaseDate = explode('-', $dataMeta->meta_value);
                                    $releaseDate = $newDataReleaseDate[0];
                                } else {
                                    $releaseDate = $dataMeta->meta_value > 0 ? date('Y', $dataMeta->meta_value) : '';
                                }
                            }
                        } else {
                            $releaseDate = $releaseYear;
                        }
                    
                        if( $dataMeta->meta_key == '_movie_run_time' ) {
                            $movieRunTime = $dataMeta->meta_value;
                        }

                        if( $dataMeta->meta_key == '_movie_original_title' ) {
                            $originalTitle = $dataMeta->meta_value;
                        }
                    }

                    $queryTaxonomy = "SELECT t.name, t.slug FROM `wp_posts` p
                                left join wp_term_relationships t_r on t_r.object_id = p.ID
                                left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'movie_genre'
                                left join wp_terms t on tx.term_id = t.term_id
                                where t.name != 'featured' AND t.name != '' AND p.ID = ". $data->ID .";";

                    $dataTaxonomys = DB::select($queryTaxonomy);

                    $genres = [];
                    $slug = [];
                    foreach( $dataTaxonomys as $k => $dataTaxonomy ) {
                        $genres[$k] = [
                            'name' => $dataTaxonomy->name,
                            'link' =>  $dataTaxonomy->slug
                        ];
                        $slug[] = "'" . $dataTaxonomy->name . "'";
                    }
            
                    $srcSet = $this->helperService->getAttachmentsByPostId($data->ID);

                    $link = 'movie/' . $data->post_name;
                    $movie = [
                        'id' => $data->ID,
                        'year' => $releaseDate,
                        'genres' => $genres,
                        'title' => $data->post_title,
                        'originalTitle' => $originalTitle,
                        'description' => $data->post_content,
                        'link' => $link,
                        'src' => $src,
                        'srcSet' => $srcSet,
                        'duration' => $movieRunTime,
                        'outlink' => ''
                    ];

                    Cache::forever($data->ID, $movie);
                }

                $movies[$key] = $movie;
            }
            $topWeeks = $this->movieService->getTopWeeks();
            $populars = $this->movieService->getPopulars();

            $data = [
                "total" => $total,
                "perPage" => $perPage,
                "data" => [
                    'topWeeks' => $topWeeks,
                    'populars' => $populars,
                    'items' => $movies
                ]
            ];

            if( $page == 1 &&  $orderBy == 'date' && $releaseYear == '' && $genre == '' ) {
                Cache::forever('movie_first', $data);
            }
        }
        
        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $title
     * @return \Illuminate\Http\Response
     */
    public function show($title, Request $request)
    {
        $titleMovie = $request->get('title', '');
        $select = "SELECT p.ID, p.post_name, p.post_title, p.post_content, p.original_title FROM wp_posts p ";
        $where = " WHERE ((p.post_type = 'movie' AND (p.post_status = 'publish'))) ";
        $whereTitle = " AND p.post_title='". $titleMovie ."'  LIMIT 1; ";

        $where = $where . $whereTitle;
        $movies = [];

        $data = DB::select($select . $where);

        if (count($data) == 0) {
            return response()->json($movies, Response::HTTP_NOT_FOUND);
        }
        $dataMovie = $data[0];
        $srcSet = [];
        $releaseYear = '';
        
        $queryTaxonomy = "SELECT t.name, t.slug FROM `wp_posts` p
                        left join wp_term_relationships t_r on t_r.object_id = p.ID
                        left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'movie_genre'
                        left join wp_terms t on tx.term_id = t.term_id
                        where t.name != 'featured' AND t.name != '' AND p.ID = ". $dataMovie->ID .";";
                        
        $dataTaxonomys = DB::select($queryTaxonomy);
        
        $genres = [];
        $slug = [];
        foreach( $dataTaxonomys as $dataTaxonomy ) {
            $genres[] = [
                'name' => $dataTaxonomy->name,
                'link' =>  $dataTaxonomy->slug
            ];
            $slug[] = "'" . $dataTaxonomy->name . "'";
        }

        $outLink = $this->helperService->getOutLink();
        if ( $outLink != '' ) {
            $outlink =  $outLink . '?pid=' . $dataMovie->ID;
        } else {
            $outlink = '';
        }

        //get 8 movies related
        $slug = join(",", $slug);
        if( $slug != '' ) {
            $queryTaxonomyRelated = "SELECT DISTINCT p.ID, p.post_name, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM `wp_posts` p
                left join wp_term_relationships t_r on t_r.object_id = p.ID
                left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'movie_genre'
                left join wp_terms t on tx.term_id = t.term_id
                where t.name != 'featured' AND t.name != '' AND (t.name IN ( " . $slug . " ) OR t.slug IN ( " . $slug . " ) ) AND p.ID != ". $dataMovie->ID ." ORDER BY p.post_date DESC LIMIT 8";
        } else {
            $queryTaxonomyRelated = "SELECT DISTINCT p.ID, p.post_name, p.post_title, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM `wp_posts` p
                left join wp_term_relationships t_r on t_r.object_id = p.ID
                left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'movie_genre'
                left join wp_terms t on tx.term_id = t.term_id
                where t.name != 'featured' AND t.name != '' AND p.ID != ". $dataMovie->ID ." ORDER BY p.post_date DESC LIMIT 8";
        }
       
        $dataRelateds = $this->movieService->getItems($queryTaxonomyRelated);

        $queryMeta = "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ". $dataMovie->ID .";";
        $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                        LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $dataMovie->ID .";";
        $dataSrcMeta = DB::select($querySrcMeta);

        $src = $this->imageUrlUpload.$dataSrcMeta[0]->meta_value;

        $dataMetas = DB::select($queryMeta);
        $movieRunTime = '';
        $originalTitle = '';

        $casts = [];

        foreach($dataMetas as $dataMeta) {
            if( $releaseYear == '' ) {
                if( $dataMeta->meta_key == '_movie_release_date' ) {
                    if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMeta->meta_value)) {
                        $newDataReleaseDate = explode('-', $dataMeta->meta_value);
                        $releaseDate = $newDataReleaseDate[0];
                    } else {
                        $releaseDate = $dataMeta->meta_value > 0 ? date('Y', $dataMeta->meta_value) : '';
                    }
                }
            } else {
                $releaseDate = $releaseYear;
            }
        
            if( $dataMeta->meta_key == '_movie_run_time' ) {
                $movieRunTime = $dataMeta->meta_value;
            }

            if( $dataMeta->meta_key == '_movie_original_title' ) {
                $originalTitle = $dataMeta->meta_value;
            }
            //show casts of movie
            if( $dataMeta->meta_key == '_cast' ) {
                $serializeCasts = $dataMeta->meta_value;
                $unserializeCasts = unserialize($serializeCasts);
                if($unserializeCasts != '' && count($unserializeCasts) > 0) {
                    $casts = $unserializeCasts;
                    $casts = array_values(array_unique($casts, SORT_REGULAR));
                    //get data of person
                    $idCasts = array_column($casts, 'id');
                    $idCasts = join(",", $idCasts);
                    $queryCasts = "SELECT DISTINCT p.ID as id, p.post_name as slug, p.post_title as name, wp.meta_value as src FROM wp_posts p
                    LEFT JOIN wp_postmeta wp ON wp.post_id = p.ID AND wp.meta_key = '_person_image_custom'
                    WHERE p.ID in ( " . $idCasts .  " ) and p.post_status = 'publish' ORDER BY p.post_date LIMIT 5;";
                    $casts = DB::select($queryCasts);
                }
            }
        }
        $srcSet = $this->helperService->getAttachmentsByPostId($dataMovie->ID);

        $movies = [
            'id' => $dataMovie->ID,
            'year' => $releaseDate,
            'genres' => $genres,
            'title' => $dataMovie->post_title,
            'originalTitle' => $originalTitle,
            'description' => $dataMovie->post_content,
            'src' => $src,
            'srcSet' => $srcSet,
            'duration' => $movieRunTime,
            'outlink' => $outlink,
            'relateds' => $dataRelateds,
            'casts' => $casts
        ];
        return response()->json($movies, Response::HTTP_OK);
    }
}
