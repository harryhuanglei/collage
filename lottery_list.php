<?php
define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init2.php');

$smarty->caching = false;
$goods_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$goods = $db->getRow('select goods_id,is_luck,little_img,goods_img,goods_name,shop_price as goods_price,market_price,promote_end_date from '.$hhs->table('goods').' where goods_id = ' . $goods_id);
if (! $goods['is_luck']) {
    # code...
    header("Location: /");exit();
}
if($goods['promote_end_date'] < time())
{
    $sql = "select u.uname,u.headimgurl,o.order_sn,o.mobile,o.is_lucker from ".$hhs->table('order_goods')." as g,".$hhs->table('order_info')." as o ,".$hhs->table('users')." as u where o.order_id = g.order_id and o.user_id=u.user_id and g.goods_id = " . $goods_id .' group by o.order_id';

    $orders = $db->getAll($sql);
    foreach ($orders as $key => $order) {
        $orders[$key]['mobile'] = hidtel($order['mobile']);
        unset($order);
    }
}
$smarty->assign('page_title',      '中奖查询');    // 页面标题
$smarty->assign('now',      time());
$smarty->assign('goods',      $goods);
$smarty->assign('team_mem',      $orders);
$smarty->display('lottery_list.dwt');

function hidtel($phone){
    $IsWhat = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i',$phone); //固定电话
    if($IsWhat == 1){
        return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i','$1****$2',$phone);
    }else{
        return  preg_replace('/(1[345678]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
    }
}
?>
