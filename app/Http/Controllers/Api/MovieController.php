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
    protected $lifeTime;
    public function __construct(MovieService $movieService, HelperService $helperService)
    {
        $this->movieService = $movieService;
        $this->helperService = $helperService;
        $this->lifeTime = env('SESSION_LIFETIME');
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
        $releaseYear = $request->get('year', '');
        $genre = $request->get('genre', '');
        $orderBy = $request->get('orderBy', '');
        $title = $request->get('title', '');

        $imageUrlUpload = env('IMAGE_URL_UPLOAD');

        $select = "SELECT p.ID, p.post_title, p.post_content, p.original_title FROM wp_posts p ";
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
        $dataTotal = DB::select($queryTotal);
        $total = $dataTotal[0]->total;

        //query limit movie
        $limit = "LIMIT " . ( ( $page - 1 ) * $perPage ) . ", $perPage ;";
        $query = $query . $limit;

        $datas = DB::select($query);

        $movies = [];
        $src = '';
        $srcSet = [];
        foreach( $datas as $key => $data ) {
            $queryMeta = "SELECT * FROM wp_postmeta WHERE post_id = ". $data->ID .";";

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
                            $releaseDate = $dataMeta->meta_value > 0 ? date('Y', $dataMeta->meta_value) : '2023';
                        }
                    }
                } else {
                    $releaseDate = $releaseYear;
                }
            
                if( $dataMeta->meta_key == '_movie_run_time' ) {
                    $movieRunTime = $dataMeta->meta_value;
                }
            }

            $queryTaxonomy = "SELECT * FROM `wp_posts` p
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

            //outlink only show in into
            if(count($datas) == 1) {
                $outlink = env('OUTLINK');
                $outlink = @file_get_contents($outlink);

                if( $outlink == NULL ) $outlink = env('DEFAULT_OUTLINK');

                $outlink =  $outlink . '?pid=' . $data->ID;
            } else {
                $outlink = '';
            }

            $srcSet = $this->helperService->getAttachmentsByPostId($data->ID);

            $movies[$key] = [
                'id' => $data->ID,
                'year' => $releaseDate,
                'genres' => $genres,
                'title' => $data->post_title,
                'originalTitle' => $data->original_title,
                'description' => $data->post_content,
                'src' => $src,
                'srcSet' => $srcSet,
                'duration' => $movieRunTime,
                'outlink' => $outlink
            ];

            if(count($datas) == 1) {
                //get 8 movies related
                $slug = join(",", $slug);
                $queryTaxonomyRelated = "SELECT * FROM `wp_posts` p
                        left join wp_term_relationships t_r on t_r.object_id = p.ID
                        left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'movie_genre'
                        left join wp_terms t on tx.term_id = t.term_id
                        where t.name != 'featured' AND t.name != '' AND t.name IN ( ".$slug." ) LIMIT 8";
                $dataRelateds = $this->movieService->getItems($queryTaxonomyRelated);
                $movies[$key]['relateds'] = $dataRelateds;
                
            }
        }

        //Cache movies topweek
        if (Cache::has('movies_top_week')) {
            $topWeeks = Cache::get('movies_top_week');
        } else {
            $topWeeks = $this->movieService->getTopWeeks();
            Cache::put('movies_top_week', $topWeeks, $this->lifeTime);
        }

        //Cache movies popular
        if (Cache::has('movies_popular')) {
            $populars = Cache::get('movies_popular');
        } else {
            $populars = $this->movieService->getPopulars();
            Cache::put('movies_popular', $populars, $this->lifeTime);
        }

        $data = [
            "total" => $total,
            "perPage" => $perPage,
            "data" => [
                'topWeeks' => $topWeeks,
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
    public function show($title)
    {
        
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
