<?php
/**
 * Created by PhpStorm.
 * User: pc03
 * Date: 2016/9/13
 * Time: 17:08
 */
define('IN_ECS', true);

error_reporting(E_ALL);
define('ROOT_PATH',  str_replace('\\', '/', dirname(__FILE__)));
require(ROOT_PATH . '/includes/cls_mysql.php');
require(ROOT_PATH . '/data/config.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);

$floor[1] = array('name'=>'农副产品','children'=>array('鲜洋芋','红洋芋粉条','土蜂蜜','玉米面','红岩大米','野生天麻'));
$floor[2] = array('name'=>'食品酒水','children'=>array('进口水果','豆制品','酸奶','面包','食用油','坚果炒货','咖啡饮料','葡萄酒'));
$floor[3] = array('name'=>'家用电器','children'=>array('电视机','空调','洗衣机','豆浆机','吸尘器','电吹风','按摩椅','净化器'));
$floor[4] = array('name'=>'母婴玩具','children'=>array('孕妈妈奶粉','益生菌','宝宝沐浴','防辐射服','手推车','益智玩具','婴儿衣服','奶粉I段'));
$floor[5] = array('name'=>'手机数码','children'=>array('移动电源','存储卡','移动硬盘','手机壳','单反相机','充电器','拍立得','耳机耳麦'));
$floor[6] = array('name'=>'服饰鞋包','children'=>array('亲子装','西服','裙装','旗袍','文胸','保暖内衣','内增高','帽子'));
$floor[7] = array('name'=>'户外汽车','children'=>array('户外野营','滑板鞋','睡袋','导航仪','车载冰箱','登山鞋','雨刷','汽车玻璃'));
$floor[8] = array('name'=>'家居家装','children'=>array('床上用品','窗帘','沙发垫','卫浴用品','五金建材','壁纸','沙发垫','四件套'));
$floor[9] = array('name'=>'电脑办公','children'=>array('复读机','打印机','墨粉','音箱','胶粘用品','平板电脑','游戏','鼠标垫'));

foreach ( $floor as $row ){
    if( count( $row['children']) > 0 ){
        foreach ( $row['children'] as $category ){
             $search = "select ec.cat_id,ec.cat_name,ecr.recommend_type from ecs_category ec left join  ecs_cat_recommend ecr on ec.cat_id = ecr.cat_id where ec.cat_name  = '{$category}'";
            $query = $db->query( $search );
            $record = $db->fetchRow( $query );
            //var_dump( $record );
            if( $record['recommend_type'] != 3 ){
                $insertsql = "insert into ecs_cat_recommend(cat_id,recommend_type) values( {$record['cat_id']}, 3 )";
                $db->query( $insertsql );
            }
        }
    }
}
?>