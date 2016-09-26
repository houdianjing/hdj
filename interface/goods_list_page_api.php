<?php
/**
 * Created by PhpStorm.
 * 功能：获取商品列表
 * User: hdj
 * Date: 2016/9/22
 * Time: 13:54
 */

/* 搜索接口说明
 *   返回所有商品：/interface/goods_info_api.php?action=list&catid=0&page=1
 *   返回分类为8的栏目及子栏目下的商品：/interface/goods_info_api.php?action=list&catid=8&page=1
 *   根据分类、会员id、店铺返回商品：/interface/goods_info_api.php?action=list&catid=0&uid=54&storeid=23&page=1
 *   post参数
 *      action：list:获取产品列表
 *      catid:获取分类id 必填
 *      uid:用户id 根据id获取会员折扣 选填  不填显示默认价格
 *      storeid:店铺id 获取该店铺的商品列表 获取全部店铺商品将cateid等于0  选填 不填显示所有的商品 值为0显示所有自营
 *      page:页码
 *   返回值
 *      1：url地址错误
 *      2：sign伪造 不正确
 *      101：分类id为空
 *      102：没有这个分类id
 *      103：分类下无数据
 *      104：成功返回json数据
 * */

define('IN_ECS', true);
require(dirname(__FILE__) . '/../mobile/includes/init.php');
require(dirname(__FILE__) .'/common.php');

$weburl = $_SERVER['HTTP_HOST'];
//action判断
$action = $_REQUEST['action'];
$actionslist = array('list');
if( !in_array( $action, $actionslist )){
    $result = array('code' => 1, 'msg' => '非法的url提交');
    exit(json_encode( $result ) );
}

////key值验证
//$key = $_REQUEST['sign'];
//$calucatekey = sortMap($_GET);
//if( $key != $calucatekey ){
//    $result = array('code' => 2, 'msg' => '伪造的key');
//    exit(json_encode( $result ) );
//}

if($action=='list') {
    if (isset($_REQUEST['catid'])){
        $cat_id = intval($_REQUEST['catid']);
    }else{
        $result = array('code'=>101,'msg' => '分类id不能为空');
        exit(json_encode($result));
    }
    $page = isset($_REQUEST['page'])   && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
    $size = isset($_CFG['page_size'])  && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
    $uid= isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : '0'; //获取用户id  根据用户id获取用户等级折扣
    $storeId= isset($_REQUEST['storeid']) ? intval($_REQUEST['storeid']) : '-1'; //获取店铺id 默认为-1，-1位全部商品 0为自营 其他为各店铺id值

     $userinfo=get_myuser_info($uid);
     $children = get_cat_children($cat_id);
     if(!$children){
         $result = array('code'=>102,'msg' => '分类id错误 无此分类');
         exit(json_encode($result));
     }
    $count = get_cagtegory_goods_count($children,$storeId);
    if($count==0){
        $result = array('code'=>103,'msg' => '该分类下无数据');
        exit(json_encode($result));
    }
    $max_page = ($count> 0) ? ceil($count / $size) : 1;
    if ($page > $max_page)
    {
        $page = $max_page;
    }
     $goodslist = category_get_goods($children,1,$userinfo['user_rank'],$userinfo['user_discount'],$storeId,$size,$page,$weburl);
     $result = array('code'=>104,'msg' => '成功返回json数据','data' =>$goodslist);
     //exit(json_encode($result));
     print_r($result);
}


/**
 * 先判断有无该分类 有则获得指定分类下所有底层分类的ID
 *
 * @access  public
 * @param   integer     $cat        指定的分类ID
 * @return  string
 */
function get_cat_children($cat = 0)
{
    if($cat==0){
        return 'g.cat_id ' . db_create_in(array_unique(array_merge(array($cat), array_keys(cat_list($cat, 0, false)))));
    }else{
        $sqlc  = 'SELECT * FROM ' .$GLOBALS['ecs']->table('category')." WHERE cat_id =".$cat;
        $catCount= $GLOBALS['db']->getOne($sqlc);
        if($catCount){
            return 'g.cat_id ' . db_create_in(array_unique(array_merge(array($cat), array_keys(cat_list($cat, 0, false)))));
        }else{
            return false;
        }
    }
}


/**
 * 获得分类下的商品
 * $is_stock 库存
 * $user_rank 用户等级
 * $user_discount 用户折扣
 * $store 商铺id
 */
function category_get_goods($children, $is_stock = 0,$user_rank=0,$user_discount=1,$store=-1,$size,$page,$weburl)
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
//    if(!empty($is_stock))
//    {
//        $where .= " AND g.goods_number > 0 ";
//    }
    /* 获得商品列表 */
    $sql = "SELECT g.goods_id, g.goods_name, g.goods_name_style, g.click_count, g.goods_number, g.market_price, " .
        " g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, " .
        " IFNULL(mp.user_price, g.shop_price * '$user_discount') AS shop_price, g.promote_price, " .
        " IF(g.promote_price != '' " .
        " AND g.promote_start_date < " . gmtime() .
        " AND g.promote_end_date > " . gmtime() . ", g.promote_price, shop_price) " .
        " AS shop_p, g.goods_type, " .
        " g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img,g.supplier_id,g.is_promote " .
        " FROM " . $GLOBALS['ecs']->table('goods') .
        " AS g " .
        " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') .
        " AS mp " .
        " ON mp.goods_id = g.goods_id " .
        " AND mp.user_rank = '$user_rank' " .
        " WHERE $where " .
        " ORDER BY g.last_update desc";
    //分页
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    $arr = array();
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

        $arr['list'][$row['goods_id']]['goods_id']         = $row['goods_id'];//商品id
        $arr['list'][$row['goods_id']]['goods_name']       = $row['goods_name'];//商品名称
        $arr['list'][$row['goods_id']]['goods_number']     = $row['goods_number'];//商品库存量
        $arr['list'][$row['goods_id']]['is_promote']       = $row['is_promote'];//商品是否促销
        $arr['list'][$row['goods_id']]['promote_price']    = ($promote_price > 0) ? price_format($promote_price) : '';//商品促销价格
        $arr['list'][$row['goods_id']]['promote_start_date']  =$row['promote_start_date'];//商品促销开始时间
        $arr['list'][$row['goods_id']]['promote_end_date']    =$row['promote_end_date']; //商品促销结束时间
        $arr['list'][$row['goods_id']]['is_new']           = $row['is_new']; //商品是否最新
        $arr['list'][$row['goods_id']]['is_hot']           = $row['is_hot']; //商品是否最热
        $arr['list'][$row['goods_id']]['is_best']          = $row['is_best']; //商品是否精品
        $arr['list'][$row['goods_id']]['goods_brief']      = $row['goods_brief']; //商品简单描述
        $arr['list'][$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'],$row['goods_name_style']);//商品名称加样式
        $arr['list'][$row['goods_id']]['market_price']     = price_format($row['market_price']);//商品市场价格
        $arr['list'][$row['goods_id']]['shop_price']       = price_format($row['shop_price']);//商品面价
        $arr['list'][$row['goods_id']]['type']             = $row['goods_type']; //商品属性类型
        $arr['list'][$row['goods_id']]['goods_thumb']      = "http://".$weburl."/".get_image_path($row['goods_id'], $row['goods_thumb'], true); //商品小图
        $arr['list'][$row['goods_id']]['goods_img']        = "http://".$weburl.'/'.get_image_path($row['goods_id'], $row['goods_img']);//商品大图
        $arr['list'][$row['goods_id']]['url']              = "http://".$weburl.'/'.build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']); //商品url
        $arr['list'][$row['goods_id']]['count']            = selled_count($row['goods_id']);//商品销量统计
        $arr['list'][$row['goods_id']]['click_count']      = $row['click_count'];//商品浏览次数
        $arr['list'][$row['goods_id']]['supplier'] = $row['supplier_id'];  //商品所属店铺id 0为自营

    }

    return $arr;
}


/**
 * 获得分类下的商品总数
 *
 * @access  public
 * @param   string     $cat_id
 * @return  integer
 */
function get_cagtegory_goods_count($children,$is_stock = 0, $store=-1)
{

    $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND ($children OR " . get_extension_goods($children) . ')';
    //分区自营还是商家  $store等于-1 则显示所有（默认） 0显示自营  商家id 显示商家销售商品
    if($store==0){
        $where .= ' AND g.supplier_id=0';
    }elseif($store>0){
        $where .= ' AND g.supplier_id='.$store;
    }else{
    }
    //库存大于0的产品
//    if(!empty($is_stock))
//    {
//        $where .= " AND g.goods_number > 0 ";
//    }
    /* 返回商品总数 */
    return $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . " AS g WHERE $where");
}



/**
 *  获取用户等级折扣数组
 *
 * @access  public
 * @param
 * @return array
 * $user       用户信息数组
 */
function get_myuser_info($id=0)
{
    $sql  = 'SELECT u.user_id, u.user_name,u.user_rank'.
        ' FROM ' .$GLOBALS['ecs']->table('users'). ' AS u ' .
        " WHERE u.user_id = '$id'";
    $user = $GLOBALS['db']->getRow($sql);
    //$bonus = get_user_bonus($id);
    if($user['user_rank'] == 0)
    {
        $user['user_rank']    = "0";
        $user['user_discount']=1;
    }else
    {
        $rank_id = $user['user_rank'];
        $sql = "SELECT rank_name,discount,rank_id FROM ".$GLOBALS['ecs']->table('user_rank')."WHERE rank_id='".$rank_id."'";
        $query= $GLOBALS['db']->getRow($sql);
        $user['user_rank']=$query['rank_id'];
        $user['user_discount']=$query['discount']/100;
    }
    return $user;
}


