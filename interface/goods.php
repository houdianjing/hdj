<?php
/**
 * Created by PhpStorm.
 * 功能：商品的接口信息
 * User: huangxq
 * Date: 2016/9/2
 * Time: 16:54
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/../mobile/includes/init.php');
require(dirname(__FILE__) .'/common.php');

$weburl = $_SERVER['HTTP_HOST'];


//action判断
$action = $_GET['action'];
$actionslist = array('promotion', 'new', 'hot');
if( !in_array( $action, $actionslist )){
    $result = array('errorno' => 101, 'msg' => '非法的url提交');
    exit(json_encode( $result ) );
}

//key值验证
$key = $_GET['sign'];
$calucatekey = sortMap($_GET);
if( $key != $calucatekey ){
    $result = array('errorno' => 102, 'msg' => '伪造的key');
    exit(json_encode( $result ) );
}

$promotion_goods = app_get_promote_goods();
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

$result = array('errorno'=>0, 'msg'=>'ok', 'result'=>$promotion_goods );
exit( json_encode( $result) );



