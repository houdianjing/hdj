<?php
/**
 * Created by PhpStorm.
 * 功能：商品的搜索信息
 * User: hdj
 * Date: 2016/9/21
 * Time: 13:54
 */

/* 搜索接口说明
 *   搜索商品demo：/interface/search_api.php?action=search&type=goods&keywords=牛奶&uid=54&page=1
 *   搜索店铺demo：/interface/search_api.php?action='search'&type='&keywords='牛奶'&uid=54
 *   获取全站用户搜索关键词demo：/interface/search_api.php?action='hot'
 *   post参数
 *      action：search:执行搜索
 *              hot:获取全站用户搜索关键词top10 （无需添加type及keywords参数）
 *      type:goods：搜索产品
 *           store：搜索商店
 *      keywordes:搜索关键词
 *            uid:根据id获取会员折扣 选填  不填显示默认价格
 *   返回值
 *      1：url地址错误
 *      2：sign伪造 不正确
 *      101：店铺名称没有输入
 *      102：搜索结果为空
 *      103：商品关键词没有输入
 *      104：店铺搜索结果为空
 *      201：成功返回搜索店铺json数据
 *      202：成功返回搜索商品json数据
 *      203：成功返回热搜前十json数据
 * */

define('IN_ECS', true);
require(dirname(__FILE__) . '/../mobile/includes/init.php');
require(dirname(__FILE__) .'/common.php');

$weburl = $_SERVER['HTTP_HOST'];
//action判断
$action = $_REQUEST['action'];
$actionslist = array('search','hot');
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

//判断是执行搜索还是获取热搜top10
if($action=='search') {
    $searchType = $_REQUEST['type']?$_REQUEST['type']:'goods';  //获取搜索类型 是搜索商品还是搜索搜索店铺 不填默认为搜索商品
    $searchText= isset($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : ''; //获取搜索关键词
    //获取用户id  根据用户id获取用户等级折扣
    $uid= isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : '0';
    $userinfo=get_myuser_info($uid);

    //搜索词不为空 执行
        //搜索类型为store  搜索店铺 返回店铺列表及店铺总数量
        if ($searchType == 'store') {
            if (!empty($searchText)) {
                $sql = "SELECT DISTINCT supplier_id
				FROM ".$ecs->table('supplier_shop_config')." AS ssc
				WHERE (
				code = 'shop_name'
				AND value LIKE '%".$searchText."%'
				)
				OR (
				code = 'shop_keywords'
				AND value LIKE '%".$searchText."%'
				)";
                $suppids = $db->getAll($sql);
                $suppliers = array();
                if(is_array($suppids)){
                    foreach($suppids as $key => $val){
                        $suppliers[] = $val['supplier_id'];
                    }
                }
                $arr = array();
                if(!empty($suppliers)){
                    $sql = "select supplier_id,code,value from ".$ecs->table('supplier_shop_config')." where supplier_id in(".implode(',',$suppliers).")";
                    $res = $info = $db->query($sql);
                    while ($row = $db->FetchRow($res)){
                        if(!isset($arr[$row['supplier_id']])){
                            //获取店铺四个最新商品
                            $gsql = "select goods_id, goods_name,shop_price, goods_thumb, goods_img from ".$ecs->table('goods')." where supplier_id=".$row['supplier_id']." AND is_delete = 0 AND is_on_sale = 1 AND is_alone_sale = 1 AND is_virtual = 0 order by goods_id desc limit 4";
                            $glist = $db->getAll($gsql);
                            $arr[$row['supplier_id']]['goodlist'] = $glist;
                            foreach($glist as $k=>$v){
                                $arr[$row['supplier_id']]['goodlist'][$k]['goods_thumb']   = "http://".$weburl."/".get_image_path($v['goods_id'], $v['goods_thumb'], true); //商品小图
                                $arr[$row['supplier_id']]['goodlist'][$k]['goods_img']     = "http://".$weburl.'/'.get_image_path($v['goods_id'], $v['goods_img']);//商品大图
                                $arr[$row['supplier_id']]['goodlist'][$k]['url']          = "http://".$weburl.'/mobile/'.build_uri('goods', array('gid'=>$v['goods_id']),$v['goods_name']); //商品url
                                $arr[$row['supplier_id']]['goodlist'][$k]['shop_price']  =  $v['shop_price'];
                            }
                        }
                        $arr[$row['supplier_id']][$row['code']] = $row['value'];
                        if($row['code']=='shop_logo'){
                            $arr[$row['supplier_id']]['shop_logo'] = "http://".$weburl.$arr[$row['supplier_id']]['shop_logo'];
                        }
                        if($row['code']=='shop_header_text'){
                            $arr[$row['supplier_id']]['shop_header_text'] = goods_desc_cl($arr[$row['supplier_id']]['shop_header_text'],$weburl); //商品描述
                        }
                        $arr[$row['supplier_id']]['supplier_id'] = $row['supplier_id'];
                        $arr[$row['supplier_id']]['shop_province_name'] = get_region_name($arr[$row['supplier_id']]['shop_province']);
                        $arr[$row['supplier_id']]['shop_city_name'] = get_region_name($arr[$row['supplier_id']]['shop_city']);
                        $arr[$row['supplier_id']]['goods_number'] = count($arr[$row['supplier_id']]['goodlist']);
                    }
                }
                $result = array('code'=>201,'msg' => '成功返回匹配json数据','data' =>$arr);
                exit(json_encode($result));
                //print_r($result);
            }else{
                $result = array('code'=>101,'msg' => '请输入店铺名称');
                exit(json_encode($result));
            }
        } else {
            //搜索类型为goods  搜索产品 返回商品匹配列表及商品匹配数量
            /* 初始化搜索条件 */
            $keywords = '';
            $tag_where = '';
            if (!empty($searchText)) {
                $arr = array();
                if (stristr($searchText, ' AND ') !== false) {
                    /* 检查关键字中是否有AND，如果存在就是并 */
                    $arr = explode('AND', $searchText);
                    $operator = " AND ";
                } elseif (stristr($searchText, ' OR ') !== false) {
                    /* 检查关键字中是否有OR，如果存在就是或 */
                    $arr = explode('OR', $searchText);
                    $operator = " OR ";
                } elseif (stristr($searchText, ' + ') !== false) {
                    /* 检查关键字中是否有加号，如果存在就是或 */
                    $arr = explode('+', $searchText);
                    $operator = " OR ";
                } else {
                    /* 检查关键字中是否有空格，如果存在就是并 */
                    $arr = explode(' ', $searchText);
                    $operator = " AND ";
                }
                $keywords = 'AND (';
                $goods_ids = array();
                foreach ($arr AS $key => $val) {
                    if ($key > 0 && $key < count($arr) && count($arr) > 1) {
                        $keywords .= $operator;
                    }
                    $val = mysql_like_quote(trim($val));
                    $sc_dsad = " OR goods_desc LIKE '%$val%'";
                    $keywords .= "(goods_name LIKE '%$val%' OR goods_sn LIKE '%$val%' OR keywords LIKE '%$val%' $sc_dsad)";
                    $sql = 'SELECT DISTINCT goods_id FROM ' . $ecs->table('tag') . " WHERE tag_words LIKE '%$val%' ";
                    $res = $db->query($sql);
                    while ($row = $db->FetchRow($res)) {
                        $goods_ids[] = $row['goods_id'];
                    }

                    $db->autoReplace($ecs->table('keywords'), array('date' => local_date('Y-m-d'),
                        'searchengine' => 'ecshop', 'keyword' => addslashes(str_replace('%', '', $val)), 'count' => 1), array('count' => 1));
                }
                $keywords .= ')';

                $goods_ids = array_unique($goods_ids);
                $tag_where = implode(',', $goods_ids);
                if (!empty($tag_where)) {
                    $tag_where = 'OR g.goods_id ' . db_create_in($tag_where);
                }
                /* 获得符合条件的商品总数 */
                $sql = "SELECT COUNT(*) FROM " . $ecs->table('goods') ." AS g ".
                    " WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 " .
                    "AND (( 1 " . $keywords . " ) " . $tag_where . " )";
                $count = $db->getOne($sql);
                if($count==0){
                    $result = array('code'=>102,'msg' => '搜索结果为空');
                    exit(json_encode($result));
                }
                /* 查询商品 */
                $sql = "SELECT g.goods_id, g.goods_name, g.market_price,g.click_count, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ".
                    "IFNULL(mp.user_price, g.shop_price * '$userinfo[user_discount]') AS shop_price, g.exclusive,  ".
                    "g.promote_price, g.promote_start_date, g.promote_end_date, g.goods_thumb, g.goods_img, g.goods_brief, g.goods_type,supplier_id,g.is_promote,g.goods_number ".
                    "FROM " .$ecs->table('goods'). " AS g ".
                    "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
                    "ON mp.goods_id = g.goods_id AND mp.user_rank = '$userinfo[user_rank]' ".
                    "WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1  AND g.is_virtual = 0 ".
                    "AND (( 1 " . $keywords . " ) ".$tag_where." ) " .
                    "ORDER BY last_update desc";
                $res = $GLOBALS['db']->query($sql);
                $arr = array();
                while ($row = $db->FetchRow($res)) {
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
                    $arr['list'][$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'],$row['goods_name_style']);//商品名称加样式
                    $arr['list'][$row['goods_id']]['goods_number']     = $row['goods_number'];//商品库存量
                    $arr['list'][$row['goods_id']]['is_promote']       = $row['is_promote'];//商品是否促销
                    $arr['list'][$row['goods_id']]['promote_price']    = ($promote_price > 0) ? price_format($promote_price) : '';//商品促销价格
                    $arr['list'][$row['goods_id']]['promote_start_date']  =$row['promote_start_date'];//商品促销开始时间
                    $arr['list'][$row['goods_id']]['promote_end_date']    =$row['promote_end_date']; //商品促销结束时间
                    $arr['list'][$row['goods_id']]['is_new']           = $row['is_new']; //商品是否最新
                    $arr['list'][$row['goods_id']]['is_hot']           = $row['is_hot']; //商品是否最热
                    $arr['list'][$row['goods_id']]['is_best']          = $row['is_best']; //商品是否精品
                    $arr['list'][$row['goods_id']]['goods_brief']      = $row['goods_brief']; //商品简单描述
                    $arr['list'][$row['goods_id']]['market_price']     = price_format($row['market_price'],false);//商品市场价格
                    $arr['list'][$row['goods_id']]['shop_price']       = price_format($row['shop_price'],false);//商品面价
                    $arr['list'][$row['goods_id']]['exclusive']         = $row['exclusive'];  //商品手机专享价
                    $arr['list'][$row['goods_id']]['type']             = $row['goods_type']; //商品属性类型
                    $arr['list'][$row['goods_id']]['goods_thumb']      = "http://".$weburl."/".get_image_path($row['goods_id'], $row['goods_thumb'], true); //商品小图
                    $arr['list'][$row['goods_id']]['goods_img']        = "http://".$weburl.'/'.get_image_path($row['goods_id'], $row['goods_img']);//商品大图
                    $arr['list'][$row['goods_id']]['url']              = "http://".$weburl.'/mobile/'.build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']); //商品url
                    $arr['list'][$row['goods_id']]['sales_num']            = selled_count($row['goods_id']);//商品销量统计
                    $arr['list'][$row['goods_id']]['click_count']      = $row['click_count'];//商品浏览次数
                    $arr['list'][$row['goods_id']]['supplier'] = $row['supplier_id'];  //商品所属店铺id 0为自营
                }
                $result = array('code'=>202,'msg' => '成功返回匹配json数据','data' =>$arr);
                exit(json_encode($result));
                //print_r($result);
            }else{
                $result = array('code'=>103,'msg' => '请输入商品关键词');
                exit(json_encode($result));
               // print_r($result);
            }
        }
}elseif($action=='hot') {
    /* 获得热搜的前十个关键词 */
    $sql = "SELECT keyword,count " .
        "FROM " . $ecs->table('keywords') .
        "ORDER BY count desc LIMIT 10";
    $res = $GLOBALS['db']->query($sql);
    $arr = array();
    while ($row = $db->FetchRow($res)) {
        $arr[$row['keyword']]['keyword'] = $row['keyword'];  //搜索关键词
        $arr[$row['keyword']]['count'] = $row['count'];  //关键词搜索次数
    }
    $result = array('code'=>203,'msg' => '成功返回热搜前十json数据', 'data' =>$arr);
    exit(json_encode($result));
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
/* 获得区域名称 */
function get_region_name($region_id)
{
    if (!$region_id)
    {
        return '';
    }
    $sql="select region_name from ". $GLOBALS['ecs']->table('region') ." where region_id='$region_id' ";
    $region_name = $GLOBALS['db']->getOne($sql);
    return $region_name;
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