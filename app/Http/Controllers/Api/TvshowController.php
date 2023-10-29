<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\TvshowService;
use App\Services\HelperService;
use Illuminate\Support\Facades\Cache;
class TvshowController extends Controller
{
    private $imageUrlUpload;
    protected $tvshowService;
    protected $helperService;
    public function __construct(TvshowService $tvshowService, HelperService $helperService)
    {
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $this->tvshowService = $tvshowService;
        $this->helperService = $helperService;
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
        $orderBy = $request->get('orderBy', 'date');
        $type = $request->get('type', '');
        $genre = $request->get('genre', '');

        if ($page == 1 && $orderBy == 'date' && $genre == '' && Cache::has('tv_show_first_' . $type)) {
            $data = Cache::get('tv_show_first_' . $type);
        } else {
            $select = "SELECT p.ID as id, p.post_title as tvshowTitle, p.post_date as postDate FROM wp_posts p ";
            $where = " WHERE p.post_type = 'tv_show' AND p.post_status = 'publish' ";
    
            if( $type != '' ) {
                $categoryTvShowKorea = config('constants.categoryTvshowKoreas');
                if( in_array($type, $categoryTvShowKorea) ) {
                    $idType = "SELECT wr.object_id
                                FROM wp_terms t
                                LEFT JOIN wp_term_taxonomy wt ON t.term_id = wt.term_id
                                LEFT JOIN wp_term_relationships wr ON wr.term_taxonomy_id = wt.term_taxonomy_id
                                WHERE slug = '". $type ."'";
                    $whereType = " AND p.ID IN ( ". $idType ." ) ";
                } else {
                    $whereType = $this->tvshowService->getWhereByType($type);
                }
                $where = $where . $whereType;
            }
            if( $genre != '' ) {
                $queryGenre = "SELECT tr.object_id FROM wp_terms t
                    left join wp_term_taxonomy tx on tx.term_id = t.term_id
                    left join wp_term_relationships tr on tr.term_taxonomy_id = tx.term_taxonomy_id
                    WHERE t.slug = '" . \urlencode($genre) . "'";
                $where = $where . "AND p.ID IN (" . $queryGenre . ") ";    
            }
    
            if( $orderBy == 'titleAsc' ) {
                $order = "ORDER BY p.post_title ASC ";
            }
            elseif( $orderBy == 'titleDesc' ) {
                $order = "ORDER BY p.post_title DESC ";
            }
            elseif ($orderBy == 'rating') {
                $selectRating = "LEFT JOIN wp_most_popular mp ON mp.post_id = p.ID ";
                $select = $select . $selectRating;
                $order = "ORDER BY mp.all_time_stats DESC ";
            }
            elseif ($orderBy == 'menuOrder') {
                $order = "ORDER BY p.menu_order DESC ";
            } else { // $orderBy == date or anything else
                $order = "ORDER BY p.post_date DESC ";
            }
    
            //query all tvshow
            $query = $select . $where . $order;
    
            $selectTotal = "SELECT COUNT(1) as total FROM wp_posts p ";
            $queryTotal = $selectTotal . $where;
    
            if( Cache::has('tv_show_query_total') && Cache::get('tv_show_query_total') === $queryTotal && Cache::has('tv_show_data_total')) {
                $total = Cache::get('tv_show_data_total');
            } else {
                $dataTotal = DB::select($queryTotal);
                $total = $dataTotal[0]->total;
                Cache::forever('tv_show_query_total', $queryTotal);
                Cache::forever('tv_show_data_total', $total);
            }
    
            //query limit tvshow
            $limit = " LIMIT " . ( ( $page - 1 ) * $perPage ) . ", $perPage ;";
            $query = $query . $limit;
            $data = $this->getData($query, $type, $genre, $total, $perPage, $page);
            if ( $page == 1 && $orderBy == 'date' && $genre == '') {
                Cache::forever('tv_show_first_' . $type, $data);
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
    public function show(Request $request)
    {
        $slug = $request->get('slug', '');
        $sql = "SELECT p.ID as id, p.post_title, p.post_content as description, p.post_date as postDate FROM wp_posts p WHERE p.post_type = 'tv_show' AND p.post_status = 'publish' AND p.post_name = '". \urlencode($slug) ."' LIMIT 1";

        $tvShow = DB::selectOne($sql);
        if (!$tvShow) {
            return response()->make('', Response::HTTP_NOT_FOUND);
        }

        $genres = $this->tvshowService->getTvshowsGenres([$tvShow->id])[$tvShow->id] ?? [];
        $metaData = $this->tvshowService->getTvShowsMetaData([$tvShow->id], null, true)[$tvShow->id] ?? [];
        
        //Seasons
        $episodeIds = \array_reduce($metaData['seasons'], function($episodeIds, $season) {
            $episodeIds = \array_merge($episodeIds, \array_map(fn($episode) => $episode->id, $season['episodes']));
            return $episodeIds;
        }, []);

        $seasons = $this->tvshowService->getEpisodeMetadata($episodeIds);
        //Get topweek
        $topWeeks = $this->tvshowService->getTopWeeks();
        //Get topmonth
        $topMonths = $this->tvshowService->getTopMonths();
        //get 8 related tv shows
        $genreSlugs = \array_column($genres, 'slug');

        $relatedSql = "SELECT DISTINCT p.ID as id, p.post_title as title, p.post_name as slug FROM `wp_posts` p
            left join wp_term_relationships t_r on t_r.object_id = p.ID
            left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'tv_show_genre'
            left join wp_terms t on tx.term_id = t.term_id
            where t.name != 'featured' AND t.slug IN ('" . join("','", $genreSlugs) . "') AND p.ID NOT IN (" . \join(",", config('constants.tv_show_error')) . ") LIMIT 10";
        $relatedTvshows = DB::select($relatedSql);

        $data = [
            'id' => $tvShow->id,
            'title' => $tvShow->title,
            'description' => $tvShow->description,
            'genres' => $genres,
            'postDate' => $tvShow->postDate,
            'topWeeks' => $topWeeks,
            'topMonths' => $topMonths,
            'relateds' => $relatedTvshows
        ] + $metaData;

        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * @param string $query
     * @param string $type
     * @param string $genre
     * @param int $total
     * @param int $perPage
     * @param int $page
     * @return array
     */
    private function getData(string $query, string $type, string $genre, int $total, int $perPage, int $page)
    {
        $tvshowItems = DB::select($query);
        if ($type != 'ott-web' && $genre != '') {
            $type = $genre;
        }
        $topWeekItems = $this->tvshowService->getTopWeeks($type);
        $popularItems = $topWeekItems;

        //Process metadata and genres
        $allIds = \array_unique(\array_merge(
            \array_map(fn($item) => $item->id, $tvshowItems),
            \array_map(fn($item) => $item->id, $popularItems)
        ));
        
        $tvshowMetaData = $this->tvshowService->getTvShowsMetaData($allIds, null);
        $lastEpisodeIds = \array_map(fn($item) => $item['lastEpisode']['id'], $tvshowMetaData);
        $episodeMetadata = $this->tvshowService->getEpisodeMetadata($lastEpisodeIds);
        $channelImages = $this->tvshowService->getTvShowChannelImage($allIds, $type);
        $genres = $this->tvshowService->getTvshowsGenres($allIds);

        $items = [];
        foreach ($tvshowItems as $item) {
            $id = (int) $item->id;
            if (!isset($tvshowMetaData[$id]['lastEpisode'])) {
                continue;
            }

            $lastEpisode = $tvshowMetaData[$id]['lastEpisode'];
            $items[] = [
                'genres' => $genres[$id] ?? [],
                'tvshowTitle' => $item->tvshowTitle,
                'postDate' => $item->postDate,
                'chanelImage' => $channelImages[$id] ?? env('IMAGE_PLACEHOLDER'),
                'originalTitle' => $tvshowMetaData[$id]['originalTitle'],
                'seasonName' => $tvshowMetaData[$id]['seasonName'],
                'src' => $tvshowMetaData[$id]['src'],
                'srcSet' => $tvshowMetaData[$id]['srcSet']
            ] + $lastEpisode + $episodeMetadata[$lastEpisode['id']];
        }
        
        $topWeeks = [];
        foreach ($topWeekItems as $item) {
            $id = (int) $item->id;
            if (!isset($tvshowMetaData[$id]['lastEpisode'])) {
                continue;
            }

            $lastEpisode = $tvshowMetaData[$id]['lastEpisode'];
            $topWeeks[] = [
                'genres' => $genres[$id] ?? [],
                'tvshowTitle' => $item->tvshowTitle,
                'postDate' => $item->postDate,
                'chanelImage' => $channelImages[$id] ?? env('IMAGE_PLACEHOLDER'),
                'originalTitle' => $tvshowMetaData[$id]['originalTitle'],
                'seasonName' => $tvshowMetaData[$id]['seasonName'],
                'src' => $tvshowMetaData[$id]['src'],
                'srcSet' => $tvshowMetaData[$id]['srcSet']
            ] + $lastEpisode + $episodeMetadata[$lastEpisode['id']];
        }

        $populars = [];
        foreach ($popularItems as $item) {
            $id = (int) $item->id;
            if (!isset($tvshowMetaData[$id]['lastEpisode'])) {
                continue;
            }

            $lastEpisode = $tvshowMetaData[$id]['lastEpisode'];
            $populars[] = [
                'genres' => $genres[$id] ?? [],
                'tvshowTitle' => $item->tvshowTitle,
                'postDate' => $item->postDate,
                'chanelImage' => $channelImages[$id] ?? env('IMAGE_PLACEHOLDER'),
                'originalTitle' => $tvshowMetaData[$id]['originalTitle'],
                'seasonName' => $tvshowMetaData[$id]['seasonName'],
                'src' => $tvshowMetaData[$id]['src'],
                'srcSet' => $tvshowMetaData[$id]['srcSet']
            ] + $lastEpisode + $episodeMetadata[$lastEpisode['id']];
        }

        return [
            "total" => $total,
            "perPage" => $perPage,
            "currentPage" => $page,
            "data" => [
                'ottChanels' => config('constants.ottChanels'),
                'topWeeks' => $topWeeks,
                'populars' => $populars,
                'items' => $items
            ]
        ];
    }
}
