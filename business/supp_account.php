<?php
define('IN_HHS', true);

if($action =='my_order')
{
	$arr=get_account_list();
	$smarty->assign('account_list',$arr['account_list']);
	$smarty->assign('pager',          $arr['pager']);
	$smarty->assign('filter',          $arr['filter']);
	$smarty->display("supp_account.dwt");
}
elseif($_REQUEST['act'] == 'account_download'){
	$arr=get_account_list(false);
	$account=$arr['account_list'];
	
	#print_r($account);die;
	#print_r($_LANG);die;

	$title="结算单";
	header("Content-type: application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$title.".xls");
	
	/* 文件标题 */
	echo hhs_iconv(EC_CHARSET, 'GB2312', $title) . "\t\n";
	/* 订单号,城市,业务,完成时间,供应商,车型 ,业务,订单金额,售价,成本,供应商结算金额  */
	echo hhs_iconv(EC_CHARSET, 'GB2312', '序号') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '结算单号') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '总金额') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '平台佣金') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '分销佣金') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '结算总额') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '结算时间') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '状态') . "\t\n";
	
	foreach($account AS $key => $value)
	{		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $key+1) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['settlement_sn']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['total']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['commission']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['fenxiao_money']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['settlement_amount']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['add_time']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $_LANG['account_settlement_status'][$value['settlement_status']]) . "\t";
		echo "\n";
	}/*
	echo "\t\t\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', '金额合计') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', $total_settlement_amount) . "\t";
	*/
	exit;
}

elseif ($_REQUEST['act'] == 'account_print'){	
	$arr=get_account_list();
	$title="结算单";		
	$smarty->assign('title',$title);
	$smarty->assign('account',$arr['account_list']);
	$smarty->assign('total_settlement_amount',$arr['total_settlement_amount']);
	$html=$smarty->fetch('account_print.dwt');
	echo $html;exit();
}
elseif($action =='account_detail')
{//echo"";exit();
	$sql="select * from ".$hhs->table('suppliers_accounts')." where id=".$_GET['suppliers_accounts_id'];
	$suppliers_account=$db->getRow($sql);
	
	$smarty->assign('settlement_status',$suppliers_account['settlement_status']);
	
	$account_detail=account_detail_list();
	

	
	$supp_row = $db->getRow("select * from".$hhs->table('supp_config')." where suppliers_id =  ".$suppliers_account['suppliers_id']);
	$smarty->assign('supp_row',$supp_row);
	
	$sql = "SELECT * FROM " . $hhs->table('settlement_action') . " WHERE settlement_id = '$_GET[suppliers_accounts_id]' ORDER BY log_time DESC,action_id DESC";
	$res = $db->query($sql);
	while ($row = $db->fetchRow($res))
	{
	    $row['status_name']    = $_LANG['account_settlement_status'][$row['status']];
	    $row['action_time']     = local_date($_CFG['time_format'], $row['log_time']);
	    $act_list[] = $row;
	}
	$smarty->assign('action_list2', $act_list);
	
	$smarty->assign('account_detail',$account_detail['row']);
	$smarty->assign('total_amount',$account_detail['total_amount']);
	$smarty->assign('total_commission',$account_detail['total_commission']);
    $smarty->assign('total_fenxiao',$account_detail['total_fenxiao']); 
	$smarty->assign('total_money',$account_detail['total_money']); 
	$smarty->assign('suppliers_accounts_id',$_GET['suppliers_accounts_id']);
	$smarty->assign('pager',          $account_detail['pager']);
	$smarty->display("supp_account.dwt");
}
elseif($action =='account_detail_form'){
	require(ROOT_PATH . 'includes/cls_json.php');
	$id=$_POST['id'];
	$remark=$_POST['remark'];
	$sql="update ".$hhs->table('suppliers_accounts')." set remark='".$remark."' where id=".$id;
	$r=$db->query($sql);
	$result=array();	
	if($r>0){
		$result['error']   = 0;
		$result['content'] = '提交成功';
	}else{
		$result['error']   = 1;
		$result['content'] = '提交失败';
	}	
	$json   = new JSON;
	echo $json->encode($result);
}
elseif($action =='account_confirm')
{//确认无误
	require(ROOT_PATH . 'includes/cls_json.php');
	$id=$_POST['id'];
	$remark=$_POST['remark'];
	$sql="update ".$GLOBALS['hhs']->table('suppliers_accounts')." set settlement_status=2,remark='$remark' where id=".$id;
	$db->query($sql);
	//操作记录
	$suppliers_info=get_suppliers_info($_SESSION['suppliers_id']);
	settlement_action($id,$remark,$suppliers_info['suppliers_name']);
	$result['error']   = 0;
	$result['content'] = '提交成功';
	$json   = new JSON;
	echo $json->encode($result);
}
elseif($action =='check_accountok')
{//确认账户信息
    require(ROOT_PATH . 'includes/cls_json.php');
    $id=$_POST['id'];
    $remark=$_POST['remark'];
    $sql="update ".$GLOBALS['hhs']->table('suppliers_accounts')." set remark='$remark',settlement_status='5' where id=".$id;
    $db->query($sql);
    //操作记录
    $suppliers_info=get_suppliers_info($_SESSION['suppliers_id']);
    settlement_action($id,$remark,$suppliers_info['suppliers_name']);
    $result['error']   = 0;
    $result['content'] = '提交成功';
    $json   = new JSON;
    echo $json->encode($result);
}
elseif($action =='account_cancel')
{//有疑义
	require(ROOT_PATH . 'includes/cls_json.php');
	$id=$_POST['id'];
	$remark=$_POST['remark'];
	$sql="update ".$GLOBALS['hhs']->table('suppliers_accounts')." set remark='$remark',settlement_status='5' where id=".$id;
	$db->query($sql);
	//操作记录
	$suppliers_info=get_suppliers_info($_SESSION['suppliers_id']);
	settlement_action($id,$remark,$suppliers_info['suppliers_name']);
	$result['error']   = 0;
	$result['content'] = '提交成功';
	$json   = new JSON;
	echo $json->encode($result);
}

elseif($action =='account_receive'){//确认收款
	require(ROOT_PATH . 'includes/cls_json.php');
	$id=empty($_REQUEST['id'])?0:$_REQUEST['id'];
	$remark=$_POST['remark'];
	$sql="update ".$GLOBALS['hhs']->table('suppliers_accounts')." set settlement_status=7,remark='$remark' where id=".$id;
	$db->query($sql);
	//操作记录
	$suppliers_info=get_suppliers_info($_SESSION['suppliers_id']);
	settlement_action($id,$remark,$suppliers_info['suppliers_name']);
	$result['error']   = 0;
	$result['content'] = '提交成功';
	$json   = new JSON;
	echo $json->encode($result);
}
elseif ($_REQUEST['act'] == 'account_detail_download'){

    $suppliers_accounts_id=$_REQUEST['suppliers_accounts_id'];
    $sql="select * from ". $GLOBALS['hhs']->table("suppliers_accounts") ." where id=".$suppliers_accounts_id;
    $row=$db->getRow($sql);
    $add_month=$row['add_month'];
    $title=substr($add_month,0,4)."年".substr($add_month,4,2)."月结算明细";
   
	
	header("Content-type: application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$title.".xls");
	
	/* 文件标题 */
	echo hhs_iconv(EC_CHARSET, 'GB2312', $title) . "\t\n";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '编号') . "\t";
    
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '订单号') . "\t";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '交易单号') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '商家') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '收货人') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '会员名称') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '支付方式') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '付款时间') . "\t";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '订单金额') . "\t";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '佣金') . "\t";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '结算金额') . "\t";
    
    echo hhs_iconv(EC_CHARSET, 'GB2312', '商品名称') . "\t";
   
    echo hhs_iconv(EC_CHARSET, 'GB2312', '商品数量') . "\t";
    echo hhs_iconv(EC_CHARSET, 'GB2312', '商品单位') . "\t\n";
    
	$total_amount=$total_commission=$total_money=0;
	
	$row=account_detail_list();
	
	foreach($row['row'] AS $key => $value)
	{
		$total_amount+=$value['amount'];
		$total_commission+=$value['commission'];
		$total_money+=($value['amount']-$value['commission']);
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['id']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['order_sn']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['transaction_order_sn']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['suppliers_name']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['consignee']) . "\t";
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['user_name']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['pay_name']) . "\t";
		
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['order_time']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['amount']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['commission']) . "\t";
		
		echo hhs_iconv(EC_CHARSET, 'GB2312', $value['money']) . "\t";
		
		foreach($value['goods']['goods_list'] as $k=>$v){
		 
		    if($k!=0){
		        echo "\t\t\t\t\t\t\t\t\t\t\t";
		    }
		    echo hhs_iconv(EC_CHARSET, 'GB2312', trim($v['goods_name'])) . "\t";
		    echo hhs_iconv(EC_CHARSET, 'GB2312', trim($v['goods_number'])) . "\t";
		    $g=explode(' ', trim($v['goods_attr']));
		    $str="";
		    foreach($g as $f){
		        $tmp=explode(':', trim($f));
		        $p=strrpos( $tmp[1],'[');
		        if($p!==false){
		            $str.=substr($tmp[1],0,$p);
		        }
		         
		    }
		    echo hhs_iconv(EC_CHARSET, 'GB2312', trim($str)) . "\t";
		    echo "\n";
		}

	}
	echo "\t\t\t\t\t\t\t";
	
	echo hhs_iconv(EC_CHARSET, 'GB2312', '金额合计') . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', $total_amount) . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', $total_commission) . "\t";
	echo hhs_iconv(EC_CHARSET, 'GB2312', $total_money) . "\t";
	exit;
    
    
}
elseif ($_REQUEST['act'] == 'account_detail_print'){
	$suppliers_accounts_id=$_REQUEST['suppliers_accounts_id'];

	$sql="select * from ". $GLOBALS['hhs']->table("suppliers_accounts") ." where id=".$suppliers_accounts_id;
	$row=$db->getRow($sql);
	$add_month=$row['add_month'];
	$title=substr($add_month,0,4)."年".substr($add_month,4,2)."月结算明细";
	$where=" where sat.suppliers_accounts_id=".$suppliers_accounts_id;
	$sql = "SELECT sat.* ".
			" FROM " . $GLOBALS['hhs']->table("suppliers_accounts_detal") . " as sat ".
			$where." ORDER BY sat.id desc ";
	$account_detail=$db->getAll($sql);
	$total_amount=$total_commission=$total_money=0;
	foreach($account_detail AS $key => $value)
	{
		$total_amount+=$value['amount'];
		$total_commission+=$value['commission'];
		$total_money+=($value['amount']-$value['commission']);			
		$account_detail[$key]['order_time']=local_date($GLOBALS['_CFG']['time_format'],$value['order_time']);		
		$account_detail[$key]['money']=$value['amount']-$value['commission'];
		$sql="select goods_number from ".$hhs->table('order_goods')." where order_id=".$value['order_id'];
		$goods_number=$db->getAll($sql);
		foreach($goods_number as $v){
			$account_detail[$key]['total_goods_num']=$v['goods_number'];
		}
		$sql="select goods_name from ".$hhs->table('order_goods')." where order_id=".$value['order_id'];
		$goods_name=$db->getAll($sql);
		foreach($goods_name as $v){
			$account_detail[$key]['goods_name']= substr($v['goods_name'].',',0,-1);
		}
	}
	$smarty->assign('title',$title);
	$smarty->assign('total_amount',$total_amount);
	$smarty->assign('total_commission',$total_commission);
	$smarty->assign('total_money',$total_money);
	$smarty->assign('account_detail',$account_detail);
	$html=$smarty->fetch('account_detail_print.dwt');
	echo $html;exit();
	 
}

elseif($action =='accounts_apply_act')

{

	$idx = $_REQUEST['idx'];

	$bank_password = $_REQUEST['bank_password'];

	$supp_config = get_supp_config($suppliers_id); 
	if($supp_config['bank_name']=='')
	{
		show_message('请先设置开户行名称');	
	}
	if($supp_config['bank_p_name']=='')
	{
		show_message('请先设置开户行姓名');	
	}
	if($supp_config['bank_account']=='')
	{
		show_message('请先设置开户行账号');	
	}


	if($supp_config['bank_password']=='')
	{
		show_message('请先设置支付密码');	
	}

	else

	{

		if($supp_config['bank_password']!=$bank_password)	

		{

			show_message('请输入正确的支付密码');	

		}

	}

	$sql = "select og.goods_price,og.goods_sn,og.rec_id,o.order_sn,og.goods_name,(og.goods_price*og.goods_number) as price,(g.commission*og.goods_number) as commission,og.goods_number,o.add_time,og.suppliers_accounts_status from ".$hhs->table('order_goods')." as og, ".$hhs->table('order_info')." as o,".$hhs->table('goods')." as g  where g.goods_id=og.goods_id and og.order_id = o.order_id  and rec_id in($idx) and og.suppliers_id='$suppliers_id' and og.suppliers_accounts_status=0";

	$list = $db->getAll($sql);

	$account_total ='';

	$price = '';

	foreach($list as $value)

	{

		$rec_ids[] = $value['rec_id'];  

		$account_total = $account_total+$value['commission'];

		$price = $price+$value['price'];	

	}

	$end_account_total = $price-$account_total;

	$bank_name = $_POST['bank_name'];

	$bank_p_name = $_POST['bank_p_name'];

	$bank_account = $_POST['bank_account'];

	$add_time = gmtime();

	$apply_desc = $_POST['apply_desc'];

	$sql = $db->query("insert into ".$hhs->table('suppliers_accounts_apply')." (account,suppliers_id,bank_name,bank_p_name,bank_account,add_time,apply_desc,rec_id) values ('$end_account_total','$suppliers_id','$bank_name','$bank_p_name','$bank_account','$add_time','$apply_desc','$rec_id')");

	show_message('申请成功','返回我的列表', 'suppliers.php?act=accounts_apply_list', 'info');

}


elseif($action =='accounts_apply_del')

{

	$id = $_GET['id'];

	$sql = $db->query("delete from ".$hhs->table('suppliers_accounts_apply')." where id='$id'");

    show_message('取消成功','返回列表', 'suppliers.php?act=accounts_apply_list', 'info');

}

elseif($action =='accounts_apply')
{
	if($_REQUEST['id']=='')
	{
		 show_message("请先选择要结算的订单");
	}
	$idx = join(",",$_REQUEST['id']);
	$sql = "select og.goods_price,og.goods_id,og.goods_sn,o.order_sn,og.goods_name,(og.goods_price*og.goods_number) as price,og.goods_number,o.add_time,og.suppliers_accounts_status from ".$hhs->table('order_goods')." as og, ".$hhs->table('order_info')." as o,".$hhs->table('goods')." as g  where og.rec_id in($idx) and g.goods_id=og.goods_id and og.order_id = o.order_id  and og.suppliers_id='$suppliers_id' and og.suppliers_accounts_status=0";
	$list = $db->getAll($sql);
	$account_total ='';
	$price_total = '';
	foreach($list as $value)
	{
		$account_total = $account_total+get_commission($value['goods_id'],$value['price']);	
		$price_total = $price_total+$value['price'];	
	}
	if($_CFG['agent_apply_account']>$price_total)
	{
		 show_message('金额满'.$_CFG['agent_apply_account']."才可提现");
	}
	else
	{
		$supp_config = $db->getRow("select * from ".$hhs->table('supp_config')." where suppliers_id='$suppliers_id'");
		$smarty->assign('supp_config',$supp_config);
		$smarty->assign('idx',$idx);
		$smarty->assign('account_total',$price_total-$account_total);
		$smarty->assign('price_total',$price_total);
		$smarty->assign('account_total',$account_total);
		$smarty->display("supp_account.dwt");
	}
}

elseif($action =='account_detail')
{//echo"";exit();
	$sql="select * from ".$hhs->table('suppliers_accounts')." where id=".$_GET['suppliers_accounts_id'];
	$suppliers_account=$db->getRow($sql);
	
	$smarty->assign('settlement_status',$suppliers_account['settlement_status']);
	
	$account_detail=account_detail_list();
	

	
	$supp_row = $db->getRow("select * from".$hhs->table('supp_config')." where suppliers_id =  ".$suppliers_account['suppliers_id']);
	$smarty->assign('supp_row',$supp_row);
	
	$sql = "SELECT * FROM " . $hhs->table('settlement_action') . " WHERE settlement_id = '$_GET[suppliers_accounts_id]' ORDER BY log_time DESC,action_id DESC";
	$res = $db->query($sql);
	while ($row = $db->fetchRow($res))
	{
	    $row['status_name']    = $_LANG['account_settlement_status'][$row['status']];
	    $row['action_time']     = local_date($_CFG['time_format'], $row['log_time']);
	    $act_list[] = $row;
	}
	$smarty->assign('action_list2', $act_list);
	
	$smarty->assign('account_detail',$account_detail['row']);
	$smarty->assign('total_amount',$account_detail['total_amount']);
	$smarty->assign('total_commission',$account_detail['total_commission']);
    $smarty->assign('total_fenxiao',$account_detail['total_fenxiao']); 
	$smarty->assign('total_money',$account_detail['total_money']); 
	$smarty->assign('suppliers_accounts_id',$_GET['suppliers_accounts_id']);
	$smarty->assign('pager',          $account_detail['pager']);
	$smarty->display("supp_account.dwt");
}

?>