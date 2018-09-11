<?php
define('IN_HHS', true);

if($action =='gen_bonus_excel')
{
    @set_time_limit(0);
    /* 获得此线下优惠劵类型的ID */
    $tid  = !empty($_GET['tid']) ? intval($_GET['tid']) : 0;
    $type_name = $db->getOne("SELECT type_name FROM ".$hhs->table('bonus_type')." WHERE type_id = '$tid'");
    /* 文件名称 */
    $bonus_filename = $type_name .'_bonus_list';
    if (EC_CHARSET != 'gbk')
    {
        $bonus_filename = hhs_iconv('UTF8', 'GB2312',$bonus_filename);
    }
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$bonus_filename.xls");

    /* 文件标题 */
    if (EC_CHARSET != 'gbk')
    {
        echo hhs_iconv('UTF8', 'GB2312', $_LANG['bonus_excel_file']) . "\t\n";
        /* 优惠劵序列号, 优惠劵金额, 类型名称(优惠劵名称), 使用结束日期 */
        echo hhs_iconv('UTF8', 'GB2312', $_LANG['bonus_sn']) ."\t";
        echo hhs_iconv('UTF8', 'GB2312', $_LANG['type_money']) ."\t";
        echo hhs_iconv('UTF8', 'GB2312', $_LANG['type_name']) ."\t";
        echo hhs_iconv('UTF8', 'GB2312', $_LANG['use_enddate']) ."\t\n";
    }
    else
    {
        echo $_LANG['bonus_excel_file'] . "\t\n";
        /* 优惠劵序列号, 优惠劵金额, 类型名称(优惠劵名称), 使用结束日期 */
        echo $_LANG['bonus_sn'] ."\t";
        echo $_LANG['type_money'] ."\t";
        echo $_LANG['type_name'] ."\t";
        echo $_LANG['use_enddate'] ."\t\n";
    }

    $val = array();
    $sql = "SELECT ub.bonus_id, ub.bonus_type_id, ub.bonus_sn, bt.type_name, bt.type_money, bt.use_end_date ".
           "FROM ".$hhs->table('user_bonus')." AS ub, ".$hhs->table('bonus_type')." AS bt ".
           "WHERE bt.type_id = ub.bonus_type_id AND ub.bonus_type_id = '$tid' ORDER BY ub.bonus_id DESC";
    $res = $db->query($sql);

    $code_table = array();
    while ($val = $db->fetchRow($res))
    {
        echo $val['bonus_sn'] . "\t";
        echo $val['type_money'] . "\t";
        if (!isset($code_table[$val['type_name']]))
        {
            if (EC_CHARSET != 'gbk')
            {
                $code_table[$val['type_name']] = hhs_iconv('UTF8', 'GB2312', $val['type_name']);
            }
            else
            {
                $code_table[$val['type_name']] = $val['type_name'];
            }
        }
        echo $code_table[$val['type_name']] . "\t";
        echo local_date('Y-m-d', $val['use_end_date']);
        echo "\t\n";
    }	
}
elseif($action =='bonus_batch')
{
   /* 去掉参数：优惠劵类型 */
    $bonus_type_id = intval($_REQUEST['bonus_type']);
    /* 取得选中的优惠劵id */
    if (isset($_POST['bonus_id']))
    {
        $bonus_id_list = $_POST['bonus_id'];
        /* 删除优惠劵 */
        if (isset($_POST['bonus_list_drop']))
        {
            $sql = "DELETE FROM " . $hhs->table('user_bonus'). " WHERE bonus_id " . db_create_in($bonus_id_list);
            $db->query($sql);
   			 show_message('操作成功','返回列表', "index.php?op=bonus&act=bonus_list&page=$page&bonus_type=$bonus_type_id", 'info');
        }
	}
	else
	{
		show_message("请先选择");
	}
}
elseif($action =='delete_bonus_list')
{
	
    $id = intval($_GET['bonus_id']);
	$bonus_type = $_GET['bonus_type'];
	$page = $_GET['page'];
    $db->query("DELETE FROM " .$hhs->table('user_bonus'). " WHERE bonus_id='$id'");
	show_message('操作成功','返回列表', "index.php?op=bonus&act=bonus_list&page=$page&bonus_type=$bonus_type", 'info');
}
elseif($action =='bonus_list')
{
    $list = get_bonus_list($suppliers_id);
    /* 赋值是否显示优惠劵序列号 */
    $bonus_type = bonus_type_info(intval($_REQUEST['bonus_type']));
    if ($bonus_type['send_type'] == SEND_BY_PRINT)
    {
        $smarty->assign('show_bonus_sn', 1);
    }
	
    $smarty->assign('bonus_list',   $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
	$smarty->assign('pager',$list['pager']);
    $smarty->display('suppliers_bonus_type.dwt');
}
elseif($action =='send_by_print')
{
    @set_time_limit(0);

    /* 红下优惠劵的类型ID和生成的数量的处理 */
    $bonus_typeid = !empty($_POST['bonus_type_id']) ? $_POST['bonus_type_id'] : 0;
    $bonus_sum    = !empty($_POST['bonus_sum'])     ? $_POST['bonus_sum']     : 1;

    /* 生成优惠劵序列号 */
    $num = $db->getOne("SELECT MAX(bonus_sn) FROM ". $hhs->table('user_bonus')." where suppliers_id='$suppliers_id'");
    $num = $num ? floor($num / 10000) : 100000;
    for ($i = 0, $j = 0; $i < $bonus_sum; $i++)
    {
        $bonus_sn = ($num + $i) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $db->query("INSERT INTO ".$hhs->table('user_bonus')." (bonus_type_id, bonus_sn,suppliers_id) VALUES('$bonus_typeid', '$bonus_sn','$suppliers_id')");

        $j++;
    }
	show_message('生成成功','优惠券列表', 'index.php?op=bonus&act=bonus_list&bonus_type='.$bonus_typeid, 'info');
}
elseif($action =='send_bonus')
{
    /* 取得参数 */
    $id = !empty($_REQUEST['id'])  ? intval($_REQUEST['id'])  : '';

    if ($_REQUEST['send_by'] == SEND_BY_USER)
    {
        $smarty->assign('id',           $id);
        // $smarty->assign('ranklist',     get_rank_list());

        // $smarty->display('bonus_by_user.htm');
        $smarty->assign('action','bonus_by_user');
        
    }
    if ($_REQUEST['send_by'] == SEND_BY_GOODS)
    {
        /* 查询此优惠劵类型信息 */
        $bonus_type = $db->GetRow("SELECT type_id, type_name FROM ".$hhs->table('bonus_type').
            " WHERE suppliers_id = '".$suppliers_id."' AND type_id='$_REQUEST[id]'");

        /* 查询优惠劵类型的商品列表 */
        $goods_list = get_bonus_goods($_REQUEST['id']);
		if(is_array($goods_list)){ //by mike add

			foreach($goods_list as $k=>$val){
				
				if(!empty($val['use_goods_name']))
				{
					$goods_list[$k]['goods_name'] = $val['goods_name']."---唯一商品名称：[".$val['use_goods_name']."]";
					
				}
				else
				{
					$goods_list[$k]['goods_name'] = $val['goods_name'];
				}
				
			}
		}
        /* 查询其他优惠劵类型的商品 */
        $sql = "SELECT goods_id FROM " .$hhs->table('goods').
               " WHERE bonus_type_id > 0 AND bonus_type_id <> '$_REQUEST[id]'";
        $other_goods_list = $db->getCol($sql);
        $smarty->assign('other_goods', join(',', $other_goods_list));



        /* 模板赋值 */
     	$smarty->assign('cat_list',     cat_list(0));
        $smarty->assign('bonus_type',  $bonus_type);
        $smarty->assign('goods_list',  $goods_list);
        $smarty->assign('action','bonus_by_goods');
    }
    elseif ($_REQUEST['send_by'] == SEND_BY_PRINT)
    {
        $smarty->assign('type_list',    get_bonus_type($suppliers_id));
		$smarty->assign('action','bonus_by_print');
    }
	$smarty->display('suppliers_bonus_type.dwt');
	
}
/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */
if ($action== 'get_goods_list')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    $filters = $json->decode($_GET['JSON']);

    $arr = get_goods_list($filters);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => $val['shop_price']);
    }

    make_json_result($opt);
}
/*------------------------------------------------------ */
//-- 添加发放优惠劵的商品
/*------------------------------------------------------ */
if ($action == 'add_bonus_goods')
{
	
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
    $add_ids    = $json->decode($_GET['add_ids']);
    $args       = $json->decode($_GET['JSON']);
    $type_id    = $args[0];
	
	//限制使用优惠卷的商品
	$use_goods_sn  = strtolower($args[1]);
	
	/*
	if(!empty($use_goods_sn))
	{
		$arr = get_goods_list($filters);
	}
	
	*/
	
    foreach ($add_ids AS $key => $val)
    {
        $sql = "UPDATE " .$hhs->table('goods'). " SET bonus_type_id='$type_id',use_goods_sn='$use_goods_sn' WHERE goods_id='$val'";
		$db->query($sql, 'SILENT') or make_json_error($db->error());
    }

	/* 重新载入 */
    $arr = get_bonus_goods($type_id);
	$opt = array();

    foreach ($arr AS $key => $val)
    {
		if(!empty($val['use_goods_name']))
		{
		    $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name']."---唯一商品名称：[".$val['use_goods_name']."]",
                        'data'  => '');
		}
		else
		{
			$opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
		}
        
    }

    make_json_result($opt);
}
/*------------------------------------------------------ */

//-- 删除发放优惠劵的商品
/*------------------------------------------------------ */
if ($action == 'drop_bonus_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;


    $drop_goods     = $json->decode($_GET['drop_ids']);
    $drop_goods_ids = db_create_in($drop_goods);
    $arguments      = $json->decode($_GET['JSON']);
    $type_id        = $arguments[0];

    $db->query("UPDATE ".$hhs->table('goods')." SET bonus_type_id = 0,use_goods_sn=0 ".
                "WHERE bonus_type_id = '$type_id' AND goods_id " .$drop_goods_ids);

    /* 重新载入 */
    $arr = get_bonus_goods($type_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    make_json_result($opt);
}
elseif($action =='delete_bonus')
{
    $id = intval($_GET['type_id']);
    $db->query("DELETE FROM ".$hhs->table('bonus_type')." WHERE type_id='$id'");

    /* 更新商品信息 */
    $db->query("UPDATE " .$hhs->table('goods'). " SET bonus_type_id = 0 WHERE bonus_type_id = '$id'");
    /* 删除用户的优惠劵 */
    $db->query("DELETE FROM " .$hhs->table('user_bonus'). " WHERE bonus_type_id = '$id'");
	show_message('操作成功','返回列表', 'index.php?op=bonus&act=bonus', 'info');
}
elseif($action =='bonus_update')
{
    /* 获得日期信息 */
    $send_startdate = local_strtotime($_POST['send_start_date']);
    $send_enddate   = local_strtotime($_POST['send_end_date']);
    $use_startdate  = local_strtotime($_POST['use_start_date']);
    $use_enddate    = local_strtotime($_POST['use_end_date']);
    /* 对数据的处理 */
    $type_name   = !empty($_POST['type_name'])  ? trim($_POST['type_name'])    : '';
    $type_id     = !empty($_POST['type_id'])    ? intval($_POST['type_id'])    : 0;
    $min_amount  = !empty($_POST['min_amount']) ? intval($_POST['min_amount']) : 0;

    //参与线上领取
    $is_online  = !empty($_POST['is_online']) ? intval($_POST['is_online']) : 0;
    if($_POST['send_type'] < 3)
        $is_online  = 0;
    $free_all    = intval($_POST['free_all']);
    $only_first    = intval($_POST['only_first']);


    $sql = "UPDATE " .$hhs->table('bonus_type'). " SET ".
           "type_name       = '$type_name', ".
           "type_money      = '$_POST[type_money]', ".
           "use_start_date  = '$use_startdate', ".
           "use_end_date    = '$use_enddate', ".
           "is_online    = '$is_online', ".
           "free_all    = '$free_all', ".
           "only_first    = '$only_first', ".
		   
		   "send_start_date  = '$send_startdate', ".
           "send_end_date    = '$send_enddate', ".
           "number    = '$_POST[number]', ".
           "is_share    = '$_POST[is_share]', ".
		   
           "send_type       = '$_POST[send_type]', ".
           "min_amount      = '$min_amount', " .
           "min_goods_amount = '" . floatval($_POST['min_goods_amount']) . "' ".
           "WHERE type_id   = '$type_id'";

   $db->query($sql);	
	show_message('操作成功','返回列表', 'index.php?op=bonus&act=bonus', 'info');

}
elseif($action =='edit_bonus')
{
    /* 获取优惠劵类型数据 */
    $type_id = !empty($_GET['type_id']) ? intval($_GET['type_id']) : 0;
    $bonus_arr = $db->getRow("SELECT * FROM " .$hhs->table('bonus_type'). " WHERE type_id = '$type_id'");
    $bonus_arr['send_start_date']   = local_date('Y-m-d', $bonus_arr['send_start_date']);
    $bonus_arr['send_end_date']     = local_date('Y-m-d', $bonus_arr['send_end_date']);
    $bonus_arr['use_start_date']    = local_date('Y-m-d', $bonus_arr['use_start_date']);
    $bonus_arr['use_end_date']      = local_date('Y-m-d', $bonus_arr['use_end_date']);
    $smarty->assign('lang',        $_LANG);
    $smarty->assign('form_act',    'bonus_update');
    $smarty->assign('bonus_arr',   $bonus_arr);
    $smarty->display('suppliers_bonus_type.dwt');
	
}
elseif($action =='bonus_insert')
{
    /* 去掉优惠劵类型名称前后的空格 */
    $type_name   = !empty($_POST['type_name']) ? trim($_POST['type_name']) : '';
    /* 初始化变量 */
    $type_id     = !empty($_POST['type_id'])    ? intval($_POST['type_id'])    : 0;
    $min_amount  = !empty($_POST['min_amount']) ? intval($_POST['min_amount']) : 0;
    /* 检查类型是否有重复 */
    $sql = "SELECT COUNT(*) FROM " .$hhs->table('bonus_type'). " WHERE type_name='$type_name' and suppliers_id='$suppliers_id'";
    if ($db->getOne($sql) > 0)
    {
        show_message('该名称已存在');
    }


    /* 获得日期信息 */
    $send_startdate = local_strtotime($_POST['send_start_date']);
    $send_enddate   = local_strtotime($_POST['send_end_date']);
    $use_startdate  = local_strtotime($_POST['use_start_date']);
    $use_enddate    = local_strtotime($_POST['use_end_date']);

    $is_share    = intval($_POST['is_share']);
    $number    = $is_share ? intval($_POST['number']):0;
    $free_all    = intval($_POST['free_all']);
    $only_first    = intval($_POST['only_first']);

    //参与线上领取
    $is_online  = !empty($_POST['is_online']) ? intval($_POST['is_online']) : 0;
    if($_POST['send_type'] < 3)
        $is_online  = 0;

    /* 插入数据库。 */
    $sql = "INSERT INTO ".$hhs->table('bonus_type')." (is_online,free_all,only_first,number,is_share,send_start_date,send_end_date,suppliers_id,type_name, type_money,use_start_date,use_end_date,send_type,min_amount,min_goods_amount)
    VALUES ('$is_online',
            '$free_all',
            '$only_first',
            '$number',
            '$is_share',
			'$send_startdate',
			'$send_enddate',
			'$suppliers_id',
			'$type_name',
            '$_POST[type_money]',
            '$use_startdate',
            '$use_enddate',
            '$_POST[send_type]',
            '$min_amount','" . floatval($_POST['min_goods_amount']) . "')";
    $db->query($sql);	
	show_message('操作成功','返回列表', 'index.php?op=bonus&act=bonus', 'info');
}
elseif($action =='add_bonus')
{
    $smarty->assign('action',       'add_bonus');

    $smarty->assign('form_act',     'bonus_insert');
    $smarty->assign('cfg_lang',     $_CFG['lang']);
    $next_month = local_strtotime('+1 months');
    $bonus_arr['send_start_date']   = local_date('Y-m-d');
    $bonus_arr['use_start_date']    = local_date('Y-m-d');
    $bonus_arr['send_end_date']     = local_date('Y-m-d', $next_month);
    $bonus_arr['use_end_date']      = local_date('Y-m-d', $next_month);
    $smarty->assign('bonus_arr',    $bonus_arr);
    $smarty->display('suppliers_bonus_type.dwt');
}
//优惠券
elseif($action =='bonus')
{

    $list = get_type_list($suppliers_id);
    $smarty->assign('action',   'bonus');
    $smarty->assign('type_list',    $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
	$smarty->assign('pager',   $list['pager']);
    $smarty->display('suppliers_bonus_type.dwt');
}

?>