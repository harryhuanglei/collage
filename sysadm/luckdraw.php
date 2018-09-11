<?php

/**



 * 小舍电商 抽奖



 * ============================================================================



 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。



 * 网站地址: http://www.baidu.com；



 * ----------------------------------------------------------------------------



 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和



 * 使用；不允许对程序代码以任何形式任何目的的再发布。



 * ============================================================================



 * $Author: pangbin $



 * $Id: luckmoney.php 17217 2014-05-12 06:29:08Z pangbin $



*/



define('IN_HHS', true);



require(dirname(__FILE__) . '/includes/init.php');



require(ROOT_PATH . '/includes/lib_order.php');



require(ROOT_PATH . '/languages/zh_cn/admin/order.php');

$exc = new exchange($hhs->table("luckdraw"), $db, 'id', 'title');

$smarty->assign('lang',       $_LANG);

/* act操作项的初始化 */

if (empty($_REQUEST['act']))
{

    $_REQUEST['act'] = 'list';
}



else



{



    $_REQUEST['act'] = trim($_REQUEST['act']);



}



/* 初始化$exc对象 */

$exc = new exchange($hhs->table('luckdraw'), $db, 'id', 'title');

/*------------------------------------------------------ */

//-- 优惠劵类型列表页面

/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'list')



{



	/* 权限判断 */



    admin_priv('luck_manage');

    $smarty->assign('ur_here',     '抽奖');



    $smarty->assign('action_link', array('text' => '添加抽奖', 'href' => 'luckdraw.php?act=add'));



    $smarty->assign('full_page',   1);



    $list = get_luckdraw_list();



    $smarty->assign('luck_list',    $list['item']);



    $smarty->assign('filter',       $list['filter']);



    $smarty->assign('record_count', $list['record_count']);



    $smarty->assign('page_count',   $list['page_count']);



    $sort_flag  = sort_flag($list['filter']);



    $smarty->assign($sort_flag['tag'], $sort_flag['img']);



    assign_query_info();



    $smarty->display('luckdraw.htm');



}



/*------------------------------------------------------ */

//-- 编辑

/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'edit')



{



    /* 权限判断 */



    admin_priv('exchange_manage');

    /* 取商品数据 */



    $sql = "SELECT eg.*, g.goods_name ".



           " FROM " . $hhs->table('luckdraw') . " AS eg ".



           "  LEFT JOIN " . $hhs->table('goods') . " AS g ON g.goods_id = eg.goods_id ".



           " WHERE eg.id='$_REQUEST[id]'";



    $goods = $db->GetRow($sql);



    $goods['option']  = '<option value="'.$goods['goods_id'].'">'.$goods['goods_name'].'</option>';



	$goods['start_time'] = local_date("Y-m-d H:i",$goods['start_time']);



	$goods['end_time'] = local_date("Y-m-d H:i",$goods['end_time']);



    $smarty->assign('goods',       $goods);



    $smarty->assign('ur_here',     '抽奖编辑');



    $smarty->assign('action_link', array('text' => '抽奖活动列表', 'href' => 'luckdraw.php?act=list&' . list_link_postfix()));



    $smarty->assign('form_action', 'update');



    assign_query_info();



    $smarty->display('luckdraw_info.htm');



}



/*------------------------------------------------------ */



//-- 翻页、排序



/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'query')



{



	check_authz_json('luck_manage');



    $list = get_luckdraw_list();



    $smarty->assign('luck_list',    $list['item']);



    $smarty->assign('filter',       $list['filter']);



    $smarty->assign('record_count', $list['record_count']);



    $smarty->assign('page_count',   $list['page_count']);



    $sort_flag  = sort_flag($list['filter']);



    $smarty->assign($sort_flag['tag'], $sort_flag['img']);



    make_json_result($smarty->fetch('luckdraw.htm'), '',



    array('filter' => $list['filter'], 'page_count' => $list['page_count']));



}



/*------------------------------------------------------ */



//-- 删除优惠劵类型



/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'remove')



{



    check_authz_json('luck_manage');



    $id = intval($_GET['id']);



    $exc->drop($id);



    $url = 'luckdraw.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);



    hhs_header("Location: $url\n");



    exit;

}



/*------------------------------------------------------ */



//-- 搜索商品



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] == 'search_goods')



{



    include_once(ROOT_PATH . 'includes/cls_json.php');



    $json = new JSON;



    $filters = $json->decode($_GET['JSON']);


    $arr = get_goods_list($filters);



    make_json_result($arr);



}



/*------------------------------------------------------ */



//-- 编辑



/*------------------------------------------------------ */



elseif ($_REQUEST['act'] =='update')

{

    /* 权限判断 */



    admin_priv('luck_manage');



   $start_time = local_strtotime($_POST['start_time']);



    $end_time   = local_strtotime($_POST['end_time']);



    if ($exc->edit("goods_id='$_POST[goods_id]', title='$_POST[title]', start_time='$start_time', end_time='$end_time',stock_num='$_POST[stock_num]',content='$_POST[content]' ,luckdraw_price='$_POST[luckdraw_price]'", $_POST['id']))



    {



        $link[0]['text'] = '返回列表';



        $link[0]['href'] = 'luckdraw.php?act=list&' . list_link_postfix();



        clear_cache_files();



        sys_msg('编辑成功', 0, $link);



    }

    else

    {

        die($db->error());

    }



}



/*------------------------------------------------------ */



//-- 优惠劵类型添加页面



/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'add')



{



    admin_priv('distribution_change');



    $smarty->assign('lang',         $_LANG);



    $smarty->assign('ur_here',      '添加抽奖');



    $smarty->assign('action_link',  array('href' => 'luckdraw.php?act=list', 'text' => '抽奖列表'));



    $smarty->assign('action',       'add');



    $smarty->assign('start_at',       local_date("Y-m-d H:i",local_strtotime("+1 day")));



    $smarty->assign('end_at',       local_date("Y-m-d H:i",local_strtotime("+31 day")));



    $smarty->assign('form_action',     'insert');



    $smarty->assign('cfg_lang',     $_CFG['lang']);



    $goods['option']      = '<option value="0">请先搜索商品生成选项列表</option>';



    $smarty->assign('goods',       $goods);



    assign_query_info();



    $smarty->display('luckdraw_info.htm');



}



/*------------------------------------------------------ */



//-- 查看活动详情



/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'view')



{

    $luck_id = empty($_REQUEST['snatch_id']) ? 0 : intval($_REQUEST['snatch_id']);



    $smarty->assign('luck_id',  $luck_id );



    $order_list = get_snatch_detail();

	//print_r($order_list);

	/* if($order_list['orders'])
	{

		foreach ($order_list['orders'] as $key => $value)
		{

			$team_status[$key] = $value['team_status'];

		}

	}else{

		$team_status_log = 2;

	}


	$smarty->assign('team_status_log',   $team_status_log); */


    $smarty->assign('full_page',        1);



    $smarty->assign('order_list',   $order_list['orders']);



    $smarty->assign('filter',       $order_list['filter']);



    $smarty->assign('record_count', $order_list['record_count']);



    $smarty->assign('page_count',   $order_list['page_count']);



    $sql="select * from " . $GLOBALS['hhs']->table('luckdraw') ." where id=".$luck_id;



    $luck_info=$db->getRow($sql);



    $can_luck=true;

    if(gmtime() < $luck_info['end_time'] ){

		/* 测试点  $can_luck=false; */

        $can_luck=false;

        /* 测试点 */

    }
    $status_sql = "select order_id from ".$GLOBALS['hhs']->table('order_info')." where luckdraw_id = ".$luck_id;
    $status=$GLOBALS['db']->getAll($status_sql);
    if($status){
			$start_status = 1;
    }
    $smarty->assign('start_status',$start_status);

    $smarty->assign('luck_status',$luck_info['luck_status']);

    $smarty->assign('can_luck',   $can_luck);

    $sort_flag  = sort_flag($bid_list['filter']);

    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    $smarty->assign('ur_here',    '抽奖活动详情' );

    $smarty->assign('action_link',  array('text' => '抽奖活动列表', 'href'=>'luckdraw.php?act=list'));

	$smarty->display('luckdraw_view.htm');



}





/*------------------------------------------------------ */



//-- 优惠劵类型添加的处理



/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'insert')

{



    /* 去掉优惠劵类型名称前后的空格 */



    $name        = !empty($_POST['title']) ? trim($_POST['title']) : '';



    /* 检查类型是否有重复 */



    $sql = "SELECT COUNT(*) FROM " .$hhs->table('luckdraw'). " WHERE `title`='$name'";



    if ($db->getOne($sql) > 0)



    {



        $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');



        sys_msg('抽奖活动名已经存在', 0, $link);



    }



    /* 初始化变量 */



    $num         = !empty($_POST['stock_num']) ? intval($_POST['stock_num']) : 0;



    $content = !empty($_POST['content']) ? intval($_POST['content']) : 0;



    /* 获得日期信息 */



    $start_time = local_strtotime($_POST['start_time']);



    $end_time   = local_strtotime($_POST['end_time']);



	$goods_id = $_POST['goods_id'];

 	$luckdraw_price = $_POST['luckdraw_price'];

 	$content = $_POST['content'];



    /* 插入数据库。 */



    $sql = "INSERT INTO ".$hhs->table('luckdraw')." (title,goods_id,stock_num,start_time, end_time,content,luckdraw_price)



    VALUES ('$name','$goods_id','$num','$start_time','$end_time','$content','$luckdraw_price')";



    $db->query($sql);



    $luck_id = $db->insert_id();



    /* 记录管理员操作 */



    admin_log($name, 'add', 'luck');



    /* 清除缓存 */



    clear_cache_files();



    /* 提示信息 */



   $link[0]['text'] = '返回列表';



    $link[0]['href'] = 'luckdraw.php?act=list';



    sys_msg('添加' . "&nbsp;" .$name . "&nbsp;" .'拼团活动成功',0, $link);



}



/*------------------------------------------------------ */



//-- 排序、



/*------------------------------------------------------ */



if ($_REQUEST['act'] == 'team_query' )



{



    $order_list = get_snatch_detail();



    //mod by coolvee.com 酷唯软件出品



    $tpl_file = 'luckdraw_view.htm';



    $smarty->assign('order_list',   $order_list['orders']);



    $smarty->assign('filter',       $order_list['filter']);



    $smarty->assign('record_count', $order_list['record_count']);



    $smarty->assign('page_count',   $order_list['page_count']);



    $sort_flag  = sort_flag($order_list['filter']);



    $smarty->assign($sort_flag['tag'], $sort_flag['img']);



    make_json_result($smarty->fetch($tpl_file), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));



}



/**

 * 获取优惠劵类型列表

 * @access  public

 * @return void

 */



function get_luckdraw_list()



{



    $result = get_filter();



    if ($result === false)



    {

        /* 查询条件 */



        $filter['sort_by']    = 'id';

        $filter['sort_order'] = 'DESC';

        $filter['goods_name'] = empty($_REQUEST['goods_name']) ? '' : trim($_REQUEST['goods_name']);

        $filter['luckdraw_name'] = empty($_REQUEST['luckdraw_name']) ? '' : trim($_REQUEST['luckdraw_name']);

        $filter['stock_num'] = empty($_REQUEST['stock_num']) ? '' : trim($_REQUEST['stock_num']);

        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);

        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);

         $filter['luckdraw_status']  = empty($_REQUEST['luckdraw_status']) ? '' : trim($_REQUEST['luckdraw_status']);



         $where .= " 1";



        if($filter['luckdraw_status']   == 1){

        	$where .= " AND luck_status = 0 AND ".gmtime()." <end_time AND ".gmtime()." > start_time";

        }elseif ($filter['luckdraw_status']   == 2){

        	$where .= " AND luck_status = 1";

        }elseif ($filter['luckdraw_status']   == 3){

        	$where .= " AND ".gmtime()." > end_time";

        }elseif ($filter['luckdraw_status']   == 4){

        	$where .= " AND ".gmtime()." < start_time";

        }

        if($filter['goods_name']){

        	$where .= " AND g.goods_name LIKE '%" . mysql_like_quote($filter['goods_name']) . "%'";

        }

        if($filter['luckdraw_name']){

        	$where .= " AND l.title LIKE '%" . mysql_like_quote($filter['luckdraw_name']) . "%'";

        }

        if($filter['stock_num']){

        	$where .= " AND l.stock_num = ".$filter['stock_num'];

        }

        if($filter['start_time']){

        	$where .= " AND l.start_time >= ".$filter['start_time'];

        }

        if($filter['end_time']){

        	$where .= " AND l.end_time <= ".$filter['end_time'];

        }



        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['hhs']->table('luckdraw')." AS l LEFT JOIN ".$GLOBALS['hhs']->table('goods')." AS g ON l.goods_id = g.goods_id where ".$where;

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);



        /* 分页大小 */



        $filter = page_and_size($filter);



       // $sql = "SELECT * FROM " .$GLOBALS['hhs']->table('luckdraw'). " ORDER BY $filter[sort_by] $filter[sort_order]";

        $sql = "SELECT  l.* , g.goods_name FROM ".$GLOBALS['hhs']->table('luckdraw')." AS l LEFT JOIN ".$GLOBALS['hhs']->table('goods')." AS g ON l.goods_id = g.goods_id where ".$where." ORDER BY $filter[sort_by] $filter[sort_order]";



        set_filter($filter, $sql);

    }

    else

    {

        $sql    = $result['sql'];

        $filter = $result['filter'];

    }



    $arr = array();



    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

	$ntime = gmtime();



    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        if($row['start_time']<$ntime&&$ntime<$row['end_time'])

		{

			$row['start'] =1;

		}



		elseif($ntime>$row['end_time'])

		{

			$row['start'] =2;

		}

		elseif($ntime<$row['start_time'])

		{

			$row['start'] =3;

		}



		$row['start_time'] = local_date($GLOBALS['_CFG']['date_format'], $row['start_time']);



        $row['end_time']   = local_date($GLOBALS['_CFG']['date_format'], $row['end_time']);



        $arr[]           = $row;



    }



    $arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;



}







/**

 * 返回活动详细列表

 * @access  public

 *

 * @return array

 */



function get_snatch_detail()



{



    $filter['snatch_id']  = empty($_REQUEST['snatch_id']) ? 0 : intval($_REQUEST['snatch_id']);



    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'o.add_time' : trim($_REQUEST['sort_by']);



    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);



    $filter['team_status'] = isset($_REQUEST['team_status']) ? intval($_REQUEST['team_status']) : -1;



    $filter['goods_name'] = empty($_REQUEST['goods_name']) ? '' : trim($_REQUEST['goods_name']);



     $filter['team_sign'] = empty($_REQUEST['team_sign']) ? '' : trim($_REQUEST['team_sign']);



     $filter['extension_id'] = empty($_REQUEST['extension_id']) ? '' : intval($_REQUEST['extension_id']);



     $filter['team_lack_num']  = empty($_REQUEST['team_lack_num']) ? '' : intval($_REQUEST['team_lack_num']);



     $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);



     $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);



    $where = " WHERE o.luckdraw_id='$filter[snatch_id]'";



    $where .=" and o.team_first=1 and o.extension_code='team_goods' " ;



 	if ($filter['team_status']!= -1){

            $where .= " AND o.team_status = '$filter[team_status]'";

        }

    if($filter['team_sign']){



    	$where .=" and o.team_sign=".$filter['team_sign'];



    }

   if($filter['goods_name']){



        $where .= " AND g.goods_name LIKE '%" . mysql_like_quote($filter['goods_name']) . "%'";



    }

    if($filter['extension_id']){



    	$where .=" and o.extension_id=".$filter['extension_id'];



    }

 	if ($filter['team_lack_num']){



        $where .= " AND (o.team_num-o.teammen_num) = '$filter[team_lack_num]'";



 	}

 	if($filter['start_time']){



 		 $where .= " AND o.pay_time >= '$filter[start_time]'";



 	}

 	if($filter['end_time']){



 		$where .= " AND o.pay_time <= '$filter[end_time]'";



 	}

    /* 获得记录总数以及总页数 */



    $sql = "SELECT count(*) FROM ".$GLOBALS['hhs']->table('order_info'). " as o  LEFT JOIN ".$GLOBALS['hhs']->table('goods')." as g ON g.goods_id=o.extension_id ". $where;



    $filter['record_count'] = $GLOBALS['db']->getOne($sql);



    $filter = page_and_size($filter);





    $sql ="SELECT o.* , (o.team_num-o.teammen_num) as team_lack_num, " .



        "(" . order_amount_field('o.') . ") AS total_fee, " .



        " u.openid,u.uname, ".



        "g.goods_name,g.goods_sn  FROM "



        . $GLOBALS['hhs']->table('order_info') . " AS o LEFT JOIN "



        .$GLOBALS['hhs']->table('users'). " AS u ON u.user_id=o.user_id LEFT JOIN "



        .$GLOBALS['hhs']->table('goods'). " AS g ON g.goods_id=o.extension_id "



        .$where .



        " ORDER BY $filter[sort_by] $filter[sort_order] ".



        " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";



    $row = $GLOBALS['db']->getAll($sql);



    foreach ($row AS $key => $value)



    {



        $row[$key]['formated_order_amount'] = price_format($value['order_amount']);



        $row[$key]['formated_money_paid'] = price_format($value['money_paid']);



        $row[$key]['formated_total_fee'] = price_format($value['total_fee']);



        $row[$key]['short_order_time'] = local_date('m-d H:i', $value['add_time']);



        $row[$key]['formated_pay_date'] = local_date('Y-m-d H:i:s', $value['pay_time']);



        if($value['team_sign'] ){



            $sql="select pay_time from ".$GLOBALS['hhs']->table('order_info')." where order_id=".$value['team_sign'];



            $team_start_time=$GLOBALS['db']->getOne($sql);



            if($team_start_time){



                $row[$key]['team_start_date'] = local_date('Y-m-d H:i:s', $team_start_time);



                $row[$key]['team_end_date'] = local_date('Y-m-d H:i:s', $team_start_time+$GLOBALS['_CFG']['team_suc_time']*24*3600);



            }



        }



    }



    $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);



    return $arr;



}



?>

