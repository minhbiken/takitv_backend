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
        $slug = $request->get('slug', '');

        if( $watch != '' ) {
            $outLink = $this->helperService->getKokoatvLink($watch);
            if( isset($outLink->link) && $outLink->link != '' ) {
                $item['watchLinks'] = $outLink->link;
            } else {
                $item['watchLinks'] = [];
            }
            return response()->json($item, Response::HTTP_OK);
        }
   
        $sql = "SELECT ID as id, post_title as title, post_name as slug, post_date as postDate FROM wp_posts WHERE post_name = '" . \urlencode($slug) . "' AND post_type = 'episode' AND post_status = 'publish' LIMIT 1";
        $episode = DB::selectOne($sql);

        if (empty($episode)) {
            return response()->make('', Response::HTTP_NOT_FOUND);
        }
        
        $tvShowData = $this->tvshowService->getTvShowData($episode->id);
        if (!$tvShowData) {
            return response()->make('', Response::HTTP_NOT_FOUND);
        }
        
        // casts
        $casts = $this->helperService->getCastsOfPost($tvShowData->id);
        $tvShowsMetaData = $this->tvshowService->getTvShowsMetaData([$tvShowData->id], null, true)[$tvShowData->id] ?? [];
        if (!$tvShowsMetaData) {
            return response()->make('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // genres
        $genres = $this->tvshowService->getTvshowsGenres([$tvShowData->id])[$tvShowData->id] ?? [];
            
        //outlink only show in into
        $outLink = $this->helperService->getOutLink();
        if ( $outLink != '' ) {
            $outlink =  $outLink . '?pid=' . $episode->id;
        } else {
            $outlink = '';
        }

        $data = [
            'id' => $episode->id,
            'title' => $episode->title,
            'genres' => $genres,
            'outlink' => $outlink,
            'postDate' => $episode->postDate,
            'tvshowTitle' => $tvShowData->title,
            'description' => $tvShowData->description,
            'tvshowSlug' => $tvShowData->slug,
            'casts' => $casts
        ] + $tvShowsMetaData;
        
        return response()->json($data, Response::HTTP_OK);
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
