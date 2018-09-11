<?php
define('IN_HHS', true);

if($action =='get_pic')

{

	$cat_list =get_pic_cat_list($suppliers_id); 

	$smarty->assign('cat_list',$cat_list);

	

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$where = " where id>0";

	if($_REQUEST['cat_id'])

	{

		$where .=" and cat_id='$_REQUEST[cat_id]'";	

	}

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('supp_pic_list'). " $where and suppliers_id = '$suppliers_id'");

	$pager  = get_pager('index.php', array('act' => $action,'op'=>'pic'), $record_count, $page);

	$pic_list = get_pic_list($suppliers_id, $pager['size'], $pager['start']);

	$smarty->assign('pager',  $pager);

    $smarty->assign('pic_list', $pic_list);

	$smarty->assign('timestamp',time());

	$smarty->assign('img_id', $_REQUEST['img_id']);
	
	$unique_salt =  md5('unique_salt'.time());

	$smarty->assign('unique_salt',$unique_salt);

	$smarty->display('suppliers_get_pic.dwt');	

	

}

elseif($action =='get_photo')

{   

	$cat_list =get_pic_cat_list($suppliers_id); 

	$smarty->assign('cat_list',$cat_list);

	

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$where = " where id>0";

	if($_REQUEST['cat_id'])

	{

		$where .=" and cat_id='$_REQUEST[cat_id]'";	

	}

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('supp_pic_list'). " $where and suppliers_id = '$suppliers_id'");

	$pager  = get_pager('index.php', array('act' => $action,'op'=>'pic'), $record_count, $page);

	$pic_list = get_pic_list($suppliers_id, $pager['size'], $pager['start']);

	$smarty->assign('pager',  $pager);

    $smarty->assign('pic_list', $pic_list);

	$smarty->assign('timestamp',time());

	$unique_salt =  md5('unique_salt'.time());

	$smarty->assign('unique_salt',$unique_salt);

	$smarty->display('suppliers_get_photo.dwt');	

	

}





elseif($action =='pic_add')

{

	$cat_list = $db->getAll("select * from ".$hhs->table('supp_pic_category')." where suppliers_id='$suppliers_id'");

	$smarty->assign('cat_list',$cat_list);

	$smarty->assign('form_act','pic_insert');

	$smarty->assign('timestamp',time());

	$unique_salt =  md5('unique_salt'.time());

	$smarty->assign('unique_salt',$unique_salt);



	$smarty->display('supp_pic.dwt');

}

elseif($action =='pic_insert')
{
	$pic = $_POST['pics'];
	$cat_id = $_POST['cat_id'];
	if(empty($pic))
	{
		show_message('请先上传图片');
	}
	foreach($pic as $id=>$value)
	{
		$pic_name = $_POST['pic_name'][$id];
		$sql = $db->query("insert into ".$hhs->table('supp_pic_list')." (pic,cat_id,pic_name,suppliers_id) values ('$value','$cat_id','$pic_name','$suppliers_id')");
	}
	show_message('添加成功','返回列表','index.php?op=pic&act=pic_list');
}

elseif($action =='pic_category_insert')
{
	$cat_name = $_POST['cat_name'];
	$count = $db->getOne("select count(*) from ".$hhs->table('supp_pic_category')." where cat_name='$cat_name' and suppliers_id='$suppliers_id'");
	if($count)
	{
		show_message('该分类名称已存在');
	}

	$sql = $db->query("insert into ".$hhs->table('supp_pic_category')." (cat_name,suppliers_id) values ('$cat_name',$suppliers_id)");
	show_message('添加成功','返回列表','index.php?op=pic&act=pic_category','info');
}
elseif($action =='pic_category_delete')
{
	$id = $_GET['id'];
	$count = $db->getOne("select count(*) from ".$hhs->table('supp_pic_list')." where cat_id='$id' and suppliers_id='$suppliers_id'");
	if($count)
	{
		show_message('该分类下有图片，请先清空该分类下图片');
	}
	else
	{
		
		$db->query("delete from ".$hhs->table('supp_pic_category')." where id='$id'");	
		show_message('删除成功');
	}
}
elseif($action =='pic_category_edit')
{
	$id = $_REQUEST['id'];
	$rows = $db->getRow("select * from ".$hhs->table('supp_pic_category')." where id='$id'");
	$smarty->assign('rows',$rows);
	$smarty->assign('form_act','pic_category_update');
	$smarty->display('supp_pic.dwt');
}
elseif($action =='pic_category_update')
{
	$id = $_REQUEST['id'];
	$cat_name = $_REQUEST['cat_name'];
	$sql = $db->query("update ".$hhs->table('supp_pic_category')." set cat_name='$cat_name' where id='$id'");
	show_message('修改成功','返回列表','index.php?op=pic&act=pic_category','info');
}
elseif($action =='pic_category_add')
{
	$smarty->assign('form_act','pic_category_insert');
	$smarty->display('supp_pic.dwt');	
}
elseif($action =='pic_category')
{
	$list = $db->getAll("select * from ".$hhs->table('supp_pic_category')." where suppliers_id='$suppliers_id'");
	$smarty->assign('list',$list);
	$smarty->display('supp_pic.dwt');
}
elseif($action=='delete_pic')
{
	include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
	$res    = array('err_msg' => '', 'result' => '', 'qty' => 1);
	$pic = $_REQUEST['pic'];
	@unlink(ROOT_PATH.$pic);
	$res['id'] =$_REQUEST['id'];
	die($json->encode($res));
	//echo "[".$temp."]";
    exit;
}
//上传提货单
elseif($action =='update_delivery_pic')
{
	include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
	$res    = array('err_msg' => '', 'result' => '', 'qty' => 1);
	$delivery_id = $_REQUEST['delivery_id'];
	$delivery_pic = $_REQUEST['delivery_pic'];
	$sql = $db->query("update ".$hhs->table('delivery_order')." set delivery_pic='$delivery_pic' where delivery_id='$delivery_id'");
	$res['err_msg']=0;
	die($json->encode($res));
	//echo "[".$temp."]";
    exit;
}


elseif($action =='edit_pic')
{
	$id = $_REQUEST['id'];

	$rows = $db->getRow("select * from ".$hhs->table('supp_pic_list')." where id='$id'");

	$smarty->assign('rows',$rows);

	$smarty->assign('form_act','edit_pic_update');

	$cat_list = $db->getAll("select * from ".$hhs->table('supp_pic_category')." where suppliers_id='$suppliers_id'");

	$smarty->assign('cat_list',$cat_list);

	$smarty->display('supp_pic.dwt');
}
elseif($action =='edit_pic_update')
{
	$data = $_POST;
	$pic = $image->upload_image($_FILES['pic'],'uploads');
	if($pic)
	{
		$data['pic'] = $pic;	
	}
	$db->autoExecute($hhs->table('supp_pic_list'), $data, 'UPDATE', "id = '$data[id]'");
	show_message('修改成功');
	getUrl('index.php?op=pic&?act=pic_list');
}
elseif($action =='drop_delete_pic')
{
	include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;

	$res    = array('err_msg' => '', 'result' => '', 'qty' => 1);

	$id = $_REQUEST['id'];

	$img = $db->getOne("select pic from ".$hhs->table('supp_pic_list')." where id='$id'");

	@unlink(ROOT_PATH.$img);

	$db->query("delete from ".$hhs->table('supp_pic_list')." where id='$id'");

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$where = " where id>0";

	if($_REQUEST['cat_id'])
	{
		$where .=" and cat_id='$_REQUEST[cat_id]'";	
	}

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('supp_pic_list'). " $where and suppliers_id = '$suppliers_id'");

	$pager  = get_pager('index.php', array('act' =>'pic_list','op'=>'pic'), $record_count, $page);

	$pic_list = get_pic_list($suppliers_id, $pager['size'], $pager['start']);



    $smarty->assign('pic_list', $pic_list);

	$res['pic_list'] =$smarty->fetch('library/pic_list.lbi');
	
	
	$pager  = get_pager('index.php', array('act' => 'pic_list','op'=>'pic'), $record_count, $page);
	$smarty->assign('pager',  $pager);
	$res['pages'] =$smarty->fetch('library/pages.lbi');

	die($json->encode($res));

	//echo "[".$temp."]";

    exit;
}
elseif($action =='pic_list')
{
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$where = " where id>0";
	$cat_id = intval($_REQUEST['cat_id']);

	if($_REQUEST['cat_id'])

	{
		$smarty->assign('cat_id',$_REQUEST['cat_id']);
		$where .=" and cat_id='$_REQUEST[cat_id]'";	
	}
	if($_REQUEST['keywords'])
	{
		$where .=" and pic_name like '%$_REQUEST[keywords]%'";	
	}
	$smarty->assign('cat_id',$_REQUEST['cat_id']);
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$hhs->table('supp_pic_list'). " $where and suppliers_id = '$suppliers_id'");
	$pager  = get_pager('index.php', array('act' => $action,'op'=>'pic','cat_id'=>$cat_id,'keywords'=>$_REQUEST['keywords']), $record_count, $page);
	$pic_list = get_pic_list($suppliers_id, 14, $pager['start']);
	$smarty->assign('pager',  $pager);
    $smarty->assign('pic_list', $pic_list);
	$cat_list = $db->getAll("select * from ".$hhs->table('supp_pic_category')." where suppliers_id='$suppliers_id'");
	$smarty->assign('cat_list',$cat_list);
    $smarty->display('supp_pic.dwt');
}
	?>
