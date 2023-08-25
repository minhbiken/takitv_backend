<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
class TvshowService {
    public function getTopWeeks()
    {
        $queryTopWeek = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM `wp_most_popular` mp
                            LEFT JOIN wp_posts p ON p.ID = mp.post_id
                            WHERE mp.post_type = 'tv_show' AND p.post_title != '' AND mp.post_id != '' AND p.ID != ''
                            ORDER BY mp.7_day_stats DESC
                            LIMIT 5";
        return $this->getItems($queryTopWeek);
    }

    public function getTopMonths()
    {
        $queryTopMonth = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM `wp_most_popular` mp
                            LEFT JOIN wp_posts p ON p.ID = mp.post_id
                            WHERE mp.post_type = 'tv_show' AND p.post_title != '' AND mp.post_id != '' AND p.ID != ''
                            ORDER BY mp.30_day_stats DESC
                            LIMIT 5";
        return $this->getItems($queryTopMonth);
    }

    public function getPopulars() {
        $queryPopular = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM `wp_most_popular` wp
                            LEFT JOIN wp_posts p ON p.ID = wp.post_id 
                            WHERE wp.post_type = 'tv_show' AND wp.post_id != '' AND p.ID != ''
                            ORDER BY wp.`7_day_stats` DESC
                            LIMIT 5";
        return $this->getItems($queryPopular);
    }

    public function getWebOTT() {
        $query = "SELECT * FROM `wp_term_taxonomy` tx
        LEFT JOIN wp_terms t ON t.term_id = tx.term_id AND t.slug = 'ott-web'
        LEFT JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tx.term_taxonomy_id
        LEFT JOIN wp_posts p ON p.ID = tr.object_id
        WHERE tx.taxonomy = 'category' AND tx.parent = 280 AND p.post_status = 'publish'
        ORDER BY p.post_date DESC;";
        return $this->getItems($query);
    }
    public function getItems($query) {
        $items = [];
        $dataItems = DB::select($query);
        $releaseDate = date('Y-M-D');
        $imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $link = '';
        foreach ( $dataItems as $dataItem ) {
            $queryEpisode = "SELECT * FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $dataItem->ID . " LIMIT 1;";
            $dataEpisode = DB::select($queryEpisode);
            
            $episodeData = $dataEpisode[0]->meta_value;
            $episodeData = unserialize($episodeData);

            $lastSeason = end($episodeData);
            $seasonNumber = $lastSeason['name'];      

            $episodeId = end($lastSeason['episodes']);
            $queryMeta = "SELECT * FROM wp_postmeta WHERE post_id = ". $episodeId .";";

            $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $dataItem->ID .";";
            $dataSrcMeta = DB::select($querySrcMeta);
            
            $src = $imageUrlUpload.$dataSrcMeta[0]->meta_value;

            $dataMetas = DB::select($queryMeta);

            foreach($dataMetas as $dataMeta) {
                if( $dataMeta->meta_key == '_episode_release_date' ) {
                    if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMeta->meta_value)) {
                        $newDataReleaseDate = explode('-', $dataMeta->meta_value);
                        $releaseDate = $newDataReleaseDate[0];
                    } else {
                        $releaseDate = $dataMeta->meta_value > 0 ? date('Y-m-d', $dataMeta->meta_value) : date('Y-m-d');
                    }
                }

                if( $dataMeta->meta_key == '_episode_number' ) {
                    $episodeNumber = $dataMeta->meta_value;
                }
            }

            $queryTaxonomy = "SELECT * FROM `wp_posts` p
                        left join wp_term_relationships t_r on t_r.object_id = p.ID
                        left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'tv_show_genre' 
                        left join wp_terms t on tx.term_id = t.term_id
                        where t.name != 'featured' AND t.name != '' AND p.ID = ". $dataItem->ID .";";
            $dataTaxonomys = DB::select($queryTaxonomy);

            $genres = [];
            foreach( $dataTaxonomys as $key => $dataTaxonomy ) {
                $genres[$key] = [
                    'name' => $dataTaxonomy->name,
                    'link' =>  $dataTaxonomy->slug
                ];
            }

            $queryChanel = "SELECT * FROM `wp_term_relationships` wp
                        LEFT JOIN wp_term_taxonomy wt ON wt.term_taxonomy_id = wp.term_taxonomy_id
                        WHERE wt.taxonomy = 'category' AND wt.description != '' AND wp.object_id = ". $dataItem->ID .";";
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

            $selectTitleEpisode = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM wp_posts p ";
            $whereTitleEpisode = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
            $whereTitleSub = " AND p.ID='". $episodeId ."' ";

            $queryTitle = $selectTitleEpisode . $whereTitleEpisode . $whereTitleSub;
            $dataEpisoTitle = DB::select($queryTitle);
            
            if( count($dataEpisoTitle) > 0 ) {
                $link = 'episode/' . $dataEpisoTitle[0]->post_title."/";
            }
            
            $items[] = [
                'id' => $dataItem->ID,
                'year' => $releaseDate,
                'genres' => $genres,
                'title' => $dataItem->post_title,
                'originalTitle' => $dataItem->original_title,
                'description' => $dataItem->post_content,
                'src' => $src,
                'link' => $link,
                'chanelImage' => $chanel,
                'seasonNumber' => $seasonNumber,
                'episodeNumber' => $episodeNumber,
                'postDateGmt' => $dataItem->post_date_gmt
            ];
        }
        return $items;
    }

    public function getSeasons($query) {
        $seasons = [];
        $dataEpisode = DB::select($query);
        
        $episodeData = $dataEpisode[0]->meta_value;
        $episodeData = unserialize($episodeData);
        arsort($episodeData);
        //Get seasons
        foreach ( $episodeData as $episodeSeasonData ) {
            $episodeDatas = $episodeSeasonData['episodes'];
            arsort($episodeDatas);
            $episodes = [];
            foreach ( $episodeDatas as $episodeSubData ) {
                $queryEpiso = "SELECT p.ID, p.post_title, p.post_date_gmt FROM wp_posts p WHERE ((p.post_type = 'episode' AND (p.post_status = 'publish'))) AND p.ID = ". $episodeSubData ." LIMIT 1;";
                $dataEpiso = DB::select($queryEpiso);
                $episodes[] = [
                    'id' => $episodeSubData,
                    'title' => count($dataEpiso) > 0 ? $dataEpiso[0]->post_title : '',
                    'postDateGmt' => count($dataEpiso) > 0 ? $dataEpiso[0]->post_date_gmt : '',
                ];
            }
            
            $seasons[] = [
                'name' => $episodeSeasonData['name'],
                'year' => $episodeSeasonData['year'],
                'number' => count($episodeSeasonData['episodes']),
                'episodes' => $episodes
            ];
        }
        return $seasons;
    }
}