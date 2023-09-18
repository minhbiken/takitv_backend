<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HelperService {
    protected $imageUrlUpload;
    public function __construct()
    {
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
    }

    public function getSliderItems($query='')
    {
        $sliders = [];
        $srcSet = [];
        $sliderDatas = DB::select($query);
        foreach ( $sliderDatas as $sliderData ) {
            $dataQuery = "SELECT * FROM `wp_postmeta` pm 
                            LEFT JOIN wp_posts p ON p.ID = pm.post_id 
                            WHERE pm.meta_key = '_wp_attached_file' AND p.post_type = 'attachment' AND p.post_parent = " . $sliderData->ID . " ORDER BY p.post_date DESC LIMIT 1;";
            $dataResult = DB::select($dataQuery);

            $titleSlider = $sliderData->post_title; 
            $linkSlider = 'movie/' . $sliderData->post_title;
            $seasonNumber = '';
            $episodeNumber = '';
            $year = '';

            $queryMeta = "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ". $sliderData->ID .";";
            $dataMetas = DB::select($queryMeta);
            if( count($dataMetas) > 0 ) {
                foreach ( $dataMetas as $dataMeta ) {
                    if( $dataMeta->meta_key == '_movie_release_date' || $dataMeta->meta_key == '_episode_release_date' ) {
                        if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMeta->meta_value)) {
                            $newDataReleaseDate = explode('-', $dataMeta->meta_value);
                            $year = $newDataReleaseDate[0];
                        } else {
                            $year = $dataMeta->meta_value > 0 ? date('Y', $dataMeta->meta_value) : date('Y');
                        }
                    }
                }
            }

            
            if( $sliderData->post_type == 'tv_show' ) {
                
                $queryEpisode = "SELECT meta_key, meta_value FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $sliderData->ID . " LIMIT 1;";
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
                    $linkSlider = 'episode/' . $dataEpisoSlider[0]->post_title;
                }

                $queryEpisodeNumber = "SELECT meta_value FROM wp_postmeta WHERE meta_key = '_episode_number' AND post_id = " . $episodeId . ";";
                $dataEpisodeNumber = DB::select($queryEpisodeNumber);
                $episodeNumber = $dataEpisodeNumber[0]->meta_value;
            }
            $srcSet = $this->getAttachmentsByPostId($sliderData->ID);
            $sliders[] = [
                'id' => $sliderData->ID,
                'year' => $year,
                'title' => $titleSlider,
                'link' => $linkSlider,
                'src' => $this->imageUrlUpload.$dataResult[0]->meta_value,
                'srcSet' => $srcSet,
                'seasonNumber' => $seasonNumber,
                'episodeNumber' => $episodeNumber,
            ];
        }
        return $sliders;
    }

    public function getCacheDataByQuery($query='', $cacheName='') {
        if (Cache::has($cacheName)) {
            $data = Cache::get($cacheName);
        } else {
            $data = DB::select($query);
            if( count($data) == 0 ) {
                $data = '';
            }
            Cache::forever($cacheName, $data);
        }
        return $data;
    }

    public function getAttachmentsByPostId($id) {
        $queryImage = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
        LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $id .";";
        $dataImage = DB::select($queryImage);
        if ( count($dataImage) > 0 ) {
            $imgUrl = $dataImage[0]->meta_value;
        } else {
            $imgUrl = '';
        }

        $query = "SELECT meta_value FROM `wp_postmeta` WHERE meta_key = '_wp_attachment_metadata' AND post_id IN (SELECT ID FROM wp_posts WHERE post_type = 'attachment' AND post_parent = " . $id . ") LIMIT 1;";
        $srcSet[$id] = [];
        $attachments = DB::select($query);
        if ( count($attachments) > 0 ) {
            $attachmentsData = unserialize($attachments[0]->meta_value);
            $fileDir = explode('/', $attachmentsData['file']);

            if ( $fileDir[0] == 'image_webp' ) {
                $fileDirReal = $fileDir[0] . '/' . $fileDir[1] . '/' . $fileDir[2] . "/";
            } else {
                $fileDirReal = $fileDir[0] . '/' . $fileDir[1] . "/";
            }
            
            if ( $imgUrl != 'image_webp/' . $attachmentsData['file'] ) {
                array_push($srcSet[$id], $this->imageUrlUpload.$imgUrl. " " . $attachmentsData['width'] . 'w');
            } else if ( $imgUrl != $attachmentsData['file'] ) {
                array_push($srcSet[$id], $this->imageUrlUpload.$imgUrl. " " . $attachmentsData['width'] . 'w');
            } else {
                array_push($srcSet[$id], $this->imageUrlUpload.$attachmentsData['file']. " " . $attachmentsData['width'] . 'w');
            }
            
            if ( isset($attachmentsData['sizes']) ) {
                foreach( $attachmentsData['sizes'] as $attachment ) {
                        array_push($srcSet[$id], $this->imageUrlUpload.$fileDirReal.$attachment['file']. " " . $attachment['width'] . 'w');
                }
            }
        }
        $srcSet = join(", ", $srcSet[$id]);
        
        return $srcSet;
    }

    public function makeCacheFirst() {
        Http::get(route('homepage.index'));
        Http::get(route('movies.index',  ['orderBy' => 'date', 'page' => 1]));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1]));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'k-drama']));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'k-show']));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'k-sisa']));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'u-drama']));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'ott-web']));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'netflix']));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'disney']));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'apple-tv']));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'tving']));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'wavve']));
        Http::get(route('tvshows.index',  ['orderBy' => 'date', 'page' => 1, 'type' => 'amazon-prime-video']));
        return true;
    }
}