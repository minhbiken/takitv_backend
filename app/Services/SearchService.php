<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class SearchService {
    public function getItems($query) {
        $items = [];
        $releaseDate = date('Y-M-D');
        $imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $datas = DB::select($query);
        $movieRunTime = '';
        $chanel = '';
        $seasonNumber = '';
        $episodeNumber = '';
        foreach( $datas as $key => $data ) {
            $queryMeta = "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ". $data->ID .";";
            $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $data->ID .";";
            $dataSrcMeta = DB::select($querySrcMeta);
            $src = $imageUrlUpload.$dataSrcMeta[0]->meta_value;
            $dataMetas = DB::select($queryMeta);
        
            foreach($dataMetas as $dataMeta) {
                if( $dataMeta->meta_key == '_movie_release_date' ) {
                    if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMeta->meta_value)) {
                        $newDataReleaseDate = explode('-', $dataMeta->meta_value);
                        $releaseDate = $newDataReleaseDate[0];
                    } else {
                        $releaseDate = $dataMeta->meta_value > 0 ? date('Y', $dataMeta->meta_value) : '';
                    }
                }
                if( $dataMeta->meta_key == '_movie_run_time' ) {
                    $movieRunTime = $dataMeta->meta_value;
                }
            }

            $queryTaxonomy = "SELECT t.name, t.slug FROM `wp_posts` p
                                left join wp_term_relationships t_r on t_r.object_id = p.ID
                                left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'movie_genre'
                                left join wp_terms t on tx.term_id = t.term_id
                                where t.name != 'featured' AND t.name != '' AND p.ID = ". $data->ID ."
                                ORDER BY t.name DESC;";

            $dataTaxonomys = DB::select($queryTaxonomy);

            $genres = [];
            foreach( $dataTaxonomys as $dataTaxonomy ) {
                $genres[] = [
                    'name' => $dataTaxonomy->name,
                    'link' =>  $dataTaxonomy->slug
                ];
            }
            $link = 'movie/' . $data->post_title;

            if( $data->post_type == 'tv_show'  ) {
                $queryChanel = "SELECT wt.description, wp.object_id FROM `wp_term_relationships` wp
                LEFT JOIN wp_term_taxonomy wt ON wt.term_taxonomy_id = wp.term_taxonomy_id
                WHERE wt.taxonomy = 'category' AND wt.description != '' AND wp.object_id = ". $data->ID .";";
                $dataChanel = DB::select($queryChanel);
                
                if( count($dataChanel) > 0 ) {
                    $chanel = $dataChanel[0]->description;
                    $newChanel = explode('src="', $chanel);
                    $newChanel = explode('" alt', $newChanel[1]);
                    $newChanel = $newChanel[0];
                    $chanel = 'https://image002.modooup.com' . $newChanel;
                } else {
                    $chanel = env('IMAGE_PLACEHOLDER');
                }

                $queryEpisode = "SELECT meta_key, meta_value FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $data->ID . " LIMIT 1;";
                $dataEpisode = DB::select($queryEpisode);
                $episodeId = '';
                if( count($dataEpisode) > 0 ) {
                    $episodeData = $dataEpisode[0]->meta_value;
                    $episodeData = unserialize($episodeData);

                    $lastSeason = end($episodeData);
                    $seasonNumber = $lastSeason['name'];      

                    $episodeId = end($lastSeason['episodes']);
                    if( $episodeId != '' ) {
                        $queryMetaTv = "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ". $episodeId .";";
                        $dataMetaTvs = DB::select($queryMetaTv);
                        foreach($dataMetaTvs as $dataMetaTv) {
                            if( $dataMetaTv->meta_key == '_episode_release_date' ) {
                                if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMetaTv->meta_value)) {
                                    $newDataReleaseDateTv = explode('-', $dataMetaTv->meta_value);
                                    $releaseDate = $newDataReleaseDateTv[0];
                                } else {
                                    $releaseDate = $dataMetaTv->meta_value > 0 ? date('Y-m-d', $dataMetaTv->meta_value) : date('Y-m-d');
                                }
                            }
            
                            if( $dataMetaTv->meta_key == '_episode_number' ) {
                                $episodeNumber = $dataMetaTv->meta_value;
                            }
                        }

                        $selectTitleEpisode = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM wp_posts p ";
                        $whereTitleEpisode = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
                        $whereTitleSub = " AND p.ID='". $episodeId ."' ";
            
                        $queryTitle = $selectTitleEpisode . $whereTitleEpisode . $whereTitleSub;
                        $dataEpisoTitle = DB::select($queryTitle);
                        
                        if( count($dataEpisoTitle) > 0 ) {
                            $link = 'episode/' . $dataEpisoTitle[0]->post_title;
                        }
                    }
                } else {
                    $link = '';
                    $episodeNumber = '';
                    $seasonNumber = '';
                }
                $item = [
                    'postType'  => $data->post_type,
                    'id' => $data->ID,
                    'year' => $releaseDate,
                    'genres' => $genres,
                    'title' => $data->post_title,
                    'originalTitle' => $data->original_title,
                    'description' => $data->post_content,
                    'src' => $src,
                    'link' => $link,
                    'duration' => $movieRunTime,
                    'chanelImage' => $chanel,
                    'seasonNumber' => $seasonNumber,
                    'episodeNumber' => $episodeNumber,
                    'postDateGmt' => $data->post_date_gmt,
                    'postDate' => $data->post_date
                ];
            }
            $items[$key] = $item;
        }
        return $items;
    }
}