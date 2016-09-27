<?php
/**
 * Created by PhpStorm.
 * 功能：获取商品详细
 * User: hdj
 * Date: 2016/9/23
 * Time: 11:54
 */

/* 商品详情接口说明
 *   返回所有商品：/interface/goods_info_api.php?action=info&id=100&uid=61
 *   post参数
 *      action：info:获取产品信息 必填
 *          id：获取产品id 必填
 *         uid:用户id 根据id获取会员折扣 选填  不填显示默认价格
 *   返回值
 *        1：url地址错误
 *        2：sign伪造 不正确
 *        101：商品id为空
 *        102：商品id不存在
 *        103：成功获取json数据
 *
 * */

define('IN_ECS', true);
require(dirname(__FILE__) . '/../mobile/includes/init.php');
require(dirname(__FILE__) .'/common.php');

$weburl = $_SERVER['HTTP_HOST'];
//action判断
$action = $_REQUEST['action'];
$actionslist = array('info');
if( !in_array( $action, $actionslist )){
    $result = array('code' => 1, 'msg' => '非法的url提交');
    exit(json_encode( $result ) );
}
//
////key值验证
//$key = $_REQUEST['sign'];
//$calucatekey = sortMap($_GET);
//if( $key != $calucatekey ){
//    $result = array('code' => 2, 'msg' => '伪造的key');
//    exit(json_encode( $result ) );
//}

if($action=='info') {
    if (isset($_REQUEST['id'])){
        $goods_id = intval($_REQUEST['id']);
    }else{
        $result = array('code'=>101,'msg' => '商品id不得为空');
        exit(json_encode($result));
    }

    //获取用户id  根据用户id获取用户等级折扣
    $uid= isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : '0';
    $userinfo=get_myuser_info($uid);
    //获取商品详细
    $goods = get_mygoods_info($goods_id,$userinfo['user_rank'],$userinfo['user_discount']);
    if(!$goods){
        $result = array('code'=>102,'msg' => '商品id不存在');
        exit(json_encode($result));
    }
    //计算积分 -1 为折扣价格 大于0位输入价格 否则为0
    if($goods["give_integral"] == '-1'){
        $give_integral=price_format($goods['rank_price']);
    }elseif($goods["give_integral"] > 0){
        $give_integral=$goods["give_integral"];
    }else{
        $give_integral=0;
    }
    $arr['goods_id']         = $goods['goods_id'];  //商品id
    $arr['goods_name']       = $goods['goods_name'];  //商品名称
    $arr['goods_brand']       = $goods['goods_brand'];  //商品名称
    $arr['goods_number']     = $goods['goods_number'];  //商品可用量
    $arr['is_new']           = $goods['is_new'];  //商品是否新品
    $arr['is_hot']           = $goods['is_hot'];  //商品是否热销
    $arr['is_best']          = $goods['is_best'];  //商品是否精品
    $arr['goods_brief']      = $goods['goods_brief'];  //商品简单描述
    $arr['goods_style_name'] = add_style($goods['goods_name'],$goods['goods_name_style']);  //商品名称 加style
    $arr['market_price']     = price_format($goods['market_price'],false);  //商品市场价格
    $arr['shop_price']       = price_format($goods['shop_price'],false);  //商品价格，可忽略 用rank_price
    $arr['rank_price']       = price_format($goods['rank_price'],false); // //商品价格  已根据等级折扣计算
    $arr['exclusive']         = $goods['exclusive'];  //商品手机专享价
    $arr['is_promote']       = $goods['is_promote'];  //商品是否促销
    $arr['promote_price']    = ($goods['promote_price'] > 0) ? price_format($goods['promote_price']) : '';  //商品促销价格
    $arr['promote_start_date']       = $goods['promote_start_date'];  //商品促销截止时间
    $arr['promote_end_date']       = $goods['promote_end_date'];  //商品促销截止时间
    $arr['is_buy']       = $goods['is_buy'];  //商品是否限购
    $arr['buymax']       = $goods['buymax'];  //商品限购数量
    $arr['buymax_start_date']       = $goods['buymax_start_date'];  //商品限购开始
    $arr['buymax_end_date']       = $goods['buymax_end_date'];  //商品限购结束
    $arr['type']             = $goods['goods_type'];  //商品所属类型
    $arr['goods_thumb']      = "http://".$weburl."/".get_image_path($goods['goods_id'], $goods['goods_thumb'], true);  //商品小图
    $arr['goods_img']        = "http://".$weburl.'/'.get_image_path($goods['goods_id'], $goods['goods_img']);  //商品大图
    $arr['url']              = "http://".$weburl.'/mobile/'.build_uri('goods', array('gid'=>$goods['goods_id']), $row['goods_name']); //商品url
    $arr['click_count']      = $goods['click_count'];  //商品浏览次数
    $arr['give_integral']      = $give_integral;  //商品购买赠送积分
    $arr['goods_weight']      = $goods['goods_weight']; //商品重量
    $arr['add_time']      = $goods['add_time']; //商品上架日期
    $arr['comment_rank']      = $goods['comment_rank']; //商品星级 1星到5星
    $arr['supplier'] = $goods['supplier_id'];  //商品所属店铺id 0为自营
    $arr['rank_discount'] = number_format(($arr['shop_price']/$arr['market_price'])*10,1);  //会员折扣
    $arr['sales_num']=selled_count($goods['goods_id']); //商品销售数量
    $arr['Comments_num']=get_evaluation_sum($goods['goods_id']); //商品评价数量
    $arr['img_gallery'] = get_mygoods_gallery($goods['goods_id'],$weburl); //商品图片列表
    $arr['promote_info'] =  get_promotion_info($goods['goods_id'],$goods['supplier_id']);//商品促销信息
    $arr['rank_price_list'] =  get_user_rank_prices($goods['goods_id'], $goods['shop_price']); //商品会员等级价格
    $arr['goods_specification'] =  $properties = get_goods_properties($goods['goods_id']); //商品规格属性
    $arr['goods_desc']=goods_desc_cl($goods['goods_desc'],$weburl); //商品描述
    //print_r($arr);
    $result = array('code'=>103,'msg' => '成功获取商品信息','data' =>$arr);
    exit(json_encode($result));
}

/**
 * 处理产品描述图片网址
 *
 * @access  public
 * @param   $content     $weburl
 * @return  array
 */
function goods_desc_cl($content,$weburl){

    $content= preg_replace('/src="http:\/\/'.$weburl.'\/includes\/ueditor\/php\/..\/..\/..\//','src="/',$content);
    $content= preg_replace('/src="\/bdimages\//','src="http://'.$weburl.'/bdimages/',$content);
    $content=preg_replace('/src="\/images\//','src="http://'.$weburl.'/images/',$content);
    return $content;
}

/**
 * 获得指定商品的相册
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  array
 */
function get_mygoods_gallery($goods_id,$weburl)
{
    $sql = 'SELECT img_id, img_url, thumb_url, img_desc' .
        ' FROM ' . $GLOBALS['ecs']->table('goods_gallery') .
        " WHERE goods_id = '$goods_id' LIMIT " . $GLOBALS['_CFG']['goods_gallery_number'];
    $row = $GLOBALS['db']->getAll($sql);
    /* 格式化相册图片路径 */
    foreach($row as $key => $gallery_img)
    {
        $row[$key]['img_url'] = "http://".$weburl.'/'.$gallery_img['img_url'];
        $row[$key]['thumb_url'] ="http://".$weburl.'/'.$gallery_img['thumb_url'];
    }
    return $row;
}

/**
 * 获得指定商品的各会员等级对应的价格
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  array
 */
function get_user_rank_prices($goods_id, $shop_price)
{
    $sql = "SELECT rank_id, IFNULL(mp.user_price, r.discount * $shop_price / 100) AS price, r.rank_name, r.discount " .
        'FROM ' . $GLOBALS['ecs']->table('user_rank') . ' AS r ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . " AS mp ".
        "ON mp.goods_id = '$goods_id' AND mp.user_rank = r.rank_id " .
        "WHERE r.show_price = 1 OR r.rank_id = '$_SESSION[user_rank]'";
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {

        $arr[$row['rank_id']] = array(
            'rank_name' => htmlspecialchars($row['rank_name']),
            'price'     => price_format($row['price']));
    }

    return $arr;
}
/**
 * 获得商品的详细信息
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  void
 */
function  get_mygoods_info($goods_id,$user_rank=0,$user_discount=1)
{
    $time = gmtime();
    $sql = 'SELECT g.*, c.measure_unit, b.brand_id, b.brand_name AS goods_brand, m.type_money AS bonus_money, ' .
        'IFNULL(AVG(r.comment_rank), 0) AS comment_rank, ' .
        "IFNULL(mp.user_price, g.shop_price * '$user_discount') AS rank_price " .
        'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('category') . ' AS c ON g.cat_id = c.cat_id ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON g.brand_id = b.brand_id ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('comment') . ' AS r '.
        'ON r.id_value = g.goods_id AND comment_type = 0 AND r.parent_id = 0 AND r.status = 1 ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('bonus_type') . ' AS m ' .
        "ON g.bonus_type_id = m.type_id AND m.send_start_date <= '$time' AND m.send_end_date >= '$time'" .
        " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
        "ON mp.goods_id = g.goods_id AND mp.user_rank = '$user_rank' ".
        "WHERE g.goods_id = '$goods_id' AND g.is_delete = 0 " .
        "GROUP BY g.goods_id";
    $row = $GLOBALS['db']->getRow($sql);
    if ($row !== false)
    {
        /* 用户评论级别取整 */
        $row['comment_rank']  = ceil($row['comment_rank']) == 0 ? 5 : ceil($row['comment_rank']);

        /* 获得商品的销售价格 */
        $row['market_price']        = price_format($row['market_price']);
        $row['shop_price_formated'] = price_format($row['shop_price']);

        /* 修正促销价格 */
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }

        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($promote_price != 0)
        {
            $watermark_img = "watermark_promote";
        }
        elseif ($row['is_new'] != 0)
        {
            $watermark_img = "watermark_new";
        }
        elseif ($row['is_best'] != 0)
        {
            $watermark_img = "watermark_best";
        }
        elseif ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot';
        }

        if ($watermark_img != '')
        {
            $row['watermark_img'] =  $watermark_img;
        }

        $row['promote_price_org'] =  $promote_price;
        $row['promote_price'] =  price_format($promote_price);

        /* 修正重量显示 */
        $row['goods_weight']  = (intval($row['goods_weight']) > 0) ?
            $row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] :
            ($row['goods_weight'] * 1000) . $GLOBALS['_LANG']['gram'];

        /* 修正上架时间显示 */
        $row['add_time']      = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);

        /* 促销时间倒计时 */
        $time = gmtime();
        if ($time >= $row['promote_start_date'] && $time <= $row['promote_end_date'])
        {
            $row['gmt_end_time']  = $row['promote_end_date'];
        }
        else
        {
            $row['gmt_end_time'] = 0;
        }

        /* 是否显示商品库存数量 */
        $row['goods_number']  = ($GLOBALS['_CFG']['use_storage'] == 1) ? $row['goods_number'] : '';

        /* 修正积分：转换为可使用多少积分（原来是可以使用多少钱的积分） */
        $row['integral']      = $GLOBALS['_CFG']['integral_scale'] ? round($row['integral'] * 100 / $GLOBALS['_CFG']['integral_scale']) : 0;

        /* 修正优惠券 */
        $row['bonus_money']   = ($row['bonus_money'] == 0) ? 0 : price_format($row['bonus_money'], false);

        /* 修正商品图片 */
        //yyy修改start
       // $row['goods_img']   = get_pc_url().'/'.get_image_path($goods_id, $row['goods_img']);
       // $row['goods_thumb'] = get_pc_url().'/'.get_image_path($goods_id, $row['goods_thumb'], true);
        //yyy修改end


        return $row;
    }
    else
    {
        return false;
    }
}

/**
 *  获取用户信息数组
 *
 * @access  public
 * @param
 *
 * @return array
 * $user       用户信息数组
 */
function get_myuser_info($id=0)
{
    $sql  = 'SELECT u.user_id, u.user_name,u.user_rank'.
        ' FROM ' .$GLOBALS['ecs']->table('users'). ' AS u ' .
        " WHERE u.user_id = '$id'";
    $user = $GLOBALS['db']->getRow($sql);
    $bonus = get_user_bonus($id);
    if($user['user_rank'] == 0)
    {
        $user['user_rank']    = "0";
        $user['user_discount']=1;
    }else
    {
        $rank_id = $user['user_rank'];
        $sql = "SELECT rank_name,discount,rank_id FROM ".$GLOBALS['ecs']->table('user_rank')."WHERE rank_id='$rank_id'";
        $query= $GLOBALS['db']->getRow($sql);
        $user['user_rank']=$query['rank_id'];
        $user['user_discount']=$query['discount']/100;
    }

    return $user;
}
function tttttt(){
	
}