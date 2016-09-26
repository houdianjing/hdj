<?php
/**
 * Created by PhpStorm.
 * Mud: user
 *
 * Date: 2016/9/12
 */
define('IN_ECS', true);
define('KAI',true);
require(dirname(__FILE__) . '/../mobile/includes/init.php');
require(dirname(__FILE__) .'/common.php');
require_once (ROOT_PATH . 'sms/sms.php');

//action判断
$action = $_REQUEST['action'];
$actionslist = array('code', 'register','repwd','login','logout','threelogin');
if (!in_array($action, $actionslist)) {
    $result = array('code' => 1, 'msg' => '非法的url提交');
    exit(json_encode($result));
}

//key值验证
if (defined('KAI')) {
    $key = $_REQUEST['sign'];
    $calucatekey = sortMap($_POST);
    if ($key != $calucatekey) {
        $result = array('code' => 2, 'msg' => '验证码签名不正确');
        exit(json_encode($result));
    }
}

if($action=='code') {
    //发送验证码
    $result = array();

    //手机号码格式验证
    $mobile = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
    $pwd = isset($_REQUEST['pwd']) ? trim($_REQUEST['pwd']) : '';

    if (empty($mobile)) {

        $result = array('code' => -3, 'msg' => '手机号码不能为空');
        exit(json_encode($result));
    }

    if($user->check_mobile_phone($mobile))
    {
        $result = array('code' => -9, 'msg' => '手机号码已经注册');
        exit(json_encode($result));
    }

    if (empty($pwd)) {

        $result = array('code' => -6, 'msg' => '密码不能为空');
        exit(json_encode($result));
    }

    $preg = '/^1[0-9]{10}$/'; // 简单的方法
    if (!preg_match($preg, $mobile)) {
        $result = array('code' => -4, 'msg' => '手机号码格式不正确');
        exit(json_encode($result));
    }

    //发送短信验证码
    $mobile_code = random(4, 1);
    $content = sprintf($GLOBALS['_CFG']['sms_register_tpl'], $mobile_code, $GLOBALS['_CFG']['sms_sign']);
    $num = sendSMS($mobile, $content);
    if ($num == true) {
        @file_put_contents("data/data.txt",$mobile.';'.$mobile_code.';'.$pwd);
        $result = array('code' => 0, 'msg' => '手机验证码发送成功');
        exit(json_encode($result));
    } else {
        $result = array('code' => -5, 'msg' => '手机验证码发送失败');
        exit(json_encode($result));
    }
}else if($action=='register'){

    $result=array();
    //手机号码格式验证
    $mobile = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
    $pwd= isset($_REQUEST['pwd']) ? trim($_REQUEST['pwd']) : '';
    $code= isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';

    if (empty($mobile)) {

        $result = array('code' => -1, 'msg' => '密码不能为空');
        exit(json_encode($result));
    }
    if (empty($pwd)) {

        $result = array('code' => -2, 'msg' => '手机号码不能为空');
        exit(json_encode($result));
    }
    $preg = '/^1[0-9]{10}$/'; // 简单的方法
    if (!preg_match($preg, $mobile)) {
        $result = array('code' => -3, 'msg' => '手机号码格式不正确');
        exit(json_encode($result));
    }
   if($user->check_mobile_phone($mobile))
    {
        $result = array('code' => -4, 'msg' => '手机号码已经注册');
        exit(json_encode($result));
    }

    $arr=explode(';',file_get_contents("data/data.txt"));
    if(!in_array($code,$arr)){
        $result = array('code' => -20, 'msg' => '验证码不正确');
        exit(json_encode($result));
    }

    if(!in_array($mobile,$arr)){
        $result = array('code' => -21, 'msg' => '手机不正确');
        exit(json_encode($result));
    }
    if(!in_array($pwd,$arr)){
        $result = array('code' => -22, 'msg' => '密码不正确');
        exit(json_encode($result));
    }

        $username = generate_username_by_mobile($mobile);
       // echo $username;exit;
        /* 手机注册 */
        $res=$db->autoExecute($ecs->table('users'),array('is_login'=>1,'user_name'=>$username,'mobile_phone'=>$mobile,'password'=>md5($pwd),'froms'=>'app'));

       if($res){
           $result = array('code' => 0, 'msg' => '注册成功','id'=> $db->insert_id());
           @unlink('data/data.txt');
           exit(json_encode($result));
       }else{
           $result = array('code' => -5, 'msg' => '注册失败');
           exit(json_encode($result));
       }

}else if($action=='login'){
    //登录操作
   $result = array();

    $mobile=isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']):'';
    $pwd=isset($_REQUEST['pwd']) ? trim($_REQUEST['pwd']):'';
    if(empty($mobile)){
        $result = array('code' => -2, 'msg' => '账号不能为空');
        exit(json_encode($result));
    }
    if(empty($pwd)){
        $result = array('code' => -3, 'msg' => '密码不能为空');
        exit(json_encode($result));
    }

    $res01=$db->getRow("select user_id,mobile_phone,password,is_login from ".$ecs->table('users')." where mobile_phone={$mobile} ");

    if($res01['user_id']){
        if($res01['is_login']!=1){
            $result = array('code' => -6, 'msg' => '用户已登录');
            exit(json_encode($result));
        }
            if($res01['password']==md5($pwd)){
                $db->query("update ".$ecs->table('users')." set is_login=2 where user_id={$res01['user_id']}");
                $result = array('code' => '0', 'msg' => '登录成功','id'=>$res01['user_id']);
                exit(json_encode($result));
            }else{
                $result = array('code' => -5, 'msg' => '密码不正确');
                exit(json_encode($result));
            }
    }else{
        $result = array('code' => -4, 'msg' => '用户不存在');
        exit(json_encode($result));
    }

}else if($action=='logout'){
    //注销
    $result = array();
	
	/*$today = date("Y-m-d",time());
        $alipaydir = "log/";
        if( !is_dir( $alipaydir ) )
            mkdir( $alipaydir );
        $alipaylog = $alipaydir.$today.".txt";
        if( !is_file( $alipaylog) )
            $file = fopen( $alipaylog, "w");
        else
            $file = fopen( $alipaylog, "a+");*/
    
    $name=isset($_REQUEST['name']) ? trim($_REQUEST['name']):'';


    if($name){
        $res=$db->query("update ".$ecs->table('users')." set is_login=1 where user_name='{$name}' or mobile_phone='$name'");
        
    }
    if($res){
        $result = array('code' => '0', 'msg' => '退出成功');
        exit(json_encode($result));
    }else{
        $result = array('code' => '-3', 'msg' => '退出失败');
        exit(json_encode($result));
    }

}else if($action=='repwd'){
    //密码重置
    $result = array();

    $mobile=isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']):'';
    $pwd=isset($_REQUEST['pwd']) ? trim($_REQUEST['pwd']):'';
    if(empty($mobile)){
        $result = array('code' => -2, 'msg' => '账号不能为空');
        exit(json_encode($result));
    }
    $res01=$db->getRow("select user_id,mobile_phone,password,is_login from ".$ecs->table('users')." where mobile_phone={$mobile}");
    if($res01['user_id']) {
        $res = $db->query("update " . $ecs->table('users') . " set is_login=1,password='" . md5($pwd) . "'  where mobile_phone={$mobile}");
        if ($res) {
            $result = array('code' => '0', 'msg' => '密码修改成功');
            exit(json_encode($result));
        } else {
            $result = array('code' => '-3', 'msg' => '密码修改失败');
            exit(json_encode($result));
        }

    }else{
        $result = array('code' => -4, 'msg' => '用户不存在');
        exit(json_encode($result));
    }
}else if($action=='threelogin'){

    //第三方登录
    $result = array();

    $name=isset($_REQUEST['name']) ? trim($_REQUEST['name']):'';
    $pwd=isset($_REQUEST['pwd']) ? trim($_REQUEST['pwd']):'';
    $type=isset($_REQUEST['type']) ? trim($_REQUEST['type']):'';

    if(empty($name)){
        $result = array('code' => -2, 'msg' => '账号不能为空');
        exit(json_encode($result));
    }
    if(empty($pwd)){
        $result = array('code' => -3, 'msg' => '密码不能为空');
        exit(json_encode($result));
    }
    if(empty($type)){
        $result = array('code' => -4, 'msg' => '类型不能为空');
        exit(json_encode($result));
    }

    $cname=$type.$name;

    $res=$db->getRow("select user_id,user_name,password,is_login from ".$ecs->table('users')." where user_name='{$cname}' and froms='{$type}'");
    //var_dump($res);exit;
    if($res && $res['is_login']==2){

        $result = array('code' => -8, 'msg' => '用户已登录');
        exit(json_encode($result));
    }
    if($res && $res['is_login']==1){
        $res3=$db->query("update ".$ecs->table('users')." set is_login=2 where user_name='{$cname}'");
        $result = array('code' => 0, 'msg' => '登录成功','id'=>$res['user_id']);
        exit(json_encode($result));
    }

    $aid=$type.'_'.random(6, 0);
    $res2=$db->autoExecute($ecs->table('users'),array('is_login'=>2,'user_name'=>$cname,'password'=>md5($pwd),'froms'=>$type,'aite_id'=>$aid));
    if($res2){
        $result = array('code' => 0, 'msg' => '登录成功','id'=>$db->insert_id());
        exit(json_encode($result));
    }else{
        $result = array('code' => -7, 'msg' => '登录失败');
        exit(json_encode($result));
    }

}

//生成账号
function generate_username_by_mobile ($mobile)
{

    $username = 'u'.substr($mobile, 0, 3);
    $charts = "ABCDEFGHJKLMNPQRSTUVWXYZ";
    $max = strlen($charts);
    for($i = 0; $i < 4; $i ++)
    {
        $username .= $charts[mt_rand(0, $max)];
    }
    $username .= substr($mobile, -4);
    $sql = "select count(*) from " . $GLOBALS['ecs']->table('users') . " where user_name = '$username'";
    $count = $GLOBALS['db']->getOne($sql);
    if($count > 0)
    {
        return generate_username_by_mobile();
    }
    return $username;
}










