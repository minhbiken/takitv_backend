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

        if ( $page == 1 &&  ( $orderBy == '' || $orderBy == 'nameDesc' ) && Cache::has('person_first') ) {
            $data = Cache::get('person_first');
        } else if ( $page == 1 && $orderBy == 'nameAsc' && Cache::has('person_asc') ) {
            $data = Cache::get('person_asc');
        } else if ( Cache::has('person_' . $orderBy . '_' . $page) ) {
            $data = Cache::get('person_' . $orderBy . '_' . $page);
        } else {
            $select = "SELECT p.ID as id, p.post_name as slug, p.post_title as name, wp.meta_value as src FROM wp_posts p LEFT JOIN wp_postmeta wp ON wp.post_id = p.ID AND wp.meta_key = '_person_image_custom' ";
            $where = " WHERE p.post_status = 'publish' AND p.post_type='person' ";
    
            if( $orderBy == '' ) {
                $order = "ORDER BY p.post_title DESC ";
            } else if( $orderBy == 'nameAsc' ) {
                $order = "ORDER BY p.post_title ASC ";
            } else if( $orderBy == 'nameDesc' ) {
                $order = "ORDER BY p.post_title DESC ";
            } else {
                $order = "ORDER BY p.post_title DESC ";
            }
    
            //query all
            $query = $select . $where . $order;
    
            $selectTotal = "SELECT COUNT(p.ID) as total FROM wp_posts p ";
            $queryTotal = $selectTotal . $where;
    
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
    
            $items = DB::select($query);
            $topWeeks = $this->topWeek();
            $data = [
                "total" => $total,
                "perPage" => $perPage,
                "data" => [
                    'topWeeks' => $topWeeks,
                    'items' => $items
                ]
            ];

            if( $page == 1 &&  ( $orderBy == '' || $orderBy == 'nameDesc' ) ) {
                Cache::forever('person_first', $data);
            } else if( $page == 1 && $orderBy == 'nameAsc' ) {
                Cache::forever('person_asc', $data);
            } else {
                Cache::forever('person_' . $orderBy . '_' . $page , $data);
            }
        }
        
        return response()->json($data, Response::HTTP_OK);
    }

    public function show(Request $request) 
    {
        $slug = $request->get('slug', '');
        $data = [];
        $queryCast = "SELECT p.ID as id, p.post_name as slug, p.post_title as name, wp.meta_value as src, wp_tv_show.meta_value as tv_show, wp_movie.meta_value as movie
        FROM wp_posts p 
        LEFT JOIN wp_postmeta wp ON wp.post_id = p.ID AND wp.meta_key = '_person_image_custom' 
        LEFT JOIN wp_postmeta wp_tv_show ON wp_tv_show.post_id = p.ID AND wp_tv_show.meta_key = '_tv_show_cast'
        LEFT JOIN wp_postmeta wp_movie ON wp_movie.post_id = p.ID AND wp_movie.meta_key = '_movie_cast'
        WHERE p.post_name= '" . $slug .  "'  ";
        $dataCast = DB::select($queryCast);
        if( count($dataCast) > 0 ) {
            $data = $dataCast[0];
            $data->tv_show = unserialize($data->tv_show);
            $tvShowData = [];
            if( $data->tv_show != '' && count($data->tv_show) > 0 ) {
                foreach( $data->tv_show as  $tvShowId) {
                    $select = "SELECT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt, p.post_date, p.post_modified 
                    FROM wp_posts p 
                    WHERE  ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) AND p.ID=". $tvShowId;
                    $tvShowData = $this->tvshowService->getItems($select);
                }
            }
            $data->tv_show = $tvShowData;
            
            $data->movie = unserialize($data->movie);
            $movie = [];
            if( $data->movie != '' && count($data->movie) > 0 ) {
                foreach( $data->movie as  $movieId) {
                    $select = "SELECT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt, p.post_date, p.post_modified 
                    FROM wp_posts p 
                    WHERE  ((p.post_type = 'movie' AND (p.post_status = 'publish'))) AND p.ID=". $movieId;
                    $movie = $this->movieService->getItems($select);
                }
            }
            $data->movie = $movie;
        } else {
            $data = [];
        }
        return response()->json($data, Response::HTTP_OK);
    }

    public function topWeek() {
        $queryTopWeek = "SELECT p.ID, p.post_name, p.post_title, p.original_title, p.post_content, p.post_type, p.post_date_gmt FROM `wp_most_popular` mp
                            LEFT JOIN wp_posts p ON p.ID = mp.post_id
                            WHERE p.post_title != '' AND mp.post_id != '' AND p.ID != '' AND p.post_status = 'publish'
                            ORDER BY mp.7_day_stats DESC
                            LIMIT 5";
        return $this->getItems($queryTopWeek);
    }

    public function getItems($query) {
        $sliders = [];
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
                
                $select = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM wp_posts p ";
                $where = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
                $whereTitle = " AND p.ID='". $episodeId ."' ";
    
                $where = $where . $whereTitle;
                $query = $select . $where;
                $dataEpisoSlider = DB::select($query);
                
                if( count($dataEpisoSlider) > 0 ) {
                    $linkSlider = 'episode/' . $dataEpisoSlider[0]->post_title;
                }
                $titleSlider = $dataEpisoSlider[0]->post_title;

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
                        'link' =>  $dataTaxonomy->slug
                    ];
                }
            }
            $sliders[] = [
                'id' => $sliderData->ID,
                'year' => $year,
                'genres' => $genres,
                'title' => $titleSlider,
                'link' => $linkSlider,
                'postType' => $sliderData->post_type,
            ];
        }
        return $sliders;
    }
}
