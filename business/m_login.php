<?php
if($action =='login')
{
	
	$smarty->assign('business_login_logo',$_CFG['business_login_logo']);
	$smarty->display('m_login.dwt');
}
elseif($action =='act_login_sub')
{
	
	$account_name = $_POST['user_name'];
	$account_password = md5($_POST['password']);
//	$agent = $db->getRow("select suppliers_id,account_id,account_type from ".$hhs->table('supp_account')." where account_name='$account_name' and account_password ='$account_password' and is_check=1");
//	if($agent)
//	{
//		
//		  $_SESSION['suppliers_id'] = $agent['suppliers_id'];		  
//		  $_SESSION['role_id'] = $agent['account_id'];
//		  $_SESSION['account_type'] = $agent['account_type'];
//		  hhs_header("Location:index.php\n");
//	}
//	else
//	{
//		  
		  $sql = "select suppliers_id,is_check from ".$hhs->table('suppliers')." where user_name='$account_name' and password='$account_password'";
		  #echo $sql;die;
		  $rows = $db->getRow($sql);
		  
		  
		  if($rows)
		  {
			  if($rows['is_check'] != 1)
			  {
				  show_message('未通过审核！');
			  }
			  else
			  {
				
				 $_SESSION['suppliers_id'] = $rows['suppliers_id'];
				 
				 hhs_header("Location:index.php?op=main&act=default\n");
			  
			  }
		  }
		  else
		  {
				 show_message('用户名或密码有误');
		  }
		
	//}
}
// elseif($action =='act_login_sub')
// {
// 	$account_name = $_POST['user_name'];
// 	$account_password = md5($_POST['password']);
// 	$agent = $db->getRow("select suppliers_id,account_id,account_type from ".$hhs->table('supp_account')." where account_name='$account_name' and account_password ='$account_password' and is_check=1");
// 	if($agent)
// 	{
		  
// 		  $_SESSION['suppliers_id'] = $agent['suppliers_id'];		  
// 		  $_SESSION['role_id'] = $agent['account_id'];
// 		  $_SESSION['account_type'] = $agent['account_type'];
// 		   hhs_header("index.php\n");
// 	}
// 	else
// 	{
// 		  $sql = "select suppliers_id,is_check from ".$hhs->table('suppliers')." where user_name='$account_name' and password='$account_password'";
// 		  #echo $sql;die;
// 		  $rows = $db->getRow($sql);
// 		  if($rows)
// 		  {
// 			  if($rows['is_check'] != 1)
// 			  {
// 				  show_message('未通过审核！');
// 			  }
// 			  else
// 			  {
// 				 $_SESSION['suppliers_id'] = $rows['suppliers_id'];
// 				 hhs_header("index.php\n");
			  
// 			  }
// 		  }
// 		  else
// 		  {
// 				 show_message('用户名或密码有误');
// 		  }
// 	}
// }
elseif($action =='logout')
{
	$user->logout();
	$_SESSION['suppliers_id'] = 0;
	$_SESSION['role_id'] = 0;
	$_SESSION['account_type'] = 0;
	hhs_header("Location:index.php?op=login&act=login   \n");
}
	
?>