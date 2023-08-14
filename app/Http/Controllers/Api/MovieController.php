<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            "total" => 50,
            "per_page" => 30,
            "current_page" => 1,
            "last_page" => 4,
            "first_page_url" => "",
            "last_page_url" => "",
            "next_page_url" => "",
            "prev_page_url" => null,
            "path" => "",
            "from" => 1,
            "to" => 30,
            "data" => [
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
                'movies_popular' => [
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
                'movies' => [
                    [
                        "id" => "%ed%99%88-%ed%8f%ac-%eb%a0%8c%ed%8a%b8",
                        "year" => "2023",
                        "title" => "홈 포 렌트",
                        "titleEn" => "บ้านเช่า..บูชายัญ",
                        "genres" => ["공포", "동양영화", "미스터리", "스릴러"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/ve8Vaze3v7gFOCaqpVWG8vkibru.jpg 600w",
                        ],
                    ],
                    [
                        "id" =>
                            "%ec%a0%81%ec%9d%b8%ea%b1%b8-%ec%9d%8c%ec%96%91%eb%af%b8%ec%9d%b8%eb%8f%84",
                        "year" => "2020",
                        "title" => "적인걸: 음양미인도",
                        "titleEn" => "阴阳美人棺",
                        "genres" => ["동양영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/6EVIn0joKXrmSw4iZEckQljurz8.jpg 600w",
                        ],
                    ],
                    [
                        "id" => "jagun-jagun-the-warrior-%ec%98%81%ec%9e%90%eb%a7%89",
                        "year" => "2023",
                        "title" => "Jagun Jagun: The Warrior (영자막)",
                        "titleEn" => "Jagun Jagun: The Warrior",
                        "genres" => ["동양영화", "액션"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/n0GXumEMtwgYj2M3YW4Iu0veYJg.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/n0GXumEMtwgYj2M3YW4Iu0veYJg.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/n0GXumEMtwgYj2M3YW4Iu0veYJg-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/n0GXumEMtwgYj2M3YW4Iu0veYJg-150x225.jpg 150w",
                        ],
                    ],
                    [
                        "id" =>
                            "%eb%a9%94%eb%a6%ac-%eb%a7%88%ec%9d%b4-%eb%8d%b0%eb%93%9c-%eb%b0%94%eb%94%94",
                        "year" => "2023",
                        "title" => "메리 마이 데드 바디",
                        "titleEn" => "關於我和鬼變成家人的那件事",
                        "genres" => ["동양영화", "미스터리", "액션", "코미디"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/m9UIiiTDTx6w1gPQgVr8cvgol91.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/m9UIiiTDTx6w1gPQgVr8cvgol91.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/m9UIiiTDTx6w1gPQgVr8cvgol91-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/m9UIiiTDTx6w1gPQgVr8cvgol91-150x225.jpg 150w",
                        ],
                    ],
                    [
                        "id" =>
                            "%ed%8a%b8%eb%a6%ac%ed%94%8c-%ec%97%91%ec%8a%a4-%eb%a6%ac%ed%84%b4%ec%a6%88",
                        "year" => "2017",
                        "title" => "트리플 엑스 리턴즈",
                        "titleEn" => "xXx: Return of Xander Cage",
                        "genres" => ["모험", "범죄", "서양영화", "액션"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/eTJVHRrWnK5Cv7vCxmwqFksbYm1.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/eTJVHRrWnK5Cv7vCxmwqFksbYm1.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/eTJVHRrWnK5Cv7vCxmwqFksbYm1-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/eTJVHRrWnK5Cv7vCxmwqFksbYm1-150x225.jpg 150w",
                        ],
                    ],
                    [
                        "id" =>
                            "%ed%98%b8%ec%8b%9c%eb%85%b8-%ea%b2%90-%ec%bd%98%ec%84%9c%ed%8a%b8-%eb%a6%ac%ec%bb%ac%eb%a0%89%ec%85%98-2015-2023",
                        "year" => "2023",
                        "title" => "호시노 겐 콘서트, 리컬렉션 2015-2023",
                        "titleEn" => "Gen Hoshino Concert Recollections 2015-2023",
                        "genres" => ["동양영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/20230727-st-213002-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/20230727-st-213002-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/20230727-st-213002-600x900.jpg 600w, https://image002.modooup.com/wp-content/uploads/2023/08/20230727-st-213002-150x225.jpg 150w",
                        ],
                    ],
                    [
                        "id" =>
                            "%eb%b8%8c%eb%9d%bc%ed%9d%90%eb%a7%88%ec%8a%a4%ed%8a%b8%eb%9d%bc-%ed%8c%8c%ed%8a%b8-%ec%9b%90%ef%bc%9a%ec%8b%9c%eb%b0%94-%eb%b8%8c%eb%9d%bc%eb%a7%88%ec%8a%a4%ed%8a%b8%eb%9d%bc-%ed%8c%8c%ed%8a%b8-1",
                        "year" => "2022",
                        "title" => "브라흐마스트라 파트 원：시바 (브라마스트라 파트 1：시바)",
                        "titleEn" => "ब्रह्मास्त्र पहला भाग: शिवा",
                        "genres" => ["동양영화", "모험", "액션", "판타지"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/7258eadd113ed95b04c63db771c76ab9b6eae61d-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/7258eadd113ed95b04c63db771c76ab9b6eae61d-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/7258eadd113ed95b04c63db771c76ab9b6eae61d-600x900.jpg 600w, https://image002.modooup.com/wp-content/uploads/2023/08/7258eadd113ed95b04c63db771c76ab9b6eae61d-150x225.jpg 150w",
                        ],
                    ],
                    [
                        "id" => "%ea%bf%80%eb%b2%8c-%eb%8c%80%ec%86%8c%eb%8f%99",
                        "year" => "2007",
                        "title" => "꿀벌 대소동",
                        "titleEn" => "Bee Movie",
                        "genres" => ["가족", "모험", "서양영화", "애니메이션", "코미디"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/eLuN9mjREtGS7BtALDUSPMu0MsE.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/eLuN9mjREtGS7BtALDUSPMu0MsE.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/eLuN9mjREtGS7BtALDUSPMu0MsE-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/eLuN9mjREtGS7BtALDUSPMu0MsE-150x225.jpg 150w",
                        ],
                    ],
                    [
                        "id" => "%ec%82%ac%eb%9e%91%ec%9d%98-%ea%b3%a0%ea%b3%a0%ed%95%99",
                        "year" => "2023",
                        "title" => "사랑의 고고학",
                        "titleEn" => "Archaeology of Love",
                        "genres" => ["드라마", "로맨스", "한국영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/5sQss4mYJa0oONrHUn03SqeVHEx-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/5sQss4mYJa0oONrHUn03SqeVHEx-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/5sQss4mYJa0oONrHUn03SqeVHEx-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/5sQss4mYJa0oONrHUn03SqeVHEx-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/5sQss4mYJa0oONrHUn03SqeVHEx.jpg 600w",
                        ],
                    ],
                    [
                        "id" => "%ed%86%b1-%ec%98%a4%eb%b8%8c-%eb%8d%94-%ec%9b%94%eb%93%9c",
                        "year" => "2022",
                        "title" => "톱 오브 더 월드",
                        "titleEn" => "मजा मा",
                        "genres" => ["동양영화", "드라마", "로맨스", "코미디"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/4aibIe4IdGQvO142HyvB7rIoAut-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/4aibIe4IdGQvO142HyvB7rIoAut-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/4aibIe4IdGQvO142HyvB7rIoAut-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/4aibIe4IdGQvO142HyvB7rIoAut-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/4aibIe4IdGQvO142HyvB7rIoAut.jpg 600w",
                        ],
                    ],
                    [
                        "id" => "%ec%bb%a8%eb%b2%84%ec%84%b8%ec%9d%b4%ec%85%98-2",
                        "year" => "2023",
                        "title" => "컨버세이션",
                        "titleEn" => "The Conversation",
                        "genres" => ["드라마", "한국영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/zV5zkOQGuZPWYcVhfFu6rZMz9yC-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/zV5zkOQGuZPWYcVhfFu6rZMz9yC-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/zV5zkOQGuZPWYcVhfFu6rZMz9yC-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/zV5zkOQGuZPWYcVhfFu6rZMz9yC-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/zV5zkOQGuZPWYcVhfFu6rZMz9yC.jpg 600w",
                        ],
                    ],
                    [
                        "id" => "%ea%b7%b8%eb%8c%80-%ec%96%b4%ec%9d%b4%ea%b0%80%eb%a6%ac",
                        "year" => "2023",
                        "title" => "그대 어이가리",
                        "titleEn" => "A song for my dear",
                        "genres" => ["드라마", "한국영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/lcjsQkNbGV7c9OC8OrQOm5CK9eh-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/lcjsQkNbGV7c9OC8OrQOm5CK9eh-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/lcjsQkNbGV7c9OC8OrQOm5CK9eh-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/lcjsQkNbGV7c9OC8OrQOm5CK9eh-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/lcjsQkNbGV7c9OC8OrQOm5CK9eh.jpg 600w",
                        ],
                    ],
                    [
                        "id" =>
                            "%eb%a7%90%ed%95%98%ec%a7%80-%eb%aa%bb%ed%95%9c-%ec%9d%b4%ec%95%bc%ea%b8%b0-%ec%a1%b0%eb%8b%88-%ed%92%8b%eb%b3%bc",
                        "year" => "2023",
                        "title" => "말하지 못한 이야기: 조니 풋볼",
                        "titleEn" => "Untold: Johnny Football",
                        "genres" => ["다큐멘터리", "서양영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/e7CKgRnRdE7bk1jl2vhJniX89cU.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/e7CKgRnRdE7bk1jl2vhJniX89cU.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/e7CKgRnRdE7bk1jl2vhJniX89cU-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/e7CKgRnRdE7bk1jl2vhJniX89cU-150x225.jpg 150w",
                        ],
                    ],
                    [
                        "id" =>
                            "%ec%9d%bc%ea%b3%b1-%ea%b0%9c%ec%9d%98-%eb%8c%80%ec%a3%84-%ec%9b%90%eb%a7%9d%ec%9d%98-%ec%97%90%eb%93%a0%eb%b2%84%eb%9f%ac-%ed%8c%8c%ed%8a%b8-2",
                        "year" => "2023",
                        "title" => "일곱 개의 대죄: 원망의 에든버러 파트 2",
                        "titleEn" => "七つの大罪 怨嗟のエジンバラ 後編",
                        "genres" => ["동양영화", "모험", "애니메이션", "액션", "판타지"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/p9WwpYRfKz3LcGva19v1SXsln1h.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/p9WwpYRfKz3LcGva19v1SXsln1h.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/p9WwpYRfKz3LcGva19v1SXsln1h-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/p9WwpYRfKz3LcGva19v1SXsln1h-150x225.jpg 150w",
                        ],
                    ],
                    [
                        "id" =>
                            "%ec%8a%a4%ed%8c%8c%ec%9d%b4%eb%8d%94%eb%a7%a8-%ec%96%b4%ed%81%ac%eb%a1%9c%ec%8a%a4-%eb%8d%94-%ec%9c%a0%eb%8b%88%eb%b2%84%ec%8a%a4",
                        "year" => "2023",
                        "title" => "스파이더맨: 어크로스 더 유니버스",
                        "titleEn" => "Spider-Man: Across the Spider-Verse",
                        "genres" => ["SF", "모험", "애니메이션", "액션"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-683x1024.jpg 683w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-768x1152.jpg 768w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-1024x1536.jpg 1024w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-1365x2048.jpg 1365w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-600x900.jpg 600w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/zG9TYiHt0fdaJiWuNEhFrfKzwoi-scaled.jpg 1707w",
                        ],
                    ],
                    [
                        "id" => "%eb%8d%94-%eb%94%9c",
                        "year" => "2022",
                        "title" => "더 딜",
                        "titleEn" => "The Deal",
                        "genres" => ["SF", "서양영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-683x1024.jpg 683w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-768x1152.jpg 768w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-1024x1536.jpg 1024w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-1365x2048.jpg 1365w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-600x900.jpg 600w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/yeqn2t4aadIvd4sgb73IOqqkLIL-scaled.jpg 1707w",
                        ],
                    ],
                    [
                        "id" => "%ec%9c%a0%eb%8b%a4%ec%9c%a0-%ec%9e%a5%ea%b5%b0",
                        "year" => "2023",
                        "title" => "유다유 장군",
                        "titleEn" => "大明奇将之荆楚剑义",
                        "genres" => ["동양영화", "드라마", "액션", "판타지"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1-683x1024.jpg 683w, https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1-768x1152.jpg 768w, https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1-600x900.jpg 600w, https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/j9dNIp4MkEqjkDn4aBb00oZtrcy-1.jpg 800w",
                        ],
                    ],
                    [
                        "id" =>
                            "%ec%9a%b0%eb%a6%ac%eb%93%a4%ec%9d%98-%ec%97%ac%eb%a6%84%eb%82%a0",
                        "year" => "2021",
                        "title" => "우리들의 여름날",
                        "titleEn" => "Geçen Yaz",
                        "genres" => ["드라마", "로맨스"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-682x1024.jpg 682w, https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-768x1152.jpg 768w, https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-1024x1536.jpg 1024w, https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-600x900.jpg 600w, https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/s2OLfim4v1WvgTzP0VjuBCISS2L.jpg 1333w",
                        ],
                    ],
                    [
                        "id" =>
                            "%ec%95%88%ec%86%8c%eb%8b%88-%ec%a0%9c%ec%85%80%eb%8b%89-%ea%b8%88%ea%b8%b0%ec%9d%98-%eb%86%8d%eb%8b%b4%eb%93%a4",
                        "year" => "2019",
                        "title" => "안소니 제셀닉: 금기의 농담들",
                        "titleEn" => "Anthony Jeselnik: Fire in the Maternity Ward",
                        "genres" => ["서양영화", "코미디"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/eo28E6qGhcOfswKrk6EcQpOcGC8-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/eo28E6qGhcOfswKrk6EcQpOcGC8-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/eo28E6qGhcOfswKrk6EcQpOcGC8-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/eo28E6qGhcOfswKrk6EcQpOcGC8-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/eo28E6qGhcOfswKrk6EcQpOcGC8-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/eo28E6qGhcOfswKrk6EcQpOcGC8-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/eo28E6qGhcOfswKrk6EcQpOcGC8-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/eo28E6qGhcOfswKrk6EcQpOcGC8.jpg 600w",
                        ],
                    ],
                    [
                        "id" => "%ea%b1%b4%ec%b6%95%ed%95%99%ea%b0%9c%eb%a1%a0",
                        "year" => "2012",
                        "title" => "건축학개론",
                        "titleEn" => "Architecture 101",
                        "genres" => ["로맨스", "코미디", "한국영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/6ChBkMt5fwYPtKvbtq8irLfuKHc-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/6ChBkMt5fwYPtKvbtq8irLfuKHc-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/6ChBkMt5fwYPtKvbtq8irLfuKHc-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/6ChBkMt5fwYPtKvbtq8irLfuKHc-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/6ChBkMt5fwYPtKvbtq8irLfuKHc-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/6ChBkMt5fwYPtKvbtq8irLfuKHc-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/6ChBkMt5fwYPtKvbtq8irLfuKHc-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/6ChBkMt5fwYPtKvbtq8irLfuKHc.jpg 600w",
                        ],
                    ],
                    [
                        "id" =>
                            "%ec%8a%a4%ed%91%bc%ed%92%80-%ec%98%a4%eb%b8%8c-%ec%8a%88%ea%b0%80",
                        "year" => "2022",
                        "title" => "스푼풀 오브 슈가",
                        "titleEn" => "Spoonful of Sugar",
                        "genres" => ["공포", "서양영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/q4cLHdAJCHqAWIIplCHEyG6JgrR-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/q4cLHdAJCHqAWIIplCHEyG6JgrR-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/q4cLHdAJCHqAWIIplCHEyG6JgrR-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/q4cLHdAJCHqAWIIplCHEyG6JgrR-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/q4cLHdAJCHqAWIIplCHEyG6JgrR-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/q4cLHdAJCHqAWIIplCHEyG6JgrR-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/q4cLHdAJCHqAWIIplCHEyG6JgrR-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/q4cLHdAJCHqAWIIplCHEyG6JgrR.jpg 600w",
                        ],
                    ],
                    [
                        "id" =>
                            "%eb%91%90-%ec%97%ac%ec%9e%90-%ed%94%bc%ec%9d%98-%eb%b3%b5%ec%88%98",
                        "year" => "2015",
                        "title" => "두 여자: 피의 복수",
                        "titleEn" => "Even Lambs Have Teeth",
                        "genres" => ["공포", "서양영화", "스릴러"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/9oH7VCWZTQeUOzufK5F9GkkwdbJ.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/9oH7VCWZTQeUOzufK5F9GkkwdbJ.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/9oH7VCWZTQeUOzufK5F9GkkwdbJ-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/9oH7VCWZTQeUOzufK5F9GkkwdbJ-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/9oH7VCWZTQeUOzufK5F9GkkwdbJ-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/9oH7VCWZTQeUOzufK5F9GkkwdbJ-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/9oH7VCWZTQeUOzufK5F9GkkwdbJ-32x48.jpg 32w",
                        ],
                    ],
                    [
                        "id" => "%ec%96%b4-%ed%95%98%ec%9d%b4%ec%96%b4-%eb%a1%9c",
                        "year" => "2023",
                        "title" => "어 하이어 로",
                        "titleEn" => "Balaur",
                        "genres" => ["드라마", "로맨스", "서양영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/gyJrK89QJlW7Uhsa6lSPZnrxbup.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/gyJrK89QJlW7Uhsa6lSPZnrxbup.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/gyJrK89QJlW7Uhsa6lSPZnrxbup-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/gyJrK89QJlW7Uhsa6lSPZnrxbup-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/gyJrK89QJlW7Uhsa6lSPZnrxbup-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/gyJrK89QJlW7Uhsa6lSPZnrxbup-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/gyJrK89QJlW7Uhsa6lSPZnrxbup-32x48.jpg 32w",
                        ],
                    ],
                    [
                        "id" =>
                            "%ec%bc%80%eb%b9%88-%ed%95%98%ed%8a%b8-%eb%82%b4-%eb%a9%8b%eb%8c%80%eb%a1%9c-%ec%82%b0%eb%8b%a4",
                        "year" => "2019",
                        "title" => "케빈 하트: 내 멋대로 산다",
                        "titleEn" => "Kevin Hart: Irresponsible",
                        "genres" => ["서양영화", "코미디"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/fpFsj2IQekSi9cjOlRyBiIoLBY4-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/fpFsj2IQekSi9cjOlRyBiIoLBY4-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/fpFsj2IQekSi9cjOlRyBiIoLBY4-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/fpFsj2IQekSi9cjOlRyBiIoLBY4-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/fpFsj2IQekSi9cjOlRyBiIoLBY4-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/fpFsj2IQekSi9cjOlRyBiIoLBY4-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/fpFsj2IQekSi9cjOlRyBiIoLBY4-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/fpFsj2IQekSi9cjOlRyBiIoLBY4.jpg 600w",
                        ],
                    ],
                    [
                        "id" => "%ec%99%84%eb%b2%bd%ed%95%9c-%ec%bb%a4%ed%94%8c",
                        "year" => "2019",
                        "title" => "완벽한 커플",
                        "titleEn" => "Das schönste Paar",
                        "genres" => ["TV 영화", "드라마", "서양영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/h4jGfhWaR4KaCjjrzUQqvsHS5wb-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/h4jGfhWaR4KaCjjrzUQqvsHS5wb-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/h4jGfhWaR4KaCjjrzUQqvsHS5wb-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/h4jGfhWaR4KaCjjrzUQqvsHS5wb-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/h4jGfhWaR4KaCjjrzUQqvsHS5wb-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/h4jGfhWaR4KaCjjrzUQqvsHS5wb-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/h4jGfhWaR4KaCjjrzUQqvsHS5wb-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/h4jGfhWaR4KaCjjrzUQqvsHS5wb.jpg 600w",
                        ],
                    ],
                    [
                        "id" => "%eb%b0%9c%ec%bd%94%eb%8b%88-%eb%ac%b4%eb%b9%84",
                        "year" => "2021",
                        "title" => "발코니 무비",
                        "titleEn" => "Film balkonowy",
                        "genres" => ["다큐멘터리", "서양영화"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/zXyprygKtN4mqyalPmjyr7CdABe-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/zXyprygKtN4mqyalPmjyr7CdABe-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/zXyprygKtN4mqyalPmjyr7CdABe-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/zXyprygKtN4mqyalPmjyr7CdABe-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/zXyprygKtN4mqyalPmjyr7CdABe-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/zXyprygKtN4mqyalPmjyr7CdABe-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/zXyprygKtN4mqyalPmjyr7CdABe-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/zXyprygKtN4mqyalPmjyr7CdABe.jpg 600w",
                        ],
                    ],
                    [
                        "id" => "%ec%82%ac%eb%9e%91%ec%9d%98-%ec%a0%84%ec%88%a0",
                        "year" => "2022",
                        "title" => "사랑의 전술",
                        "titleEn" => "Aşk Taktikleri",
                        "genres" => ["로맨스", "서양영화", "코미디"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/9mXoEX9RNsRD1bG8nmJSfhwaM3O-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/9mXoEX9RNsRD1bG8nmJSfhwaM3O-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/9mXoEX9RNsRD1bG8nmJSfhwaM3O-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/9mXoEX9RNsRD1bG8nmJSfhwaM3O-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/9mXoEX9RNsRD1bG8nmJSfhwaM3O-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/9mXoEX9RNsRD1bG8nmJSfhwaM3O-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/9mXoEX9RNsRD1bG8nmJSfhwaM3O-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/9mXoEX9RNsRD1bG8nmJSfhwaM3O.jpg 600w",
                        ],
                    ],
                    [
                        "id" => "%ec%95%84%eb%ac%b4%ed%8a%bc-%ec%9a%b0%eb%a6%ac",
                        "year" => "2019",
                        "title" => "아무튼, 우리",
                        "titleEn" => "A pesar de todo",
                        "genres" => ["서양영화", "코미디"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/1GH4KCS8IgWcDt5toXYFYX5AmX4-300x450.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/1GH4KCS8IgWcDt5toXYFYX5AmX4-300x450.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/1GH4KCS8IgWcDt5toXYFYX5AmX4-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/1GH4KCS8IgWcDt5toXYFYX5AmX4-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/1GH4KCS8IgWcDt5toXYFYX5AmX4-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/1GH4KCS8IgWcDt5toXYFYX5AmX4-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/1GH4KCS8IgWcDt5toXYFYX5AmX4-32x48.jpg 32w, https://image002.modooup.com/wp-content/uploads/2023/08/1GH4KCS8IgWcDt5toXYFYX5AmX4.jpg 600w",
                        ],
                    ],
                    [
                        "id" =>
                            "%eb%a8%b8%eb%a3%ac-%eb%b2%a0%eb%a0%88-%ec%9d%b4%eb%a6%84%ec%97%86%eb%8a%94-%ec%98%81%ec%9b%85",
                        "year" => "2017",
                        "title" => "머룬 베레: 이름없는 영웅",
                        "titleEn" => "Bordo Bereliler: Suriye",
                        "genres" => ["모험", "서양영화", "액션", "전쟁"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/2YzPrusfOZLa1dJTcI5RBDqnUFp.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/2YzPrusfOZLa1dJTcI5RBDqnUFp.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/2YzPrusfOZLa1dJTcI5RBDqnUFp-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/2YzPrusfOZLa1dJTcI5RBDqnUFp-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/2YzPrusfOZLa1dJTcI5RBDqnUFp-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/2YzPrusfOZLa1dJTcI5RBDqnUFp-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/2YzPrusfOZLa1dJTcI5RBDqnUFp-32x48.jpg 32w",
                        ],
                    ],
                    [
                        "id" =>
                            "%eb%84%88%eb%a5%bc-%ec%82%ac%eb%9e%91%ed%96%88%eb%8d%98-%ed%95%9c-%ec%82%ac%eb%9e%8c%ec%9d%98-%eb%82%98%ec%97%90%ea%b2%8c",
                        "year" => "2022",
                        "title" => "너를 사랑했던 한 사람의 나에게",
                        "titleEn" => "君を愛したひとりの僕へ",
                        "genres" => ["SF", "동양영화", "로맨스", "애니메이션"],
                        "thumbnail" => [
                            "src" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/fcxJCxRSJQ9XLJUjjDdHJvuLYWm.jpg",
                            "srcset" =>
                                "https://image002.modooup.com/wp-content/uploads/2023/08/fcxJCxRSJQ9XLJUjjDdHJvuLYWm.jpg 300w, https://image002.modooup.com/wp-content/uploads/2023/08/fcxJCxRSJQ9XLJUjjDdHJvuLYWm-200x300.jpg 200w, https://image002.modooup.com/wp-content/uploads/2023/08/fcxJCxRSJQ9XLJUjjDdHJvuLYWm-150x225.jpg 150w, https://image002.modooup.com/wp-content/uploads/2023/08/fcxJCxRSJQ9XLJUjjDdHJvuLYWm-16x24.jpg 16w, https://image002.modooup.com/wp-content/uploads/2023/08/fcxJCxRSJQ9XLJUjjDdHJvuLYWm-24x36.jpg 24w, https://image002.modooup.com/wp-content/uploads/2023/08/fcxJCxRSJQ9XLJUjjDdHJvuLYWm-32x48.jpg 32w",
                        ],
                    ],
                ]
            ]
        ];
        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
