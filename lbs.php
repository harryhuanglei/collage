<?php
/**
 * 小舍电商 微信坐标
 * ============================================================================
 * * 版权所有 2012-2014 无锡三舍文化传媒有限公司，并保留所有权利。
 * 网站地址: http://www.baidu.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: pangbin $
 * $Id: goods.php 17217 2014-05-12 06:29:08Z pangbin $
*/

define('IN_HHS', true);
require(dirname(__FILE__) . '/includes/init2.php');
require(dirname(__FILE__) . '/includes/lib_geocoding.php');

include_once('includes/cls_json.php');
$json = new JSON();
$result = array('error' => 0,'message'=>'', 'content' => '');
$action = isset($_REQUEST['act'])  ? $_REQUEST['act'] : '';
$baiduak = $_CFG['baidu_ak'];
$baiduak ='f044dc5b8c415453e60dfd1245b6e40a';



//百度定位
if($action =='save_location_baidu')
{
	$latitude=$_REQUEST['lat'];
	$longitude=$_REQUEST['lng'];
	$localtion = $longitude.",".$latitude;
	$results = Geocoding::getchangebaidulocaltion($baiduak,$localtion);		
	$locations = explode(",",$results['locations']);
	$latitude = $locations[1];
	$longitude = $locations[0];
	$localtion = $latitude.",".$longitude;
	$results = Geocoding::getbaidulocationaddress($baiduak,$results['locations']);
	
	if(empty($_SESSION['lat']) && empty($_SESSION['lng'])){
		
			
			$_SESSION['lat'] = $latitude;
			$_SESSION['lng'] = $longitude;
	}
	$result['address'] =$results['regeocode']['pois'][0]['name'];
	$city_id = get_str_replace_region_name(2,$results['regeocode']['addressComponent']['city']);
	if($city_id)
	{
		$_SESSION['site_id'] = $city_id;
		$city_name =$results['regeocode']['addressComponent']['city'];
		$city_name = str_replace(array('省', '市', '自治区', '回族', '地区', '维吾尔','壮族'), '', $city_name);
	}
	else
	{
		$_SESSION['site_id'] = 1;
		$city_name ='中国';
	}
	$result = array('error' => 0,'city_name'=>$city_name);
	die($json->encode($result));
}
function get_str_replace_region_name($type,$name)
{
        if ($type == 1) {
            $region_name = str_replace(array('省', '市', '自治区', '回族', '地区', '维吾尔','壮族'), '', $name);
            $sql = 'SELECT `region_id` from ' . $GLOBALS['hhs']->table('region') . " where `region_name` = '{$region_name}' and region_type ='{$type}'";
            return $GLOBALS['db']->getOne($sql);
        } elseif ($type == 2) {
            $region_name = str_replace(array('省', '市', '自治区', '回族', '地区'), '', $name);
            $sql = 'SELECT `region_id` from ' . $GLOBALS['hhs']->table('region') . " where `region_name` = '{$region_name}'";
            return $GLOBALS['db']->getOne($sql);
        }
		else
		{
            $sql = 'SELECT `region_id` from ' . $GLOBALS['hhs']->table('region') . " where `region_name` = '{$name}'";
            return $GLOBALS['db']->getOne($sql);

		}
}
?>