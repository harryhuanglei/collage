<?php
header("content-type:text/html;charset=utf-8");
define('IN_HHS', true);
require(dirname(__FILE__) . '/../../includes/init2.php');
$key = $db->getOne('SELECT value FROM ' . $hhs->table('shop_config') . ' WHERE code = \'juhekey\'');

$getcom = trim($_GET["com"]);
$getNu = trim($_GET["nu"]);
$smarty->assign('expressid', $_GET["com"] );
$smarty->assign('expressno', $_GET["nu"] );

include_once("kuaidi_config.php");

if(isset($postcom)&&isset($getNu)){
    
    $url="http://v.juhe.cn/exp/index?key=".$key."&com=".$postcom."&no=".$getNu  ;
    $get_content=file_get_contents($url);

    //echo '<iframe src="'.$get_content.'" width="534" height="340" frameborder="no" border="0" marginwidth="0" marginheight="0" scrolling="no" allowtransparency="yes"><br/>' . $powered;
	$express=json_decode($get_content, true);
	krsort($express['result']['list']);
	//$express['result']['list']=$arr;
	$smarty->assign('express', $express );
	$result=$smarty->fetch('library/express.lbi');
	//$result=$smarty->assign('express',    $express);
	echo json_encode($result);
}else{
	echo '查询失败，请重试';
}
exit();
?>
