<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SearchService {

    /** @param MovieService $movieService */
    protected $movieService;

    /**
     * @param MovieService $movieService
     */
    public function __construct(MovieService $movieService) {
        $this->movieService = $movieService;
    }

    public function getItems($query) {
        $items = [];
        $datas = DB::select($query);
        $chanel = '';
        $seasonNumber = '';
        $episodeNumber = '';

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
                'slug' => $data->post_name,
                'originalTitle' => $data->original_title,
                'link' => $link,
                'chanelImage' => $chanel,
                'seasonNumber' => $seasonNumber,
                'episodeNumber' => $episodeNumber
            ] + ($metadata[$data->ID] ?? []);
        }
        return $items;
    }
}