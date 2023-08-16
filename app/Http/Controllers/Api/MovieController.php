<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('limit', 30);
        $releaseYear = $request->get('year', '');
        $genre = $request->get('genre', '');
        $orderBy = $request->get('orderBy', '');
        $title = $request->get('title', '');

        $imageUrlUpload = env('IMAGE_URL_UPLOAD');

        $select = "SELECT * FROM wp_posts p ";
        $where = " WHERE  p.comment_count = 0 AND ((p.post_type = 'movie' AND (p.post_status = 'publish'))) ";

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
                WHERE t.name IN (". $genre .")";
            $where = $where . "AND p.ID IN ( ". $queryGenre ." ) ";    
        }

        if( $title != '' ) {
            $whereTitle = " AND ( p.original_title LIKE '%". $title ."%' OR p.post_title LIKE '%". $title ."%' ) ";
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
        
        $queryTotal = $query;
        $dataTotal = DB::select($queryTotal);
        $total = count($dataTotal);

        //query limit movie
        $limit = "LIMIT " . ( ( $page - 1 ) * $perPage ) . ", $perPage ;";
        $query = $query . $limit;
        
        $datas = DB::select($query);

        $movies = [];
        foreach( $datas as $data ) {
            $queryMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $data->ID .";";
            $dataMeta = DB::select($queryMeta);

            if( $releaseYear == '' ) {
                $queryReleaseDate = "SELECT * FROM wp_postmeta where meta_key = '_movie_release_date' and post_id =". $data->ID .";";
                
                $dataReleaseDate = DB::select($queryReleaseDate);
                if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataReleaseDate[0]->meta_value)) {
                    $newDataReleaseDate = explode('-', $dataReleaseDate[0]->meta_value);
                    $releaseDate = $newDataReleaseDate[0];
                } else {
                    $releaseDate = $dataReleaseDate[0]->meta_value > 0 ? date('Y', $dataReleaseDate[0]->meta_value) : '2023';
                }
            } else {
                $releaseDate = $releaseYear;
            }
            
            $queryTaxonomy = "SELECT * FROM `wp_posts` p
                                left join wp_term_relationships t_r on t_r.object_id = p.ID
                                left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id
                                left join wp_terms t on tx.term_id = t.term_id
                                where p.ID = ". $data->ID .";";

            $dataTaxonomy = DB::select($queryTaxonomy);

            $genres = [];
            foreach( $dataTaxonomy as $data ) {
                $genres[] = [
                    'name' => $data->name,
                    'link' =>  $data->taxonomy . '/' .  $data->slug
                ];
            }

            $link = $data->post_type == 'movie' ? 'movie/'.$data->post_name."/" : 'tv-show/'.$data->post_name."/";
            
            $movies[] = [
                'pid' => $data->ID,
                'year' => $releaseDate,
                'genres' => $genres,
                'title' => $data->post_title,
                'originalTitle' => $data->original_title,
                'description' => $data->post_content,
                'link' => $link,
                'src' => $imageUrlUpload.$dataMeta[0]->meta_value
            ];
        }

        $data = [
            "total" => $total,
            "perPage" => $perPage,
            "currentPage" => $page,
            "lastPage" => 4,
            "from" => ( $page - 1 ) * $perPage,
            "to" => $perPage,
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
                'populars' => [
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
                ],
                'movies' => $movies
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
    public function show($title)
    {
        $imageUrlUpload = env('IMAGE_URL_UPLOAD');
       
        $select = "SELECT * FROM wp_posts p ";
        $where = " WHERE  p.comment_count = 0 AND ((p.post_type = 'movie' AND (p.post_status = 'publish'))) AND p.post_title ='" . $title . "'";
    
        $query = $select . $where;
        
        $data = DB::select($query);

        if( isset($data[0]) ) {
            $data = $data[0];
        } else {
            return abort(404);
        }

        $queryMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
        LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $data->ID .";";
        $dataMeta = DB::select($queryMeta);

        $queryReleaseDate = "SELECT * FROM wp_postmeta where meta_key = '_movie_release_date' and post_id =". $data->ID .";";  
        $dataReleaseDate = DB::select($queryReleaseDate);
        if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataReleaseDate[0]->meta_value)) {
            $newDataReleaseDate = explode('-', $dataReleaseDate[0]->meta_value);
            $releaseDate = $newDataReleaseDate[0];
        } else {
            $releaseDate = $dataReleaseDate[0]->meta_value > 0 ? date('Y', $dataReleaseDate[0]->meta_value) : '2023';
        }

        $queryTaxonomy = "SELECT * FROM `wp_posts` p
                                left join wp_term_relationships t_r on t_r.object_id = p.ID
                                left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id
                                left join wp_terms t on tx.term_id = t.term_id
                                where p.ID = ". $data->ID .";";
        $dataTaxonomy = DB::select($queryTaxonomy);

        $genres = [];
        foreach( $dataTaxonomy as $data ) {
            $genres[] = [
                'name' => $data->name,
                'link' =>  $data->taxonomy . '/' .  $data->slug
            ];
        }

        $link = $data->post_type == 'movie' ? 'movie/'.$data->post_name."/" : 'tv-show/'.$data->post_name."/";

        $movie = [
            'pid' => $data->ID,
            'year' => $releaseDate,
            'genres' => $genres,
            'title' => $data->post_title,
            'originalTitle' => $data->original_title,
            'description' => $data->post_content,
            'link' => $link,
            'src' => $imageUrlUpload.$dataMeta[0]->meta_value,
            'relateds' => [
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
            ]
        ];
        return response()->json($movie, Response::HTTP_OK);
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
