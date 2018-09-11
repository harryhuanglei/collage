<?php 

session_start();

$code=$_GET['code'];

$state=urldecode($_GET['state']);


if($code)
{
    
    
	if(strpos($state,'?')!==false){
	
	    header("location:http://".$_SERVER['HTTP_HOST'].$state."&code=".$code  );
	}else{
	    header("location:http://".$_SERVER['HTTP_HOST'].$state."?code=".$code  );
	}
	 exit();
	
}

else

{

	echo "未授权";

}

/*
function get_openid($appid,$appsecret,$code)

{

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code");

	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);

	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$tmpInfo = curl_exec($ch);

	if(curl_errno($ch))

	{

		return curl_error($ch);

	}

	curl_close($ch);

	return json_decode($tmpInfo,true);

}
*/


?>