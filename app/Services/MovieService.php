<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\HelperService;
class MovieService {
    protected $helperService;
    protected $lifeTime;
    protected $imageUrlUpload;
    public function __construct(HelperService $helperService)
    {
        $this->helperService = $helperService;
        $this->lifeTime = env('SESSION_LIFETIME');
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
    }

    public function getTopWeeks()
    {
        $queryTopWeek = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM `wp_most_popular` mp
                            LEFT JOIN wp_posts p ON p.ID = mp.post_id
                            WHERE p.post_type = 'movie' AND p.post_title != '' AND mp.post_id != '' AND p.ID != ''
                            ORDER BY mp.7_day_stats DESC
                            LIMIT 5";
        return $this->getItems($queryTopWeek);
    }

    public function getPopulars() {
        $queryPopular = "SELECT p.ID, p.post_title, p.original_title, p.post_content, p.post_date_gmt FROM `wp_most_popular` wp
                            LEFT JOIN wp_posts p ON p.ID = wp.post_id 
                            WHERE p.post_type = 'movie' AND wp.post_id != '' AND p.ID != ''

                            ORDER BY wp.`1_day_stats` DESC
                            LIMIT 6";
        return $this->getItems($queryPopular);
    }

    public function getItems($query) {
        $items = [];
        $dataItems = DB::select($query);
        $releaseDate = '2023';
        $imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $movieRunTime = '';
        $outlink = '';
        $srcSet = [];
        if( count($dataItems) > 0 ) {
            foreach ( $dataItems as $dataItem ) {
                $queryMeta = "SELECT * FROM wp_postmeta WHERE post_id = ". $dataItem->ID .";";
                $dataMetas = DB::select($queryMeta);
                if( count($dataMetas) > 0 ) {
                    foreach ( $dataMetas as $dataMeta ) {
                        if( $dataMeta->meta_key == '_movie_release_date' ) {
                            if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMeta->meta_value)) {
                                $newDataReleaseDate = explode('-', $dataMeta->meta_value);
                                $releaseDate = $newDataReleaseDate[0];
                            } else {
                                $releaseDate = $dataMeta->meta_value > 0 ? date('Y', $dataMeta->meta_value) : '2023';
                            }
                        }
                    
                        if( $dataMeta->meta_key == '_movie_run_time' ) {
                            $movieRunTime = $dataMeta->meta_value;
                        }
                    }
                }


                $querySrcMeta = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
                            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $dataItem->ID .";";
                $dataSrcMeta = DB::select($querySrcMeta);

                $src = $imageUrlUpload.$dataSrcMeta[0]->meta_value;

                $queryTaxonomy = "SELECT * FROM `wp_posts` p
                                    left join wp_term_relationships t_r on t_r.object_id = p.ID
                                    left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'movie_genre'
                                    left join wp_terms t on tx.term_id = t.term_id
                where t.name != 'featured' AND t.name != '' AND p.ID = ". $dataItem->ID .";";
                $dataTaxonomys = DB::select($queryTaxonomy);

                $genres = [];
                if ( count($dataTaxonomys) > 0 ) {
                    foreach( $dataTaxonomys as $dataTaxonomy ) {
                        $genres[] = [
                            'name' => $dataTaxonomy->name,
                            'link' =>  $dataTaxonomy->slug
                        ];
                    }
                }

                $srcSet = $this->helperService->getAttachmentsByPostId($dataItem->ID);
                
                $items[] = [
                    'year' => $releaseDate,
                    'genres' => $genres,
                    'title' => $dataItem->post_title,
                    'originalTitle' => $dataItem->original_title,
                    'src' => $src,
                    'srcSet' => $srcSet,
                    'movieRunTime' => $movieRunTime,
                    'outlink' => $outlink
                ];
            }
        }
        return $items;
    }
}