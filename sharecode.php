<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');
$act = isset($_GET['act']) ? trim($_GET['act']) : 'default';
$user_id = $_SESSION['user_id'];
if ($act == 'show') {
	include_once('includes/cls_json.php');
	$json  = new JSON;

	if (empty($user_id)) {
		$result = array('error' => 1, 'message' => '请先登录', 'content' => '');
		ob_end_clean();
		die($json->encode($result));
	}
	$file_name = 'data/share/u_'.$user_id.'.jpg';
	$force = intval($_POST['force']);
	if ($force == 0 && is_file(ROOT_PATH . $file_name) && (filemtime(ROOT_PATH . $file_name)+30*86400)>time()) {
		$result = array('error' => 0, 'message' => '', 'content' => $file_name);
		ob_end_clean();
		die($json->encode($result));
	}
	//时效二维码
	$qrcode = $weixin->getWxCode($user_id);
	if (! $qrcode) {
		$result = array('error' => 1, 'message' => '系统出错了，请稍后再试', 'content' => '');
		ob_end_clean();
		die($json->encode($result));
	}
	$avatar = getUserAvatar($user_id);
	$text   = !empty($_POST['text']) ? trim($_POST['text']) : '大王叫我来巡山';
	$uname  = $db->getOne('select uname from '.$hhs->table('users').' where user_id = ' . $user_id);

	getFinal($user_id,$uname,$text,$avatar,$qrcode);

	$result = array('error' => 0, 'message' => '', 'content' => $file_name);
	ob_end_clean();
	die($json->encode($result));
}
else
{
	$file_name = 'data/share/u_'.$user_id.'.jpg';
	$exist = 0;
	if (is_file(ROOT_PATH . $file_name) && (filemtime(ROOT_PATH . $file_name)+30*86400)>time()) {
		$exist = 1;
	}	
	$smarty->assign('exist', $exist);
	$smarty->assign('file_name', $file_name);

	$smarty->display('sharecode.dwt');
}

function getUserAvatar($user_id)
{
	global $db,$hhs,$weixin;
	$avatar = $db->getOne('select headimgurl from '.$hhs->table('users').' where user_id = ' . $user_id);
	$img    = $weixin->httpGet($avatar);
	$path   = ROOT_PATH . 'data/avatar/u_'.$user_id.'.jpg';
	file_put_contents($path,$img);
	return $path;
}
function getFinal($user_id,$uname,$text, $avatar,$file_name)
{
	global $_CFG;
	$font_file = ROOT_PATH . 'font/simhei.ttf';//字体
	$fx_img    = ROOT_PATH . str_replace('../', '', $GLOBALS['_CFG']['share_bg']);//背景图

	 //背景图
	 $is_very = file_get_contents($fx_img);
	 if(strlen($is_very) < 1)
	 {
		return false;	 
	 }
	 $QR = imagecreatefromstring($is_very); 
	
	 //二维码
	 $wx_code = imagecreatefromstring(file_get_contents($file_name));
	 //分销说明(字体)
	 
	 $pic_valid_time  = '本二维码有效期至'.local_date("Y-m-d H:i",local_strtotime("+1 month")) ;
	 
	 //字体颜色
	 $color = imagecolorallocate($QR, 51, 51, 51);
	 $white = imagecolorallocate($QR, 255, 255, 255);
	 //微信头像
	 $wx_logo = imagecreatefromstring(file_get_contents($avatar)); 
	
	 $QR_width    = imagesx($QR);//背景图宽度 
	 $QR_height   = imagesy($QR);//背景图高度 
	 
	 $wx_code_width  = imagesx($wx_code);//二维码图片宽度 
	 $wx_code_height = imagesy($wx_code);//二维码图片高度 
	 
	 $wx_logo_width  = imagesx($wx_logo);
	 $wx_logo_height = imagesy($wx_logo);
	
	 //载入头像 
	 imagecopyresampled($QR, $wx_logo, 214, 52, 0, 0, 86, 86, $wx_logo_width, $wx_logo_height);
	 //载入二维码
	 imagecopyresampled($QR, $wx_code, $_CFG['x_pos'], $_CFG['y_pos'], 0, 0, 180, 180, $wx_code_width, $wx_code_height);
	 //载入字体
	 imagefttext($QR , 18, 0, 267, 183, $color, $font_file, mb_convert_encoding($uname, 'html-entities', 'UTF-8'));
	 imagefttext($QR , 18, 0, 210, 220, $white, $font_file, mb_convert_encoding($text, 'html-entities', 'UTF-8'));
	 //载入到期时间字体
	 imagefttext($QR , 9, 0, ($_CFG['x_pos']+10), ($_CFG['y_pos']+200), $color, $font_file, mb_convert_encoding($pic_valid_time, 'html-entities', 'UTF-8'));

	//输出图片 
	//header("Content-type: image/jpeg");
    //imagejpeg($QR);
	//每次发送指令每次重新生成二维码
	imagejpeg($QR,$file_name);
	//销毁资源 
	imagedestroy($QR);	
}