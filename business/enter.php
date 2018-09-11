<?php

/**
 * 小舍电商 首页文件
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: index.php 17217 2014-05-12 06:29:08Z pangbin $
*/
define('IN_HHS', true);
require(dirname(__FILE__) . '/../includes/init2.php');
$smarty->template_dir  = ROOT_PATH  . '/business/templates';
$smarty->compile_dir   = ROOT_PATH . 'temp/compiled/business';

include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);

/*------------------------------------------------------ */
//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
/*------------------------------------------------------ */
/* 缓存编号 */
    assign_template();
    $position = assign_ur_here('','商家入驻');
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置
    $smarty->assign('categories',      get_categories_tree()); // 分类树
    $smarty->assign('helps',           get_shop_help());       // 网店帮助
	$smarty->assign('about',            get_shop_about());
	
	$smarty->assign('provinces',    get_regions(1, '1'));
    $smarty->assign('cities',    get_regions(2,$supplier['province_id']));
	$smarty->assign('district_list',    get_regions(3,$supplier['city_id']));

 	if($_REQUEST['act'] =='enter_act')
	{
		$data =$_REQUEST;
		$data['add_time'] = gmtime();
		$c = $db->getOne("select count(*) from ".$hhs->table('suppliers')." where suppliers_name='$data[suppliers_name]'");
		if($c)
		{
		    show_message('该商家名称已被占用','返回重新添加', 'enter.php', 'info');
		}
		
		$is_reg = $db->getOne("select count(*) from ".$hhs->table('suppliers')." where user_name='$data[user_name]'");
		if($is_reg)
		{
			show_message('该登录用户名已被占用','返回重新添加', 'enter.php', 'info');
		}
		/*
	   if ($_FILES['business_license']['tmp_name'] != '')
	   {
		  if (!$image->check_img_type($_FILES['business_license']['type']))
			 {
				   show_message('企业营业执照格式不正确');
			  }
		}
		$data['business_license'] = upload_article_file($_FILES['business_license']);
		//echo $data['business_license'];exit;
	   if($_FILES['business_scope']['tmp_name'] != '')
	   {
		  if (!$image->check_img_type($_FILES['business_scope']['type']))
			 {
				   show_message('企业组织机构代码证格式不正确');
			  }
		}
		$data['business_scope'] = upload_article_file($_FILES['business_scope']);
		
		
		
	   if($_FILES['cards']['tmp_name'] != '')
	   {
		  if (!$image->check_img_type($_FILES['cards']['type']))
			 {
				   show_message('企业法人身份证格式不正确');
			  }
		}
		$data['cards'] = upload_article_file($_FILES['cards']);
		
		
	   if($_FILES['certificate']['tmp_name'] != '')
	   {
		  if (!$image->check_img_type($_FILES['certificate']['type']))
			 {
				   show_message('税务登记证格式不正确');
			  }
		}
		$data['certificate'] = upload_article_file($_FILES['certificate']);
		//$data['business_license'] = $image->upload_image($_FILES['business_license'],'business_file');
		//$data['business_scope'] = $image->upload_image($_FILES['business_scope'],'business_file');
		//$data['cards'] = $image->upload_image($_FILES['cards'],'business_file');
		//$data['certificate'] = $image->upload_image($_FILES['certificate'],'business_file');
		*/
		$data['is_check'] =0;
		$data['password'] =md5($data['password']);
		$db->autoExecute($hhs->table('suppliers'), $data, 'INSERT');
		$suppliers_id = $db->insert_id();
	    $dir = 'business/uploads/'.$suppliers_id;
	    is_dir($dir) or mkdir($dir, 0777);
		chmod($dir,0777);
		show_message('入驻成功，请等待工作人员审核!','返回主站', 'suppliers.php', 'info');
	}elseif($_REQUEST['act'] =='is_suppliers_name'){
	    $suppliers_name=$_REQUEST['suppliers_name'];
	    $c = $db->getOne("select count(*) from ".$hhs->table('suppliers')." where suppliers_name='$suppliers_name'");
	    if($c)
	    {
	       echo 1;
	    }else{
	    	echo 0;
	    }
	}elseif($_REQUEST['act'] =='is_user_name'){
	    $user_name=$_REQUEST['user_name'];
	    $c = $db->getOne("select count(*) from ".$hhs->table('suppliers')." where user_name='$user_name'");
	    if($c)
	    {
	       echo 1;
	    }else{
	    	echo 0;
	    }
	}
	else
	{
	    
		$smarty->display('enter.dwt');
	}
function upload_article_file($upload)
{
	
    $filename = cls_image::random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
    //$path     =  "/data/business_file/" . $filename;
	$path     = ROOT_PATH. DATA_DIR . "/business_file/" . $filename;

    if (move_upload_file($upload['tmp_name'], $path))
    {
		return DATA_DIR . "/business_file/" . $filename;
    }
    else
    {
        return false;
    }
}
?>
