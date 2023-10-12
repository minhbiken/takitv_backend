<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SearchService {

    /** @param MovieService $movieService */
    protected $movieService;
    /** @param HelperService $helperService */
    protected $helperService;
    /**
     * @param MovieService $movieService
     */
    public function __construct(MovieService $movieService, HelperService $helperService) {
        $this->movieService = $movieService;
        $this->helperService = $helperService;
    }

    public function getItems($query) {
        $items = [];
        $datas = DB::select($query);
        $chanel = '';
        $seasonNumber = '';
        $episodeNumber = '';
        $slug = '';
        $postIds = \array_map(fn($item) => $item->ID, $datas);
        $metadata = $this->movieService->getMoviesMetadata($postIds, ['_thumbnail_id']);
        foreach( $datas as $data ) {     
            $postName = urldecode($data->post_name);
            $link = 'movie/' . $postName;

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
            
                            if( $dataMetaTv->meta_key == '_episode_number' ) {
                                $episodeNumber = $dataMetaTv->meta_value;
                            }
                        }

                        $selectTitleEpisode = "SELECT p.ID, p.post_name, p.post_title FROM wp_posts p ";
                        $whereTitleEpisode = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
                        $whereTitleSub = " AND p.ID='". $episodeId ."' ";
            
                        $queryTitle = $selectTitleEpisode . $whereTitleEpisode . $whereTitleSub;
                        $dataEpisoTitle = DB::select($queryTitle);
                        
                        if( count($dataEpisoTitle) > 0 ) {
                            $link = 'episode/' . $dataEpisoTitle[0]->post_title;
                            $slug = $dataEpisoTitle[0]->post_title;
                        }
                    }
                } else {
                    $link = '';
                    $episodeNumber = '';
                    $seasonNumber = '';
                }
            }
            $items[] = [
                'postType'  => $data->post_type,
                'id' => $data->ID,
                'title' => $data->post_title,
                'slug' => $slug,
                'originalTitle' => $data->original_title,
                'link' => $link,
                'chanelImage' => $chanel,
                'seasonNumber' => $seasonNumber,
                'episodeNumber' => $episodeNumber
            ] + ($metadata[$data->ID] ?? []);
        }
        return $items;
    }

    public function getTopWeeks($type='')
    {
        if( $type == '' ) {
            $queryByType = '';
        } else {
            $queryByType =  "AND (t.slug = '" . $type . "' OR t.name = '" . $type . "'   )" ;
        }
        $queryTopWeek = "SELECT DISTINCT(p.ID) as get_not_exist, p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt, p.post_date, mp.7_day_stats, p.post_status FROM wp_posts p
                        LEFT JOIN wp_most_popular mp ON p.ID = mp.post_id
                        LEFT JOIN wp_term_relationships tr ON tr.object_id = mp.post_id
                        LEFT JOIN wp_term_taxonomy tx on tr.term_taxonomy_id = tx.term_taxonomy_id
                        LEFT JOIN wp_terms t ON t.term_id = tx.term_id
                        WHERE p.post_type = 'tv_show' " . $queryByType . " AND (p.post_status = 'publish')
                        ORDER BY mp.7_day_stats DESC
                        LIMIT 5;";
                        
        $dataTopWeek = $this->getItemTopWeek($queryTopWeek);
        return $dataTopWeek;
    }

    public function getItemTopWeek($query='', $type='') {
        $items = [];
        $dataItems = DB::select($query);
        $releaseDate = date('Y-M-D');
        $imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $link = '';
        $srcSet = [];
        $src= '';
        $originalTitle = '';
        $episodeTitle = '';
        $tvShowSlug = '';
        foreach ( $dataItems as $dataItem ) {
            $queryOriginalTitle = "SELECT meta_key, meta_value FROM `wp_postmeta` WHERE meta_key = '_original_title' AND post_id =". $dataItem->ID . " LIMIT 1;";
            $dataOriginalTitle = DB::select($queryOriginalTitle);
            $originalTitle = $dataOriginalTitle[0]->meta_value;

            $queryEpisode = "SELECT meta_key, meta_value, post_id FROM `wp_postmeta` WHERE meta_key = '_seasons' AND post_id =". $dataItem->ID . " LIMIT 1;";
            $dataEpisode = DB::select($queryEpisode);
            
            $episodeData = $dataEpisode[0]->meta_value;
            $episodeData = unserialize($episodeData);

            $lastSeason = end($episodeData);
            $seasonNumber = $lastSeason['name'];      

            $episodeId = end($lastSeason['episodes']);
            $queryMeta = "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ". $episodeId .";";

            $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $dataItem->ID .";";
            $dataSrcMeta = DB::select($querySrcMeta);
            
            if ( count($dataSrcMeta) > 0 ) {
                $src = $imageUrlUpload.$dataSrcMeta[0]->meta_value;
            }

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

            $queryTaxonomy = "SELECT t.name, t.slug FROM `wp_posts` p
                        LEFT JOIN wp_term_relationships t_r on t_r.object_id = p.ID
                        LEFT JOIN wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'tv_show_genre' 
                        LEFT JOIN wp_terms t on tx.term_id = t.term_id
                        WHERE t.name != 'featured' AND t.name != '' AND p.ID = ". $dataItem->ID ." ORDER BY t.name ASC;";
            $dataTaxonomys = DB::select($queryTaxonomy);

            $genres = [];
            foreach( $dataTaxonomys as $key => $dataTaxonomy ) {
                $genres[$key] = [
                    'name' => $dataTaxonomy->name,
                    'link' =>  $dataTaxonomy->slug,
                    'slug' =>  $dataTaxonomy->slug
                ];
            }

            $constantChanelList = config('constants.chanelList');
            if( in_array($type, $constantChanelList) ) {
                $queryChanel = "SELECT wt.description, wp.object_id FROM `wp_term_relationships` wp
                LEFT JOIN wp_term_taxonomy wt ON wt.term_taxonomy_id = wp.term_taxonomy_id
                RIGHT JOIN wp_terms t ON t.term_id = wt.term_id AND t.slug = '" . $type . "'
                WHERE wt.taxonomy = 'category' AND wt.description != '' AND wp.object_id = ". $dataItem->ID .";";
            } else {
                $queryChanel = "SELECT wt.description, wp.object_id FROM `wp_term_relationships` wp
                LEFT JOIN wp_term_taxonomy wt ON wt.term_taxonomy_id = wp.term_taxonomy_id
                WHERE wt.taxonomy = 'category' AND wt.description != '' AND wp.object_id = ". $dataItem->ID .";";
            }

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

            $tvShowSlug = $dataItem->post_name;
            $link = 'tv-show/' . $tvShowSlug;   
            
            $srcSet = $this->helperService->getAttachmentsByPostId($dataItem->ID);
            $item = [
                'id' => $dataItem->ID,
                'year' => $releaseDate,
                'genres' => $genres,
                'tvshowTitle' => $dataItem->post_title,
                'tvShowSlug' => $tvShowSlug,
                'title' => $episodeTitle,
                'episodeId' => $episodeId,
                'originalTitle' => $originalTitle,
                'description' => $dataItem->post_content,
                'src' => $src,
                'srcSet' => $srcSet,
                'link' => $link,
                'slug' => $tvShowSlug,
                'chanelImage' => $chanel,
                'seasonNumber' => $seasonNumber,
                'episodeNumber' => $episodeNumber,
                'postDateGmt' => $dataItem->post_date_gmt,
                'postDate' => $dataItem->post_date
            ];
            $items[] = $item;
        }
        return $items;
    }

}