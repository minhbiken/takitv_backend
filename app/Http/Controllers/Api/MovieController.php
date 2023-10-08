<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\MovieService;
use App\Services\HelperService;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\VarDumper\VarDumper;

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
        
        if( $page == 1 &&  $orderBy == 'date' && $releaseYear == '' && $genre == '' && Cache::has('movie_first') ) {
            $data = Cache::get('movie_first');
        } else {

            $select = "SELECT p.ID as id, p.post_name as slug, p.post_title as title FROM wp_posts p ";
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
            $items = DB::select($query);

            $queryTopWeek = "SELECT p.ID as id, p.post_name as slug, p.post_title as title FROM `wp_most_popular` mp
                            LEFT JOIN wp_posts p ON p.ID = mp.post_id
                            WHERE p.post_type = 'movie' AND p.post_title != '' AND mp.post_id != '' AND p.ID != ''
                            ORDER BY mp.7_day_stats DESC
                            LIMIT 5";
            $topWeeks = DB::select($queryTopWeek);
            $queryPopular = "SELECT p.ID as id, p.post_name as slug, p.post_title title FROM `wp_most_popular` wp
                            LEFT JOIN wp_posts p ON p.ID = wp.post_id 
                            WHERE p.post_type = 'movie' AND wp.post_id != '' AND p.ID != ''

                            ORDER BY wp.`1_day_stats` DESC
                            LIMIT 6";
            $populars = DB::select($queryPopular);
            $topWeeks = DB::select($queryTopWeek);

            //Process metadata and genres
            $allPostIds = \array_merge(
                \array_map(fn($item) => $item->id, $items),
                \array_map(fn($item) => $item->id, $populars),
                \array_map(fn($item) => $item->id, $topWeeks),
            );
            $thumbnailPostIds = \array_merge(
                \array_map(fn($item) => $item->id, $items),
                \array_map(fn($item) => $item->id, $populars),

            );
            $noThmunbnailPostIds = \array_map(fn($item) => $item->id, $topWeeks);

            $genres = $this->movieService->getMoviesGenres($allPostIds);
            $metaData = $this->movieService->getMoviesMetadata($thumbnailPostIds);
            $metaDataTopWeeks = $this->movieService->getMoviesMetadata($noThmunbnailPostIds, ['_movie_release_date']);
            foreach ($items as &$item) {
                $item = \get_object_vars($item);
                $item['genres'] = $genres[(int) $item['id']] ?? [];
                $item += $metaData[(int) $item['id']];
            }

            foreach ($populars as &$item) {
                $item = \get_object_vars($item);
                $item['genres'] = $genres[(int) $item['id']] ?? [];
                $item += $metaData[(int) $item['id']];
            }

            foreach ($topWeeks as &$item) {
                $item = \get_object_vars($item);
                $item['genres'] = $genres[(int) $item['id']] ?? [];
                $item += $metaDataTopWeeks[(int) $item['id']];
            }

            $data = [
                "total" => $total,
                "perPage" => $perPage,
                "data" => [
                    'topWeeks' => $topWeeks,
                    'populars' => $populars,
                    'items' => $items
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
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $slug = $request->get('slug', '');
        if (empty($slug)) {
            return response()->json([], Response::HTTP_BAD_REQUEST);
        }
        $sql = "SELECT ID as id, post_title as title, post_content as description FROM wp_posts WHERE post_type = 'movie' AND post_status = 'publish' AND post_name = '" . \urlencode($slug) . "' LIMIT 1;";
        $data = DB::selectOne($sql);

        if (empty($data)) {
            return response()->json([], Response::HTTP_NOT_FOUND);
        }
        $postId = (int) $data->id;

        $genres = $this->movieService->getMoviesGenres([$postId]);
        $genreSlugs = empty($genres) ? [] : \array_map((fn($genre) => $genre['slug']), $genres[$postId]);
          

        $outlink = env('OUTLINK');
        $outlink = @file_get_contents($outlink);
        if( $outlink == NULL ) $outlink = env('DEFAULT_OUTLINK');
        $outlink =  $outlink . '?pid=' . $postId;

        //get 8 movies related
        $movieRelateds = $this->movieService->getRelatedMovies($postId, $genreSlugs);
        $relatedMovieIds = \array_map(fn($item) => $item->id, $movieRelateds);
        $metaData = $this->movieService->getMoviesMetadata(\array_merge([$postId], $relatedMovieIds));
        
        $relatedMovieGenres = $this->movieService->getMoviesGenres($relatedMovieIds);
        foreach ($movieRelateds as &$item) {
            $postIds[] = $item->id;
            $item = \get_object_vars($item) + [
                'genres' => $relatedMovieGenres[$item->id] ?? [],
            ] + $metaData[$item->id] ?? [];
        }

        $casts = $this->movieService->getCastsOfPost($postId);

        $movie = \get_object_vars($data) + ($metaData[$data->id] ?? []) + [
            'genres' => $genres[$postId] ?? [],
            'outlink' => $outlink,
            'relateds' => $movieRelateds,
            'casts' => $casts
        ];

        return response()->json($movie, Response::HTTP_OK);
    }
}
