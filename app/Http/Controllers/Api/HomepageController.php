<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\MovieService;
use App\Services\TvshowService;
class HomepageController extends Controller
{

    protected $movieService;
    protected $tvshowService;
    public function __construct(MovieService $movieService, TvshowService $tvshowService)
    {
        $this->movieService = $movieService;
        $this->tvshowService = $tvshowService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $imageUrlUpload = env('IMAGE_URL_UPLOAD');

        //Get header slider
        $sliderQuery = "SELECT meta_key, ID, post_title, post_name, post_type, post_date, meta_value, IF(pm.meta_value IS NOT NULL , CAST( pm.meta_value AS UNSIGNED ) , 0 ) as sort_order
        FROM wp_posts as p
        LEFT JOIN wp_postmeta as pm ON p.ID = pm.post_id and pm.meta_key= '_sort_order'
        WHERE ID IN ( SELECT object_id FROM `wp_term_relationships` WHERE term_taxonomy_id IN (17 , 43) ) 
            AND p.post_status = 'publish'
        ORDER BY sort_order ASC, post_date DESC;";
        $sliderDatas = DB::select($sliderQuery);
        $sliders = [];
        foreach ( $sliderDatas as $sliderData ) {
            $dataQuery = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $sliderData->ID .";";
            $dataResult = DB::select($dataQuery);

            $titleSlider = $sliderData->post_title; 
            $linkSlider = 'movie/' . $sliderData->post_title."/";
            $seasonNumber = '';
            $episodeNumber = '';
            
            if( $sliderData->post_type == 'tv_show' ) {
                $queryMetaSlider = "SELECT * FROM wp_postmeta WHERE meta_key = '_episode_number' AND post_id = ". $sliderData->ID ." LIMIT 1;";
                
                $dataMetaSlider = DB::select($queryMetaSlider);

                if( count($dataMetaSlider) > 0 ) {
                    $episodeNumber = $dataMetaSlider[0]->meta_value;
                }
        
                $queryEpisode = "SELECT * FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $sliderData->ID . " LIMIT 1;";
                $dataEpisode = DB::select($queryEpisode);
                
                $episodeData = $dataEpisode[0]->meta_value;
                $episodeData = unserialize($episodeData);
    
                $lastSeason = end($episodeData);
                $seasonNumber = $lastSeason['name'];

                $episodeId = end($lastSeason['episodes']);
    
                $select = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM wp_posts p ";
                $where = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
                $whereTitle = " AND p.ID='". $episodeId ."' ";
    
                $where = $where . $whereTitle;
                $query = $select . $where;
                $dataEpisoSlider = DB::select($query);
                
                if( count($dataEpisoSlider) > 0 ) {
                    $linkSlider = 'episode/' . $dataEpisoSlider[0]->post_title . "/";
                }
            }

            $sliders[] = [
                'title' => $titleSlider,
                'link' => $linkSlider,
                'src' => $imageUrlUpload.$dataResult[0]->meta_value,
                'seasonNumber' => $seasonNumber,
                'episodeNumber' => $episodeNumber,
            ];
        }

        //Get Chanel slider random between USA and Korea
        $sliderRandoms = [];
        $queryKoreaSlider = "SELECT ID, post_title, post_name, post_type, post_date , IF(pm1.meta_value IS NOT NULL , CAST( pm1.meta_value AS UNSIGNED ) , 0 ) as sort_order,
                                IF(pm2.meta_value IS NOT NULL , CAST( pm2.meta_value AS UNSIGNED ) , 0 ) as slide_img
                                FROM wp_posts as p
                                INNER JOIN wp_postmeta as pm0 ON p.ID = pm0.post_id AND pm0.meta_key='_korea_featured' and pm0.meta_value=1
                                LEFT JOIN wp_postmeta as pm1 ON p.ID = pm1.post_id and pm1.meta_key= '_sort_order_korea'
                                LEFT JOIN wp_postmeta as pm2 ON p.ID = pm2.post_id and pm2.meta_key= '_korea_image_id'
                                ORDER BY sort_order ASC, post_date DESC;";

        $queryUsaSlider = "SELECT ID, post_title, post_name, post_type, post_date , IF(pm1.meta_value IS NOT NULL , CAST( pm1.meta_value AS UNSIGNED ) , 0 ) as sort_order,
                    IF(pm2.meta_value IS NOT NULL , CAST( pm2.meta_value AS UNSIGNED ) , 0 ) as slide_img
                    FROM wp_posts as p
                    INNER JOIN wp_postmeta as pm0 ON p.ID = pm0.post_id AND pm0.meta_key='_ott_featured' and pm0.meta_value=1
                    LEFT JOIN wp_postmeta as pm1 ON p.ID = pm1.post_id and pm1.meta_key= '_sort_order_ott'
                    LEFT JOIN wp_postmeta as pm2 ON p.ID = pm2.post_id and pm2.meta_key= '_ott_image_id'
                    ORDER BY sort_order ASC, post_date DESC";

        $randomSlider[0] = $queryKoreaSlider;
        $randomSlider[1] = $queryUsaSlider;

        $queryRandom = $randomSlider[rand(0,1)];
        $randomDatas = DB::select($queryRandom);

        foreach ( $randomDatas as $randomData ) {
            $dataRandomQuery = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $randomData->ID .";";
            $dataRandomResult = DB::select($dataRandomQuery);

            $linkRandom = $randomData->post_type == 'movie' ? 'movie/'.$randomData->post_name."/" : 'tv-show/'.$randomData->post_name."/";

            $releaseDate = '2023';

            $queryMetaRandom = "SELECT * FROM wp_postmeta WHERE post_id = ". $randomData->ID .";";
            $dataMetaRandoms = DB::select($queryMetaRandom);

            $episodeNumber = '';
            foreach ($dataMetaRandoms as $dataMetaRandom) {
                if ( $randomData->post_type == 'movie' && $dataMetaRandom->meta_key == '_movie_release_date' ) {
                    if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMetaRandom->meta_value)) {
                        $newDataReleaseDate = explode('-', $dataMetaRandom->meta_value);
                        $releaseDate = $newDataReleaseDate[0];
                    } else {
                        $releaseDate = $dataMetaRandom->meta_value > 0 ? date('Y', $dataMetaRandom->meta_value) : '2023';
                    }
                } else if ( $randomData->post_type == 'tv_show' && $dataMetaRandom->meta_key == '_episode_release_date' ) {
                    if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMetaRandom->meta_value)) {
                        $newDataReleaseDate = explode('-', $dataMetaRandom->meta_value);
                        $releaseDate = $newDataReleaseDate[0];
                    } else {
                        $releaseDate = $dataMetaRandom->meta_value > 0 ? date('Y', $dataMetaRandom->meta_value) : '2023';
                    }

                    if( $dataMetaRandom->meta_key == '_episode_number' ) {
                        $episodeNumber = $dataMetaRandom->meta_value;
                    }
                }
            }

            if( $randomData->post_type == 'tv_show' ) {
                $queryEpisode = "SELECT * FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $randomData->ID . " LIMIT 1;";
                $dataEpisode = DB::select($queryEpisode);
                
                $episodeData = $dataEpisode[0]->meta_value;
                $episodeData = unserialize($episodeData);
                
                $totalEpisodeData = count($episodeData);
    
                $seasonNumber = $episodeData[$totalEpisodeData - 1]['position'];            
            }

            $sliderRandoms[] = [
                'year' => $releaseDate,
                'title' => $randomData->post_title,
                'seasonNumber' => $seasonNumber + 1,
                'episodeNumber' => $episodeNumber,
                'link' => $linkRandom,
                'src' => $imageUrlUpload.$dataRandomResult[0]->meta_value,
            ];
        }


        //get 12 tv-show
        $queryTvshow = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM `wp_posts` p
                            LEFT JOIN wp_term_relationships t_r ON t_r.object_id = p.ID
                            LEFT JOIN wp_term_taxonomy tx ON t_r.term_taxonomy_id = tx.term_taxonomy_id
                            LEFT JOIN wp_terms t ON tx.term_id = t.term_id
                            WHERE ((p.post_type = 'tv_show' AND (p.post_status = 'publish')))
                            ORDER BY p.post_date DESC 
                            LIMIT 12;";
        $dataTvshow = $this->tvshowService->getItems($queryTvshow);

        //get 12 k-drama
        $queryKdrama = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM `wp_posts` p
                            LEFT JOIN wp_term_relationships t_r ON t_r.object_id = p.ID
                            LEFT JOIN wp_term_taxonomy tx ON t_r.term_taxonomy_id = tx.term_taxonomy_id
                            LEFT JOIN wp_terms t ON tx.term_id = t.term_id
                            WHERE ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) AND t.slug = 'k-drama'
                            ORDER BY p.post_date DESC 
                            LIMIT 12;";
        $dataKDramas = $this->tvshowService->getItems($queryKdrama);

        //get 12 k-show 
        $queryKshow = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM `wp_posts` p
                            LEFT JOIN wp_term_relationships t_r ON t_r.object_id = p.ID
                            LEFT JOIN wp_term_taxonomy tx ON t_r.term_taxonomy_id = tx.term_taxonomy_id
                            LEFT JOIN wp_terms t ON tx.term_id = t.term_id
                            WHERE ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) AND t.slug = 'k-show'
                            ORDER BY p.post_date DESC 
                            LIMIT 12;";
        $dataKshows = $this->tvshowService->getItems($queryKshow);

        //get 12 k-sisa
        $queryKsisa = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM `wp_posts` p
                            LEFT JOIN wp_term_relationships t_r ON t_r.object_id = p.ID
                            LEFT JOIN wp_term_taxonomy tx ON t_r.term_taxonomy_id = tx.term_taxonomy_id
                            LEFT JOIN wp_terms t ON tx.term_id = t.term_id
                            WHERE ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) AND t.slug = 'k-sisa'
                            ORDER BY p.post_date DESC 
                            LIMIT 12;";
        $dataKsisa = $this->tvshowService->getItems($queryKsisa);

        //get 12 u-drama
        $queryUdrama = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM `wp_posts` p
                            LEFT JOIN wp_term_relationships t_r ON t_r.object_id = p.ID
                            LEFT JOIN wp_term_taxonomy tx ON t_r.term_taxonomy_id = tx.term_taxonomy_id
                            LEFT JOIN wp_terms t ON tx.term_id = t.term_id
                            WHERE ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) AND t.slug = 'u-drama'
                            ORDER BY p.post_date DESC 
                            LIMIT 12;";
        $dataUdrama = $this->tvshowService->getItems($queryUdrama);

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
                'tv-show' => $dataKshows,
                'k-drama' => $dataKDramas,
                'k-show' => $dataKshows,
                'k-sisa' => $dataKsisa,
                'u-drama' => $dataUdrama
            ]
        ];
        
        //Get 12 movies 
        $movies = [];
        $queryMovie = "SELECT * FROM wp_posts p WHERE  ((p.post_type = 'movie' AND (p.post_status = 'publish'))) ORDER BY p.post_date DESC LIMIT 12";
        $dataMovies = DB::select($queryMovie);

        $movieNewests = [];
        
        foreach ( $dataMovies as $key => $dataMovie ) {
            $queryMetaMovie = "SELECT * FROM wp_postmeta WHERE post_id = ". $dataMovie->ID .";";

            $querySrcMetaMovie = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $dataMovie->ID .";";
            $dataSrcMetaMovie = DB::select($querySrcMetaMovie);

            $srcMovie = $imageUrlUpload.$dataSrcMetaMovie[0]->meta_value;
            
            $releaseDate = '';
            $dataMetaMovies = DB::select($queryMetaMovie);
            foreach($dataMetaMovies as $dataMetaMovie) {
                if( $dataMetaMovie->meta_key == '_movie_release_date' ) {
                    if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMetaMovie->meta_value)) {
                        $newDataReleaseDate = explode('-', $dataMetaMovie->meta_value);
                        $releaseDate = $newDataReleaseDate[0];
                    } else {
                        $releaseDate = $dataMetaMovie->meta_value > 0 ? date('Y', $dataMetaMovie->meta_value) : '2023';
                    }
                }
                
                if( $dataMetaMovie->meta_key == '_movie_run_time' ) {
                    $movieRunTime = $dataMetaMovie->meta_value;
                }
            }
            
            $queryTaxonomyMovie = "SELECT * FROM `wp_posts` p
                        left join wp_term_relationships t_r on t_r.object_id = p.ID
                        left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id
                        left join wp_terms t on tx.term_id = t.term_id
                        where p.ID = ". $dataMovie->ID .";";

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
                    'year' => $releaseDate,
                    'genres' => $genreMovies,
                    'title' => $dataMovie->post_title,
                    'originalTitle' => $dataMovie->original_title,
                    'description' => $dataMovie->post_content,
                    'src' => $srcMovie,
                    'movieRunTime' => $movieRunTime
                ];
            }
            
            $movies[] = [
                'year' => $releaseDate,
                'genres' => $genreMovies,
                'title' => $dataMovie->post_title,
                'originalTitle' => $dataMovie->original_title,
                'description' => $dataMovie->post_content,
                'src' => $srcMovie,
                'movieRunTime' => $movieRunTime
            ];
        }

        $topWeeks = $this->movieService->getTopWeeks();

        //Get movies newest of Korea for slider in bottom
        $queryKoreaMovie = "SELECT ID, post_title, original_title, post_name, post_type, post_date , IF(pm1.meta_value IS NOT NULL , CAST( pm1.meta_value AS UNSIGNED ) , 0 ) as sort_order,
                                IF(pm2.meta_value IS NOT NULL , CAST( pm2.meta_value AS UNSIGNED ) , 0 ) as slide_img
                                FROM wp_posts as p
                                INNER JOIN wp_postmeta as pm0 ON p.ID = pm0.post_id AND pm0.meta_key = '_korea_featured' and pm0.meta_value = 1
                                LEFT JOIN wp_postmeta as pm1 ON p.ID = pm1.post_id and pm1.meta_key = '_sort_order_korea'
                                LEFT JOIN wp_postmeta as pm2 ON p.ID = pm2.post_id and pm2.meta_key = '_korea_image_id'
                                WHERE p.post_type = 'movie'
                                ORDER BY sort_order ASC, post_date DESC;";

        $movieKoreas = $this->movieService->getItems($queryKoreaMovie);
        
        $data = [
            'sliders' => $sliders,
            'otts' => [
                'ottChanels' => [
                    [
                        'link' => 'ott-web/disney/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/disney_plus.png'
                    ],
                    [
                        'link' => 'ott-web/apple-tv/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/appletv.png'
                    ],
                    [
                        'link' => 'ott-web/tving/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/tving.png'
                    ],
                    [
                        'link' => 'ott-web/wavve/',
                        'src' => 'https://peekletv.takitv.net/outlink/wavve.png'
                    ],
                    [
                        'link' => 'ott-web/amazon-prime-video/',
                        'src' => 'https://peekletv.takitv.net/outlink/prime.png'
                    ]
                ],
                'ottTitle' => '오늘의 미국 넷플릭스 순위',
                'ottSliders' => $sliderRandoms
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

        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}