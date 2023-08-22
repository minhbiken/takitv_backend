<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
class TvshowController extends Controller
{
    private $imageUrlUpload;
    public function __construct()
    {
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        ini_set('display_errors', 1);
        ini_set('memory_limit', '-1');
        $page = $request->get('page', 1);
        $perPage = $request->get('limit', env('PAGE_LIMIT'));
        $orderBy = $request->get('orderBy', '');
        $title = $request->get('title', '');
        $type = $request->get('type', '');

        $select = "SELECT * FROM wp_posts p ";
        $where = " WHERE  ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) ";

        if( $title != '' ) {
            $whereTitle = " AND ( p.original_title LIKE '%". $title ."%' OR p.post_title LIKE '%". $title ."%' ) ";
            $where = $where . $whereTitle;
        }

        if($type != '') {
            $idType = "SELECT wr.object_id
                            FROM wp_terms t
                            LEFT JOIN wp_term_taxonomy wt ON t.term_id = wt.term_id
                            LEFT JOIN wp_term_relationships wr ON wr.term_taxonomy_id = wt.term_taxonomy_id
                            WHERE slug = '". $type ."'";
            $whereType = " AND p.ID IN ( ". $idType ." ) ";
            $where = $where . $whereType;
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
            $selectRating = "LEFT JOIN wp_most_popular mp ON mp.post_id = p.ID ";
            $select = $select . $selectRating;
            $order = "ORDER BY mp.all_time_stats DESC ";
        } else if($orderBy == 'menuOrder') {
            $order = "ORDER BY p.menu_order DESC ";
        } else {
            $order = "ORDER BY p.post_date DESC ";
        }

        //query all tvshow
        $query = $select . $where . $order;

        $selectTotal = "SELECT COUNT(1) as total FROM wp_posts p ";
        $whereTotal = " WHERE  p.comment_count = 0 AND ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) ";

        if($type != '') {
            $whereTotal = $whereTotal . $whereType;
        }
        
        $queryTotal = $selectTotal . $where;
        $dataTotal = DB::select($queryTotal);
        $total = $dataTotal[0]->total;

        //query limit tvshow
        $limit = " LIMIT " . ( ( $page - 1 ) * $perPage ) . ", $perPage ;";
        $query = $query . $limit;
        $datas = DB::select($query);

        $movies = [];
        $populars = [];
        foreach( $datas as $key => $data ) {
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
            
            $src = $this->imageUrlUpload.$dataSrcMeta[0]->meta_value;

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

            //outlink only show in into
            if(count($datas) == 1) {
                $outlink = env('OUTLINK');
                $outlink = @file_get_contents($outlink);

                if( $outlink == NULL ) $outlink = env('DEFAULT_OUTLINK');

                $outlink =  $outlink . '?pid=' . $episodeId;
            } else {
                $outlink = '';
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

            $movies[$key] = [
                'id' => $data->ID,
                'year' => $releaseDate,
                'genres' => $genres,
                'title' => $data->post_title,
                'originalTitle' => $data->original_title,
                'description' => $data->post_content,
                'src' => $src,
                'outlink' => $outlink,
                'chanelImage' => $chanel,
                'seasonNumber' => $seasonNumber + 1,
                'episodeNumber' => $episodeNumber,
                'postDateGmt' => $data->post_date_gmt
            ];

            if(count($datas) == 1) {
                $movies[$key]['relateds'] = [
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
                        'link' => 'movie/%eb%b9%84%eb%8b%90%ed%95%98%ec%9a%b0%ec%8a%a4/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'movie-genre/wmovie/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '노 하드 필링',
                        'link' => 'movie/%eb%85%b8-%ed%95%98%eb%93%9c-%ed%95%84%eb%a7%81/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/gD72DhJ7NbfxvtxGiAzLaa0xaoj.jpg'
                    ],
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => 'TV 영화',
                                'link' => 'movie-genre/tv-%ec%98%81%ed%99%94/'
                            ],
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'https://web.takitv.net/movie-genre/wmovie/'
                            ]
                        ],
                        'title' => '완벽한 커플',
                        'link' => 'movie/%ec%99%84%eb%b2%bd%ed%95%9c-%ec%bb%a4%ed%94%8c/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h4jGfhWaR4KaCjjrzUQqvsHS5wb-300x450.jpg'
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
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg'
                    ],
                    [
                        'year' => '2022',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ]
                        ],
                        'title' => '톱 오브 더 월드',
                        'link' => 'movie/%ed%86%b1-%ec%98%a4%eb%b8%8c-%eb%8d%94-%ec%9b%94%eb%93%9c/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/4aibIe4IdGQvO142HyvB7rIoAut-300x450.jpg'
                    ],
                    [
                        'year' => '2020',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ]
                        ],
                        'title' => '적인걸: 음양미인도',
                        'link' => 'movie/%ec%a0%81%ec%9d%b8%ea%b1%b8-%ec%9d%8c%ec%96%91%eb%af%b8%ec%9d%b8%eb%8f%84/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg'
                    ],
                    [
                        'year' => '2022',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ]
                        ],
                        'title' => '톱 오브 더 월드',
                        'link' => 'movie/%ed%86%b1-%ec%98%a4%eb%b8%8c-%eb%8d%94-%ec%9b%94%eb%93%9c/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/4aibIe4IdGQvO142HyvB7rIoAut-300x450.jpg'
                    ],
                    [
                        'year' => '2020',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ]
                        ],
                        'title' => '적인걸: 음양미인도',
                        'link' => 'movie/%ec%a0%81%ec%9d%b8%ea%b1%b8-%ec%9d%8c%ec%96%91%eb%af%b8%ec%9d%b8%eb%8f%84/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg'
                    ],
                ];
            }

            if( $key <= 4) {
                $populars[$key] = [
                    'id' => $data->ID,
                    'year' => $releaseDate,
                    'genres' => $genres,
                    'title' => $data->post_title,
                    'originalTitle' => $data->original_title,
                    'description' => $data->post_content,
                    'src' => $src,
                    'outlink' => $outlink,
                    'chanelImage' => $chanel,
                    'seasonNumber' => $seasonNumber,
                    'episodeNumber' => $episodeNumber,
                    'postDateGmt' => $data->post_date_gmt
                ];
            }
        }

        $data = [
            "total" => $total,
            "perPage" => $perPage,
            "currentPage" => $page,
            "data" => [
                'top5' => [
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
                        'link' => 'movie/%eb%b9%84%eb%8b%90%ed%95%98%ec%9a%b0%ec%8a%a4/'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'movie-genre/wmovie/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '노 하드 필링',
                        'link' => 'movie/%eb%85%b8-%ed%95%98%eb%93%9c-%ed%95%84%eb%a7%81/'
                    ],
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => 'TV 영화',
                                'link' => 'movie-genre/tv-%ec%98%81%ed%99%94/'
                            ],
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'https://web.takitv.net/movie-genre/wmovie/'
                            ]
                        ],
                        'title' => '완벽한 커플',
                        'link' => 'movie/%ec%99%84%eb%b2%bd%ed%95%9c-%ec%bb%a4%ed%94%8c/'
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
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg'
                    ],
                    [
                        'year' => '2022',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ]
                        ],
                        'title' => '톱 오브 더 월드',
                        'link' => 'movie/%ed%86%b1-%ec%98%a4%eb%b8%8c-%eb%8d%94-%ec%9b%94%eb%93%9c/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/4aibIe4IdGQvO142HyvB7rIoAut-300x450.jpg'
                    ],
                ],
                'populars' => $populars,
                'items' => $movies
            ]
        ];
        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $title
     * @return \Illuminate\Http\Response
     */
    public function show($title, Request $request)
    {
        $seasonPosition = $request->get('seasonPosition', 0);

        $select = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM wp_posts p ";
        $where = " WHERE  ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) ";
        $whereTitle = " AND p.post_title='". $title ."' ";

        $where = $where . $whereTitle;
        
        $dataPost = DB::select($select . $where);

        $query = "SELECT * FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id  IN (". $dataPost[0]->ID . ");";
        $dataTV = DB::select($query);
        
        $dataSeason = $dataPost[0];
       
        $movies = [];

        if (count($dataPost) == 0) {
            return response()->json($movies, Response::HTTP_NOT_FOUND);
        }

        //Get seasons
        $seasons = [];
        $episodes = [];
        
        $queryEpisode = "SELECT * FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $dataSeason->ID . " LIMIT 1;";
        $dataEpisode = DB::select($queryEpisode);
        
        $episodeData = $dataEpisode[0]->meta_value;
        $episodeData = unserialize($episodeData);

        foreach ( $episodeData[0]['episodes'] as $episo ) {
            $queryEpiso = "SELECT p.ID, p.post_title, p.post_date_gmt FROM wp_posts p WHERE ((p.post_type = 'episode' AND (p.post_status = 'publish'))) AND p.ID = ". $episo ." LIMIT 1;";
            $dataEpiso = DB::select($queryEpiso);
            $episodes[] = [
                'title' => count($dataEpiso) > 0 ? $dataEpiso[0]->post_title : '',
                'post_date_gmt' => count($dataEpiso) > 0 ? $dataEpiso[0]->post_date_gmt : '',
            ];
        }
        $seasons[] = [
            'name' => $episodeData[0]['name'],
            'year' => $episodeData[0]['year'],
            'number' => count($episodeData[0]['episodes']),
            'episodes' => $episodes
        ];

        $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $dataSeason->ID .";";
        $dataSrcMeta = DB::select($querySrcMeta);
        $src = $this->imageUrlUpload.$dataSrcMeta[0]->meta_value;
     
        $episodeId = end($episodeData[0]['episodes']);

        //outlink only show in into
        $outlink = env('OUTLINK');
        $outlink = @file_get_contents($outlink);

        if( $outlink == NULL ) $outlink = env('DEFAULT_OUTLINK');
        $outlink =  $outlink . '?pid=' . $episodeId;

        $movies[] = [
            'id' => $dataSeason->ID,
            'title' => $dataSeason->post_title,
            'originalTitle' => $dataSeason->original_title,
            'description' => $dataSeason->post_content,
            'src' => $src,
            'outlink' => $outlink,
            'postDateGmt' => $dataSeason->post_date_gmt,
            'seasons' => $seasons
        ];

        return response()->json($movies, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
