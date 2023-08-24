<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\TvshowService;
class EpisodeController extends Controller
{
    private $imageUrlUpload;
    protected $tvshowService;
    public function __construct(TvshowService $tvshowService)
    {
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $this->tvshowService = $tvshowService;
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
    public function show($episodeTitle)
    {
        $imageUrlUpload = env('IMAGE_URL_UPLOAD');

        $select = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM wp_posts p ";
        $where = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
        $whereTitle = " AND p.post_title='". $episodeTitle ."' ";

        $where = $where . $whereTitle;
        $movies = [];
        $query = $select . $where;
        $dataPost = DB::select($query);
        $tvshowTitle = '';
        if (count($dataPost) == 0) {
            return response()->json($movies, Response::HTTP_NOT_FOUND);
        }

        //get all seasons and episode
        $querySeasonEpisode = "SELECT * FROM wp_posts p LEFT JOIN wp_postmeta wp ON wp.post_id = p.ID WHERE wp.meta_key = '_seasons' AND meta_value LIKE '%" . $dataPost[0]->ID . "%' LIMIT 1;";
        $tvshowTitleData = DB::select($querySeasonEpisode);
        $tvshowTitle = $tvshowTitleData[0]->post_title;
        $seasons = $this->tvshowService->getSeasons($querySeasonEpisode);

        $datapostId = DB::select($querySeasonEpisode);

        $dataSeason = $dataPost[0];
        $queryTaxonomy = "SELECT * FROM `wp_posts` p
                        left join wp_term_relationships t_r on t_r.object_id = p.ID
                        left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'tv_show_genre'
                        left join wp_terms t on tx.term_id = t.term_id
                        where t.name != 'featured' AND p.ID = ". $datapostId[0]->ID .";";
        $dataTaxonomys = DB::select($queryTaxonomy);
        $genres = [];
        foreach( $dataTaxonomys as $dataTaxonomy ) {
            $genres[] = [
                'name' => $dataTaxonomy->name,
                'link' =>  $dataTaxonomy->slug
            ];
        }
    
        //outlink only show in into
        $outlink = env('OUTLINK');
        $outlink = @file_get_contents($outlink);

        if( $outlink == NULL ) $outlink = env('DEFAULT_OUTLINK');
        $outlink =  $outlink . '?pid=' . $dataSeason->ID;

        $datapostId = DB::select($querySeasonEpisode);
        $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $datapostId[0]->ID .";";
        
        $dataSrcMeta = DB::select($querySrcMeta);
        $src = $imageUrlUpload.$dataSrcMeta[0]->meta_value;

        $seasonName = '';
        foreach ( $seasons as $season ) {
            foreach ( $season['episodes'] as $episode ) {
                if ( $dataSeason->post_title == $episode['title'] ) {
                    $seasonName = $season['name'];
                    break;
                }
            }
        }

        $movies = [
            'id' => $dataSeason->ID,
            'title' => $dataSeason->post_title,
            'originalTitle' => $datapostId[0]->original_title,
            'description' => $datapostId[0]->post_content,
            'genres' => $genres,
            'src' => $src,
            'outlink' => $outlink,
            'postDateGmt' => $dataSeason->post_date_gmt,
            'seasonName' => $seasonName,
            'tvshowTitle' => $tvshowTitle,
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
