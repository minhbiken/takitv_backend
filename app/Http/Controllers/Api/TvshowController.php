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
        $orderBy = $request->get('orderBy', '');
        $type = $request->get('type', '');
        $genre = $request->get('genre', '');

        if (false) {
        // if ($page == 1 && ($orderBy == 'date' || $orderBy == '') && $genre == '' && Cache::has('tv_show_first_' . $type)) {
            $data = Cache::has('tv_show_first_' . $type);
        } else {
            $select = "SELECT p.ID as id, p.post_title as title, p.post_date as postDate FROM wp_posts p ";
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
            } else {
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
            if ( $page == 1 && ($orderBy == 'date' || $orderBy == '') && $genre == '') {
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
        $titleTvshow = $request->get('slug', '');
        $newtitleTvshow = urlencode($titleTvshow);
        $select = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt, p.post_date, p.post_modified FROM wp_posts p ";
        $where = " WHERE  ((p.post_type = 'tv_show' AND (p.post_status = 'publish'))) ";
        $whereTitle = " AND p.post_name='". $newtitleTvshow ."' LIMIT 1; ";

        $where = $where . $whereTitle;
        $movies = [];
        $tvShowSlug = '';

        $dataPost = DB::select($select . $where);
        $link = '';
        if (count($dataPost) == 0) {
            return response()->json($movies, Response::HTTP_NOT_FOUND);
        }
        $queryTaxonomy = "SELECT t.name, t.slug FROM `wp_posts` p
                        left join wp_term_relationships t_r on t_r.object_id = p.ID
                        left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'tv_show_genre'
                        left join wp_terms t on tx.term_id = t.term_id
                        where t.name != 'featured' AND t.name != '' AND p.ID = ". $dataPost[0]->ID .";";
        $dataTaxonomys = DB::select($queryTaxonomy);

        $genres = [];
        $slug = [];
        foreach( $dataTaxonomys as $dataTaxonomy ) {
            $genres[] = [
                'name' => $dataTaxonomy->name,
                'link' => $dataTaxonomy->slug,
                'slug' => $dataTaxonomy->slug
            ];
            $slug[] = "'" . $dataTaxonomy->name . "'";
        }

        $dataSeason = $dataPost[0];
    
        $queryEpisode = "SELECT meta_value FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $dataSeason->ID . " LIMIT 1;";
        $dataEpisode = DB::select($queryEpisode);
        $seasons = $this->tvshowService->getSeasons($dataEpisode);
        
        $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $dataSeason->ID .";";
        $dataSrcMeta = DB::select($querySrcMeta);
        $src = $this->imageUrlUpload.$dataSrcMeta[0]->meta_value;
     
        $episodeData = DB::select($queryEpisode);
        $episodeData = $episodeData[0]->meta_value;
        $episodeData = unserialize($episodeData);
        
        $lastSeason = end($episodeData);
        $episodeId = end($lastSeason['episodes']);

        //outlink only show in into
        $outlink = env('OUTLINK');
        $outlink = @file_get_contents($outlink);

        if( $outlink == NULL ) $outlink = env('DEFAULT_OUTLINK');
        $outlink =  $outlink . '?pid=' . $episodeId;

        //Get topweek
        $topWeeks = $this->tvshowService->getTopWeeks();

        //Get topmonth
        $topMonths = $this->tvshowService->getTopMonths();

        //get 8 movies related
        $slug = join(",", $slug);

        if ( $slug == '' ) {
            $dataRelateds = [];
        } else {
            $arrayTvShowError = config('constants.tv_show_error');
            $arrayTvShowError = join(",", $arrayTvShowError);
            $queryTaxonomyRelated = "SELECT DISTINCT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM `wp_posts` p
            left join wp_term_relationships t_r on t_r.object_id = p.ID
            left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'tv_show_genre'
            left join wp_terms t on tx.term_id = t.term_id
            where t.name != 'featured' AND t.name != '' AND ( t.name IN ( ".$slug." ) OR t.slug IN ( ".$slug." ) )AND p.ID NOT IN ( " . $arrayTvShowError . " ) LIMIT 10";
            $dataRelateds = $this->tvshowService->getItems($queryTaxonomyRelated);
        }

        $selectTitleEpisode = "SELECT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM wp_posts p ";
        $whereTitleEpisode = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
        $whereTitleSub = " AND p.ID='". $episodeId ."' ";

        $queryTitle = $selectTitleEpisode . $whereTitleEpisode . $whereTitleSub;
        $dataEpisoTitle = DB::select($queryTitle);
        
        if( count($dataEpisoTitle) > 0 ) {
            $link = 'episode/' . $dataEpisoTitle[0]->post_title ."/";
            $tvShowSlug = $dataEpisoTitle[0]->post_name;
        }

        $srcSet = $this->helperService->getAttachmentsByPostId($dataSeason->ID);
        $movies = [
            'id' => $dataSeason->ID,
            'title' => $dataSeason->post_title,
            'slug' => $tvShowSlug,
            'originalTitle' => $dataSeason->original_title,
            'description' => $dataSeason->post_content,
            'genres' => $genres,
            'src' => $src,
            'srcSet' => $srcSet,
            'link' => $link,
            'outlink' => $outlink,
            'postDateGmt' => $dataSeason->post_date_gmt,
            'postDate' => $dataSeason->post_date,
            'seasons' => $seasons,
            'topWeeks' => $topWeeks,
            'topMonths' => $topMonths,
            'relateds' => $dataRelateds
        ];

        return response()->json($movies, Response::HTTP_OK);
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
    private function getData(string $query, string $type, string $genre, int $total, int $perPage, int $page) {
        $tvshows = DB::select($query);
        
        if( $type == 'ott-web' ) {
            $topWeeks = $this->tvshowService->getTopWeekOTT();
            $populars = $topWeeks;
        } else {
            if ($genre != '') {
                $type = $genre;
            }
            $topWeeks = $this->tvshowService->getTopWeeks($type);
            $populars = $this->tvshowService->getPopulars($type);
        }

        //Process metadata and genres
        $tvshowIds = \array_map(fn($item) => $item->id, $tvshows);
        $tvshowMetaData = $this->tvshowService->getTvShowsMetaData($tvshowIds);
        $lastEpisodeIds = \array_map(fn($item) => $item['lastEpisodeId'], $tvshowMetaData);
        $episodeMetadata = $this->tvshowService->getEpisodeMetadata($lastEpisodeIds);
        $genres = $this->tvshowService->getTvshowsGenres($tvshowIds);
        $channelImages = $this->tvshowService->getTvShowChannelImage($tvshowIds, $type);

        $items = [];
        foreach ($tvshows as $tvshow) {
            $tvshowId = (int) $tvshow->id;
            $lastEpisodeId = $tvshowMetaData[$tvshowId]['lastEpisodeId'];
            unset($tvshowMetaData[$tvshowId]['lastEpisodeId']);
            $items[] = [
                'id' => $lastEpisodeId,
                'genres' => $genres[$tvshowId] ?? [],
                'tvshowTitle' => $tvshow->title,
                'postDate' => $tvshow->postDate,
                'chanelImage' => $channelImages[$tvshowId] ?? env('IMAGE_PLACEHOLDER'),
            ] + $episodeMetadata[$lastEpisodeId] + ($tvshowMetaData[$tvshowId] ?? []);
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
