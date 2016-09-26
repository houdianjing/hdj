<?php
/**
 * Created by PhpStorm.
 * 功能：获取商品列表
 * User: hdj
 * Date: 2016/9/22
 * Time: 13:54
 */

/* 搜索接口说明
 *   返回所有商品：/interface/goods_info_api.php?action=list&catid=0
 *   返回分类为8的栏目及子栏目下的商品：/interface/goods_info_api.php?action=list&catid=8
 *   根据分类、会员id、店铺返回商品：/interface/goods_info_api.php?action=list&catid=0&uid=54&storeid=23
 *   post参数
 *      action：list:获取产品列表
 *      catid:获取分类id 必填
 *      uid:用户id 根据id获取会员折扣 选填  不填显示无折扣价格
 *      storeid:店铺id 获取该店铺的商品列表 获取全部店铺商品将cateid等于0  选填 不填显示所有的商品 值为0显示所有自营
 *
 * */

define('IN_ECS', true);
require(dirname(__FILE__) . '/../mobile/includes/init.php');
require(dirname(__FILE__) .'/common.php');

$weburl = $_SERVER['HTTP_HOST'];
//action判断
$action = $_REQUEST['action'];
$actionslist = array('list');
if( !in_array( $action, $actionslist )){
    $result = array('code' => 0, 'msg' => '非法的url提交');
    exit(json_encode( $result ) );
}

////key值验证
//$key = $_REQUEST['sign'];
//$calucatekey = sortMap($_GET);
//if( $key != $calucatekey ){
//    $result = array('code' => 0, 'msg' => '伪造的key');
//    exit(json_encode( $result ) );
//}

if($action=='list') {
    if (isset($_REQUEST['catid'])){
        $cat_id = intval($_REQUEST['catid']);
    }else{
        $result = array('code'=>0,'msg' => '访问错误','data' =>'');
        exit(json_encode($result));
    }
    $uid= isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : '0'; //获取用户id  根据用户id获取用户等级折扣
    $storeId= isset($_REQUEST['storeid']) ? intval($_REQUEST['storeid']) : '-1'; //获取店铺id 默认为-1，-1位全部商品 0为自营 其他为各店铺id值

     $userinfo=get_myuser_info($uid);
     $children = get_children($cat_id);
     $goodslist = category_get_goods($children,1,$userinfo['user_rank'],$userinfo['user_discount'],$storeId);
     $result = array('code'=>1,'msg' => '','data' =>$goodslist);
    exit(json_encode($result));

}

/**
 * 获得分类下的商品
 * $is_stock 库存
 * $user_rank 用户等级
 * $user_discount 用户折扣
 * $store 商铺id
 */
function category_get_goods($children, $is_stock = 0,$user_rank=0,$user_discount=1,$store=-1)
{
    $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ".
        "g.is_delete = 0 AND ($children OR " . get_extension_goods($children) . ')';
    //分区自营还是商家  $store等于-1 则显示所有（默认） 0显示自营  商家id 显示商家销售商品
    if($store==0){
        $where .= ' AND g.supplier_id=0';
    }elseif($store>0){
        $where .= ' AND g.supplier_id='.$store;
    }else{

    }

    //库存大于0的产品
    if(!empty($is_stock))
    {
        $where .= " AND g.goods_number > 0 ";
    }

    /* 获得商品列表 */
    $sql = "SELECT g.goods_id, g.goods_name, g.goods_name_style, g.click_count, g.goods_number, g.market_price, " .
        " g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, " .
        " IFNULL(mp.user_price, g.shop_price * '$user_discount') AS shop_price, g.promote_price, " .
        " IF(g.promote_price != '' " .
        " AND g.promote_start_date < " . gmtime() .
        " AND g.promote_end_date > " . gmtime() . ", g.promote_price, shop_price) " .
        " AS shop_p, g.goods_type, " .
        " g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img,g.supplier_id " .
        " FROM " . $GLOBALS['ecs']->table('goods') .
        " AS g " .
        " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') .
        " AS mp " .
        " ON mp.goods_id = g.goods_id " .
        " AND mp.user_rank = '$user_rank' " .
        " WHERE $where " .
        " ORDER BY g.last_update desc";

    $res = $GLOBALS['db']->query($sql);
    $count=$GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . " AS g WHERE $where");
    $arr = array();
    $arr['count'] = $count;
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
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
            $watermark_img = "watermark_promote_small";
        }
        elseif ($row['is_new'] != 0)
        {
            $watermark_img = "watermark_new_small";
        }
        elseif ($row['is_best'] != 0)
        {
            $watermark_img = "watermark_best_small";
        }
        elseif ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot_small';
        }

        if ($watermark_img != '')
        {
            $arr['list'][$row['goods_id']]['watermark_img'] =  $watermark_img;
        }

        $arr['list'][$row['goods_id']]['goods_id']         = $row['goods_id'];
        $arr['list'][$row['goods_id']]['goods_name']       = $row['goods_name'];
        $arr['list'][$row['goods_id']]['goods_number']     = $row['goods_number'];
        $arr['list'][$row['goods_id']]['name']             = $row['goods_name'];
        $arr['list'][$row['goods_id']]['is_promote']       = $row['is_promote'];
        $arr['list'][$row['goods_id']]['is_new']           = $row['is_new'];
        $arr['list'][$row['goods_id']]['is_hot']           = $row['is_hot'];
        $arr['list'][$row['goods_id']]['is_best']          = $row['is_best'];
        $arr['list'][$row['goods_id']]['goods_brief']      = $row['goods_brief'];
        $arr['list'][$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'],$row['goods_name_style']);
        $arr['list'][$row['goods_id']]['market_price']     = price_format($row['market_price']);
        $arr['list'][$row['goods_id']]['shop_price']       = price_format($row['shop_price']);
        $arr['list'][$row['goods_id']]['type']             = $row['goods_type'];
        $arr['list'][$row['goods_id']]['promote_price']    = ($promote_price > 0) ? price_format($promote_price) : '';
        $arr['list'][$row['goods_id']]['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr['list'][$row['goods_id']]['goods_img']        = get_image_path($row['goods_id'], $row['goods_img']);
        $arr['list'][$row['goods_id']]['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
        //$arr[$row['goods_id']]['comment_count']    = get_comment_count($row['goods_id']);
        $arr['list'][$row['goods_id']]['count']            = selled_count($row['goods_id']);
        $arr['list'][$row['goods_id']]['click_count']      = $row['click_count'];
        $arr['list'][$row['goods_id']]['supplier'] = $row['supplier_id'];  //商品所属店铺id 0为自营

    }

    return $arr;
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


