<?php
/**
 * Created by PhpStorm.
 * 功能：商品的接口信息
 * Date: 2016/9/2
 * Time: 16:54
 */

define('IN_ECS', true);
//define('PRO',true);
require(dirname(__FILE__) . '/../mobile/includes/init.php');
require(dirname(__FILE__) .'/common.php');

$weburl = $_SERVER['HTTP_HOST'];


//action判断
$action = $_REQUEST['action'];
$actionslist = array('promotion', 'new', 'hot');
$result=array();
if( !in_array( $action, $actionslist )){
    $result = array('code' => 1, 'msg' => '非法的url提交');
    exit(json_encode( $result ) );
}

if($action=='promotion'){

	if(defined('PRO')){
		//key值验证
		$key = $_GET['sign'];
		$calucatekey = sortMap($_POST);
		if( $key != $calucatekey ){
			$result = array('code' => -1, 'msg' => '验证不正确');
			exit(json_encode( $result ) );
		}
	}

	$promotion_goods = app_get_promote_goods();
	echo '<pre>';
	//var_dump( $promotion_goods );
	//转化图片地址和物品url为线上
	foreach ( $promotion_goods as $index => $row ){
	  foreach ( $row as $key => $value ){
		 if( $key == "thumb" || $key == "goods_img"){
			 $promotion_goods[$index][$key] = $weburl.str_replace("./..","",$value);
		 }
		  if( $key == "url" ){
			  $promotion_goods[$index][$key] = $weburl."/mobile/".$value;
		  }
	  }
	}
	var_dump($promotion_goods);exit;
	$result = array('code'=>0, 'msg'=>'ok', 'result'=>$promotion_goods );
	exit( json_encode( $result) );
}else if($action=='new'){
	
	if(defined('NEW')){
		//key值验证
		$key = $_GET['sign'];
		$calucatekey = sortMap($_POST);
		if( $key != $calucatekey ){
			$result = array('code' => -1, 'msg' => '验证不正确');
			exit(json_encode( $result ) );
		}
	}

	$promotion_goods = get_recommend_goods('new');
	echo '<pre>';
	//var_dump( $promotion_goods );
	//转化图片地址和物品url为线上
	foreach ( $promotion_goods as $index => $row ){
	  foreach ( $row as $key => $value ){
		 if( $key == "thumb" || $key == "goods_img"){
			 $promotion_goods[$index][$key] = $weburl.str_replace("./..","",$value);
		 }
		  if( $key == "url" ){
			  $promotion_goods[$index][$key] = $weburl."/mobile/".$value;
		  }
	  }
	}
	var_dump($promotion_goods);exit;
	$result = array('code'=>0, 'msg'=>'ok', 'result'=>$promotion_goods );
	exit( json_encode( $result) );
}else if($action=='hot'){
	
	if(defined('HOT')){
		//key值验证
		$key = $_GET['sign'];
		$calucatekey = sortMap($_POST);
		if( $key != $calucatekey ){
			$result = array('code' => -1, 'msg' => '验证不正确');
			exit(json_encode( $result ) );
		}
	}

	$promotion_goods = get_recommend_goods('hot');
	echo '<pre>';
	//var_dump( $promotion_goods );
	//转化图片地址和物品url为线上
	foreach ( $promotion_goods as $index => $row ){
	  foreach ( $row as $key => $value ){
		 if( $key == "thumb" || $key == "goods_img"){
			 $promotion_goods[$index][$key] = $weburl.str_replace("./..","",$value);
		 }
		  if( $key == "url" ){
			  $promotion_goods[$index][$key] = $weburl."/mobile/".$value;
		  }
	  }
	}
	var_dump($promotion_goods);exit;
	$result = array('code'=>0, 'msg'=>'ok', 'result'=>$promotion_goods );
	exit( json_encode( $result) );
}



