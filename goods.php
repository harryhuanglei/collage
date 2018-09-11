<?php
/**
 * 小舍电商 商品详情
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: goods.php 17217 2014-05-12 06:29:08Z pangbin $
*/
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = false;
}
assign_template();

// $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
// $smarty->assign('affiliate', $affiliate);
/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */
$goods_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
/*------------------------------------------------------ */
//-- 改变属性、数量时重新计算商品价格
/*------------------------------------------------------ */
if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'price')
{
    include('includes/cls_json.php');
    $json   = new JSON;
    $res    = array('err_msg' => '', 'result' => '', 'qty' => 1);
    $attr_id    = isset($_REQUEST['attr']) ? explode(',', $_REQUEST['attr']) : array();
    $number     = (isset($_REQUEST['number'])) ? intval($_REQUEST['number']) : 1;
    $luckdraw_id = $_REQUEST['luckdraw_id'] ? $_REQUEST['luckdraw_id'] : '';
    $attr_id=array_filter($attr_id);
    if(count($attr_id)>0){
        foreach($attr_id as $k=>$v)
        {   
            $goods_attr_info = get_attr_name($v);
	        $goods_attr[$k] = $goods_attr_info['attr_value'];
            $goods_attr_img = $goods_attr_info['attr_img'];
        }
        $goods_attr = implode(' ',$goods_attr);
        $res['attr_name'] = '已选 '.$goods_attr;
        $res['goods_attr_img'] = $goods_attr_img;
	}
	else
	{
		$res['attr_name'] = '';
	}
    if ($goods_id == 0)
    {
        $res['err_msg'] = $_LANG['err_change_attr'];
        $res['err_no']  = 1;
    }
    else
    {
        $goods = $db->getRow("select limit_buy_bumber,team_price,shop_price,promote_price,promote_start_date,promote_end_date,is_miao,goods_number from ".$hhs->table('goods')." where goods_id='$goods_id'");
        $limit_buy_bumber = $goods['limit_buy_bumber'];
        if ($number == 0)
        {
            $res['qty'] = $number = 1;
        }
        else
        {
            $res['qty'] = $number;
        }
        $res['goods_number'] = $goods['goods_number'];
        if ($attr_id) {
            $sql = "SELECT * FROM " .$GLOBALS['hhs']->table('products'). " WHERE goods_id = '$goods_id' LIMIT 0, 1";
            $prod = $GLOBALS['db']->getRow($sql);            
            if (is_spec($attr_id) && !empty($prod))
            {
                $product_info = get_products_info($goods_id, $attr_id);
                /*是否存在商品属性*/
                $product_info['attr_pro_check'] = 1;
            }
            if (empty($product_info))
            {
                /*判断属性存在且属性库存为零*/
                $product_info = array('product_number' => 0, 'product_id' => 0,'attr_pro_check' => 1);
            }
                $res = array_merge($res,$product_info);

                //print_r($res); die;
            
        }
        // 秒杀价格修正
        if ($goods['is_miao']) 		{	
            $promote_price = bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);			
            if($promote_price>0)
            {
                $goods['team_price'] = $goods['promote_price'];
            }			
        }        
        if($number>$limit_buy_bumber&&$limit_buy_bumber>0)
        {
            $res['err_msg'] = '购买数量不可大于限购数量';
            $shop_price  = get_final_price($goods_id, $limit_buy_bumber, true, $attr_id);
            $res['result'] = price_format($shop_price * $limit_buy_bumber);
            $res['number'] = $limit_buy_bumber;
            if ($goods['team_price']>0)			{
                $attr_price  = spec_price($attr_id,true);				
                $team_price  = $goods['team_price'] + $attr_price;				
                $res['team_price'] = price_format($team_price * $limit_buy_bumber);
            }
			$products = get_products_info($goods_id, $attr_id);
			if($products){
			    $res['product_number'] = $products['product_number'];
		    }
            die($json->encode($res)); 
        }
        else
        {	
            $shop_price  = get_final_price($goods_id, $number, true, $attr_id);
            $res['result'] = price_format($shop_price * $number);
            if ($goods['team_price']>0)			{
                $attr_price  = spec_price($attr_id,true);
                $team_price  = $goods['team_price'] + $attr_price;
                $res['team_price'] = price_format($team_price * $number);
            }
			//秒杀商品属性价格
			$promote_price = bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
			if($promote_price)
			{
				$team_price  = get_final_price($goods_id, $number, true, $attr_id,1);
				$res['team_price'] = price_format($team_price * $number);
			}
			if($luckdraw_id){
				$luckdraw_price_sql = "select luckdraw_price from ".$GLOBALS['hhs']->table('luckdraw')." where id = ".$luckdraw_id;
				$luckdraw_price = $GLOBALS['db']->getOne($luckdraw_price_sql);
				$attr_price  = spec_price($attr_id,true);
				$luckdraw_price +=$attr_price;
				$res['team_price'] = price_format($luckdraw_price);
			}
			$products = get_products_info($goods_id, $attr_id);
			if($products){
			    $res['product_number'] = $products['product_number'];
		    }			
            die($json->encode($res)); 
        }
    }
    die($json->encode($res));
}
if(!empty($_REQUEST['act']) && $_REQUEST['act'] =='save_location'){
	include_once('includes/cls_json.php');
	$json = new JSON();
	$result = array('error' => 0,'message'=>'', 'content' => '');
	$lat=$_REQUEST['lat'];
	$lng=$_REQUEST['lng'];
	$xaphp_sopenid=$_SESSION['xaphp_sopenid'];
	$sql="update ".$hhs->table('users')." set lat='$lat',lng='$lng' where openid='$xaphp_sopenid' ";
	$db->query($sql);
	//setcookie("lat",$lat);
	//setcookie("lng",$lng);
	die($json->encode($result));
}
/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */
$cache_id = $goods_id . '-' . $_SESSION['user_rank'].'-'.$_CFG['lang'];
$cache_id = sprintf('%X', crc32($cache_id));
$smarty->assign('bonus_free_all',           isset($_REQUEST['bonus_free_all']) ? 1:0);
if (!$smarty->is_cached('goods.dwt', $cache_id))
{
    $smarty->assign('image_width',  $_CFG['image_width']);
    $smarty->assign('image_height', $_CFG['image_height']);
    $smarty->assign('id',           $goods_id);
    $smarty->assign('type',         0);
    $smarty->assign('cfg',          $_CFG);
     $todayDate = gmtime();
$luckdraw_goods = $GLOBALS['db']->getOne("SELECT goods_id FROM " . $GLOBALS['hhs']->table('luckdraw') . " WHERE start_time < ".$todayDate." and end_time >".$todayDate." and luck_status = 0 and goods_id = ".$goods_id);
    if($luckdraw_goods > 0)
    {
        show_message("该商品现已参加抽奖活动，请前往抽奖活动页面", "抽奖活动", "luckdraw.php");
    }
    /* 获得商品的信息 */
    $goods = get_goods_info($goods_id);

	if($goods['is_on_sale'] == 0)
	{
		show_message('商品已下架', '', 'index.php');
	}
	$smartygoods = $goods;
    if ($goods === false )
    {
        /* 如果没有找到任何记录则跳回到首页 */
        hhs_header("Location: ./index.php\n");
        exit;
    }
    else
    {
        $smarty->assign('d_team_num', $goods['team_num']-1);
        $smarty->assign('goods_id',           $goods['goods_id']);
        $smarty->assign('promote_end_time',   $goods['gmt_end_time']);
        /* meta */
        if ($goods['promote_price_org'] > 0) {
            $goods['team_price'] = price_format($goods['promote_price']);
        }
        /* current position */
        $smarty->assign('page_title',   $goods['goods_name']);                    // 页面标题
        $properties = get_goods_properties($goods_id);  // 获得商品的规格和属性
        $smarty->assign('properties',          $properties['pro']);                              // 商品属性
        $smarty->assign('specification',       $properties['spe']);                              // 商品规格
                                      // 关联商品
        $smarty->assign('pictures',            get_goods_gallery($goods_id));                    // 商品相册
     /**
     * 销量
     * @var string
     */
    $store_id = $goods['suppliers_id'];
    $sql = "SELECT sum(`sales_num`) FROM ".$hhs->table('goods')." WHERE `suppliers_id` = " .$store_id;
    $sales_num = $db->getOne($sql);
    $sql = "SELECT count(*) FROM  ".$hhs->table('order_goods')." as o,".$hhs->table('goods')." as g WHERE g.`goods_id` = o.`goods_id` and g.`suppliers_id` = " .$store_id;
    $sales_num += $db->getOne($sql);
    $smarty->assign('sales_num',$sales_num);
        // assign_dynamic('goods');
        //$volume_price_list = get_volume_price_list($goods['goods_id'], '1');
       // $smarty->assign('volume_price_list',$volume_price_list);    // 商品优惠价格区间
    }
}
if ($goods['suppliers_id']){
    $stores_info = get_suppliers_info($goods['suppliers_id']);
    $smarty->assign('stores_info',$stores_info);
    $smarty->assign('qq',$stores_info['qq']);
}
else{
    $smarty->assign('qq',$_CFG['qq']);
}
// comments
$comments = assign_comment($goods_id,0,1);
$smarty->assign('comments', $comments['comments'] );
$smarty->assign('comments_nums', $comments['pager']['record_count'] );
$smarty->assign('rand_goods', getRandsGoods(6,$_SESSION['user_id']));
// 
$sql = "select g.shop_price as goods_price,g.team_price,g.goods_name,g.goods_id,g.goods_img,c.rec_id from ".$hhs->table("goods")." as g LEFT JOIN ".$hhs->table("collect_goods")." as c ON  c.user_id='".$_SESSION['user_id']."' and c.goods_id=g.goods_id where g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_miao = 0 AND g.is_luck = 0 order by rand() limit 6";
$rands_goods = $db->getAll($sql);
foreach($rands_goods as $ids=>$v)
{
	if($v['team_price']=='0.00')
	{
		$rands_goods[$ids]['team_price'] = $v['goods_price'];
	}
}
    $smarty->assign('rands_goods', $rands_goods );
    //print_r($rands_goods);
$sales_num = $goods['sales_num'];
if($_SESSION['sid'])
{
    include_once(ROOT_PATH . 'includes/lib_fenxiao.php');
    $f_uid = $_SESSION['sid'];
    $share_info = getPidInfo($f_uid);
    $smarty->assign('share_info',$share_info);
}
if ($goods['suppliers_id']){
    $current_region_type=get_region_type($_SESSION['site_id']); 
    if($current_region_type<=2){
         $where.=" and (g.city_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }elseif($current_region_type==3){
        $where.=" and (g.district_id='".$_SESSION['site_id'] . "' or g.city_id=1) ";
    }    
    $stores_info = get_suppliers_info($goods['suppliers_id']);
    $sql = "SELECT count(*) FROM ".$hhs->table('goods')." as g WHERE is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0 and  `suppliers_id` = " . $goods['suppliers_id'].$where;
    $stores_info['goods_num'] = $db->getOne($sql);
    $sql = "SELECT sum(`sales_num`) FROM ".$hhs->table('goods')." as g WHERE `suppliers_id` = " .$goods['suppliers_id'].$where;
    $stores_info['sales_num'] = $db->getOne($sql);
    $sql = "SELECT count(*) FROM  ".$hhs->table('order_goods')." as o,".$hhs->table('goods')." as g WHERE g.`goods_id` = o.`goods_id` and g.`suppliers_id` = " .$goods['suppliers_id'].$where;
    $stores_info['sales_num'] += $db->getOne($sql);
    $smarty->assign('stores_info',$stores_info);
}
    $g_id = $_REQUEST['id'];
    $u_id = $_REQUEST['uid'];
 if($_REQUEST['from'] && isset($_REQUEST['isappinstalled']) && $u_id!=$_SESSION['user_id']&&$u_id){
     $sql = "select *,count(user_id) as count from ".$hhs->table('share_info')." where user_id = $u_id";  //查询是否有分享记录
     $rs = $db->getRow($sql);
         if($rs['count']>0){
            $sql = "select allow_sharej,share_j from ".$hhs->table('goods')." where goods_id = $g_id"; //查询该商品是否有分享得积分
             $row = $db->getRow($sql);
              if($rs['child_id']!=''){
                    $child = unserialize($rs['child_id']);
                 }else{
                    $child=array();
                 }
             if($row['allow_sharej']==1 && $row['share_j']>0 && !in_array($_SESSION['user_id'],$child)){
                 $sql = "update ".$hhs->table('users')." set pay_points=pay_points+'{$row['share_j']}' where user_id=$u_id";
                 $db->query($sql);
                 $sqq = "insert into ".$hhs->table('account_log')."(user_id,pay_points,change_time,change_desc,change_type) values('$u_id','{$row['share_j']}',".gmtime().",'分享商品得积分',99)";
                 $db->query($sqq);
                 array_push($child,$_SESSION['user_id']);
                 $new_data = serialize($child);
                 $sql = "update ".$hhs->table('share_info')." set child_id='$new_data' where id='{$rs['id']}'";
                 $ros=$db->query($sql);
                    require(ROOT_PATH.'/includes/modules/payment/wxpay.php');
                    $sqs = "select openid from ".$hhs->table('users')." where user_id = $u_id";
                    $openid = $db->getOne($sqs);
                        $url = 'user.php?act=integral_details';
                        $desc = "恭喜小主，您分享的商品获得".$row['share_j']."积分，\r\n积分可用于在积分商城兑换商品";
                        $weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);
                        $weixin->send_wxmsg($openid, '分享成功' , $url , $desc);
             }
         }
 }
$smarty->assign('buy_num',get_buy_sum($goods_id)+$sales_num);
$smarty->assign('now_time',  gmtime());           // 当前系统时间
$timestamp=time();
$smarty->assign('timestamp', $timestamp );
$smarty->assign('appid', $appid);
$class_weixin=new class_weixin($appid,$appsecret);
$signature=$class_weixin->getSignature($timestamp);
$smarty->assign('signature', $signature);
 $sql = "select * from ".$hhs->table('share_log')." where goods_id=$goods_id";
 $row = $db->getRow($sql);
 if($row['goods_id']==''){
include_once(ROOT_PATH . 'includes/lib_image.php');
$img = new image();
   $file = ROOT_PATH.$goods['goods_img'];
   $type = end(explode('.',$file)); //'http://'.$_SERVER['HTTP_HOST']."/"
   $end = "/images/share/".gmtime().".".$type;
    $img2 = ROOT_PATH.$end;
    $show = 'http://'.$_SERVER['HTTP_HOST'].$end;
    $sq = "select value from ".$hhs->table('shop_config')." where id=948";
    $share = $db->getOne($sq);
    $share = substr($share,3,strlen($share));
    $water = ROOT_PATH."/".$share;
    $img->param($file)->water($img2,$water,8,90);
    $sql="insert into ".$hhs->table('share_log')."(goods_id,thumb) values('$goods_id','$end')";
    $db->query($sql);
  }else{
    $show = 'http://'.$_SERVER['HTTP_HOST'].$row['thumb'];
    include_once(ROOT_PATH . 'includes/lib_image.php');
    $img = new image();
    /*要加水印的图片*/
    $file = ROOT_PATH.$goods['goods_img'];
    /*获取图片的类型*/
    $type = end(explode('.',$file));
    /*水印图片*/
    $sq = "select value from ".$hhs->table('shop_config')." where id=948";
    $share = $db->getOne($sq);
    $share = substr($share,3,strlen($share));
    $water = ROOT_PATH.$share;
    /*水印图片保存路径*/
    $end = "images/share/goods_share".$goods_id.".".$type;
    $img2 = ROOT_PATH.$end;
    $i_show = $img->param($file)->water($img2,$water,8,100);
    $show = 'http://'.$_SERVER['HTTP_HOST'].'/'.$end;
    if(!$i_show)
    {
        $show = 'http://'.$_SERVER['HTTP_HOST'].'/'.$goods['goods_img'];
    }
  } 
//$smarty->assign('jssdk', jssdk($appid,$secret,$timestamp));
$smarty->assign('imgUrl',$show);
$smarty->assign('title', $goods['goods_name']);
$smarty->assign('desc', mb_substr($_CFG['goods_share_dec'], 0,30,'utf-8')  );
/*
if(($pos=strrpos($_SERVER[REQUEST_URI], "from"))!==false){
	$uri=substr($_SERVER[REQUEST_URI],0,$pos-1);
}else{
    $uri=$_SERVER[REQUEST_URI];
}*/
$link="http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?id='.$goods_id.'&uid='.$uid;//"/goods.php";
$smarty->assign('link', $link );
$smarty->assign('link2', urlencode($link) );
/* 更新点击次数 */
$db->query('UPDATE ' . $hhs->table('goods') . " SET click_count = click_count + 1 WHERE goods_id = '$_REQUEST[id]'");
$liked = $db->getOne("select rec_id from ".$hhs->table("collect_goods")." where user_id='".$_SESSION['user_id']."' and goods_id='".$goods['goods_id']."'");
$rec_id=$liked;
$liked = $liked ? 'liked' : '';
$smarty->assign('liked', $liked);
$smarty->assign('rec_id', $rec_id);
// $goods['shop_price'] = get_final_price($goods_id, 1 );
//查找附近的团
if($goods['is_nearby']){
    $xaphp_sopenid=$_SESSION['xaphp_sopenid'];
    $sql="select * from ".$hhs->table("users")." where openid='$xaphp_sopenid' ";
    $weixin_user=$db->getRow($sql);
    $myLat = $weixin_user['lat'];
    $myLng = $weixin_user['lng'];/**/
    $sql="select oi.user_id,oi.team_sign,oi.pay_time,g.goods_name,g.team_price,u.user_name,u.uname,u.headimgurl, (oi.team_num-oi.teammen_num ) as progress from ".$hhs->table("order_info")." as oi left join "
       .$hhs->table("goods")." as g on oi.extension_id=g.goods_id left join "
       .$hhs->table('users')." as u on oi.user_id=u.user_id where oi.team_first=1 and oi.team_status=1 and oi.pay_status = 2 and oi.extension_code='team_goods' and oi.extension_id='$goods_id' ";
       // echo $sql;
    $group_list=$db->getAll($sql);
    $time=gmtime();
    foreach($group_list as $k=>$v){
        $ctime=$group_list[$k]['pay_time']+$_CFG['team_suc_time']*24*3600-$time;
        if($ctime < 0 || $group_list[$k]['user_id'] == $_SESSION['user_id'])
        {
            unset($group_list[$k]);
            continue;
        }
        $group_list[$k]['team_price']=price_format($v['team_price']);        
        $hour=intval($ctime/3600);
        $minu=intval(($ctime%3600)/60);
        $group_list[$k]['finish_str']="剩余 ".$hour."小时".$minu."分结束";
		$group_list[$k]['times']=local_date('M d, Y H:i:s',$group_list[$k]['pay_time']+$_CFG['team_suc_time']*24*3600);
    }
    $smarty->assign('group_list', $group_list );
}
if($goods['is_luck'] >0)
{
    // 夺宝
    $left_num = $GLOBALS['db']->getOne('SELECT (team_num-teammen_num) as left_num FROM '.$GLOBALS['hhs']->table('order_info').' where goods_id = "'.$goods['goods_id'].'" AND pay_status=2 order by order_id desc');
    $left_num = $left_num>0 ? $left_num : $goods['team_num'];   
    $smartygoods['goods_number']       = $left_num;
    // $left_num        = ($goods['team_num']-$goods['goods_number']);
    $schedule        = 100 *(1 - $left_num/$goods['team_num']);
    $smarty->assign('left_num', $goods['team_num']-$left_num );//剩余的人
    $smarty->assign('schedule', $schedule );//进度条
    //当前期数购买的人
    $sql = 'select IFNULL(u.uname,u.user_name) as uname,u.headimgurl,o.add_time,o.province, o.city, (select count(*) from '.$hhs->table("order_luck").' as l where l.order_id=o.order_id) as buy_nums from '.$hhs->table("users").' as u,'.$hhs->table("order_info").' as o WHERE u.user_id = o.user_id and o.goods_id = '.$goods['goods_id'].' AND o.luck_times = "'.$goods['luck_times'].'" AND o.pay_status = 2';
     //echo $sql;die();
    $buy_rows = $db->getAll($sql);
    foreach ($buy_rows as $key => $row) {
        $buy_rows[$key]['add_time']  = local_date("Y-m-d H:i:s",$row['add_time']);
        $buy_rows[$key]['province'] = get_regions_name($row['province']);
        $buy_rows[$key]['city']     = get_regions_name($row['city']);
    }
    $smarty->assign('buy_rows', $buy_rows );
// 购买列表数据字段
// uname 名字
// headimgurl 头像地址
// add_time 购买时间
// province 省份
// city 城市
// buy_nums 购买份数
    // 往期中奖
    $sql = 'select IFNULL(u.uname,u.user_name) as uname,u.headimgurl,o.add_time,o.province, o.city, (select count(*) from '.$hhs->table("order_luck").' as l where l.order_id=o.order_id) as buy_nums,lk.id as lucker_id,o.luck_times from '.$hhs->table("users").' as u,'.$hhs->table("order_info").' as o,'.$hhs->table("order_luck").' as lk WHERE u.user_id = o.user_id and o.goods_id = "'.$goods['goods_id'].'" AND o.pay_status = 2 and o.is_lucker = 1 AND lk.order_id=o.order_id AND lk.is_lucker = 1';
    $luck_rows = $db->getAll($sql);
    foreach ($luck_rows as $key => $row) {
        $luck_rows[$key]['add_time']  = local_date("Y-m-d H:i:s",$row['add_time']);
        $luck_rows[$key]['province']  = get_regions_name($row['province']);
        $luck_rows[$key]['city']      = get_regions_name($row['city']);
        //开奖时间
        $open_time = $db->getOne("select pay_time from ".$hhs->table("order_info")." WHERE goods_id = '".$goods['goods_id']."' AND pay_status = 2 and luck_times = '".$row['luck_times']."' order by order_id desc");
        $luck_rows[$key]['open_time'] = local_date("Y-m-d H:i:s",$open_time);
    }    
    $smarty->assign('luck_rows', $luck_rows );
	$smarty->assign('desc', mb_substr($_CFG['db_desc'], 0,30,'utf-8')  );
// 中间列表数据字段
// uname 名字
// headimgurl 头像地址
// add_time 购买时间
// province 省份
// city 城市
// buy_nums 购买份数
// lucker_id 幸运id
// open_time 开奖时间
// luck_times 期数
    $smarty->assign('goods',              $smartygoods);
    $smarty->display('ms-details.dwt',      $cache_id);
}
else
{
    $smarty->assign('goods',              $smartygoods);
    $alone = isset($_REQUEST['alone'])  ? intval($_REQUEST['alone']) : 0;
    if ($alone>0) {
    $smarty->display('goods_alone.dwt',      $cache_id);
    }
    else
    {
    $smarty->display('goods.dwt',      $cache_id);
    }
}
/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */
/**
 * 获得商品选定的属性的附加总价格
 *
 * @param   integer     $goods_id
 * @param   array       $attr
 *
 * @return  void
 */
function get_attr_amount($goods_id, $attr)
{
    $sql = "SELECT SUM(attr_price) FROM " . $GLOBALS['hhs']->table('goods_attr') .
        " WHERE goods_id='$goods_id' AND " . db_create_in($attr, 'goods_attr_id');
    return $GLOBALS['db']->getOne($sql);
}
function get_buy_sum($goods_id)
{
    $sql = 'SELECT IFNULL(SUM(g.goods_number), 0) ' .
        'FROM ' . $GLOBALS['hhs']->table('order_info') . ' AS o, ' .
            $GLOBALS['hhs']->table('order_goods') . ' AS g ' .
        "WHERE o.order_id = g.order_id " .
        "AND o.order_status  in (0,1,5)  ".
        " AND o.shipping_status in (0,1,2) "  .
        " AND o.pay_status in (1,2) ".
        " AND g.goods_id = ".$goods_id;
    return $GLOBALS['db']->getOne($sql);
}
function get_regions_name($region_id)
{
    return $GLOBALS['db']->getOne("select region_name from ".$GLOBALS['hhs']->table('region')." where region_id='$region_id'");
}
function get_attr_name($goods_attr_id)
{
    return $GLOBALS['db']->getRow("select attr_value,attr_img from ".$GLOBALS['hhs']->table('goods_attr')." where goods_attr_id='$goods_attr_id'");
}
?>