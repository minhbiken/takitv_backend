<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\HelperService;
class PersonController extends Controller
{
    protected $imageUrlUpload;
    protected $tvshowService;
    protected $helperService;
    
    public function __construct(HelperService $helperService)
    {
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $this->helperService = $helperService;
    }

    public function show(Request $request)
    {
        $slug = $request->get('slug', '');
        print_r($slug); die;
        $data = [];
        
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
