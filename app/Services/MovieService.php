<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
class MovieService {
    public function getTopWeeks()
    {
        $queryTopWeek = "SELECT p.ID, p.post_title FROM `wp_most_popular` mp
                            LEFT JOIN wp_posts p ON p.ID = mp.post_id
                            WHERE mp.post_type = 'movie' and p.post_title != ''
                            ORDER BY mp.7_day_stats DESC
                            LIMIT 5";
        return $this->getItems($queryTopWeek);
    }

    public function getPopulars() {
        $queryPopular = "SELECT p.ID, wp.post_type, wp.post_id, wp.1_day_stats, p.post_title FROM `wp_most_popular` wp
                            LEFT JOIN wp_posts p ON p.ID = wp.post_id 
                            WHERE wp.post_type = 'movie' AND p.ID != ''
                            ORDER BY wp.`1_day_stats` DESC
                            LIMIT 6";
        return $this->getItems($queryPopular);
    }

    public function getItems($query) {
        $items = [];
        $dataItems = DB::select($query);
        $releaseDate = '2023';
        if( count($dataItems) > 0 ) {
            foreach ( $dataItems as $dataItem ) {
                $queryMeta = "SELECT * FROM wp_postmeta WHERE meta_key = '_movie_release_date' and post_id = ". $dataItem->ID .";";
                $dataMetas = DB::select($queryMeta);
                if( count($dataMetas) > 0 ) {
                    foreach ( $dataMetas as $dataMeta ) {
                        if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/", $dataMeta->meta_value)) {
                            $newDataReleaseDate = explode('-', $dataMeta->meta_value);
                            $releaseDate = $newDataReleaseDate[0];
                        } else {
                            $releaseDate = $dataMeta->meta_value > 0 ? date('Y', $dataMeta->meta_value) : '2023';
                        }
                    }
                }

                $queryTaxonomy = "SELECT * FROM `wp_posts` p
                                    left join wp_term_relationships t_r on t_r.object_id = p.ID
                                    left join wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id
                                    left join wp_terms t on tx.term_id = t.term_id
                where t.name != 'featured' AND p.ID = ". $dataItem->ID .";";
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
                
                $items[] = [
                    'year' => $releaseDate,
                    'genres' => $genres,
                    'title' => $dataItem->post_title,
                ];
            }
        }
        return $items;
    }
}