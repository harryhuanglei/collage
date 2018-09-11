<?php
define('IN_HHS', true);
if($action =='bank_config')
{
	$supp_row = $db->getRow("select * from".$hhs->table('supp_config')."where suppliers_id = '".$suppliers_id."' ");
	$smarty->assign('supp_row',$supp_row);
	$smarty->display("supp_set.dwt");
}
//更新运费类型

else if($action =='update_shipping_type')

{

	$bank_name = $_POST['bank_name'];

	$bank_p_name = $_POST['bank_p_name'];

	$bank_account = $_POST['bank_account'];

	$bank_password = $_POST['bank_password'];

	$supp_isset = $db->getOne("select id from".$hhs->table('supp_config')."where suppliers_id = '".$suppliers_id."' ");



	if($supp_isset){

		//存在则更新

		  $sql = 'UPDATE ' . $hhs->table('supp_config') . " SET `bank_name`='$bank_name',`bank_p_name`='$bank_p_name',bank_account='$bank_account',bank_password='$bank_password'  WHERE `id`='" . $supp_isset. "'";			

		  $res = $db->query($sql);

		  if($res){

			show_message('修改成功', $_LANG['back_up_page'], './index.php?op=set&act=bank_config', 'info');

		  }

	} 

	else

	{

		//不存在则添加	

		  $sql = "INSERT INTO ". $hhs->table('supp_config') . " (`suppliers_id`,`bank_name`, `bank_p_name`, `bank_account`, `bank_password`) VALUES ('$suppliers_id','$bank_name', '$bank_p_name','$bank_account','$bank_password')";

		  $res = $db->query($sql);

		  if($res){

			show_message('设置成功', $_LANG['back_up_page'], './index.php?op=set&act=bank_config', 'info');

		  }

	}

}
else if($action =='add_ad')
{
	$smarty->assign('action','add_ad');
	$smarty->assign('suppliers_id',$suppliers_id);
	$smarty->assign('status','ad_insert');
	$smarty->display('supp_set.dwt');
}
else if($action  == 'ad_insert')
{
	$name = $_REQUEST['name'];
	$link         = $_REQUEST['link'];
	$sort_order    = $_REQUEST['sort_order'];
	$photo_file = $image->upload_image($_FILES['photo_file']);
	$sql = "INSERT INTO ". $hhs->table('supp_photo') . " (`name`,`link`, `sort_order`, `supp_id`,`photo_file`) VALUES ('$name','$link', '$sort_order', '$suppliers_id','$photo_file')";
	$res = $db->query($sql);
	if($res){
	  show_message('添加成功', $_LANG['back_up_page'], './index.php?op=set&act=ad', 'info');
	}else{
	  show_message('添加失败', $_LANG['back_up_page'], './index.php?op=set&act=ad', 'error');
	}
}
else if($action  == 'ad_update')
{
	$name = $_REQUEST['name'];
	$link     = $_REQUEST['link'];
	$sort_order   = $_REQUEST['sort_order'];
	$photo_file = $image->upload_image($_FILES['photo_file']);
	$photo_id = $_REQUEST['photo_id'];
	if($photo_file)
	{
		$old_photo_file = $db->getOne("select photo_file from ".$hhs->table('supp_photo')." where `photo_id`='" . $photo_id. "'");
		if($old_photo_file)
		{
			@unlink(ROOT_PATH . $old_photo_file);
		}
		$sql = 'UPDATE ' . $hhs->table('supp_photo') . " SET `name`='$name',`link`='$link' ,`photo_file`='$photo_file', `sort_order`='$sort_order'  WHERE `photo_id`='" . $photo_id. "'";
	}
	else
	{
		$sql = 'UPDATE ' . $hhs->table('supp_photo') . " SET `name`='$name',`link`='$link' , `sort_order`='$sort_order'  WHERE `photo_id`='" . $photo_id. "'";
	}
	$res = $db->query($sql);
	if($res){
	   show_message('编辑成功', $_LANG['back_up_page'], './index.php?op=set&act=ad', 'info');
	}else{

	   show_message('编辑失败', $_LANG['back_up_page'], './index.php?op=set&act=ad', 'error');
	}
}
else if($action  == 'ad_delete')
{
	$id = $_REQUEST['id'];
	$old_photo_file = $db->getOne("select photo_file from ".$hhs->table('supp_photo')." where photo_id='" . $photo_id. "'");
	if($old_photo_file)
	{
		@unlink(ROOT_PATH . $old_photo_file);
	}
    $sql = 'delete from ' . $hhs->table('supp_photo') . " WHERE `photo_id`='" . $id. "'";
	$res = $db->query($sql);
	if($res){
	  show_message('删除成功', $_LANG['back_up_page'], './index.php?op=set&act=ad', 'info');
	}else{
	  show_message('删除失败', $_LANG['back_up_page'], './index.php?op=set&act=ad', 'error');
	}
}

elseif($action=='supp_info')
{ 
    $supplier=$db->getRow("SELECT * FROM " .$hhs->table('suppliers')."where suppliers_id='$suppliers_id'");
	$regions=$db->getAll("SELECT * FROM " .$hhs->table('region'));
	
	
	$smarty->assign('cities',    get_sitelists());
    $smarty->assign('district_list',    get_regions(3,$supplier['city_id']));  

     // $smarty->assign('cities',    get_sitelists());
	 $smarty->assign('district_list',    get_regions(3,$supplier['city_id']));
    $supplier["term_validity"]=date("Y-m-d",strtotime($supplier["term_validity"]));

    $smarty->assign("regions_list",$regions);
	
	create_html_editor_xaphp('suppliers_desc',$supplier['suppliers_desc'],$suppliers_id);

	$smarty->assign("supp_list",$supplier);
	$photo_list = $regions=$db->getAll("SELECT * FROM " .$hhs->table('supp_photo')."where supp_id = ".$suppliers_id);
	$smarty->assign("photo_list",$photo_list);

	$smarty->display("supp_set.dwt");
}
elseif($action=='supp_update')
{    
	   $show_photo = $_FILES['show_photo'];
       $business_scope = trim($_POST['business_scope']);
		$province_id = trim($_POST['province_id']);
		$city_id = trim($_POST['city_id']);
		$district_id = trim($_POST['district_id']);
		$address = trim($_POST['address']);
		$email = trim($_POST['email']);				$shopowner_phone = trim($_POST['shopowner_phone']);		
		$phone = trim($_POST['phone']);
		$qq = trim($_POST['qq']);
		$suppliers_desc = trim($_POST['suppliers_desc']);
		$url_name = trim($_POST['url_name']);
		$suppliers_id = trim($_POST['suppliers_id']);
		$map_info = $_POST['map_info'];
		$supp_type = trim($_POST['supp_type']);
		//商品LOGO
		$supp_logo   = $_FILES['supp_logo'];
		$supp_banner = $_FILES['supp_banner'];
		//检测为个人入住还是商户入驻
		$supp_type      = trim($_REQUEST['supp_type']);
		//商品LOGO
		$supp_logo        = $image->upload_image($supp_logo);
		$supp_banner     = $image->upload_image($_FILES['supp_banner']);
		//身份证照片（双面）
		$shenfen_phone    = $image->upload_image($shenfen_phone);
		//营业执照照片（双面）
		$business_license = $image->upload_image($_FILES['business_license'],'business_file');
		$business_scope = $image->upload_image($_FILES['business_scope'],'business_file');
		$cards = $image->upload_image($_FILES['cards'],'business_file');
		$certificate = $image->upload_image($_FILES['certificate'],'business_file');
		$real_name =$_POST['real_name'];
		$email1 = $_POST['email1'];
		$phone1 = $_POST['phone1'];
		
		$longitude = $_POST['longitude'];
		$latitude = $_POST['latitude'];
		
		
				if(!empty($url_name)){
    		$count = $db->getOne("select count(*) from ".$hhs->table('suppliers')." where url_name='$url_name' and suppliers_id<>'$suppliers_id'");    
    		if($count)     
    		{    
    			show_message('输入的二级域名系统中已存在，请重新输入');    
    		}		}
		
	   $where="";
	   if(!empty($supp_logo)){
		$where.=" ,supp_logo='".$supp_logo."'";
	   }
	   if(!empty($show_photo))
	   {
			$where.=" ,show_photo='".$show_photo."'";
	   }
	   if(!empty($business_scope)){
		$where.=" ,business_scope='".$business_scope."'";
	   }
	   if(!empty($business_license)){
		$where.=" ,business_license='".$business_license."'";
	   }
	   if(!empty($cards)){
		$where.=" ,cards='".$cards."'";
	   }
	   if(!empty($certificate)){
		$where.=" ,certificate='".$certificate."'";
	   }
	   if(!empty($supp_banner)){
		$where.=" ,supp_banner='".$supp_banner."'";
	   }
	   
	   

	$sql = "UPDATE " .$hhs->table('suppliers'). " SET longitude='".$longitude."',latitude='".$latitude."',business_scope = '".$business_scope."',email1='".$email1."', phone1='".$phone1."', url_name='".$url_name."',province_id = '".$province_id."', city_id = '".$city_id."',district_id = '".$district_id."',address = '".$address."',email = '".$email."',phone = '".$phone."',  shopowner_phone = '".$shopowner_phone."' ,map_info='$map_info',suppliers_desc = '".$suppliers_desc."',qq = '".$qq."',real_name = '".$real_name."',identification_card = '".$identification_card."',company_name = '".$company_name."',business_license_number = '".$business_license_number."'".$where." WHERE suppliers_id = '".$suppliers_id."'";
	$res = $db->query($sql);
	if($res){
		show_message('编辑成功', $_LANG['back_up_page'], 'index.php?op=set&act=supp_info', 'info');
	}else{
		show_message('编辑失败', $_LANG['back_up_page'], 'index.php?op=set&act=supp_info', 'error');
	}
	/*

		$data["term_validity"]=strtotime($date["term_validity"]);
	move_uploaded_file($_FILES["business_license"]["tmp_name"],"images/".$_FILES["business_license"]["name"]);

		move_uploaded_file($_FILES["enterprise_license"]["tmp_name"],"images/".$_FILES["enterprise_license"]["name"]);

		$data["business_license"]="images/".$_FILES["business_license"]["name"];

		$data["enterprise_license"]="images/".$_FILES["enterprise_license"]["name"];

		

		$db->autoExecute($hhs->table('suppliers'), $data, 'UPDATE', "suppliers_id = '$data[suppliers_id]'");

	    show_message('更新成功','企业资料', 'suppliers.php?act=supp_info', 'info');	

		*/

}
elseif($action =='ad')
{
	$sql = "select * from ".$hhs->table('supp_photo')."where supp_id = ".$suppliers_id." order by photo_id asc";

	$ad_list = $db->getAll($sql);
	$smarty->assign('ad_list',$ad_list);
	$smarty->assign('action','ad');
	$smarty->display('supp_set.dwt');
}
else if($action =='edit_ad')
{
	$id = $_REQUEST['id'];
	$sql = "select * from ".$hhs->table('supp_photo')."where photo_id = ".$id;
	$ad_info = $db->getRow($sql);
	$smarty->assign('ad_info',$ad_info);
	$smarty->assign('action','edit_ad');
	$smarty->assign('status','ad_update');
	$smarty->display('supp_set.dwt');

}
/* 修改会员密码 */

elseif ($action == 'act_edit_password')
{

    include_once(ROOT_PATH . 'includes/lib_passport.php');

    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : null;

    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
	
	$comfirm_password = isset($_POST['comfirm_password']) ? trim($_POST['comfirm_password']) : '';
	
	
	$agent = $db->getRow("select suppliers_id,account_id,account_type,account_name,account_password from ".$hhs->table('supp_account')." where account_id='".$_SESSION['role_id']."' and account_password ='".md5($old_password)."' and is_check=1");
	
	$supp_info = $db->getRow("select * from ".$hhs->table('suppliers')." where suppliers_id ='$_SESSION[suppliers_id]'");
	
	if($agent)
	{
		if (($agent['account_password'] != md5($old_password)))
		{
			show_message('旧密码输入不正确');
		}
		else if($new_password != $comfirm_password)
		{
			show_message('新密码和确认新密码不一致');
		}
		else if($old_password == $new_password)
		{
			show_message('原始密码和新的修改密码不能一样');
		}
	
		$p =  md5($new_password);
	
		$sql="UPDATE ".$hhs->table('supp_account'). "SET account_password='".$p."'  WHERE account_id= '".$_SESSION['role_id']."'";
		
	}
	else if($supp_info)
	{
		
		if (($supp_info && ($supp_info['password'] != md5($old_password))))
		{
			show_message('旧密码输入不正确');
		}
		else if($new_password != $comfirm_password)
		{
			show_message('新密码和确认新密码不一致');
		}
		else if($old_password == $new_password)
		{
			show_message('原始密码和新的修改密码不能一样');
		}
		else
		{
			$p =  md5($new_password);
			$sql="UPDATE ".$hhs->table('suppliers'). "SET password='".$p."'  WHERE suppliers_id= '".$_SESSION['suppliers_id']."'";
		}
	
		
	}
	
	$res = $db->query($sql);
	
	if($res)
	{
		unset($_SESSION['suppliers_id']);
		show_message('密码修改成功','返回登录', 'index.php?op=login&act=login', 'info');
	}
	else
	{
		show_message('密码修改失败','返回', 'index.php', 'info');
	}

     

}
elseif($action =='edit_password')
{

	$smarty->display("supp_set.dwt");
}
elseif($action =='user_message')
{

	$list = get_comment_list($suppliers_id);

	#print_r($list);

	$smarty->assign('comment_list',$list);

	$smarty->assign('action','user_message');

	$smarty->display("supp_set.dwt");

}









//回复用户评论(同时查看评论详情)

if ($action =='reply')

{

	

    $comment_info = array();

    $reply_info   = array();

    $id_value     = array();



    /* 获取评论详细信息并进行字符处理 */

    $sql = "SELECT * FROM " .$hhs->table('comment'). " WHERE comment_id = '$_REQUEST[id]'";

    $comment_info = $db->getRow($sql);

    $comment_info['content']  = str_replace('\r\n', '<br />', htmlspecialchars($comment_info['content']));

    $comment_info['content']  = nl2br(str_replace('\n', '<br />', $comment_info['content']));

    $comment_info['add_time'] = local_date($_CFG['time_format'], $comment_info['add_time']);



    /* 获得评论回复内容 */

    $sql = "SELECT * FROM ".$hhs->table('comment'). " WHERE parent_id = '$_REQUEST[id]'";

    $reply_info = $db->getRow($sql);



    if (empty($reply_info))

    {

        $reply_info['content']  = '';

        $reply_info['add_time'] = '';

    }

    else

    {

        $reply_info['content']  = nl2br(htmlspecialchars($reply_info['content']));

        $reply_info['add_time'] = local_date($_CFG['time_format'], $reply_info['add_time']);

    }

    /* 获取管理员的用户名和Email地址 */

    $sql = "SELECT user_name, email FROM ". $hhs->table('admin_user').

           " WHERE user_id = '$_SESSION[admin_id]'";

    $admin_info = $db->getRow($sql);



    /* 取得评论的对象(文章或者商品) */

    if ($comment_info['comment_type'] == 0)

    {

        $sql = "SELECT goods_name FROM ".$hhs->table('goods').

               " WHERE goods_id = '$comment_info[id_value]'";

        $id_value = $db->getOne($sql);

    }

    else

    {

        $sql = "SELECT title FROM ".$hhs->table('article').

               " WHERE article_id='$comment_info[id_value]'";

        $id_value = $db->getOne($sql);

    }



    /* 模板赋值 */

    $smarty->assign('msg',          $comment_info); //评论信息

    $smarty->assign('admin_info',   $admin_info);   //管理员信息

    $smarty->assign('reply_info',   $reply_info);   //回复的内容

    $smarty->assign('id_value',     $id_value);  //评论的对象

    $smarty->assign('send_fail',   !empty($_REQUEST['send_ok']));



    $smarty->assign('ur_here',      $_LANG['comment_info']);

    $smarty->assign('action_link',  array('text' => $_LANG['05_comment_manage'],

    'href' => 'comment_manage.php?act=list'));



    /* 页面显示 */

    $smarty->assign('action','reply');

	$smarty->display("supp_set.dwt");

}





if ($action == 'update_comment_status')

{

    if ($_REQUEST['check'] == 'allow')

    {

        /* 允许评论显示 */

        $sql = "UPDATE " .$hhs->table('comment'). " SET status = 1 WHERE comment_id = '$_REQUEST[id]'";

        $db->query($sql);



        //add_feed($_REQUEST['id'], COMMENT_GOODS);



        /* 清除缓存 */

        clear_cache_files();

        hhs_header("Location:?op=set&act=reply&id=$_REQUEST[id]\n");

        exit;

    }

    else

    {

        /* 禁止评论显示 */

        $sql = "UPDATE " .$hhs->table('comment'). " SET status = 0 WHERE comment_id = '$_REQUEST[id]'";

        $db->query($sql);



        /* 清除缓存 */

        clear_cache_files();



        hhs_header("Location:?op=set&act=reply&id=$_REQUEST[id]\n");

        exit;

    }

}

/*------------------------------------------------------ */

//-- 处理 回复用户评论

/*------------------------------------------------------ */

if ($action=='action')
{
    /* 获取IP地址 */

    $ip     = real_ip();



    /* 获得评论是否有回复 */

    $sql = "SELECT comment_id, content, parent_id FROM ".$hhs->table('comment').

           " WHERE parent_id = '$_REQUEST[comment_id]'";

    $reply_info = $db->getRow($sql);
    if (!empty($reply_info['content']))
    {
        /* 更新回复的内容 */
        $sql = "UPDATE ".$hhs->table('comment')." SET ".
               "email     = '$_POST[email]', ".
               "user_name = '$_POST[user_name]', ".

               "content   = '$_POST[content]', ".

               "add_time  =  '" . gmtime() . "', ".

               "ip_address= '$ip', ".

               "status    = 0".

               " WHERE comment_id = '".$reply_info['comment_id']."'";

    }
    else
    {

        /* 插入回复的评论内容 */
        $sql = "INSERT INTO ".$hhs->table('comment')." (comment_type, id_value, email, user_name , ".

                    "content, add_time, ip_address, status, parent_id) ".

               "VALUES('$_POST[comment_type]', '$_POST[id_value]','$_POST[email]', " .

                    "'$_SESSION[admin_name]','$_POST[content]','" . gmtime() . "', '$ip', '0', '$_POST[comment_id]')";

    }
    $db->query($sql);
    /* 更新当前的评论状态为已回复并且可以显示此条评论 */
    $sql = "UPDATE " .$hhs->table('comment'). " SET status = 1 WHERE comment_id = '$_POST[comment_id]'";

    $db->query($sql);
    /* 清除缓存 */

    clear_cache_files();
    hhs_header("Location:?op=set&act=reply&id=$_REQUEST[comment_id]\n");

    exit;

}
/*------------------------------------------------------ */

//-- 删除某一条评论

/*------------------------------------------------------ */

elseif ($action == 'delete_comment')
{
    $id = intval($_GET['id']);
    $sql = "DELETE FROM " .$hhs->table('comment'). " WHERE comment_id = '$id'";
    $res = $db->query($sql);
    if ($res)
    {
        $db->query("DELETE FROM " .$hhs->table('comment'). " WHERE parent_id = '$id'");
    }
    show_message('删除成功', $_LANG['back_up_page'], './index.php?op=set&act=user_message', 'info');
    exit;
}

?>