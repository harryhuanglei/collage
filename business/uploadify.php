<?php

/*

*

*油灯网www.yauld.cn 2013-05-01 九霄云仙

*

*/

$suppliers_id = $_POST['jsessionid']; 

// 定义目标文件夹，相对于根目录

$targetFolder = 'uploads/'.$suppliers_id;

define('ROOT_PATH', str_replace('business/uploadify.php', '', str_replace('\\', '/', __FILE__)));

$targetPath = rtrim(ROOT_PATH,'/') .'/business/'. $targetFolder;
//$_SERVER['DOCUMENT_ROOT']
//接收令牌信息，hash处理

$verifyToken = md5('unique_salt' . $_POST['timestamp']);

$img_w = empty($_POST['img_w']) ? 0 : intval($_POST['img_w']);

$img_h = empty($_POST['img_h']) ? 0 : intval($_POST['img_h']);

$close_img = empty($_POST['close_img']) ? 0 : intval($_POST['close_img']);

$img_id = isset($_POST['img_id']) ? trim($_POST['img_id']) : 'goods_img_url';

if (!empty($_FILES) && $_POST['token'] == $verifyToken) {//存在上传信息，且通过令牌校验

	//文件被上传后在服务端储存的临时文件名

	$tempFile = $_FILES['Filedata']['tmp_name'];
	if($img_w && $img_h && $close_img)
	{
	
		$goods_img_arr = getimagesize($tempFile);
			
			
		if(($goods_img_arr[0] != $img_w || $goods_img_arr[1] != $img_h) && $img_id == 'goods_img_url')
		{
			
			echo 1;
			
			exit;
				
		}
		
		if(($goods_img_arr[0] != $img_w || $goods_img_arr[1] != $img_h) && $img_id == 'little_img')
		{
			
			echo 2;
			
			exit;
				
		}
	
	}
	

	//根据客户端提交文件的原名称生成一个无重复的文件名

	$newName=getNewName($_FILES['Filedata']['name']);

	//定义目标文件完全路径

	$targetFile = $targetPath . '/' . $newName;

	//校验文件类型

	$verifyTypes = array('jpg','gif','png','flv'); //校验类型

	$fileTypes = getExtName($_FILES['Filedata']['name']);// 文件扩展名

		

	if (in_array($fileTypes,$verifyTypes)) {//校验通过

		move_uploaded_file($tempFile,$targetFile);

		//输出的字符串由表单页面onUploadSuccess方法的data参数接收，这里输出上传后的文件路径

		echo $targetFolder.'/'.$newName;

	} else {

		//输出的字符串由表单页面onUploadError方法的data参数接收

		echo '非法文件类型';

	}

}



//生成一个无重复的文件名

function getNewName($filename){

	//年月日时分秒格式的字符串

	$timeNow = date('YmdHis',time());

	//生成一个8位小写字母的随机字符串

	$randKey = '';

	for ($a = 0; $a < 8; $a++) {

		$randKey .= chr(mt_rand(97, 122));

	}

	//取得原文件的扩展名

	$extName = ".".getExtName($filename);

	//组成新文件名

	$newName=$timeNow.$randKey.$extName;

	return $newName;

}



//取得文件扩展名

function getExtName($filename){

	//取得文件关联数组信息

	$fileParts = pathinfo($filename);

	//文件扩展名转换为小写，返回

	return strtolower($fileParts['extension']);

}


?>