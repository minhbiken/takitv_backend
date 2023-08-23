<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\MovieService;
class HomepageController extends Controller
{

    protected $movieService;
    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
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
        $sliderData = DB::select($sliderQuery);

        $sliders = [];
        foreach ( $sliderData as $data ) {
            $dataQuery = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $data->ID .";";
            $dataResult = DB::select($dataQuery);

            $link = $data->post_type == 'movie' ? 'movie/'.$data->post_name."/" : 'tv-show/'.$data->post_name."/";

            $sliders[] = [
                'title' => $data->post_title,
                'link' => $link,
                'src' => $imageUrlUpload.$dataResult[0]->meta_value
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

            $linkRandom = $data->post_type == 'movie' ? 'movie/'.$randomData->post_name."/" : 'tv-show/'.$randomData->post_name."/";

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
                $queryEpisode = "SELECT * FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $data->ID . " LIMIT 1;";
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

        //Get 12 tv-shows 

        $categories = [
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
        ];

        //get tvshows of tv-show 
        $tvshows = [];
        $queryTvShow = "SELECT * FROM wp_posts p WHERE  ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) ORDER BY p.post_date DESC LIMIT 12";
        $dataTvShows = DB::select($queryTvShow);
        
        foreach ( $dataTvShows as $data ) {
            $queryEpisode = "SELECT * FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $data->ID . " LIMIT 1;";
            $dataEpisode = DB::select($queryEpisode);
            
            $episodeData = $dataEpisode[0]->meta_value;
            $episodeData = unserialize($episodeData);
            
            $totalEpisodeData = count($episodeData);

            $seasonNumber = $episodeData[$totalEpisodeData - 1]['position'];            

            $episodeId = end($episodeData[$totalEpisodeData - 1]['episodes']);
            $queryMeta = "SELECT * FROM wp_postmeta WHERE post_id = ". $episodeId .";";

            $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $data->ID .";";
            $dataSrcMeta = DB::select($querySrcMeta);
            
            $src = $imageUrlUpload.$dataSrcMeta[0]->meta_value;

            $dataMetas = DB::select($queryMeta);

            foreach($dataMetas as $dataMeta) {
                if( $dataMeta->meta_key == '_episode_release_date' ) {
                    if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMeta->meta_value)) {
                        $newDataReleaseDate = explode('-', $dataMeta->meta_value);
                        $releaseDate = $newDataReleaseDate[0];
                    } else {
                        $releaseDate = $dataMeta->meta_value > 0 ? date('Y', $dataMeta->meta_value) : '2023';
                    }
                }

                if( $dataMeta->meta_key == '_episode_number' ) {
                    $episodeNumber = $dataMeta->meta_value;
                }
            }

            $queryTaxonomy = "SELECT * FROM `wp_posts` p
                        left join wp_term_relationships t_r on t_r.object_id = p.ID
                        left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id
                        left join wp_terms t on tx.term_id = t.term_id
                        where p.ID = ". $data->ID .";";
            $dataTaxonomys = DB::select($queryTaxonomy);

            $genres = [];
            foreach( $dataTaxonomys as $dataTaxonomy ) {
                $genres[] = [
                    'name' => $dataTaxonomy->name,
                    'link' =>  $dataTaxonomy->slug
                ];
            }

            $queryChanel = "SELECT * FROM `wp_term_relationships` wp
                        LEFT JOIN wp_term_taxonomy wt ON wt.term_taxonomy_id = wp.term_taxonomy_id
                        WHERE wt.taxonomy = 'category' AND wt.description != '' AND wp.object_id = ". $data->ID .";";
            $dataChanel = DB::select($queryChanel);
            
            if( count($dataChanel) > 0 ) {
                $chanel = $dataChanel[0]->description;
                $newChanel = explode('src="', $chanel);
                $newChanel = explode('" alt', $newChanel[1]);
                $newChanel = $newChanel[0];
                $chanel = 'https://image002.modooup.com' . $newChanel;
            } else {
                $chanel = env('IMAGE_PLACEHOLDER');
            }

            $tvshows[] = [
                'id' => $data->ID,
                'year' => $releaseDate,
                'genres' => $genres,
                'title' => $data->post_title,
                'originalTitle' => $data->original_title,
                'description' => $data->post_content,
                'src' => $src,
                'chanelImage' => $chanel,
                'seasonNumber' => $seasonNumber + 1,
                'episodeNumber' => $episodeNumber,
                'postDateGmt' => $data->post_date_gmt
            ];
        }

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
        $movieKoreas = [];
        $queryKoreaMovie = "SELECT ID, post_title, original_title, post_name, post_type, post_date , IF(pm1.meta_value IS NOT NULL , CAST( pm1.meta_value AS UNSIGNED ) , 0 ) as sort_order,
                                IF(pm2.meta_value IS NOT NULL , CAST( pm2.meta_value AS UNSIGNED ) , 0 ) as slide_img
                                FROM wp_posts as p
                                INNER JOIN wp_postmeta as pm0 ON p.ID = pm0.post_id AND pm0.meta_key = '_korea_featured' and pm0.meta_value = 1
                                LEFT JOIN wp_postmeta as pm1 ON p.ID = pm1.post_id and pm1.meta_key = '_sort_order_korea'
                                LEFT JOIN wp_postmeta as pm2 ON p.ID = pm2.post_id and pm2.meta_key = '_korea_image_id'
                                WHERE p.post_type = 'movie'
                                ORDER BY sort_order ASC, post_date DESC;";

        $movieDataKoreas = DB::select($queryKoreaMovie);
        foreach ( $movieDataKoreas as $movieDataKorea ) {
            $queryMetaKorea = "SELECT * FROM wp_postmeta WHERE post_id = ". $movieDataKorea->ID .";";
            $dataMetaKorea = DB::select($queryMetaKorea);

            $linkKoreaMovie = 'movie/'.$movieDataKorea->post_name."/";
            $releaseDateKorea = '2023';
            $episodeNumber = '';

            $dataMetaRandoms = DB::select($queryMetaKorea);
            foreach ($dataMetaRandoms as $dataMetaRandom) {
                if ( $dataMetaRandom->meta_key == '_movie_release_date' ) {
                    if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMetaRandom->meta_value)) {
                        $newDataReleaseDateKorea = explode('-', $dataMetaRandom->meta_value);
                        $releaseDateKorea = $newDataReleaseDateKorea[0];
                    } else {
                        $releaseDateKorea = $dataMetaRandom->meta_value > 0 ? date('Y', $dataMetaRandom->meta_value) : '2023';
                    }
                }
                if( $dataMetaRandom->meta_key == '_movie_run_time' ) {
                    $movieRunTime = $dataMetaRandom->meta_value != '' ? $dataMetaRandom->meta_value : '';
                }
            }

            $querySrcMetaKorea = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                                    LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' 
                                    WHERE p.post_status = 'publish' and p.ID =". $movieDataKorea->ID .";";
            $dataSrcMetaKorea = DB::select($querySrcMetaKorea);
            $src = $imageUrlUpload.$dataSrcMetaKorea[0]->meta_value;


            $queryTaxonomyKorea = "SELECT * FROM `wp_posts` p
                        left join wp_term_relationships t_r on t_r.object_id = p.ID
                        left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id
                        left join wp_terms t on tx.term_id = t.term_id
                        where t.name != 'featured' AND p.ID = ". $movieDataKorea->ID .";";

            $dataTaxonomyKoreas = DB::select($queryTaxonomyKorea);

            $genreKoreas = [];
            foreach( $dataTaxonomyKoreas as $dataTaxonomyKorea ) {
                $genreKoreas[] = [
                    'name' => $dataTaxonomyKorea->name,
                    'link' =>  $dataTaxonomyKorea->slug
                ];
            }

            $movieKoreas[] = [
                'year' => $releaseDateKorea,
                'title' => $movieDataKorea->post_title,
                'originalTitle' => $movieDataKorea->original_title,
                'description' => $data->post_content,
                'link' => $linkKoreaMovie,
                'src' => $src,
                'movieRunTime' => $movieRunTime,
                'genres' => $genreKoreas,
            ];
        }
        
        $data = [
            // 'menus' => [
            //     [
            //         'item' => '8092',
            //         'title'=> '영화',
            //         'link' => 'movie'
            //     ],
            //     [
            //         'item' => '161947',
            //         'title'=> 'TV',
            //         'link' => 'tv-shows',
            //         'subMenu' => [
            //             [
            //                 'item' => '8093',
            //                 'title'=> '드라마',
            //                 'link' => 'k-drama'
            //             ],
            //             [
            //                 'item' => '8094',
            //                 'title'=> '예능',
            //                 'link' => 'k-show'
            //             ],
            //             [
            //                 'item' => '8095',
            //                 'title'=> '시사',
            //                 'link' => 'k-sisa'
            //             ]
            //         ]
            //     ],
            //     [
            //         'item' => '118282',
            //         'title'=> '미드',
            //         'link' => 'u-drama'
            //     ],
            //     [
            //         'item' => '8098',
            //         'title'=> 'OTT',
            //         'link' => 'ott-web'
            //     ]
            // ],
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
                'categories' => [
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
                    ],
                ],
                'items' => $tvshows
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