<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-type: text/plain");
header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if( $_REQUEST['url'] != '' ){  
	$arr_link = array(
		'gamejuicy.com_post' => '5',
		'kokoatv.net_post' => '73',
		'dietwhen.com_post' => '2',
		'apkoxa.com_post' => '20',

	); 

	$arr_child = array(
	
	);
	if( isset($arr_link['apkoxa.com_post']) ){
		$list_post = @file_get_contents(__DIR__.'/outlink/list_post_apkoxa.json');
		$list_post = json_decode($list_post);
		if( is_array($list_post) && count($list_post) > 0 ){
			foreach ($list_post as $k => $v){
				$arr_child['apkoxa.com_post'][] = $v;
			}
		}
	}

	if( isset($arr_link['dietwhen.com_post']) ){
		$list_post = @file_get_contents(__DIR__.'/outlink/list_dietwhen.json');
		$list_post = json_decode($list_post);
		if( is_array($list_post) && count($list_post) > 0 ){
			foreach ($list_post as $k => $v){
				$arr_child['dietwhen.com_post'][] = $v;
			}
		}
	}


	if( isset($arr_link['kokoatv.net_post']) ){
		$list_post = @file_get_contents(__DIR__.'/outlink/list_kokoa.json');
		$list_post = json_decode($list_post);
		if( is_array($list_post) && count($list_post) > 0 ){
			foreach ($list_post as $k => $v){
				$arr_child['kokoatv.net_post'][] = $v;
			}
		}
	}

	if( isset($arr_link['gamejuicy.com_post']) ){
		$list_post = @file_get_contents(__DIR__.'/outlink/list_gamejuicy.json');
		$list_post = json_decode($list_post);
		if( is_array($list_post) && count($list_post) > 0 ){
			foreach ($list_post as $k => $v){
				$arr_child['gamejuicy.com_post'][] = $v;
			}
		}
	}
	



	$newLinks = array();
	$newLinksC = array();
	foreach ($arr_link as $k => $v){
		$v = doubleval($v);
		$v = intval($v);
		if( isset($arr_child[$k]) ){
			$child_count = count($arr_child[$k]);
			$child_num = floor( $v / $child_count );
			$child_plus = 0;
			if( $child_num*$child_count < $v ){
				$child_plus = $v - ( $child_num*$child_count );
			}

			$plus = $child_num;
			$total_plus = $plus;
			$add_m = 0;

			foreach ($arr_child[$k] as $k1 => $v1) {
				$add_m++;
				$link_n = "";
				if( $k1 == 0 ){ 
					$plus = $total_plus + $child_plus;
				}else{ 
					if( $total_plus != 0 ){
						$plus = $total_plus;
					}
				}

				if($plus == $v && $plus <= $child_count){
					$add = 1;
					$plus = $add;
				}else $add = $plus;
				if( $add_m > $v && $add > 0 ){
					$add = 0;
				}
				if( strpos($k,"_post") !== false || strpos($k,"_page") !== false  ){
					$nk = str_replace("_post","",$k);
					$nk = str_replace("_page","",$nk);
					$link_n = $nk."/".$v1;
				}else $link_n = $k."/".$v1;
				$newLinksC = array_merge($newLinksC, array_fill(0, $add, $link_n));	
			}
		}
	}
	$newData = array_merge( $newLinksC , $newLinks );

	$nlink = $newData[array_rand($newData)];
	echo "https://".$nlink;
}
?>
