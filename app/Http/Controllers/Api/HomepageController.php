<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
class HomepageController extends Controller
{
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
        
        foreach ( $dataMovies as $dataMovie ) {
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

        $data = [
            'menus' => [
                [
                    'item' => '8092',
                    'title'=> '영화',
                    'link' => 'movie'
                ],
                [
                    'item' => '161947',
                    'title'=> 'TV',
                    'link' => 'tv-shows',
                    'sub_menu' => [
                        [
                            'item' => '8093',
                            'title'=> '드라마',
                            'link' => 'k-drama'
                        ],
                        [
                            'item' => '8094',
                            'title'=> '예능',
                            'link' => 'k-show'
                        ],
                        [
                            'item' => '8095',
                            'title'=> '시사',
                            'link' => 'k-sisa'
                        ]
                    ]
                ],
                [
                    'item' => '118282',
                    'title'=> '미드',
                    'link' => 'u-drama'
                ],
                [
                    'item' => '8098',
                    'title'=> 'OTT',
                    'link' => 'ott-web'
                ]
            ],
            'sliders' => $sliders,
            'otts' => [
                'ott_chanels' => [
                    [
                        'link' => 'ott-web/netflix/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/netflix.png'
                    ],
                    [
                        'link' => 'ott-web/disney/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/disney_plus.png'
                    ],
                    [
                        'link' => 'ott-web/apple-tv/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/appletv.png'
                    ],
                ],
                'ott_title' => '오늘의 미국 넷플릭스 순위',
                'ott_sliders' => $sliderRandoms
            ],
            'tv-shows' => [
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
                'title' => '최신등록 방송',
                'items' => $tvshows
            ],
            'movies' => [
                'title' => '최신등록영화',
                'items' => $movies
            ],
            'movies-carousel' => [
                [
                    'year' => '2023',
                    'genres' => [
                        [
                            'name' => '동양영화',
                            'link' => 'movie-genre/amovie/'
                        ],
                        [
                            'name' => '로맨스',
                            'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                        ],
                    ],
                    'title' => '유 & 미 & 미',
                    'origin_title' => 'เธอกับฉันกับฉัน',
                    'link' => 'movie/%ec%9c%a0-%eb%af%b8-%eb%af%b8-2/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0-150x225.jpg 150w'
                ],
                [
                    'year' => '2023',
                    'genres' => [
                        [
                            'name' => '공포',
                            'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                        ],
                        [
                            'name' => '동양영화',
                            'link' => 'movie-genre/amovie/'
                        ],
                        [
                            'name' => '미스터리',
                            'link' => 'movie-genre/%eb%af%b8%ec%8a%a4%ed%84%b0%eb%a6%ac/'
                        ],
                        [
                            'name' => '스릴러',
                            'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                        ]
                    ],
                    'title' => '홈 포 렌트',
                    'origin_title' => 'บ้านเช่า..บูชายัญ',
                    'link' => 'movie/%ed%99%88-%ed%8f%ac-%eb%a0%8c%ed%8a%b8/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru.jpg 600w'
                ],
                [
                    'year' => '2020',
                    'genres' => [
                        [
                            'name' => '동양영화',
                            'link' => 'movie-genre/amovie/'
                        ],
                    ],
                    'title' => '阴阳美人棺',
                    'origin_title' => '阴阳美人棺',
                    'link' => 'movie/%ec%a0%81%ec%9d%b8%ea%b1%b8-%ec%9d%8c%ec%96%91%eb%af%b8%ec%9d%b8%eb%8f%84/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8.jpg 600w'
                ],
                [
                    'year' => '2019',
                    'genres' => [
                        [
                            'name' => '공포',
                            'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                        ],
                        [
                            'name' => '동양영화',
                            'link' => 'movie-genre/amovie/'
                        ],
                    ],
                    'title' => '피를 빠는 인형 파생',
                    'origin_title' => '血を吸う粘土 派生',
                    'link' => 'movie/%ed%94%bc%eb%a5%bc-%eb%b9%a0%eb%8a%94-%ec%9d%b8%ed%98%95-%ed%8c%8c%ec%83%9d/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr.jpg 600w'
                ],
                [
                    'year' => '2020',
                    'genres' => [
                        [
                            'name' => '동양영화',
                            'link' => 'movie-genre/amovie/'
                        ],
                        [
                            'name' => '코미디',
                            'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                        ],
                    ],
                    'title' => '비르 다스: 인도로 인도할게',
                    'origin_title' => 'Vir Das: For India',
                    'link' => 'movie/%eb%b9%84%eb%a5%b4-%eb%8b%a4%ec%8a%a4-%ec%9d%b8%eb%8f%84%eb%a1%9c-%ec%9d%b8%eb%8f%84%ed%95%a0%ea%b2%8c/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs.jpg 600w'
                ],
                [
                    'year' => '2007',
                    'genres' => [
                        [
                            'name' => '로맨스',
                            'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                        ],
                        [
                            'name' => '코미디',
                            'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                        ],
                    ],
                    'title' => '색즉시공 시즌 2',
                    'origin_title' => 'Sex Is Zero 2',
                    'link' => 'movie/%ec%83%89%ec%a6%89%ec%8b%9c%ea%b3%b5-%ec%8b%9c%ec%a6%8c-2/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl.jpg 600w'
                ],
                [
                    'year' => '2023',
                    'genres' => [
                        [
                            'name' => '드라마',
                            'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                        ],
                        [
                            'name' => '스릴러',
                            'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                        ],
                        [
                            'name' => '한국영화',
                            'link' => 'movie-genre/kmovie/'
                        ],
                    ],
                    'title' => '비닐하우스',
                    'origin_title' => 'Greenhouse',
                    'link' => 'movie/%eb%b9%84%eb%8b%90%ed%95%98%ec%9a%b0%ec%8a%a4/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf.jpg 600w'
                ],
                [
                    'year' => '2019',
                    'genres' => [
                        [
                            'name' => '다큐멘터리',
                            'link' => 'movie-genre/%eb%8b%a4%ed%81%90%eb%a9%98%ed%84%b0%eb%a6%ac/'
                        ],
                        [
                            'name' => '서양영화',
                            'link' => 'movie-genre/wmovie/'
                        ]
                    ],
                    'title' => '비닐하우스',
                    'origin_title' => 'Brené Brown: The Call to Courage',
                    'link' => 'movie/%eb%b8%8c%eb%a0%88%eb%84%a4-%eb%b8%8c%eb%9d%bc%ec%9a%b4-%eb%82%98%eb%a5%bc-%eb%b0%94%ea%be%b8%eb%8a%94-%ec%9a%a9%ea%b8%b0/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH.jpg 600w'
                ]
            ],
            'movies-list' => [
                'top_5' => [
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => '다큐멘터리',
                                'link' => 'movie-genre/%eb%8b%a4%ed%81%90%eb%a9%98%ed%84%b0%eb%a6%ac/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'movie-genre/wmovie/'
                            ]
                        ],
                        'title' => '비닐하우스'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '스릴러',
                                'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                            ],
                            [
                                'name' => '한국영화',
                                'link' => 'movie-genre/kmovie/'
                            ],
                        ],
                        'title' => '비닐하우스'
                    ],
                    [
                        'year' => '2007',
                        'genres' => [
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '색즉시공 시즌 2'
                    ],
                    [
                        'year' => '2020',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '비르 다스: 인도로 인도할게'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '공포',
                                'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                            ],
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '미스터리',
                                'link' => 'movie-genre/%eb%af%b8%ec%8a%a4%ed%84%b0%eb%a6%ac/'
                            ],
                            [
                                'name' => '스릴러',
                                'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                            ]
                        ],
                        'title' => '홈 포 렌트'
                    ],
                ],
                'movies_new' => [
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                        ],
                        'title' => '유 & 미 & 미',
                        'link' => 'movie/%ec%9c%a0-%eb%af%b8-%eb%af%b8-2/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0-150x225.jpg 150w'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '공포',
                                'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                            ],
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '미스터리',
                                'link' => 'movie-genre/%eb%af%b8%ec%8a%a4%ed%84%b0%eb%a6%ac/'
                            ],
                            [
                                'name' => '스릴러',
                                'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                            ]
                        ],
                        'title' => '홈 포 렌트',
                        'link' => 'movie/%ed%99%88-%ed%8f%ac-%eb%a0%8c%ed%8a%b8/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru.jpg 600w'
                    ],
                    [
                        'year' => '2020',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                        ],
                        'title' => '阴阳美人棺',
                        'link' => 'movie/%ec%a0%81%ec%9d%b8%ea%b1%b8-%ec%9d%8c%ec%96%91%eb%af%b8%ec%9d%b8%eb%8f%84/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8.jpg 600w'
                    ],
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => '공포',
                                'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                            ],
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                        ],
                        'title' => '피를 빠는 인형 파생',
                        'link' => 'movie/%ed%94%bc%eb%a5%bc-%eb%b9%a0%eb%8a%94-%ec%9d%b8%ed%98%95-%ed%8c%8c%ec%83%9d/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr.jpg 600w'
                    ],
                    [
                        'year' => '2020',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '비르 다스: 인도로 인도할게',
                        'link' => 'movie/%eb%b9%84%eb%a5%b4-%eb%8b%a4%ec%8a%a4-%ec%9d%b8%eb%8f%84%eb%a1%9c-%ec%9d%b8%eb%8f%84%ed%95%a0%ea%b2%8c/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs.jpg 600w'
                    ],
                    [
                        'year' => '2007',
                        'genres' => [
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '색즉시공 시즌 2',
                        'link' => 'movie/%ec%83%89%ec%a6%89%ec%8b%9c%ea%b3%b5-%ec%8b%9c%ec%a6%8c-2/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl.jpg 600w'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '스릴러',
                                'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                            ],
                            [
                                'name' => '한국영화',
                                'link' => 'movie-genre/kmovie/'
                            ],
                        ],
                        'title' => '비닐하우스',
                        'link' => 'movie/%eb%b9%84%eb%8b%90%ed%95%98%ec%9a%b0%ec%8a%a4/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf.jpg 600w'
                    ],
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => '다큐멘터리',
                                'link' => 'movie-genre/%eb%8b%a4%ed%81%90%eb%a9%98%ed%84%b0%eb%a6%ac/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'movie-genre/wmovie/'
                            ]
                        ],
                        'title' => '비닐하우스',
                        'link' => 'movie/%eb%b8%8c%eb%a0%88%eb%84%a4-%eb%b8%8c%eb%9d%bc%ec%9a%b4-%eb%82%98%eb%a5%bc-%eb%b0%94%ea%be%b8%eb%8a%94-%ec%9a%a9%ea%b8%b0/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH.jpg 600w'
                    ]
                ]
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