<?php
/**
 * Created by PhpStorm.
 * User: huangxq
 * function：接口的通用处理函数
 * Date: 2016/9/2
 * Time: 18:26
 */

//验证签名算法
 function sortMap($data){
    $secretkey = "wuxibuy";
     unset($data['sign']);
     unset($data['XDEBUG_SESSION_START']);
    ksort( $data );
    $string = "";
    $dot = "";
    foreach( $data as $key=>$val ){
        $string .=$dot.$key."=".$val;
        $dot = "&";
    }
    $string .= "&key=".$secretkey;
    $string = md5(md5($string));
    return $string;
}