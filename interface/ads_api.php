<?php
/**
 * Created by PhpStorm.
 * Mud: user
 *
 * Date: 2016/9/12
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/../mobile/includes/init.php');
require(dirname(__FILE__) .'/common.php');


//action判断
$action = $_REQUEST['action'];
$actionslist = array('ads');
if (!in_array($action, $actionslist)) {
    $result = array('code' => 1, 'msg' => '非法的url提交');
    exit(json_encode($result));
}


if($action=='ads') {
    //发送验证码
    $result = array();
    //key值验证
   /* if (defined('ADS')) {
        $key = $_REQUEST['sign'];
        $calucatekey = sortMap($_POST);
        if ($key != $calucatekey) {
            $result = array('code' => -1, 'msg' => '验证码签名不正确');
            exit(json_encode($result));
        }
    }*/
	//echo '<pre>';
	$weburl = $_SERVER['HTTP_HOST'];
	$ads=get_flash_xml();
	foreach($ads as $k=>$v){
		foreach($v as $k2=>$vo){
			if( $k2 == "src" ){
          $ads[$k][$k2] = $weburl."/bbcshop/".$vo;
      }
		}
	}
	//var_dump($ads);
	if($ads){
		 $result = array('code' => 0, 'msg' => '轮播图片获取成功','data'=>$ads);
            exit(json_encode($result));
	}else{
		$result = array('code' =>-1 , 'msg' => '轮播图片获取失败');
            exit(json_encode($result));
		
	} 

   
}

	/***获取首页广告***/
function get_flash_xml()
{
    $flashdb = array();
	
    if (file_exists( '../data/flash_data.xml'))
    {
		

        // 兼容v2.7.0及以前版本
        if (!preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"/', file_get_contents('../data/flash_data.xml'), $t, PREG_SET_ORDER))
        {
            preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"/', file_get_contents( '../data/flash_data.xml'), $t, PREG_SET_ORDER);
        }

        if (!empty($t))
        {
            foreach ($t as $key => $val)
            {
                $val[4] = isset($val[4]) ? $val[4] : 0;
                $flashdb[] = array('src'=>$val[1],'url'=>$val[2],'text'=>$val[3],'sort'=>$val[4]);
				
				//print_r($flashdb);
            }
        }
    }
    return $flashdb;
}












