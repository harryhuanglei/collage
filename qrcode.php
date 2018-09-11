<?php
header("Content-Type: text/html;charset=utf-8");//输出不乱码，你懂的
define('IN_HHS', true);

include 'includes/init2.php';
include 'phpqrcode/phpqrcode.php';

if (empty($_SESSION['user_id'])) {
	exit();
}

$act  = isset($_GET['act'])? 0 : 1;

$id   = isset($_GET['id'])? intval($_GET['id']) : 0;
$info = $act? getShareUserInfo($_SESSION['user_id']) : getShareStoreInfo($id,$_SESSION['user_id']);

$url    = $info['url']; //二维码内容  
$qrcode = 'phpqrcode/temp/test.png'; 
$string = $info['text'];
$avatar = $info['avatar'];
$desc   = '邀请您来购物，还能获得佣金哦～';
$meta   = '长按图片识别二维码。该二维码长期有效。';
// echo $avatar;
// print_r($info);
// die();
$errorCorrectionLevel = 'L';//容错级别   
$matrixPointSize = 6;//生成图片大小   
//生成二维码图片  
ob_end_clean();
header("Content-type: image/png");
QRcode::png($url, $qrcode, $errorCorrectionLevel, $matrixPointSize, 2);   
$font_file = 'font/simhei.ttf';
$im        = imagecreate(300,350);
$bg        = imagecolorallocate($im,255,255,255);
$color     = imagecolorallocate($im, 0, 0, 0);
if($act){
	$avatar    = get_avatar($avatar);
	$avatar    = imagecreatefromstring($avatar);	
}
else{
	switch ($info['type']) {
		case 'png':
			$avatar    = imagecreatefrompng($avatar);
			break;
		case 'jpg':
		case 'jpeg':
			$avatar    = imagecreatefromjpeg($avatar);
			break;
		case 'bmp':
			$avatar    = imagecreatefromwbmp($avatar);
			break;
		case 'gif':
			$avatar    = imagecreatefromgif($avatar);
			break;
		default:
			# code...
			break;
	}
}
// $avatar    = imagecreatefromwebp($avatar);
$qr        = imagecreatefrompng($qrcode);
$qr_width  = imagesx($qr);//二维码图片宽度   
$qr_height = imagesy($qr);//二维码图片高度  
$av_width  = imagesx($avatar);//二维码图片宽度   
$av_height = imagesy($avatar);//二维码图片高度  

imagesavealpha($im,true);
imagefttext($im, 13, 0, 60, 100, $color, $font_file,mb_convert_encoding($string,'html-entities','UTF-8'));
imagecopyresampled($im, $avatar, 118, 10, 0, 0, 64, 64, $av_width, $av_height); 
imagesavealpha($im,true);
imagecopyresampled($im, $rd, 118, 10, 0, 0, 64, 64, $av_width, $av_height); 
imagecopyresampled(
	$im, 
	$qr, 
	(300-$qr_width)/2, 
	(350-$qr_height-20), 
	0, 
	0, 
	$qr_width, 
	$qr_height, 
	$qr_width, 
	$qr_height
); 
imagefttext($im, 8, 0, 60, 120, $color, $font_file,mb_convert_encoding($desc,'html-entities','UTF-8'));
imagefttext($im, 8, 0, 50, 340, $color, $font_file,mb_convert_encoding($meta,'html-entities','UTF-8'));

imagepng($im);

imagedestroy($im);
function get_avatar($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$tmpInfo = curl_exec($ch);
	if(curl_errno($ch))
	{
		return curl_error($ch);
	}
	curl_close($ch);
	return $tmpInfo;
}


function getShareUserInfo($user_id){
	$sql           = "select `uname` as 'user_name',`headimgurl` as 'avatar' from ".$GLOBALS['hhs']->table('users')." where `user_id` = " . $user_id;
	$row           = $GLOBALS['db']->getRow($sql);
	$row['text']   = "我是："  . $row['user_name'];
	$row['url']    = $GLOBALS['hhs']->url() . 'myshop.php?uid=' . $user_id;
	return $row;
}

function getShareStoreInfo($id,$user_id = 0){
	$sql           = "select `suppliers_name` as 'user_name',`supp_logo` as 'avatar' from ".$GLOBALS['hhs']->table('suppliers')." where `suppliers_id` = " . $id;
	$row           = $GLOBALS['db']->getRow($sql);
	$row['text']   = "店铺："  . $row['user_name'];
	$row['avatar'] = ROOT_PATH . $row['avatar'];
	$row['type']   = strtolower(pathinfo($row['avatar'], PATHINFO_EXTENSION));
	$row['url']    = $GLOBALS['hhs']->url() . 'store.php?id='.$id.'&uid=' . $user_id;
	return $row;
}