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
        $perPage = $request->get('limit', 24);

        $imageUrlUpload = env('IMAGE_URL_UPLOAD');

        $query = "SELECT * FROM wp_posts WHERE  wp_posts.comment_count = 0 AND ((wp_posts.post_type = 'movie' AND (wp_posts.post_status = 'publish'))) ORDER BY wp_posts.post_date DESC
                LIMIT " . ( ( $page - 1 ) * $perPage ) . ", $perPage";
        $datas = DB::select($query);
        
        $movies = [];
        foreach( $datas as $data ) {
            $queryMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $data->ID .";";
            $dataMeta = DB::select($queryMeta);

            $queryReleaseDate = "SELECT * FROM wp_postmeta where meta_key = '_movie_release_date' and post_id =". $data->ID .";";
            $dataReleaseDate = DB::select($queryReleaseDate);
            $releaseDate = $dataReleaseDate[0]->meta_value;
            $releaseDate = date('Y', $releaseDate);

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
                "year" => $releaseDate,
                'genres' => $genres,
                'title' => $data->post_title,
                "original_title" => $data->original_title,
                'link' => $link,
                'src' => $imageUrlUpload.$dataMeta[0]->meta_value
            ];
        }
        
        $data = [
            "total" => count($movies),
            "per_page" => $perPage,
            "current_page" => $page,
            "last_page" => 4,
            "first_page_url" => "",
            "last_page_url" => "",
            "next_page_url" => "",
            "prev_page_url" => null,
            "path" => "",
            "from" => ( $page - 1 ) * $perPage,
            "to" => $perPage,
            "data" => [
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
                'movies_popular' => [
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
