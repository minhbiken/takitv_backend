<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\HelperService;
class CastController extends Controller
{
    protected $imageUrlUpload;
    protected $tvshowService;
    protected $helperService;
    
    public function __construct(HelperService $helperService)
    {
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $this->helperService = $helperService;
    }

    public function index(Request $request) {
        $page = $request->get('page', 1);
        $perPage = $request->get('limit', env('PAGE_LIMIT'));
        $orderBy = $request->get('orderBy', '');

        $select = "SELECT p.ID as id, p.post_name as slug, p.post_title as name, wp.meta_value as src FROM wp_posts p LEFT JOIN wp_postmeta wp ON wp.post_id = p.ID AND wp.meta_key = '_person_image_custom' ";
        $where = " WHERE p.post_status = 'publish' AND p.post_type='person' ";

        if( $orderBy == '' ) {
            $order = "ORDER BY p.post_title DESC ";
        } else if( $orderBy == 'titleAsc' ) {
            $order = "ORDER BY p.post_title ASC ";
        } else if( $orderBy == 'titleDesc' ) {
            $order = "ORDER BY p.post_title DESC ";
        }

        //query all
        $query = $select . $where . $order;

        $selectTotal = "SELECT COUNT(p.ID) as total FROM wp_posts p ";
        $queryTotal = $selectTotal . $where;
        $dataTotal = DB::select($queryTotal);
        $total = $dataTotal[0]->total;

        //query limit
        $limit = "LIMIT " . ( ( $page - 1 ) * $perPage ) . ", $perPage ;";
        $query = $query . $limit;

        $items = DB::select($query);
        $topWeeks = [];
        $data = [
            "total" => $total,
            "perPage" => $perPage,
            "data" => [
                'topWeeks' => $topWeeks,
                'items' => $items
            ]
        ];
        
        return response()->json($data, Response::HTTP_OK);
    }

    public function show(Request $request) 
    {
        $slug = $request->get('slug', '');
        print_r($slug); die;
        $data = [];
        
        $page = $request->get('page', 1);
        $perPage = $request->get('limit', env('PAGE_LIMIT'));
        $orderBy = $request->get('orderBy', '');

        $select = "SELECT p.ID, p.post_name, p.post_title, p.post_type, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM wp_posts p ";
        $where = " WHERE p.post_status = 'publish' AND p.post_type IN ('tv_show', 'movie') ";

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

        //query all
        $query = $select . $where . $order;

        $selectTotal = "SELECT COUNT(p.ID) as total FROM wp_posts p ";
        $queryTotal = $selectTotal . $where;
        $dataTotal = DB::select($queryTotal);
        $total = $dataTotal[0]->total;

        //query limit
        $limit = "LIMIT " . ( ( $page - 1 ) * $perPage ) . ", $perPage ;";
        $query = $query . $limit;
        $items = [];
        $topWeeks = $this->tvshowService->getTopWeeks();
        $data = [
            "total" => $total,
            "perPage" => $perPage,
            "data" => [
                'topWeeks' => $topWeeks,
                'items' => $items
            ]
        ];
        
        return response()->json($data, Response::HTTP_OK);
    }
}
