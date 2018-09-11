<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');
if ((DEBUG_MODE & 2) != 2)

{

    $smarty->caching = false;

}
//团购人数不足提醒
if($_REQUEST['act'] == 'send_team_info')
{
	
	$goods = get_team_info();
	
	echo json_encode($goods);
}

//发送优惠券信息
if($act == 'send_bouns')
{
	$data['share_status'] = $_REQUEST['share_status'];
	
	$data = get_user_bouns();
	
	echo json_encode($data);
}


//分页获取

//code by luo

$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';



if($act == 'next')

{

    include('includes/cls_json.php');

    $json   = new JSON;

    $res    = array('err_msg' => '', 'result' => '');

    $page             = intval($_REQUEST['page']);

    $rows             = get_goodslist($page);

    $res['goodslist'] = $rows['goodslist'];

    $res['nextPage']  = $rows['nextPage'];

    header('Content-Type: application/json');

    echo $json->encode($res);

    exit();

}

/* 缓存编号 */

$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('index.dwt', $cache_id))

{

	/*APP是否下载下载*/
	$smarty->assign('open_app', $_CFG['open_app']);
	$smarty->assign('app_loaddown_url', $_CFG['app_loaddown_url']);
   assign_template();

   $smarty->assign('site_list',           get_sitelists());

   $smarty->assign('page_title',$_CFG['shop_name']);    // 页面标题

    /* meta information */

  //  $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));

   // $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));

   // $res = get_goodslist();

  //  $smarty->assign('goods_list',    $res['goodslist']);   // 最新goods

   // $smarty->assign('nextPage',    $res['nextPage']);   // 最新goods

    /* 页面中的动态内容*/

	$smarty->assign('categories',      get_categories_tree());
	
	//print_r(get_categories_tree());

	$smarty->assign('zero_list',    get_typeof_goods('zero',16,1));   // 0元购

	$smarty->assign('tejia_list',    get_typeof_goods('mall',$_CFG['index_show_mall_num'],1));   // 特价

	$smarty->assign('tuan_list',    get_typeof_goods('team',$_CFG['index_show_team_num'],1,$_SESSION['user_id']));

	$smarty->assign('miao_list',    get_promote_goods()); 

	//print_r(get_promote_goods());



    // assign_dynamic('index');

} 


	$site_id = empty($_REQUEST['site_id']) ? 1 : intval($_REQUEST['site_id']);
	
	$smarty->assign('banner',getads(1,10,$site_id));




$smarty->assign('appid', $appid);

$timestamp=time();

$smarty->assign('timestamp', $timestamp );

$class_weixin=new class_weixin($appid,$appsecret);

$signature=$class_weixin->getSignature($timestamp);

$smarty->assign('signature', $signature);

$smarty->assign('imgUrl', 'http://'.$_SERVER['HTTP_HOST'].'/themes/'.$_CFG['template'].'/images/logo.gif');

$smarty->assign('title', $_CFG['index_share_title']);

$smarty->assign('desc', mb_substr($_CFG['index_share_dec'], 0,30,'utf-8')  );

/*

$smarty->assign('title', 'aaa'.$_CFG['index_share_title']);

$smarty->assign('desc', mb_substr('bbbb'.$_CFG['index_share_dec'], 0,30,'utf-8')  );

*/

$link="http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'].'?uid='.$uid;//"/index.php";

$smarty->assign('link', $link);

$smarty->assign('link2', urlencode($link) );



    $loading =$smarty->fetch('loading.html');

    $smarty->assign('loading',    $loading);

$kuaibao = $GLOBALS['db']->getAll("SELECT title , article_id FROM " . $GLOBALS['hhs']->table('article') ." WHERE cat_id = 38");
$smarty->assign('kuaibao',$kuaibao);





#print_r(get_sitelists());

#print_r(get_site_id($ip));

/*

if ($_REQUEST['act'] == 'test')

{

    $redirect_uri="http://" . $_SERVER['HTTP_HOST'] . "/wxpay/wx_oauth2.php"; 

    $redirect_uri=urlencode($redirect_uri);

    $smarty->assign('redirect_uri', $redirect_uri );

    

    $smarty->display('test.dwt');exit();

}*/

$smarty->display('index.dwt');





function get_flash_xml($type = 1)

{

    $city_id = get_city_id();
	

    // $city_id = get_city_id();

    $flashdb = $GLOBALS['db']->getAll("select * from ".$GLOBALS['hhs']->table('ad')." where position_id='$type' and city_id='$city_id' order by order_sort");

	

	foreach($flashdb as $idx=>$v)

	{

		$flashdb[$idx]['url'] = $v['ad_link'];

		$flashdb[$idx]['src'] = '../data/afficheimg/'.$v['ad_code'];

		

	}

    return $flashdb;



}

function get_team_info()
{
	
	
	$sql = "SELECT transaction_id,team_num,user_id,teammen_num,(team_num-teammen_num) as team_lack_num,team_status,team_sign,team_first,extension_code,order_id,order_sn,add_time,pay_time FROM " . $GLOBALS['hhs']->table('order_info')." WHERE team_status = 1 AND extension_code = 'team_goods'";

	$row = $GLOBALS['db']->getAll($sql);
	
	foreach($row as $value)
	{
		
		
		if(!empty($value['pay_time']))
		{
			
			$today = gmtime();
		
			$end_team_time = $value['pay_time']+$GLOBALS['_CFG']['team_suc_time']*86400;
			
			$cur_date = intval($end_team_time-$today);
			
			if($cur_date == 14400)
			{	
				
				//客户信息
				$user_list = $GLOBALS['db']->getRow("SELECT openid,uname from ".$GLOBALS['hhs']->table('users')." where user_id=".$value['user_id']);
				
				//商品信息
				$goods_info = $GLOBALS['db']->getRow("SELECT g.goods_thumb,g.goods_brief,g.goods_name,og.goods_price FROM ".$GLOBALS['hhs']->table('goods')." AS g LEFT JOIN ".$GLOBALS['hhs']->table('order_goods'). " AS og ON g.goods_id = og.goods_id WHERE og.order_id = ".$value['order_id']);
				
				
				include_once ROOT_PATH."wxpay/class_weixin.php";
				
				$weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);
				
				$url = 'share.php?team_sign='.$value['team_sign'];
    			
				$desc = '亲爱的'.$user_list['uname'].'您参加的'.$goods_info['goods_price'].'元'.$goods_info['goods_name'].$goods_info['goods_brief'].'拼团\r\n目前人数不足，尚未组团成功！将于4小时后结束\r\n快去叫身边的小伙伴一起来参团吧!\r\n点击立即分享>>';
    			
				$picurl = $goods_info['goods_thumb'];
				
				$weixin->send_wxmsg($user_list['openid'], '参团人数不足提醒！' , $url , $desc,$picurl);
			}
			
			
		}
	}
	
}


//获取优惠券信息
function get_user_bouns()
{
	$user_id=$GLOBALS['db']->getAll("select user_id,uname from ".$GLOBALS['hhs']->table('users'));
	
	foreach ($user_id as $value)
	{
		$sql = "SELECT u.order_id,u.user_id,b.free_all,u.bonus_sn,b.type_id, u.order_id,u.used_time,b.is_share, b.type_name, b.type_money, b.min_goods_amount, b.use_start_date, b.use_end_date ".
        " FROM " .$GLOBALS['hhs']->table('user_bonus'). " AS u ,".
        $GLOBALS['hhs']->table('bonus_type'). " AS b".
        " WHERE u.bonus_type_id = b.type_id AND u.user_id = '" .$value['user_id']. "'";
		
    $res = $GLOBALS['db']->getAll($sql);
	
	
	
	foreach($res as $row){
		$today = gmtime();
        
        /* 先判断是否被使用 */
		
        if (empty($row['order_id']))
        {
           
		    /* 没有被使用 */
			$cur_data = intval($row['use_end_date'] - $today);
			
           if ($cur_data == 14400)//86400一天经理要求提前4小时
            {
				
				include_once ROOT_PATH."languages/zh_cn/wx_msg.php";
				
              	$openid=$GLOBALS['db']->getOne("select openid from ".$GLOBALS['hhs']->table('users')." where user_id=".$row['user_id'] );
				
				include_once ROOT_PATH."wxpay/class_weixin.php";
				
				$weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);
				
				$url = 'user.php?act=bonus';
    			
				$desc = '亲爱的'.$value['uname'].'您有一张优惠券将于今晚24时过期！\r\n消费满'.$row['min_goods_amount'].'元免'.$row['type_money'].'\r\n点击立即使用>>';
    			
				$weixin->send_wxmsg($openid, '优惠券提醒！' , $url , $desc);
  
            	}
			
        	}
		
    	}
	}
	
	
}



?>

