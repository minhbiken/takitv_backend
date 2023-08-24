<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
class HelperService {
    public function getSliderItems($query)
    {
        $sliders = [];
        $imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $sliderDatas = DB::select($query);
        foreach ( $sliderDatas as $sliderData ) {
            $dataQuery = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $sliderData->ID .";";
            $dataResult = DB::select($dataQuery);

            $titleSlider = $sliderData->post_title; 
            $linkSlider = 'movie/' . $sliderData->post_title."/";
            $seasonNumber = '';
            $episodeNumber = '';
            
            if( $sliderData->post_type == 'tv_show' ) {
                
                $queryEpisode = "SELECT * FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $sliderData->ID . " LIMIT 1;";
                $dataEpisode = DB::select($queryEpisode);
                
                $episodeData = $dataEpisode[0]->meta_value;
                $episodeData = unserialize($episodeData);
    
                $lastSeason = end($episodeData);
                $seasonNumber = $lastSeason['name'];

                $episodeId = end($lastSeason['episodes']);
                
                $select = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM wp_posts p ";
                $where = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
                $whereTitle = " AND p.ID='". $episodeId ."' ";
    
                $where = $where . $whereTitle;
                $query = $select . $where;
                $dataEpisoSlider = DB::select($query);
                
                if( count($dataEpisoSlider) > 0 ) {
                    $linkSlider = 'episode/' . $dataEpisoSlider[0]->post_title . "/";
                }

                $queryEpisodeNumber = "SELECT * FROM wp_postmeta WHERE meta_key = '_episode_number' AND post_id = " . $episodeId . ";";
                $dataEpisodeNumber = DB::select($queryEpisodeNumber);
                $episodeNumber = $dataEpisodeNumber[0]->meta_value;
            }

            $sliders[] = [
                'title' => $titleSlider,
                'link' => $linkSlider,
                'src' => $imageUrlUpload.$dataResult[0]->meta_value,
                'seasonNumber' => $seasonNumber,
                'episodeNumber' => $episodeNumber,
            ];
        }
        return $sliders;
    }
}