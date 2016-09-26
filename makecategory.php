<?php
/**
 * Created by PhpStorm.
 * User: pc03
 * Date: 2016/9/12
 * Time: 18:16
 */
define('IN_ECS', true);

error_reporting(E_ALL);
define('ROOT_PATH',  str_replace('\\', '/', dirname(__FILE__)));
require(ROOT_PATH . '/includes/cls_mysql.php');
require(ROOT_PATH . '/data/config.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);

$category = array();
$category[0]['name'] = '家用电器';
$category[0]['children'] = array(
    array('name'=>'大家电','children' => array( array('name'=>'电视机'),array('name'=>'空调'),array('name'=>'洗衣机'),array('name'=>'冰箱'))),
    array( 'name' => '厨用家电', 'children' => array( array('name'=>'油烟机'),array('name'=>'燃气灶'),array('name'=>'电饭煲'),array('name'=>'微波炉'),array('name'=>'电磁炉'),array('name'=>'压力锅'),array('name'=>'豆浆机'),array('name'=>'电水壶'),array('name'=>'其他厨房电器'))),
    array( 'name' => '生活电器', 'children' => array( array('name'=>'电风扇'),array('name'=>'吸尘器'),array('name'=>'净化器'),array('name'=>'加湿器'),array('name'=>'熨斗'),array('name'=>'取暖器'),array('name'=>'饮水机'),array('name'=>'台灯'))),
    array( 'name' => '个护家电', 'children' => array( array('name'=>'电吹风'),array('name'=>'剃须刀'),array('name'=>'卷发器'),array('name'=>'理发器'),array('name'=>'脱毛器'),array('name'=>'健康秤'),array('name'=>'按摩器'),array('name'=>'按摩椅')))
);

$category[1]['name'] = '手机数码';
$category[1]['children'] = array(
    array('name'=>'手机','children' => ''),
    array( 'name' => '数码影音', 'children' => array( array('name'=>'耳机耳麦'),array('name'=>'音响'),array('name'=>'MP3/MP4'),array('name'=>'收录音机'))),
    array( 'name' => '手机配件', 'children' => array( array('name'=>'移动电源'),array('name'=>'手机壳'),array('name'=>'贴膜'),array('name'=>'数据线'),array('name'=>'手机耳机'),array('name'=>'充电器'))),
    array( 'name' => '智能设备', 'children' => array( array('name'=>'智能手表'),array('name'=>'智能手环'),array('name'=>'智能配件'))),
    array( 'name' => '摄影摄像', 'children' => array( array('name'=>'镜头'),array('name'=>'拍立得'),array('name'=>'数码相机'),array('name'=>'单反相机'),array('name'=>'摄像机'))),
    array( 'name' => '存储网络', 'children' => array( array('name'=>'存储卡'),array('name'=>'移动硬盘'),array('name'=>'U盘'),array('name'=>'路由器'),array('name'=>'交换机'),array('name'=>'刻录光盘'),array('name'=>'固态硬盘'))),
);

$category[2]['name'] = '食品生鲜、酒水饮料';
$category[2]['children'] = array(
    array('name'=>'水果','children' => array( array('name'=>'进口水果'),array('name'=>'国产水果'),array('name'=>'水果礼盒'))),
    array( 'name' => '蔬菜', 'children' => array( array('name'=>'菌菇'),array('name'=>'豆类'),array('name'=>'豆制品'),array('name'=>'根茎类'),array('name'=>'绿叶菜'),array('name'=>'葱蒜'),array('name'=>'瓜果'))),
    array( 'name' => '肉/蛋类', 'children' => array( array('name'=>'猪肉'),array('name'=>'牛肉'),array('name'=>'羊肉'),array('name'=>'禽类'),array('name'=>'腌腊制品'),array('name'=>'牛排'),array('name'=>'香肠'),array('name'=>'蛋类'))),
    array( 'name' => '海鲜水产', 'children' => array( array('name'=>'鱼类'),array('name'=>'虾类'),array('name'=>'蟹类'),array('name'=>'鱿鱼/墨鱼'),array('name'=>'贝类'),array('name'=>'海参'),array('name'=>'水产礼盒'),array('name'=>'水产干货'))),
    array( 'name' => '乳制品', 'children' => array( array('name'=>'鲜奶'),array('name'=>'酸奶'),array('name'=>'奶酪'),array('name'=>'黄油'))),
    array( 'name' => '速冻食品', 'children' => array( array('name'=>'水饺'),array('name'=>'汤圆'),array('name'=>'馄饨'),array('name'=>'丸类'),array('name'=>'速冻面条'))),
    array( 'name' => '烘焙', 'children' => array( array('name'=>'甜点'),array('name'=>'面包'),array('name'=>'蛋糕'))),
    array( 'name' => '调味品', 'children' => array( array('name'=>'食用油'),array('name'=>'调味汁'),array('name'=>'调味油'),array('name'=>'调味酱'),array('name'=>'调味料'))),
    array( 'name' => '零食', 'children' => array( array('name'=>'休闲零食'),array('name'=>'坚果炒货'),array('name'=>'糖果巧克力'),array('name'=>'饼干糕点'),array('name'=>'果脯蜜饯'))),
    array( 'name' => '饮料', 'children' => array( array('name'=>'果蔬汁'),array('name'=>'水'),array('name'=>'乳饮料'),array('name'=>'茶饮料'),array('name'=>'咖啡饮料'),array('name'=>'功能饮料'))),
    array( 'name' => '酒', 'children' => array( array('name'=>'白酒'),array('name'=>'啤酒'),array('name'=>'葡萄酒'),array('name'=>'黄酒'),array('name'=>'滋补酒'),array('name'=>'果酒'),array('name'=>'洋酒'))),
    array( 'name' => '冲调饮品', 'children' => array( array('name'=>'冲饮果汁'),array('name'=>'麦片/谷类'),array('name'=>'奶粉'),array('name'=>'蜂蜜'),array('name'=>'豆奶'),array('name'=>'茶奶'),array('name'=>'芝麻糊'),array('name'=>'核桃粉'))),
    array( 'name' => '方便速食', 'children' => array( array('name'=>'方便面'),array('name'=>'火腿肠'),array('name'=>'面制品'),array('name'=>'粉丝/米线'),array('name'=>'酱菜/泡菜'),array('name'=>'八宝粥'),array('name'=>'罐头'))),
    array( 'name' => '咖啡/茶', 'children' => array( array('name'=>'茶叶礼盒'),array('name'=>'绿茶'),array('name'=>'龙井'),array('name'=>'铁观音'),array('name'=>'袋茶'),array('name'=>'乌龙'),array('name'=>'普洱'),array('name'=>'速溶咖啡'),array('name'=>'咖啡豆粉'),array('name'=>'咖啡伴侣'))),
    array( 'name' => '营养/保健', 'children' => array( array('name'=>'燕窝'),array('name'=>'参类'),array('name'=>'维生素'),array('name'=>'蛋白粉'),array('name'=>'无糖饮料'))),

);

$category[3]['name'] = '农副产品';
$category[3]['children'] = array(
    array('name'=>'巫溪洋芋','children' => array( array('name'=>'洋芋果果'),array('name'=>'洋芋粉面子'),array('name'=>'红洋芋粉条'),array('name'=>'鲜洋芋'))),
    array( 'name' => '土特产', 'children' => array( array('name'=>'土蜂蜜'),array('name'=>'野生天麻'),array('name'=>'莲藕'),array('name'=>'手工粉条'),array('name'=>'红薯'),array('name'=>'玉溪李子'),array('name'=>'蒲玲柚子'),array('name'=>'红岩大米'),array('name'=>'高山蔬菜'),array('name'=>'花台火龙果'),array('name'=>'玉米面'))),
);

$category[4]['name'] = '母婴玩具';
$category[4]['children'] = array(
    array('name'=>'孕婴奶粉','children' => array( array('name'=>'孕妈妈奶粉'),array('name'=>'奶粉I段'),array('name'=>'奶粉II段'),array('name'=>'奶粉III段'))),
    array( 'name' => '营养/辅食', 'children' => array( array('name'=>'面食类'),array('name'=>'宝宝零食'),array('name'=>'铁锌钙'),array('name'=>'益生菌'))),
    array( 'name' => '婴孕洗护', 'children' => array( array('name'=>'宝宝沐浴'),array('name'=>'婴儿防晒'),array('name'=>'防蚊驱蚊'),array('name'=>'爽身粉'),array('name'=>'奶瓶清洗'))),
    array( 'name' => '喂养用品', 'children' => array( array('name'=>'奶瓶'),array('name'=>'奶嘴'),array('name'=>'水壶水杯'),array('name'=>'吸奶器'))),
    array( 'name' => '车床用品', 'children' => array( array('name'=>'安全座椅'),array('name'=>'手推车'),array('name'=>'婴儿床'))),
    array( 'name' => '孕妈专区', 'children' => array( array('name'=>'防辐射服'),array('name'=>'妈咪包'),array('name'=>'哺乳文胸'),array('name'=>'腰凳'))),
    array( 'name' => '玩具', 'children' => array( array('name'=>'婴幼玩具'),array('name'=>'遥控模型'),array('name'=>'积木拼插'),array('name'=>'益智玩具'))),
    array( 'name' => '婴幼服饰', 'children' => array( array('name'=>'婴儿衣服'),array('name'=>'婴幼内衣裤'),array('name'=>'婴幼套装'),array('name'=>'婴幼鞋'),array('name'=>'婴幼配饰'))),
);

$category[5]['name'] = '服饰内衣、鞋包配饰';
$category[5]['children'] = array(
    array('name'=>'男装','children' => array( array('name'=>'T恤'),array('name'=>'衬衫'),array('name'=>'西服'),array('name'=>'牛仔裤'),array('name'=>'休闲裤'),array('name'=>'卫衣'),array('name'=>'外套'),array('name'=>'风衣'),array('name'=>'毛衣'),array('name'=>'羽绒服'),array('name'=>'大衣'),array('name'=>'皮草'))),
    array( 'name' => '女装', 'children' => array( array('name'=>'衬衫'),array('name'=>'小西装'),array('name'=>'大衣'),array('name'=>'风衣'),array('name'=>'棉服'),array('name'=>'皮草'),array('name'=>'牛仔裤'),array('name'=>'裙装'),array('name'=>'旗袍'))),
    array( 'name' => '童装', 'children' => array(array('name'=>'亲子装'))),
    array( 'name' => '内衣裤', 'children' => array(array('name'=>'男士内裤'),array('name'=>'女士内裤'),array('name'=>'打底衫'),array('name'=>'保暖内衣'),array('name'=>'文胸'),array('name'=>'塑身衣'),array('name'=>'吊带/背心'),array('name'=>'丝袜'),array('name'=>'男士睡衣'),array('name'=>'女士睡衣'),array('name'=>'袜子'))),
    array( 'name' => '女鞋', 'children' => array(array('name'=>'单鞋'),array('name'=>'平底鞋'),array('name'=>'高跟鞋'),array('name'=>'休闲鞋'),array('name'=>'皮鞋'),array('name'=>'帆布鞋'),array('name'=>'凉鞋'),array('name'=>'女靴'))),
    array( 'name' => '男鞋', 'children' => array(array('name'=>'单鞋'),array('name'=>'休闲鞋'),array('name'=>'皮鞋'),array('name'=>'凉鞋'),array('name'=>'内增高'),array('name'=>'拖鞋'),array('name'=>'棉鞋'),array('name'=>'男靴'))),
    array( 'name' => '腕表', 'children' => array(array('name'=>'电子表'),array('name'=>'手表'),array('name'=>'机械表'),array('name'=>'智能手表'))),
    array( 'name' => '眼镜', 'children' => array(array('name'=>'墨镜'),array('name'=>'近视镜'),array('name'=>'老花镜'))),
    array( 'name' => '服饰配饰', 'children' => array(array('name'=>'围巾/披肩'),array('name'=>'帽子'),array('name'=>'手套'),array('name'=>'口罩'),array('name'=>'领带/领结'))),
);

$category[6]['name'] = '家居家装';
$category[6]['children'] = array(
     array('name'=>'床上用品','children' => array( array('name'=>'床品套件'),array('name'=>'枕头'),array('name'=>'床褥/床垫'),array('name'=>'被子'),array('name'=>'四件套'),array('name'=>'凉席'),array('name'=>'蚊帐'))),
     array( 'name' => '家纺', 'children' => array( array('name'=>'毛巾'),array('name'=>'窗帘'),array('name'=>'沙发垫'),array('name'=>'桌布'))),
     array( 'name' => '家具', 'children' => array( array('name'=>'沙发'),array('name'=>'桌椅'),array('name'=>'柜子'),array('name'=>'床'))),
     array( 'name' => '家具装饰', 'children' => array( array('name'=>'节庆饰品'),array('name'=>'地毯'),array('name'=>'摆件挂件'),array('name'=>'装饰画'),array('name'=>'花瓶'),array('name'=>'壁纸'))),
    array( 'name' => '五金建材', 'children' => array( array('name'=>'灯类'),array('name'=>'五金电料'),array('name'=>'卫浴用品'),array('name'=>'管材'),array('name'=>'线材'))),
);

$category[7]['name'] = '个护美妆';
$category[7]['children'] = array(
    array('name'=>'美发护发','children' => array(array('name'=>'洗发水'),array('name'=>'染发'),array('name'=>'护发'),array('name'=>'发膜'),array('name'=>'发蜡'),array('name'=>'假发'))),
    array('name'=>'洗浴用品','children' => array(array('name'=>'沐浴露'),array('name'=>'洗手液'),array('name'=>'香皂'),array('name'=>'浴盐'))),
    array( 'name' => '口腔护理', 'children' => array(array('name'=>'牙膏'),array('name'=>'牙刷'),array('name'=>'牙线'))),
    array( 'name' => '护肤品', 'children' => array(array('name'=>'面膜'),array('name'=>'乳液'),array('name'=>'爽肤水'),array('name'=>'隔离/防晒霜'))),
    array( 'name' => '身体护理', 'children' => array(array('name'=>'身体乳'),array('name'=>'美体塑身'),array('name'=>'精油'),array('name'=>'卫生巾'))),
    array( 'name' => '彩妆', 'children' => array(array('name'=>'BB霜'),array('name'=>'腮红/眼影'),array('name'=>'唇彩'),array('name'=>'粉底'),array('name'=>'眼线笔/眉笔'),array('name'=>'香水'),array('name'=>'面部彩妆'),array('name'=>'美妆工具'))),
);

$category[8]['name'] = '户外健身、汽车用品';
$category[8]['children'] = array(
     array('name'=>'运动服','children' => array(array('name'=>'运动套装'),array('name'=>'舞蹈/瑜伽服'),array('name'=>'运动棉服'),array('name'=>'运动配饰'),array('name'=>'泳衣'),array('name'=>'游泳配件'),array('name'=>'冲锋衣裤'),array('name'=>'运动背包'))),
     array( 'name' => '运动鞋', 'children' => array(array('name'=>'跑步鞋'),array('name'=>'滑板鞋'),array('name'=>'篮球鞋'),array('name'=>'足球鞋'),array('name'=>'登山鞋'),array('name'=>'综合训练鞋'))),
     array( 'name' => '户外野营', 'children' => array(array('name'=>'睡袋'),array('name'=>'便携座椅'),array('name'=>'户外装备'),array('name'=>'垂钓用品'),array('name'=>'野餐烧烤'),array('name'=>'骑行用品'),array('name'=>'户外照明'))),
     array( 'name' => '维修保养', 'children' => array(array('name'=>'机油'),array('name'=>'防冻液'),array('name'=>'火花塞'),array('name'=>'雨刷'),array('name'=>'蓄电池'),array('name'=>'贴膜'),array('name'=>'改装配件'),array('name'=>'汽车玻璃'))),
     array( 'name' => '车载电器', 'children' => array(array('name'=>'行车记录仪'),array('name'=>'导航仪'),array('name'=>'车载影音'),array('name'=>'车载冰箱'),array('name'=>'倒车雷达'),array('name'=>'蓝牙设备'),array('name'=>'车载电台'),array('name'=>'汽车音响'))),
    array( 'name' => '维修保养', 'children' => array(array('name'=>'机油'),array('name'=>'防冻液'),array('name'=>'火花塞'),array('name'=>'雨刷'),array('name'=>'蓄电池'),array('name'=>'贴膜'),array('name'=>'改装配件'),array('name'=>'汽车玻璃'))),
    array( 'name' => '美容清洗', 'children' => array(array('name'=>'车蜡'),array('name'=>'镀晶镀膜'),array('name'=>'玻璃水'),array('name'=>'洗车水枪'),array('name'=>'毛巾掸子'))),
    array( 'name' => '汽车装饰', 'children' => array(array('name'=>'脚垫'),array('name'=>'坐垫'),array('name'=>'座套'),array('name'=>'后备箱垫'),array('name'=>'方向盘套'),array('name'=>'挂件摆饰'),array('name'=>'车身装饰件'))),
);

$category[9]['name'] = '图文音像、电脑办公';
$category[9]['children'] = array(
     array('name'=>'文具','children' => array(array('name'=>'装订用品'),array('name'=>'笔类'),array('name'=>'裁剪用品'),array('name'=>'胶粘用品'),array('name'=>'几何用具'),array('name'=>'本册/便签'),array('name'=>'财务行政用具'))),
     array( 'name' => '图书', 'children' => array(array('name'=>'教育图书'),array('name'=>'管理图书'),array('name'=>'科技图书'),array('name'=>'少儿图书'),array('name'=>'社会科学'),array('name'=>'生活图书'))),
     array( 'name' => '电子教育', 'children' => array(array('name'=>'复读机'),array('name'=>'学生电脑'),array('name'=>'电子词典'),array('name'=>'点读机'),array('name'=>'早教益智'))),
     array( 'name' => '音像制品', 'children' => array(array('name'=>'音乐'),array('name'=>'影视'),array('name'=>'游戏'),array('name'=>'动漫'))),
    array( 'name' => '电脑整机', 'children' => array(array('name'=>'平板电脑'),array('name'=>'笔记本'),array('name'=>'台式机'),array('name'=>'主机'),array('name'=>'显示器'))),
    array( 'name' => '电脑外设', 'children' => array(array('name'=>'鼠标'),array('name'=>'键盘'),array('name'=>'摄像头'),array('name'=>'鼠标垫'),array('name'=>'音箱'),array('name'=>'耳麦'),array('name'=>'线材'))),
    array( 'name' => '打印设备', 'children' => array(array('name'=>'打印机'),array('name'=>'一体机'),array('name'=>'硒鼓'),array('name'=>'色带'),array('name'=>'墨盒'),array('name'=>'墨粉')))
);

$search = " select count(*) as num from ecs_category";
$query = $db->query( $search );
$record = $db->fetchRow( $query );

if( $record['num'] > 0 )
    exit( "后台商品分类不为空，请先清空分类，再执行重新生成分类文件！");

addcategory( $category );

//插入指定数组的分类，指定父级id
function addcategory( $category, $parentid = 0 ){
    foreach( $category as $cate) {
        $insert = "insert into ecs_category(parent_id,cat_name) values($parentid,'{$cate['name']}')";
        $GLOBALS['db']->query($insert);
        $cat_id = $GLOBALS['db']->insert_id();
        if ($cate['children'] && count($cate['children']) > 0) {
            addcategory($cate['children'], $cat_id);
        }
    }
}

?>