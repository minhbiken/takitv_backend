<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\TvshowService;
use App\Services\HelperService;
use Illuminate\Support\Facades\Cache;
class EpisodeController extends Controller
{
    protected $imageUrlUpload;
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
    public function index()
    {
        //
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
     * @param  string  $episodeTitle
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $watch = $request->get('watch', '');
        if( $watch != '' ) {
            $outLink = $this->helperService->getKokoatvLink($watch);
            if( isset($outLink->link) && $outLink->link != '' ) {
                $item['watchLinks'] = $outLink->link;
            } else {
                $item['watchLinks'] = [];
            }
            return response()->json($item, Response::HTTP_OK);
        } else {
            $slug = $request->get('slug', '');
            $imageUrlUpload = env('IMAGE_URL_UPLOAD');
    
            $sql = "SELECT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM wp_posts p WHERE p.post_name = '" . \urlencode($slug) . "' AND p.post_type = 'episode' AND p.post_status = 'publish' LIMIT 1";
    
            $data = DB::selectOne($sql);
            if (empty($data)) {
                return response()->make('', Response::HTTP_NOT_FOUND);
            }
            
            $tvshowTitle = '';
            $tvshowSlug = '';
            //get all seasons and episode
            $src = '';
            $casts = [];
            $tvShowId = $this->tvshowService->getTvShowId($data->ID);
            if (!$tvShowId) {
                return response()->make('', Response::HTTP_NOT_FOUND);
            }
            
            if( $tvShowId ) {
                $casts = $this->helperService->getCastsOfPost($tvShowId);
                $querySeasonEpisode = "SELECT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, pm.meta_value, pm.meta_key, pm.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID WHERE p.ID=" . $tvShowId . " AND pm.meta_key='_seasons' ORDER BY p.ID ASC LIMIT 1;";
                $tvshowTitleData = DB::select($querySeasonEpisode);
                $tvshowTitle = $tvshowTitleData[0]->post_title;
                $tvshowSlug = $tvshowTitleData[0]->post_name;
                $seasons = $this->tvshowService->getSeasons($tvshowTitleData);

                $dataSeason = $dataPost[0];
                $queryTaxonomy = "SELECT t.name, t.slug FROM `wp_posts` p
                                left join wp_term_relationships t_r on t_r.object_id = p.ID
                                left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'tv_show_genre'
                                left join wp_terms t on tx.term_id = t.term_id
                                where t.name != 'featured' AND t.name != '' AND p.ID = ". $tvshowTitleData[0]->ID .";";
                $dataTaxonomys = DB::select($queryTaxonomy);
                $genres = [];
                foreach( $dataTaxonomys as $dataTaxonomy ) {
                    $genres[] = [
                        'name' => $dataTaxonomy->name,
                        'link' => $dataTaxonomy->slug,
                        'slug' => $dataTaxonomy->slug
                    ];
                }
                //outlink only show in into
                $outLink = $this->helperService->getOutLink();
                if ( $outLink != '' ) {
                    $outlink =  $outLink . '?pid=' . $dataSeason->ID;
                } else {
                    $outlink = '';
                }
                $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                                    LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $tvshowTitleData[0]->ID .";";
                $dataSrcMeta = DB::select($querySrcMeta);
                
                if( count($dataSrcMeta) > 0 ) {
                    $src = $imageUrlUpload.$dataSrcMeta[0]->meta_value;
                }
                
                $seasonName = '';
                foreach ( $seasons as $season ) {
                    foreach ( $season['episodes'] as $episode ) {
                        if ( $dataSeason->post_title == $episode['title'] ) {
                            $seasonName = $season['name'];
                            break;
                        }
                    }
                }
                $srcSet = $this->helperService->getAttachmentsByPostId($tvshowTitleData[0]->ID);
                $movies = [
                    'id' => $dataSeason->ID,
                    'title' => $dataSeason->post_title,
                    'originalTitle' => $tvshowTitleData[0]->original_title,
                    'description' => $tvshowTitleData[0]->post_content,
                    'genres' => $genres,
                    'src' => $src,
                    'srcSet' => $srcSet,
                    'outlink' => $outlink,
                    'postDateGmt' => $dataSeason->post_date_gmt,
                    'postDate' => $dataSeason->post_date,
                    'seasonName' => $seasonName,
                    'tvshowTitle' => $tvshowTitle,
                    'tvshowSlug' => $tvshowSlug,
                    'seasons' => $seasons,
                    'casts' => $casts
                ];
            }
            return response()->json($movies, Response::HTTP_OK);
        }
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
