<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\HelperService;
use Illuminate\Support\Facades\Cache;
class TvshowService {
    
    protected $helperService;
    protected $imageUrlUpload;

    public function __construct(HelperService $helperService)
    {
        $this->helperService = $helperService;
        $this->imageUrlUpload = env('IMAGE_URL_UPLOAD');
    }
    
    /**
     * @param string $type
     * @return array
     */
    public function getTopWeeks(string $type = '')
    {
        if( $type == '' ) {
            $sql = 'SELECT p.ID as id, p.post_title as tvshowTitle, p.post_date as postDate FROM wp_posts p LEFT JOIN wp_most_popular mp ON p.ID = mp.post_id WHERE p.post_type = \'tv_show\' AND p.post_status = \'publish\' ORDER BY mp.7_day_stats DESC LIMIT 5';
        }    
        elseif ($type == 'ott-web') {
            $sql = "SELECT p.ID AS id, p.post_title AS tvshowTitle, p.post_date as postDate FROM wp_posts p
            LEFT JOIN wp_most_popular mp ON p.ID = mp.post_id
            LEFT JOIN wp_term_relationships tr ON tr.object_id = mp.post_id
            LEFT JOIN wp_term_taxonomy tx ON tr.term_taxonomy_id = tx.term_taxonomy_id
            WHERE p.post_type = 'tv_show' AND tx.parent = 280
            ORDER BY mp.7_day_stats DESC LIMIT 5;";
        } else {
            $sql = "SELECT p.ID as id, p.post_title as tvshowTitle, p.post_date as postDate FROM wp_posts p
            LEFT JOIN wp_most_popular mp ON p.ID = mp.post_id
            LEFT JOIN wp_term_relationships tr ON tr.object_id = mp.post_id
            LEFT JOIN wp_term_taxonomy tx ON tr.term_taxonomy_id = tx.term_taxonomy_id
            LEFT JOIN wp_terms t ON t.term_id = tx.term_id
            WHERE p.post_type = 'tv_show' AND t.slug = '{$type}' AND p.post_status = 'publish'
            ORDER BY mp.7_day_stats DESC LIMIT 5;";
        }

        return DB::select($sql);
    }

    /**
     * @param string $type
     * @return array
     */
    public function getTopMonths(string $type = '')
    {
        if( $type == '' ) {
            $sql = 'SELECT p.ID as id, p.post_title as tvshowTitle, p.post_date as postDate FROM wp_posts p LEFT JOIN wp_most_popular mp ON p.ID = mp.post_id WHERE p.post_type = \'tv_show\' AND p.post_status = \'publish\' ORDER BY mp.30_day_stats DESC LIMIT 5';
        } else {
            $sql = "SELECT p.ID as id, p.post_title as tvshowTitle, p.post_date as postDate FROM wp_posts p
            LEFT JOIN wp_most_popular mp ON p.ID = mp.post_id
            LEFT JOIN wp_term_relationships tr ON tr.object_id = mp.post_id
            LEFT JOIN wp_term_taxonomy tx ON tr.term_taxonomy_id = tx.term_taxonomy_id
            LEFT JOIN wp_terms t ON t.term_id = tx.term_id
            WHERE p.post_type = 'tv_show' AND t.slug = '{$type}' AND p.post_status = 'publish'
            ORDER BY mp.30_day_stats DESC LIMIT 5;";
        }

        return DB::select($sql);
    }

    /**
     * @param string $type
     * @return array
     */
    public function getPopulars(string $type = '')
    {
        if( $type == '' ) {
            $sql = 'SELECT p.ID as id, p.post_title as tvshowTitle, p.post_date as postDate FROM wp_posts p LEFT JOIN wp_most_popular mp ON p.ID = mp.post_id WHERE p.post_type = \'tv_show\' AND p.post_status = \'publish\' ORDER BY mp.7_day_stats DESC LIMIT 5';
        } else {
            $sql = "SELECT p.ID as id, p.post_title as tvshowTitle, p.post_date as postDate FROM wp_posts p
                LEFT JOIN wp_most_popular mp ON p.ID = mp.post_id
                LEFT JOIN wp_term_relationships tr ON tr.object_id = mp.post_id
                LEFT JOIN wp_term_taxonomy tx on tr.term_taxonomy_id = tx.term_taxonomy_id
                LEFT JOIN wp_terms t ON t.term_id = tx.term_id
                WHERE p.post_type = 'tv_show' AND t.slug = '{$type}' AND p.post_status = 'publish'
                ORDER BY mp.7_day_stats DESC LIMIT 5;";
        }
        
        return DB::select($sql);
    }

    public function getItems($query='', $type='') {
        $items = [];
        $dataItems = DB::select($query);
        $releaseDate = date('Y-M-D');
        $imageUrlUpload = env('IMAGE_URL_UPLOAD');
        $link = '';
        $srcSet = [];
        $src= '';
        $originalTitle = '';
        $episodeTitle = '';
        $episodeName = '';
        $tvShowSlug = '';
        $episodeNumber = '';
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

            if( $type == '' || in_array($type , config('constants.categoryTvshowKoreas')) || in_array($type , config('constants.chanelList')) ) {
                $queryByType = '';
            } else {
                $queryByType =  "AND (t.slug = '" . $type . "' OR t.name = '" . $type . "'   )" ;
            }
            $queryTaxonomy = "SELECT t.name, t.slug FROM `wp_posts` p
                        LEFT JOIN wp_term_relationships t_r on t_r.object_id = p.ID
                        LEFT JOIN wp_term_taxonomy tx on t_r.term_taxonomy_id = tx.term_taxonomy_id AND tx.taxonomy = 'tv_show_genre' 
                        LEFT JOIN wp_terms t on tx.term_id = t.term_id
                        WHERE t.name != 'featured' AND t.name != '' ";
            $queryTaxonomy = $queryTaxonomy .  $queryByType;
            $queryTaxonomy = $queryTaxonomy . " AND p.ID = ". $dataItem->ID ." ORDER BY t.name ASC;";
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

            $selectTitleEpisode = "SELECT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt, p.post_date FROM wp_posts p ";
            $whereTitleEpisode = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
            $whereTitleSub = " AND p.ID='". $episodeId ."' ";

            $queryTitle = $selectTitleEpisode . $whereTitleEpisode . $whereTitleSub;
            $dataEpisoTitle = DB::select($queryTitle);
            
            if( count($dataEpisoTitle) > 0 ) {
                $episodeTitle = $dataEpisoTitle[0]->post_title;
                $tvShowSlug = $dataEpisoTitle[0]->post_name;
                $episodeName = $dataEpisoTitle[0]->post_name;
                $link = 'episode/' . $episodeTitle;                
            }
            
            //$srcSet = $this->helperService->getAttachmentsByPostId($dataItem->ID);
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
                'slug' => $episodeName,
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

    public function getSeasons($dataEpisode=array()) {
        $seasons = [];
        if( count($dataEpisode) > 0 ) {
            $episodeData = $dataEpisode[0]->meta_value;
            $episodeData = unserialize($episodeData);
            arsort($episodeData);
            //Get seasons
            foreach ( $episodeData as $episodeSeasonData ) {
                $episodeDatas = $episodeSeasonData['episodes'];
                arsort($episodeDatas);
                $episodes = [];
                foreach ( $episodeDatas as $episodeSubData ) {
                    $queryEpiso = "SELECT p.ID, p.post_title, p.post_name, p.post_date_gmt, p.post_date FROM wp_posts p WHERE ((p.post_type = 'episode' AND (p.post_status = 'publish'))) AND p.ID = ". $episodeSubData ." LIMIT 1;";
                    $dataEpiso = DB::select($queryEpiso);
                    if( count($dataEpiso) > 0 ) {
                        $queryThumb = "SELECT meta_value, meta_key, post_id FROM wp_postmeta WHERE meta_key='_thumbnail_id' AND post_id=". $dataEpiso[0]->ID;
                        $dataThumb = DB::select($queryThumb);
                        $thumbnails = [
                            'src' => '',
                            'srcSet' => ''
                        ];
                        if ( count($dataThumb) > 0) {
                            $thumbnails = $this->getTvShowThumbnail((int) $dataThumb[0]->meta_value);
                        }
                        $episodes[] = [
                            'id' => $episodeSubData,
                            'title' => count($dataEpiso) > 0 ? $dataEpiso[0]->post_title : '',
                            'slug' => count($dataEpiso) > 0 ? $dataEpiso[0]->post_name : '',
                            'src' => $thumbnails['src'],
                            'srcSet' => $thumbnails['srcSet'],
                            'postDateGmt' => count($dataEpiso) > 0 ? $dataEpiso[0]->post_date_gmt : '',
                            'postDate' => count($dataEpiso) > 0 ? $dataEpiso[0]->post_date : '',
                        ];
                    }
                }
                $seasons[] = [
                    'name' => $episodeSeasonData['name'],
                    'year' => $episodeSeasonData['year'],
                    'number' => count($episodeSeasonData['episodes']),
                    'episodes' => $episodes
                ];
            }
        }
        return $seasons;
    }

    public function getWhereByType($type = '') {
        if( $type  == 'ott-web'  ) {
            $whereByType = "SELECT tr.object_id FROM wp_terms t 
            INNER JOIN wp_term_taxonomy tx ON tx.term_id = t.term_id AND tx.taxonomy = 'category' 
            INNER JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tx.term_taxonomy_id 
            INNER JOIN wp_posts p ON p.ID = tr.object_id AND p.post_type = 'tv_show' 
            AND p.post_status = 'publish' WHERE tx.parent = 280 ";
            
            $where = " AND p.ID IN ( ". $whereByType ." ) ";
        } else {
            $whereByType = "SELECT tr.object_id FROM wp_terms t 
            INNER JOIN wp_term_taxonomy tx ON tx.term_id = t.term_id AND tx.taxonomy = 'category' 
            INNER JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tx.term_taxonomy_id 
            INNER JOIN wp_posts p ON p.ID = tr.object_id AND p.post_type = 'tv_show' 
            AND p.post_status = 'publish' WHERE tx.parent = 280 AND t.slug = '" . $type . "'";
            $where = " AND p.ID IN ( ". $whereByType ." ) ";
        }
        return $where;
    }

    public function getTvShowRandom() {
        $queryKoreaSlider = "SELECT ID, post_title, post_name, post_type, post_date , IF(pm1.meta_value IS NOT NULL , CAST( pm1.meta_value AS UNSIGNED ) , 0 ) as sort_order,
                                    IF(pm2.meta_value IS NOT NULL , CAST( pm2.meta_value AS UNSIGNED ) , 0 ) as slide_img
                                    FROM wp_posts as p
                                    INNER JOIN wp_postmeta as pm0 ON p.ID = pm0.post_id AND pm0.meta_key='_korea_featured' and pm0.meta_value=1
                                    LEFT JOIN wp_postmeta as pm1 ON p.ID = pm1.post_id and pm1.meta_key= '_sort_order_korea'
                                    LEFT JOIN wp_postmeta as pm2 ON p.ID = pm2.post_id and pm2.meta_key= '_korea_image_id'
                                    ORDER BY sort_order ASC, post_date DESC;";

        $queryUsaSlider = "SELECT ID, post_title, post_name, post_type, post_date , IF(pm1.meta_value IS NOT NULL , CAST( pm1.meta_value AS UNSIGNED ) , 0 ) as sort_order,
                    IF(pm2.meta_value IS NOT NULL , CAST( pm2.meta_value AS UNSIGNED ) , 0 ) as slide_img
                    FROM wp_posts as p
                    INNER JOIN wp_postmeta as pm0 ON p.ID = pm0.post_id AND pm0.meta_key='_ott_featured' and pm0.meta_value=1
                    LEFT JOIN wp_postmeta as pm1 ON p.ID = pm1.post_id and pm1.meta_key= '_sort_order_ott'
                    LEFT JOIN wp_postmeta as pm2 ON p.ID = pm2.post_id and pm2.meta_key= '_ott_image_id'
                    ORDER BY sort_order ASC, post_date DESC";
        
        $randomSlider[0] = [ 'title' => '오늘의 한국 넷플릭스 순위', 'query' => $queryKoreaSlider ];
        $randomSlider[1] = [ 'title' => '오늘의 미국 넷플릭스 순위', 'query' => $queryUsaSlider ];
        $queryRandom = $randomSlider[rand(0,1)];
        return $this->getSliderRandomItems($queryRandom);
        //return $this->helperService->getSliderItems($queryRandom);
    }

    public function getSliderRandomItems($queryRandom) {
        $query = $queryRandom['query'];
        $sliders = [];
        $sliderDatas = DB::select($query);
        $src = '';
        $slug = '';
        foreach ( $sliderDatas as $sliderData ) {
            $dataQuery = "SELECT * FROM `wp_postmeta` pm 
            LEFT JOIN wp_posts p ON p.ID = pm.post_id 
            WHERE pm.meta_key = '_wp_attached_file' AND p.post_type = 'attachment' AND p.ID = " . $sliderData->slide_img . " ORDER BY p.post_date DESC LIMIT 1;";

            $dataResult = DB::select($dataQuery);
            if( count($dataResult) > 0 ) {
                $src = $dataResult[0]->meta_value;
            }

            $titleSlider = $sliderData->post_title;
            $slug = $sliderData->post_name;
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
                
                $select = "SELECT p.ID, p.post_title, p.post_name, p.original_title, p.post_content, p.post_date_gmt FROM wp_posts p ";
                $where = " WHERE  ((p.post_type = 'episode' AND (p.post_status = 'publish'))) ";
                $whereTitle = " AND p.ID='". $episodeId ."' ";
    
                $where = $where . $whereTitle;
                $query = $select . $where;
                $dataEpisoSlider = DB::select($query);
                
                if( count($dataEpisoSlider) > 0 ) {
                    $linkSlider = 'episode/' . $dataEpisoSlider[0]->post_title;
                    $slug = $dataEpisoSlider[0]->post_name;
                }

                $queryEpisodeNumber = "SELECT meta_value FROM wp_postmeta WHERE meta_key = '_episode_number' AND post_id = " . $episodeId . ";";
                $dataEpisodeNumber = DB::select($queryEpisodeNumber);
                $episodeNumber = $dataEpisodeNumber[0]->meta_value;
            }

            $sliders['items'][] = [
                'id' => $sliderData->ID,
                'year' => $year,
                'title' => $titleSlider,
                'link' => $linkSlider,
                'slug' => $sliderData->post_name,
                'src' => $this->imageUrlUpload.$src,
                'slug' => $slug,
                'seasonNumber' => $seasonNumber,
                'episodeNumber' => $episodeNumber,
                'post_type' => $sliderData->post_type
            ];
            $sliders['title'] = $queryRandom['title'];
        }
        return $sliders;
    }

    /**
     * Return array with format [postId => ['originalTitle', 'seasonNumber, 'lastEpisode', 'src', 'srcSet']]
     * @param array $postIds
     * @param array $thumbnailPostIds
     * @return array
     */
    public function getTvShowsMetaData(array $postIds, $thumbnailPostIds)
    {
        $data = [];
        $fields = [
            '_original_title',
            '_thumbnail_id',
            '_seasons'
        ];

        $queryMeta = 'SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE post_id IN (' . \implode(',', $postIds) . ') AND meta_key IN (\'' . \implode('\',\'', $fields) . '\') GROUP BY post_id, meta_key LIMIT ' . (\count($postIds) * \count($fields));
        $metaData = DB::select($queryMeta);

        foreach ($metaData as $value) {
            $postId = (int) $value->post_id;
            if (!\array_key_exists($postId, $data)) {
                $data[$postId] = [];
            }
            
            if ($value->meta_key == '_original_title') {
                $data[$postId]['originalTitle'] = $value->meta_value;
            }
            elseif ($value->meta_key == '_seasons') {
                $seasons = \unserialize($value->meta_value);
                $lastSeason = \end($seasons);
                $data[$postId]['seasonNumber'] = $lastSeason['name'];
                $idx = \count($lastSeason['episodes']) - 1;
                do {
                    $episodeId = (int) $lastSeason['episodes'][$idx];
                    $lastEpisode = $this->getEpisodeTitleAndSlug($episodeId);
                    $idx --;
                } while ($idx >= 0 && empty($lastEpisode));
                
                if (!empty($lastEpisode)) {
                    $data[$postId]['lastEpisode'] = $lastEpisode + ['id' => $episodeId];
                }
            }
            elseif ($value->meta_key == '_thumbnail_id' && \in_array($postId, $thumbnailPostIds)) {
                $thumbnails = $this->getTvShowThumbnail((int) $value->meta_value);
                $data[$postId] += $thumbnails;
            }
        }

        return $data;
    }

    /**
     * Return array with format [id => ['episodeNumber']]
     * @param array $episodeIds
     * @return array
     */
    public function getEpisodeMetadata(array $episodeIds)
    {
        $sql = 'SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE post_id IN (' . \implode(',', $episodeIds) . ') AND meta_key = \'_episode_number\' LIMIT ' . (\count($episodeIds));
        $data = [];

        foreach (DB::select($sql) as $value) {
            $episodeId = (int) $value->post_id;
            $data[$episodeId] = [
                'episodeNumber' => $value->meta_value
            ];
        }

        return $data;
    }

    /**
     * Return array with format postId => [['name', 'slug'], ['name', 'slug']]
     * @param array $tvShowIds
     * @return array
     */
    public function getTvshowsGenres(array $tvShowIds)
    {
        $data = [];
        $sql = 'SELECT a.object_id , c.name, c.slug 
            FROM wp_term_relationships a 
            LEFT JOIN wp_term_taxonomy b ON a.term_taxonomy_id = b.term_taxonomy_id 
            LEFT JOIN wp_terms c ON b.term_id = c.term_id 
            WHERE a.object_id IN (' . \implode(',', $tvShowIds) . ') AND b.taxonomy = \'tv_show_genre\' AND c.name != \'featured\' AND c.name != \'\'';
        $queryData = DB::select($sql);
        foreach ($queryData as $value) {
            if (!isset($data[$value->object_id])) {
                $data[$value->object_id] = [];
            }
            $data[(int) $value->object_id][] = [
                'name' => $value->name,
                'slug' => $value->slug
            ];
        }

        return $data;
    }

    /**
     * @param array $tvshowIds
     * @param string $type
     * @return string
     */
    public function getTvShowChannelImage(array $tvshowIds, string $type)
    {
        $constantChanelList = config('constants.chanelList');
        if( in_array($type, $constantChanelList) ) {
            $sql = "SELECT wt.description, wp.object_id FROM `wp_term_relationships` wp
            LEFT JOIN wp_term_taxonomy wt ON wt.term_taxonomy_id = wp.term_taxonomy_id
            RIGHT JOIN wp_terms t ON t.term_id = wt.term_id AND t.slug = '" . $type . "'
            WHERE wt.taxonomy = 'category' AND wt.description != '' AND wp.object_id IN (". \implode(',', $tvshowIds) . ") group by wp.object_id LIMIT " . count($tvshowIds) . ";";
        } else {
            $sql = "SELECT wt.description, wp.object_id FROM `wp_term_relationships` wp
            LEFT JOIN wp_term_taxonomy wt ON wt.term_taxonomy_id = wp.term_taxonomy_id
            WHERE wt.taxonomy = 'category' AND wt.description != '' AND wp.object_id IN (" . \implode(',', $tvshowIds) . ") group by wp.object_id LIMIT " . count($tvshowIds) . ";";
        }

        $data = [];
        foreach (DB::select($sql) as $item) {
            if (!empty($item->description)) {
                $parts = \explode('src="', $item->description);
                $parts = \explode('" alt', $parts[1]);
                $chanel = 'https://image002.modooup.com' . $parts[0];
            } else {
                $chanel = env('IMAGE_PLACEHOLDER');
            }
            $data[$item->object_id] = $chanel;
        }

        return $data;
    }

    /**
     * @param int $episodeId
     * @return int|null
     */
    public function getTvShowId(int $episodeId)
    {
        $sql = "SELECT meta_value FROM wp_postmeta WHERE post_id = {$episodeId} AND meta_key='_tv_show_id' LIMIT 1";
        $data = DB::selectOne($sql);
        return $data ? (int) $data->meta_value : null;
    }

    /**
     * @param int $postmetaId
     * @return array
     */
    private function getTvShowThumbnail(int $postmetaId)
    {
        $data = [
            'src' => '',
            'srcSet' => ''
        ];
        $sql = 'SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ' . $postmetaId . ' AND meta_key IN (\'_wp_attached_file\', \'_wp_attachment_metadata\') ORDER BY meta_id DESC LIMIT 2';
        $metaData = DB::select($sql);
        foreach ($metaData as $value) {
            if ($value->meta_key == '_wp_attached_file') {
                $data['src'] = $this->imageUrlUpload . $value->meta_value;
            }
            elseif ($value->meta_key == '_wp_attachment_metadata') {
                $srcSetVal = \unserialize($value->meta_value);
                if (!isset($monthYear)) {
                    $monthYear = \substr($srcSetVal['file'], 0, (\strpos($srcSetVal['file'], 'image_webp') !== false) ? 19 : 8);
                }

                $srcSet = $this->imageUrlUpload . $srcSetVal['file'] . ' ' . $srcSetVal['width'] . 'w';
                if (isset($srcSetVal['sizes'])) {
                    foreach ($srcSetVal['sizes'] as $size) {
                        $srcSet .= ', ' . $this->imageUrlUpload . $monthYear . $size['file'] . ' ' . $size['width'] . 'w';
                    }
                }
                $data['srcSet'] = $srcSet;
            }
        }

        return $data;
    }

    /**
     * @param int $episodeId
     * @return string
     */
    private function getEpisodeTitleAndSlug(int $episodeId)
    {
        $sql = "SELECT post_title as title, post_name as slug FROM wp_posts WHERE ID = {$episodeId} AND post_status = 'publish' LIMIT 1";
        $result = DB::selectOne($sql);
        return empty($result) ? [] : \get_object_vars($result);
    }

    /**
     * @param int $tvShowId
     * @return array
     */
    private function getEpisodes(int $tvShowId)
    {
        $sql = "";
    }
}