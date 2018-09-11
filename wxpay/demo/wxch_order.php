<?php

$time = time();

if($user_id > 0) 
{
	$access_token = access_token($db);
	$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
	
	$query_sql = "SELECT openid FROM " . $hhs->table('users') . " WHERE user_id = '$user_id'";
	$ret_w = $db->getRow($query_sql);
	$wxid = $ret_w['openid'];


/*
	$sql = "SELECT * FROM hhs_wxch_order WHERE order_name = '$wxch_order_name'";
	$cfg_order = $db->getRow($sql);
	*/
	$cfg_baseurl = $db->getOne("SELECT cfg_value FROM hhs_weixin_cfg WHERE cfg_name = 'baseurl'");
	$cfg_murl = $db->getOne("SELECT cfg_value FROM hhs_weixin_cfg WHERE cfg_name = 'murl'");
	
	if($wxch_order_name=='pay'){
	    if($team_first==1){
	        $w_title = '付款成功';//$cfg_order['title'];
	        $w_url = $cfg_baseurl.$cfg_murl.'share.php?team_sign='.$team_sign;
	        $w_description="恭喜您荣升为团长！马上叫小伙伴来参团，组团成功才能享受优惠哦~";
	        
	        $picurl='';
	    }
	}elseif($wxch_order_name=='warn'){
	    $w_title = '参团人数不足提醒';
	    $w_url = $cfg_baseurl.$cfg_murl.'share.php?team_sign='.$team_sign;
	    $w_description="您参加的 ".$goods_name."还剩10小时，目前人数不足，尚未组团成功！快去叫身边的小伙伴一起来参团吧";
	    $picurl='';
	}
	
	
	$post_msg = '{
       "touser":"'.$wxid.'",
       "msgtype":"news",
       "news":{
           "articles": [
            {
                "title":"'.$w_title.'",
                "description":"'.$w_description.'",
                "url":"'.$w_url.'",
                "picurl":"'.$picurl.'"
            }
            ]
       }
   }';
 
	
	$ret_json = curl_grab_page($url, $post_msg);
	$ret = json_decode($ret_json);
	//var_dump($ret);exit();
	
	if($ret->errmsg != 'ok') 
	{
		$access_token = new_access_token($db);
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
		$ret_json = curl_grab_page($url, $post_msg);
		$ret = json_decode($ret_json);
	}
}
function new_access_token($db) 
{
	$time = time();
	$ret = $db->getRow("SELECT * FROM `hhs_weixin_config` WHERE `id` = 1");
	$appid = $ret['appid'];
	$appsecret = $ret['appsecret'];
	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
	$ret_json = curl_get_contents($url);
	$ret = json_decode($ret_json);
	if($ret->access_token)
	{
		$db->query("UPDATE `hhs_weixin_config` SET `access_token` = '$ret->access_token',`dateline` = '$time' WHERE `id` =1;");
	}
	return $ret->access_token;
}
function access_token($db) 
{
	$ret = $db->getRow("SELECT * FROM `hhs_weixin_config` WHERE `id` = 1");
	$appid = $ret['appid'];
	$appsecret = $ret['appsecret'];
	$access_token = $ret['access_token'];
	$dateline = $ret['dateline'];
	$time = time();
	if(($time - $dateline) >= 7200) 
	{
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
		$ret_json = curl_get_contents($url);
		$ret = json_decode($ret_json);
		if($ret->access_token)
		{
			$db->query("UPDATE `hhs_weixin_config` SET `access_token` = '$ret->access_token',`dateline` = '$time' WHERE `id` =1;");
			return $ret->access_token;
		}
	}
	elseif(empty($access_token)) 
	{
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
		$ret_json = curl_get_contents($url);
		$ret = json_decode($ret_json);
		if($ret->access_token)
		{
			$db->query("UPDATE `hhs_weixin_config` SET `access_token` = '$ret->access_token',`dateline` = '$time' WHERE `id` =1;");
			return $ret->access_token;
		}
	}
	else 
	{
		return $access_token;
	}
}
function curl_get_contents($url) 
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$r = curl_exec($ch);
	curl_close($ch);
	return $r;
}
function curl_grab_page($url,$data,$proxy='',$proxystatus='',$ref_url='') 
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($proxystatus == 'true') 
	{
		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
		curl_setopt($ch, CURLOPT_PROXY, $proxy);
	}
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_URL, $url);
	if(!empty($ref_url))
	{
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_REFERER, $ref_url);
	}
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	ob_start();
	return curl_exec ($ch);
	ob_end_clean();
	curl_close ($ch);
	unset($ch);
}
?>