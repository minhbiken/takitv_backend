<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
class HomepageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $imageUrlUpload = env('IMAGE_URL_UPLOAD');

        $sliderQuery = "SELECT meta_key, ID, post_title, post_name, post_type, post_date, meta_value, IF(pm.meta_value IS NOT NULL , CAST( pm.meta_value AS UNSIGNED ) , 0 ) as sort_order
        FROM wp_posts as p
        LEFT JOIN wp_postmeta as pm ON p.ID = pm.post_id and pm.meta_key= '_sort_order'
        WHERE ID IN ( SELECT object_id FROM `wp_term_relationships` WHERE term_taxonomy_id IN (17 , 43) ) 
            AND p.post_status = 'publish'
        ORDER BY sort_order ASC, post_date DESC;";
        $sliderData = DB::select($sliderQuery);

        $sliders = [];
        foreach ( $sliderData as $data ) {
            $dataQuery = "SELECT am.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' 
            LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_status = 'publish' and p.ID =". $data->ID .";";
            $dataResult = DB::select($dataQuery);

            $link = $data->post_type == 'movie' ? 'movie/'.$data->post_name."/" : 'tv-show/'.$data->post_name."/";

            $sliders[] = [
                'title' => $data->post_title,
                'link' => $link,
                'src' => $imageUrlUpload.$dataResult[0]->meta_value
            ];
        }
        
        $data = [
            'menus' => [
                [
                    'item' => '8092',
                    'title'=> '영화',
                    'link' => 'movie'
                ],
                [
                    'item' => '161947',
                    'title'=> 'TV',
                    'link' => 'tv-shows',
                    'sub_menu' => [
                        [
                            'item' => '8093',
                            'title'=> '드라마',
                            'link' => 'k-drama'
                        ],
                        [
                            'item' => '8094',
                            'title'=> '예능',
                            'link' => 'k-show'
                        ],
                        [
                            'item' => '8095',
                            'title'=> '시사',
                            'link' => 'k-sisa'
                        ]
                    ]
                ],
                [
                    'item' => '118282',
                    'title'=> '미드',
                    'link' => 'u-drama'
                ],
                [
                    'item' => '8098',
                    'title'=> 'OTT',
                    'link' => 'ott-web'
                ]
            ],
            'sliders' => $sliders,
            'otts' => [
                'ott_chanels' => [
                    [
                        'link' => 'ott-web/netflix/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/netflix.png'
                    ],
                    [
                        'link' => 'ott-web/disney/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/disney_plus.png'
                    ],
                    [
                        'link' => 'ott-web/apple-tv/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/appletv.png'
                    ],
                ],
                'ott_title' => '오늘의 미국 넷플릭스 순위',
                'ott_sliders' => [
                    [
                        'year' => '2023',
                        'episode' => '시즌 2 – 10화',
                        'title' => '링컨 차를 타는 변호사',
                        'link' => 'episode/%eb%a7%81%ec%bb%a8-%ec%b0%a8%eb%a5%bc-%ed%83%80%eb%8a%94-%eb%b3%80%ed%98%b8%ec%82%ac-%ec%8b%9c%ec%a6%8c-2-10%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/img.png'
                    ],
                    [
                        'year' => '2023',
                        'episode' => '',
                        'title' => '말하지 못한 이야기: 조니 풋볼',
                        'link' => 'movie/%eb%a7%90%ed%95%98%ec%a7%80-%eb%aa%bb%ed%95%9c-%ec%9d%b4%ec%95%bc%ea%b8%b0-%ec%a1%b0%eb%8b%88-%ed%92%8b%eb%b3%bc/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/download-3.jpg'
                    ],
                    [
                        'year' => '2023',
                        'episode' => '시즌 9 – 10화',
                        'title' => '슈츠',
                        'link' => 'episode/%ec%8a%88%ec%b8%a0-%ec%8b%9c%ec%a6%8c-9-10%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/06/htEHyzUSGkSe0kspwzCvwHetYaT.jpg'
                    ],
                    [
                        'year' => '2023',
                        'episode' => '',
                        'title' => '슈퍼배드 2',
                        'link' => 'movie/%ec%8a%88%ed%8d%bc%eb%b0%b0%eb%93%9c-2/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2022/06/AAAABbxlH8zFMd-PD6nWzfpPeCca_OH-dQfs_MeLQ0N4aFkQIyk0j-Rcs7TZB4VRAejQjHG3HJTV7g4b54_0X__A6ud-TL2I8qNqWnhF.jpg'
                    ],
                    [
                        'year' => '2023',
                        'episode' => '시즌 3 – 10화',
                        'title' => '스위트 매그놀리아',
                        'link' => 'episode/%ec%8a%a4%ec%9c%84%ed%8a%b8-%eb%a7%a4%ea%b7%b8%eb%86%80%eb%a6%ac%ec%95%84-%ec%8b%9c%ec%a6%8c-3-10%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/download-3.jpg'
                    ],
                    [
                        'year' => '2023',
                        'episode' => '',
                        'title' => '파탈',
                        'link' => 'movie/%ed%8c%8c%ed%83%88/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2022/12/6076d510498e1ee1a5b4e333-1682495071455.jpg'
                    ],
                    [
                        'year' => '2023',
                        'episode' => '6화',
                        'title' => 'Fisk (영자막)',
                        'link' => 'episode/fisk-6%ed%99%94-%ec%98%81%ec%9e%90%eb%a7%89/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/p19607388_b_h10_ae.jpg'
                    ],
                    [
                        'year' => '2023',
                        'episode' => '',
                        'title' => '리버 와일드 (영자막)',
                        'link' => 'movie/%eb%a6%ac%eb%b2%84-%ec%99%80%ec%9d%bc%eb%93%9c-%ec%98%81%ec%9e%90%eb%a7%89/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/download.jpg'
                    ],
                    [
                        'year' => '2023',
                        'episode' => '6화',
                        'title' => '나의 행복한 결혼',
                        'link' => 'episode/%eb%82%98%ec%9d%98-%ed%96%89%eb%b3%b5%ed%95%9c-%ea%b2%b0%ed%98%bc-6%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/AAAABUegy9gjOIGMp_gHDTo0r3UAIkVcoIp7bZcFzPNE83crUdFMh3JtckBdetNXKSVv33p5Eka2xVg8jmQjfgrAdwGkLPgMBactptQS-FPShlrxg_R9SDJU7HHH77EEL4xMayhCJSeFPEYvm32EbdnNOzfAlU1AoezK0c1k0pMF0ytGdpAkb1HZJXLoVAXCEiQnE9amfwsl.jpg'
                    ],
                    [
                        'year' => '2010',
                        'episode' => '',
                        'title' => '슈퍼배드',
                        'link' => 'movie/%ec%8a%88%ed%8d%bc%eb%b0%b0%eb%93%9c/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2022/06/p0xDnJ.webp'
                    ],
                ]
            ],
            'tv-shows' => [
                'categories' => [
                    [
                        'title' => '전체',
                        'link' => 'tv-show'
                    ],
                    [
                        'title' => '드라마',
                        'link' => 'k-drama'
                    ],
                    [
                        'title' => '예능',
                        'link' => 'k-show'
                    ],
                    [
                        'title' => '시사/교양',
                        'link' => 'k-sisa'
                    ],
                    [
                        'title' => '미드',
                        'link' => 'u-drama'
                    ],
                ],
                'title' => '최신등록 방송',
                'items' => [
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/tv_chosun.png'
                        ],
                        'release' => '12 시간 전',
                        'episode' => '14화',
                        'title' => '미스터로또',
                        'origin_title' => 'Mr.Lotto',
                        'link' => 'episode/%ed%95%98%eb%93%9c-%ec%85%80-6%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/22cjCaUikOW14dr1htuhCVaUbzk.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/06/F_webp_480_1-transformed.jpeg 960w, https://image002.modooup.com/wp-content/uploads/2023/06/F_webp_480_1-transformed-208x300.jpeg 208w, https://image002.modooup.com/wp-content/uploads/2023/06/F_webp_480_1-transformed-709x1024.jpeg 709w, https://image002.modooup.com/wp-content/uploads/2023/06/F_webp_480_1-transformed-768x1109.jpeg 768w, https://image002.modooup.com/wp-content/uploads/2023/06/F_webp_480_1-transformed-17x24.jpeg 17w, https://image002.modooup.com/wp-content/uploads/2023/06/F_webp_480_1-transformed-25x36.jpeg 25w, https://image002.modooup.com/wp-content/uploads/2023/06/F_webp_480_1-transformed-33x48.jpeg 33w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/tvn.png'
                        ],
                        'release' => '12 시간 전',
                        'episode' => '2화',
                        'title' => '알쓸별잡',
                        'origin_title' => 'Useless Dictionaries',
                        'link' => 'episode/%ec%95%8c%ec%93%b8%eb%b3%84%ec%9e%a1-2%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/image_readtop_2023_592454_16910398215571734.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/image_readtop_2023_592454_16910398215571734.jpg 500w, https://image002.modooup.com/wp-content/uploads/2023/08/image_readtop_2023_592454_16910398215571734-212x300.jpg 212w, https://image002.modooup.com/wp-content/uploads/2023/08/image_readtop_2023_592454_16910398215571734-17x24.jpg 17w, https://image002.modooup.com/wp-content/uploads/2023/08/image_readtop_2023_592454_16910398215571734-25x36.jpg 25w, https://image002.modooup.com/wp-content/uploads/2023/08/image_readtop_2023_592454_16910398215571734-34x48.jpg 34w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/mbn.png'
                        ],
                        'release' => '12 시간 전',
                        'episode' => '2화',
                        'title' => '불꽃밴드',
                        'origin_title' => 'Flame Band',
                        'link' => 'episode/%ec%95%8c%ec%93%b8%eb%b3%84%ec%9e%a1-2%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/618627_872289_173.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/618627_872289_173.jpg 900w, https://image002.modooup.com/wp-content/uploads/2023/08/618627_872289_173-212x300.jpg 212w, https://image002.modooup.com/wp-content/uploads/2023/08/618627_872289_173-724x1024.jpg 724w, https://image002.modooup.com/wp-content/uploads/2023/08/618627_872289_173-768x1086.jpg 768w, https://image002.modooup.com/wp-content/uploads/2023/08/618627_872289_173-17x24.jpg 17w, https://image002.modooup.com/wp-content/uploads/2023/08/618627_872289_173-25x36.jpg 25w, https://image002.modooup.com/wp-content/uploads/2023/08/618627_872289_173-34x48.jpg 34w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/kbs_joy_1.png'
                        ],
                        'release' => '12 시간 전',
                        'episode' => '7화',
                        'title' => '중매술사',
                        'origin_title' => 'Matchmaker',
                        'link' => 'episode/%ec%a4%91%eb%a7%a4%ec%88%a0%ec%82%ac-7%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/AKR20230628114000005_01_i_P4.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/AKR20230628114000005_01_i_P4.jpg 600w, https://image002.modooup.com/wp-content/uploads/2023/08/AKR20230628114000005_01_i_P4-212x300.jpg 212w, https://image002.modooup.com/wp-content/uploads/2023/08/AKR20230628114000005_01_i_P4-17x24.jpg 17w, https://image002.modooup.com/wp-content/uploads/2023/08/AKR20230628114000005_01_i_P4-25x36.jpg 25w, https://image002.modooup.com/wp-content/uploads/2023/08/AKR20230628114000005_01_i_P4-34x48.jpg 34w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/ena.png'
                        ],
                        'release' => '13 시간 전',
                        'episode' => '21화',
                        'title' => '나는 SOLO 그 후, 사랑은 계속된다(나솔사계)',
                        'origin_title' => 'I’m SOLO, Love Goes On',
                        'link' => 'episode/%ec%a4%91%eb%a7%a4%ec%88%a0%ec%82%ac-7%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/1.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/1.jpg 800w, https://image002.modooup.com/wp-content/uploads/2023/08/1-220x300.jpg 220w, https://image002.modooup.com/wp-content/uploads/2023/08/1-751x1024.jpg 751w, https://image002.modooup.com/wp-content/uploads/2023/08/1-768x1047.jpg 768w, https://image002.modooup.com/wp-content/uploads/2023/08/1-18x24.jpg 18w, https://image002.modooup.com/wp-content/uploads/2023/08/1-26x36.jpg 26w, https://image002.modooup.com/wp-content/uploads/2023/08/1-35x48.jpg 35w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/ena.png'
                        ],
                        'release' => '13 시간 전',
                        'episode' => '21화',
                        'title' => '나는 SOLO 그 후, 사랑은 계속된다(나솔사계)',
                        'origin_title' => 'I’m SOLO, Love Goes On',
                        'link' => 'episode/%ec%a4%91%eb%a7%a4%ec%88%a0%ec%82%ac-7%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/1.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/1.jpg 800w, https://image002.modooup.com/wp-content/uploads/2023/08/1-220x300.jpg 220w, https://image002.modooup.com/wp-content/uploads/2023/08/1-751x1024.jpg 751w, https://image002.modooup.com/wp-content/uploads/2023/08/1-768x1047.jpg 768w, https://image002.modooup.com/wp-content/uploads/2023/08/1-18x24.jpg 18w, https://image002.modooup.com/wp-content/uploads/2023/08/1-26x36.jpg 26w, https://image002.modooup.com/wp-content/uploads/2023/08/1-35x48.jpg 35w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/jtbc.png'
                        ],
                        'release' => '13 시간 전',
                        'episode' => '14화',
                        'title' => '기적의 형제',
                        'origin_title' => 'Miraculous Brothers',
                        'link' => 'episode/%ea%b8%b0%ec%a0%81%ec%9d%98-%ed%98%95%ec%a0%9c-14%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/06/scNOGjafZ1k29oz8hX2dNC6Ahdh.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/06/scNOGjafZ1k29oz8hX2dNC6Ahdh.jpg 720w, https://image002.modooup.com/wp-content/uploads/2023/06/scNOGjafZ1k29oz8hX2dNC6Ahdh-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/06/scNOGjafZ1k29oz8hX2dNC6Ahdh-683x1024.jpg 683w, https://image002.modooup.com/wp-content/uploads/2023/06/scNOGjafZ1k29oz8hX2dNC6Ahdh-600x900.jpg 600w, https://image002.modooup.com/wp-content/uploads/2023/06/scNOGjafZ1k29oz8hX2dNC6Ahdh-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/06/scNOGjafZ1k29oz8hX2dNC6Ahdh-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/06/scNOGjafZ1k29oz8hX2dNC6Ahdh-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/06/scNOGjafZ1k29oz8hX2dNC6Ahdh-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/06/scNOGjafZ1k29oz8hX2dNC6Ahdh-32x48.jpg 32w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/kbs_1.png'
                        ],
                        'release' => '13 시간 전',
                        'episode' => '733화',
                        'title' => '한밤의 시사토크 더 라이브',
                        'origin_title' => 'Midnight Talk: The Live',
                        'link' => 'episode/%ed%95%9c%eb%b0%a4%ec%9d%98-%ec%8b%9c%ec%82%ac%ed%86%a0%ed%81%ac-%eb%8d%94-%eb%9d%bc%ec%9d%b4%eb%b8%8c-733%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/05/N201909171106051.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/05/N201909171106051.jpg 680w, https://image002.modooup.com/wp-content/uploads/2023/05/N201909171106051-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/05/N201909171106051-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/05/N201909171106051-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/05/N201909171106051-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/05/N201909171106051-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/05/N201909171106051-32x48.jpg 32w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/ena.png'
                        ],
                        'release' => '13 시간 전',
                        'episode' => '6화',
                        'title' => '오랫동안 당신을 기다렸습니다',
                        'origin_title' => 'Longing for You',
                        'link' => 'episode/%ec%98%a4%eb%9e%ab%eb%8f%99%ec%95%88-%eb%8b%b9%ec%8b%a0%ec%9d%84-%ea%b8%b0%eb%8b%a4%eb%a0%b8%ec%8a%b5%eb%8b%88%eb%8b%a4-6%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/07/hfoIsYGi9LhwaUzPNvIqj8NFkTH0-pLOKb4jPFyncmHtqA-70ydy41JDz7OuJ9jBp31GWKf9Hz-eBIlqHa6MYU277j849wXbrwuXhsScFP-UFWsEisi-uvdKu4jwxi0Ra8iSBSCt44PxqMlRKtLgdYysZsKfYSDdR0-xSfheLAU.webp',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/07/hfoIsYGi9LhwaUzPNvIqj8NFkTH0-pLOKb4jPFyncmHtqA-70ydy41JDz7OuJ9jBp31GWKf9Hz-eBIlqHa6MYU277j849wXbrwuXhsScFP-UFWsEisi-uvdKu4jwxi0Ra8iSBSCt44PxqMlRKtLgdYysZsKfYSDdR0-xSfheLAU.webp 1000w, https://image002.modooup.com/wp-content/uploads/2023/07/hfoIsYGi9LhwaUzPNvIqj8NFkTH0-pLOKb4jPFyncmHtqA-70ydy41JDz7OuJ9jBp31GWKf9Hz-eBIlqHa6MYU277j849wXbrwuXhsScFP-UFWsEisi-uvdKu4jwxi0Ra8iSBSCt44PxqMlRKtLgdYysZsKfYSDdR0-xSfheLAU-210x300.webp 210w, https://image002.modooup.com/wp-content/uploads/2023/07/hfoIsYGi9LhwaUzPNvIqj8NFkTH0-pLOKb4jPFyncmHtqA-70ydy41JDz7OuJ9jBp31GWKf9Hz-eBIlqHa6MYU277j849wXbrwuXhsScFP-UFWsEisi-uvdKu4jwxi0Ra8iSBSCt44PxqMlRKtLgdYysZsKfYSDdR0-xSfheLAU-717x1024.webp 717w, https://image002.modooup.com/wp-content/uploads/2023/07/hfoIsYGi9LhwaUzPNvIqj8NFkTH0-pLOKb4jPFyncmHtqA-70ydy41JDz7OuJ9jBp31GWKf9Hz-eBIlqHa6MYU277j849wXbrwuXhsScFP-UFWsEisi-uvdKu4jwxi0Ra8iSBSCt44PxqMlRKtLgdYysZsKfYSDdR0-xSfheLAU-768x1097.webp 768w, https://image002.modooup.com/wp-content/uploads/2023/07/hfoIsYGi9LhwaUzPNvIqj8NFkTH0-pLOKb4jPFyncmHtqA-70ydy41JDz7OuJ9jBp31GWKf9Hz-eBIlqHa6MYU277j849wXbrwuXhsScFP-UFWsEisi-uvdKu4jwxi0Ra8iSBSCt44PxqMlRKtLgdYysZsKfYSDdR0-xSfheLAU-17x24.webp 17w, https://image002.modooup.com/wp-content/uploads/2023/07/hfoIsYGi9LhwaUzPNvIqj8NFkTH0-pLOKb4jPFyncmHtqA-70ydy41JDz7OuJ9jBp31GWKf9Hz-eBIlqHa6MYU277j849wXbrwuXhsScFP-UFWsEisi-uvdKu4jwxi0Ra8iSBSCt44PxqMlRKtLgdYysZsKfYSDdR0-xSfheLAU-25x36.webp 25w, https://image002.modooup.com/wp-content/uploads/2023/07/hfoIsYGi9LhwaUzPNvIqj8NFkTH0-pLOKb4jPFyncmHtqA-70ydy41JDz7OuJ9jBp31GWKf9Hz-eBIlqHa6MYU277j849wXbrwuXhsScFP-UFWsEisi-uvdKu4jwxi0Ra8iSBSCt44PxqMlRKtLgdYysZsKfYSDdR0-xSfheLAU-34x48.webp 34w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/mbc.png'
                        ],
                        'release' => '13 시간 전',
                        'episode' => '228화',
                        'title' => '실화탐사대',
                        'origin_title' => 'True Story Expedition',
                        'link' => 'episode/%ec%8b%a4%ed%99%94%ed%83%90%ec%82%ac%eb%8c%80-228%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/06/a9c0349f-f230-4f6f-b1f5-7bff9de0d562.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/06/a9c0349f-f230-4f6f-b1f5-7bff9de0d562.jpg 1080w, https://image002.modooup.com/wp-content/uploads/2023/06/a9c0349f-f230-4f6f-b1f5-7bff9de0d562-197x300.jpg 197w, https://image002.modooup.com/wp-content/uploads/2023/06/a9c0349f-f230-4f6f-b1f5-7bff9de0d562-672x1024.jpg 672w, https://image002.modooup.com/wp-content/uploads/2023/06/a9c0349f-f230-4f6f-b1f5-7bff9de0d562-768x1170.jpg 768w, https://image002.modooup.com/wp-content/uploads/2023/06/a9c0349f-f230-4f6f-b1f5-7bff9de0d562-1008x1536.jpg 1008w, https://image002.modooup.com/wp-content/uploads/2023/06/a9c0349f-f230-4f6f-b1f5-7bff9de0d562-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/06/a9c0349f-f230-4f6f-b1f5-7bff9de0d562-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/06/a9c0349f-f230-4f6f-b1f5-7bff9de0d562-31x48.jpg 31w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/sbs.png'
                        ],
                        'release' => '13 시간 전',
                        'episode' => '1화',
                        'title' => '국민사형투표',
                        'origin_title' => 'The Killing Vote',
                        'link' => 'episode/%ea%b5%ad%eb%af%bc%ec%82%ac%ed%98%95%ed%88%ac%ed%91%9c-1%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fdppsko04lBaMHftYWoKTuBq9gy.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fdppsko04lBaMHftYWoKTuBq9gy.jpg 600w, https://image002.modooup.com/wp-content/uploads/2023/08/fdppsko04lBaMHftYWoKTuBq9gy-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/fdppsko04lBaMHftYWoKTuBq9gy-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/fdppsko04lBaMHftYWoKTuBq9gy-150x225.jpg 150w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/sbs_fil.png'
                        ],
                        'release' => '13 시간 전',
                        'episode' => '7화',
                        'title' => '스타맛세권 먹어보쇼',
                        'origin_title' => 'STAR EAT-SHOW (Mukbang)',
                        'link' => 'episode/%ec%8a%a4%ed%83%80%eb%a7%9b%ec%84%b8%ea%b6%8c-%eb%a8%b9%ec%96%b4%eb%b3%b4%ec%87%bc-7%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/42NZ2M2T5ZUNE5QKCCGYWDOJXU.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/42NZ2M2T5ZUNE5QKCCGYWDOJXU.jpg 530w, https://image002.modooup.com/wp-content/uploads/2023/08/42NZ2M2T5ZUNE5QKCCGYWDOJXU-213x300.jpg 213w, https://image002.modooup.com/wp-content/uploads/2023/08/42NZ2M2T5ZUNE5QKCCGYWDOJXU-17x24.jpg 17w, https://image002.modooup.com/wp-content/uploads/2023/08/42NZ2M2T5ZUNE5QKCCGYWDOJXU-26x36.jpg 26w, https://image002.modooup.com/wp-content/uploads/2023/08/42NZ2M2T5ZUNE5QKCCGYWDOJXU-34x48.jpg 34w'
                    ],
                    [
                        'chanel' => [
                            'src' => 'https://image002.modooup.com/wp-content/uploads/2022/05/kbs_2.png'
                        ],
                        'release' => '13 시간 전',
                        'episode' => '7화',
                        'title' => '스타맛세권 먹어보쇼',
                        'origin_title' => 'Second House',
                        'link' => 'episode/%ec%84%b8%ec%bb%a8-%ed%95%98%ec%9a%b0%ec%8a%a4-%ec%8b%9c%ec%a6%8c-2-11%ed%99%94/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/06/022200350050-transformed.jpeg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/06/022200350050-transformed.jpeg 1000w, https://image002.modooup.com/wp-content/uploads/2023/06/022200350050-transformed-212x300.jpeg 212w, https://image002.modooup.com/wp-content/uploads/2023/06/022200350050-transformed-723x1024.jpeg 723w, https://image002.modooup.com/wp-content/uploads/2023/06/022200350050-transformed-768x1087.jpeg 768w, https://image002.modooup.com/wp-content/uploads/2023/06/022200350050-transformed-17x24.jpeg 17w, https://image002.modooup.com/wp-content/uploads/2023/06/022200350050-transformed-25x36.jpeg 25w, https://image002.modooup.com/wp-content/uploads/2023/06/022200350050-transformed-34x48.jpeg 34w'
                    ],
                ]
            ],
            'movies' => [
                'title' => '최신등록영화',
                'items' => [
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                        ],
                        'title' => '유 & 미 & 미',
                        'origin_title' => 'เธอกับฉันกับฉัน',
                        'link' => 'movie/%ec%9c%a0-%eb%af%b8-%eb%af%b8-2/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0-150x225.jpg 150w'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '공포',
                                'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                            ],
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '미스터리',
                                'link' => 'movie-genre/%eb%af%b8%ec%8a%a4%ed%84%b0%eb%a6%ac/'
                            ],
                            [
                                'name' => '스릴러',
                                'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                            ]
                        ],
                        'title' => '홈 포 렌트',
                        'origin_title' => 'บ้านเช่า..บูชายัญ',
                        'link' => 'movie/%ed%99%88-%ed%8f%ac-%eb%a0%8c%ed%8a%b8/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru.jpg 600w'
                    ],
                    [
                        'year' => '2020',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                        ],
                        'title' => '阴阳美人棺',
                        'origin_title' => '阴阳美人棺',
                        'link' => 'movie/%ec%a0%81%ec%9d%b8%ea%b1%b8-%ec%9d%8c%ec%96%91%eb%af%b8%ec%9d%b8%eb%8f%84/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8.jpg 600w'
                    ],
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => '공포',
                                'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                            ],
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                        ],
                        'title' => '피를 빠는 인형 파생',
                        'origin_title' => '血を吸う粘土 派生',
                        'link' => 'movie/%ed%94%bc%eb%a5%bc-%eb%b9%a0%eb%8a%94-%ec%9d%b8%ed%98%95-%ed%8c%8c%ec%83%9d/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr.jpg 600w'
                    ],
                    [
                        'year' => '2020',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '비르 다스: 인도로 인도할게',
                        'origin_title' => 'Vir Das: For India',
                        'link' => 'movie/%eb%b9%84%eb%a5%b4-%eb%8b%a4%ec%8a%a4-%ec%9d%b8%eb%8f%84%eb%a1%9c-%ec%9d%b8%eb%8f%84%ed%95%a0%ea%b2%8c/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs.jpg 600w'
                    ],
                    [
                        'year' => '2007',
                        'genres' => [
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '색즉시공 시즌 2',
                        'origin_title' => 'Sex Is Zero 2',
                        'link' => 'movie/%ec%83%89%ec%a6%89%ec%8b%9c%ea%b3%b5-%ec%8b%9c%ec%a6%8c-2/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl.jpg 600w'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '스릴러',
                                'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                            ],
                            [
                                'name' => '한국영화',
                                'link' => 'movie-genre/kmovie/'
                            ],
                        ],
                        'title' => '비닐하우스',
                        'origin_title' => 'Greenhouse',
                        'link' => 'movie/%eb%b9%84%eb%8b%90%ed%95%98%ec%9a%b0%ec%8a%a4/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf.jpg 600w'
                    ],
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => '다큐멘터리',
                                'link' => 'movie-genre/%eb%8b%a4%ed%81%90%eb%a9%98%ed%84%b0%eb%a6%ac/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'movie-genre/wmovie/'
                            ]
                        ],
                        'title' => '비닐하우스',
                        'origin_title' => 'Brené Brown: The Call to Courage',
                        'link' => 'movie/%eb%b8%8c%eb%a0%88%eb%84%a4-%eb%b8%8c%eb%9d%bc%ec%9a%b4-%eb%82%98%eb%a5%bc-%eb%b0%94%ea%be%b8%eb%8a%94-%ec%9a%a9%ea%b8%b0/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH.jpg 600w'
                    ],
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => '서양영화',
                                'link' => 'movie-genre/wmovie/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ]
                        ],
                        'title' => '휘트니 커밍스: 만져도 돼?',
                        'origin_title' => 'Whitney Cummings: Can I Touch It?',
                        'link' => 'movie/%ed%9c%98%ed%8a%b8%eb%8b%88-%ec%bb%a4%eb%b0%8d%ec%8a%a4-%eb%a7%8c%ec%a0%b8%eb%8f%84-%eb%8f%bc/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/pxDekfQeobu7zONfRGzA5vy0RxL-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/pxDekfQeobu7zONfRGzA5vy0RxL-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/pxDekfQeobu7zONfRGzA5vy0RxL-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/pxDekfQeobu7zONfRGzA5vy0RxL-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/pxDekfQeobu7zONfRGzA5vy0RxL.jpg 600w'
                    ],
                    [
                        'year' => '2021',
                        'genres' => [
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'movie-genre/wmovie/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                            [
                                'name' => '판타지',
                                'link' => 'movie-genre/%ed%8c%90%ed%83%80%ec%a7%80/'
                            ]
                        ],
                        'title' => '룰리',
                        'origin_title' => 'Lulli',
                        'link' => 'movie/%eb%a3%b0%eb%a6%ac/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ztJHKv6KSSgCTWAI2STrX2BeuJZ-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ztJHKv6KSSgCTWAI2STrX2BeuJZ-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/ztJHKv6KSSgCTWAI2STrX2BeuJZ-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/ztJHKv6KSSgCTWAI2STrX2BeuJZ-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/ztJHKv6KSSgCTWAI2STrX2BeuJZ.jpg 600w'
                    ],
                    [
                        'year' => '2022',
                        'genres' => [
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'movie-genre/wmovie/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ]
                        ],
                        'title' => '룰리',
                        'origin_title' => 'Red, White & Royal Blue',
                        'link' => 'movie/%eb%a0%88%eb%93%9c-%ed%99%94%ec%9d%b4%ed%8a%b8-%eb%a1%9c%ec%97%b4-%eb%b8%94%eb%a3%a8-%eb%b9%a8%ea%b0%95-%ed%8c%8c%eb%9e%91-%ec%96%b4%ec%a8%8c%eb%93%a0-%ec%b0%ac%eb%9e%80/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/pw2q0dMQYFFtYzKfbtoMKU8iyc9-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/pw2q0dMQYFFtYzKfbtoMKU8iyc9-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/pw2q0dMQYFFtYzKfbtoMKU8iyc9-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/pw2q0dMQYFFtYzKfbtoMKU8iyc9-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/pw2q0dMQYFFtYzKfbtoMKU8iyc9.jpg 600w'
                    ],
                ]
            ],
            'movies-carousel' => [
                [
                    'year' => '2023',
                    'genres' => [
                        [
                            'name' => '동양영화',
                            'link' => 'movie-genre/amovie/'
                        ],
                        [
                            'name' => '로맨스',
                            'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                        ],
                    ],
                    'title' => '유 & 미 & 미',
                    'origin_title' => 'เธอกับฉันกับฉัน',
                    'link' => 'movie/%ec%9c%a0-%eb%af%b8-%eb%af%b8-2/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0-150x225.jpg 150w'
                ],
                [
                    'year' => '2023',
                    'genres' => [
                        [
                            'name' => '공포',
                            'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                        ],
                        [
                            'name' => '동양영화',
                            'link' => 'movie-genre/amovie/'
                        ],
                        [
                            'name' => '미스터리',
                            'link' => 'movie-genre/%eb%af%b8%ec%8a%a4%ed%84%b0%eb%a6%ac/'
                        ],
                        [
                            'name' => '스릴러',
                            'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                        ]
                    ],
                    'title' => '홈 포 렌트',
                    'origin_title' => 'บ้านเช่า..บูชายัญ',
                    'link' => 'movie/%ed%99%88-%ed%8f%ac-%eb%a0%8c%ed%8a%b8/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru.jpg 600w'
                ],
                [
                    'year' => '2020',
                    'genres' => [
                        [
                            'name' => '동양영화',
                            'link' => 'movie-genre/amovie/'
                        ],
                    ],
                    'title' => '阴阳美人棺',
                    'origin_title' => '阴阳美人棺',
                    'link' => 'movie/%ec%a0%81%ec%9d%b8%ea%b1%b8-%ec%9d%8c%ec%96%91%eb%af%b8%ec%9d%b8%eb%8f%84/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8.jpg 600w'
                ],
                [
                    'year' => '2019',
                    'genres' => [
                        [
                            'name' => '공포',
                            'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                        ],
                        [
                            'name' => '동양영화',
                            'link' => 'movie-genre/amovie/'
                        ],
                    ],
                    'title' => '피를 빠는 인형 파생',
                    'origin_title' => '血を吸う粘土 派生',
                    'link' => 'movie/%ed%94%bc%eb%a5%bc-%eb%b9%a0%eb%8a%94-%ec%9d%b8%ed%98%95-%ed%8c%8c%ec%83%9d/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr.jpg 600w'
                ],
                [
                    'year' => '2020',
                    'genres' => [
                        [
                            'name' => '동양영화',
                            'link' => 'movie-genre/amovie/'
                        ],
                        [
                            'name' => '코미디',
                            'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                        ],
                    ],
                    'title' => '비르 다스: 인도로 인도할게',
                    'origin_title' => 'Vir Das: For India',
                    'link' => 'movie/%eb%b9%84%eb%a5%b4-%eb%8b%a4%ec%8a%a4-%ec%9d%b8%eb%8f%84%eb%a1%9c-%ec%9d%b8%eb%8f%84%ed%95%a0%ea%b2%8c/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs.jpg 600w'
                ],
                [
                    'year' => '2007',
                    'genres' => [
                        [
                            'name' => '로맨스',
                            'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                        ],
                        [
                            'name' => '코미디',
                            'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                        ],
                    ],
                    'title' => '색즉시공 시즌 2',
                    'origin_title' => 'Sex Is Zero 2',
                    'link' => 'movie/%ec%83%89%ec%a6%89%ec%8b%9c%ea%b3%b5-%ec%8b%9c%ec%a6%8c-2/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl.jpg 600w'
                ],
                [
                    'year' => '2023',
                    'genres' => [
                        [
                            'name' => '드라마',
                            'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                        ],
                        [
                            'name' => '스릴러',
                            'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                        ],
                        [
                            'name' => '한국영화',
                            'link' => 'movie-genre/kmovie/'
                        ],
                    ],
                    'title' => '비닐하우스',
                    'origin_title' => 'Greenhouse',
                    'link' => 'movie/%eb%b9%84%eb%8b%90%ed%95%98%ec%9a%b0%ec%8a%a4/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf.jpg 600w'
                ],
                [
                    'year' => '2019',
                    'genres' => [
                        [
                            'name' => '다큐멘터리',
                            'link' => 'movie-genre/%eb%8b%a4%ed%81%90%eb%a9%98%ed%84%b0%eb%a6%ac/'
                        ],
                        [
                            'name' => '서양영화',
                            'link' => 'movie-genre/wmovie/'
                        ]
                    ],
                    'title' => '비닐하우스',
                    'origin_title' => 'Brené Brown: The Call to Courage',
                    'link' => 'movie/%eb%b8%8c%eb%a0%88%eb%84%a4-%eb%b8%8c%eb%9d%bc%ec%9a%b4-%eb%82%98%eb%a5%bc-%eb%b0%94%ea%be%b8%eb%8a%94-%ec%9a%a9%ea%b8%b0/',
                    'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-300x450.jpg',
                    'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH.jpg 600w'
                ]
            ],
            'movies-list' => [
                'top_5' => [
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => '다큐멘터리',
                                'link' => 'movie-genre/%eb%8b%a4%ed%81%90%eb%a9%98%ed%84%b0%eb%a6%ac/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'movie-genre/wmovie/'
                            ]
                        ],
                        'title' => '비닐하우스'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '스릴러',
                                'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                            ],
                            [
                                'name' => '한국영화',
                                'link' => 'movie-genre/kmovie/'
                            ],
                        ],
                        'title' => '비닐하우스'
                    ],
                    [
                        'year' => '2007',
                        'genres' => [
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '색즉시공 시즌 2'
                    ],
                    [
                        'year' => '2020',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '비르 다스: 인도로 인도할게'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '공포',
                                'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                            ],
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '미스터리',
                                'link' => 'movie-genre/%eb%af%b8%ec%8a%a4%ed%84%b0%eb%a6%ac/'
                            ],
                            [
                                'name' => '스릴러',
                                'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                            ]
                        ],
                        'title' => '홈 포 렌트'
                    ],
                ],
                'movies_new' => [
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                        ],
                        'title' => '유 & 미 & 미',
                        'link' => 'movie/%ec%9c%a0-%eb%af%b8-%eb%af%b8-2/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/tYWGz26UCPGC2dI7fERFalAFgv0-150x225.jpg 150w'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '공포',
                                'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                            ],
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '미스터리',
                                'link' => 'movie-genre/%eb%af%b8%ec%8a%a4%ed%84%b0%eb%a6%ac/'
                            ],
                            [
                                'name' => '스릴러',
                                'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                            ]
                        ],
                        'title' => '홈 포 렌트',
                        'link' => 'movie/%ed%99%88-%ed%8f%ac-%eb%a0%8c%ed%8a%b8/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru.jpg 600w'
                    ],
                    [
                        'year' => '2020',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                        ],
                        'title' => '阴阳美人棺',
                        'link' => 'movie/%ec%a0%81%ec%9d%b8%ea%b1%b8-%ec%9d%8c%ec%96%91%eb%af%b8%ec%9d%b8%eb%8f%84/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8.jpg 600w'
                    ],
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => '공포',
                                'link' => 'movie-genre/%ea%b3%b5%ed%8f%ac/'
                            ],
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                        ],
                        'title' => '피를 빠는 인형 파생',
                        'link' => 'movie/%ed%94%bc%eb%a5%bc-%eb%b9%a0%eb%8a%94-%ec%9d%b8%ed%98%95-%ed%8c%8c%ec%83%9d/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/h0tzfZQWZV95vZmePXQIuMswcGr.jpg 600w'
                    ],
                    [
                        'year' => '2020',
                        'genres' => [
                            [
                                'name' => '동양영화',
                                'link' => 'movie-genre/amovie/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '비르 다스: 인도로 인도할게',
                        'link' => 'movie/%eb%b9%84%eb%a5%b4-%eb%8b%a4%ec%8a%a4-%ec%9d%b8%eb%8f%84%eb%a1%9c-%ec%9d%b8%eb%8f%84%ed%95%a0%ea%b2%8c/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/fkYvRHVVs6lTmH9u85AIqjqeuOs.jpg 600w'
                    ],
                    [
                        'year' => '2007',
                        'genres' => [
                            [
                                'name' => '로맨스',
                                'link' => 'movie-genre/%eb%a1%9c%eb%a7%a8%ec%8a%a4/'
                            ],
                            [
                                'name' => '코미디',
                                'link' => 'movie-genre/%ec%bd%94%eb%af%b8%eb%94%94/'
                            ],
                        ],
                        'title' => '색즉시공 시즌 2',
                        'link' => 'movie/%ec%83%89%ec%a6%89%ec%8b%9c%ea%b3%b5-%ec%8b%9c%ec%a6%8c-2/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/7CXkBFZvQ6kKeiDJeUQOZRyLHMl.jpg 600w'
                    ],
                    [
                        'year' => '2023',
                        'genres' => [
                            [
                                'name' => '드라마',
                                'link' => 'movie-genre/%eb%93%9c%eb%9d%bc%eb%a7%88/'
                            ],
                            [
                                'name' => '스릴러',
                                'link' => 'movie-genre/%ec%8a%a4%eb%a6%b4%eb%9f%ac/'
                            ],
                            [
                                'name' => '한국영화',
                                'link' => 'movie-genre/kmovie/'
                            ],
                        ],
                        'title' => '비닐하우스',
                        'link' => 'movie/%eb%b9%84%eb%8b%90%ed%95%98%ec%9a%b0%ec%8a%a4/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/hVb49BvAYeILDcXkGJvzAYnZ8bf.jpg 600w'
                    ],
                    [
                        'year' => '2019',
                        'genres' => [
                            [
                                'name' => '다큐멘터리',
                                'link' => 'movie-genre/%eb%8b%a4%ed%81%90%eb%a9%98%ed%84%b0%eb%a6%ac/'
                            ],
                            [
                                'name' => '서양영화',
                                'link' => 'movie-genre/wmovie/'
                            ]
                        ],
                        'title' => '비닐하우스',
                        'link' => 'movie/%eb%b8%8c%eb%a0%88%eb%84%a4-%eb%b8%8c%eb%9d%bc%ec%9a%b4-%eb%82%98%eb%a5%bc-%eb%b0%94%ea%be%b8%eb%8a%94-%ec%9a%a9%ea%b8%b0/',
                        'src' => 'https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-300x450.jpg',
                        'srcset' => 'https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/cnjzskLPzbQiQCwJGRt05FFTUpH.jpg 600w'
                    ]
                ]
            ],
        ];

        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}