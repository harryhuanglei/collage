<?php





if($user_id > 0) 


{


	//$access_token = access_token($db);   


    $weixin_config_rows = $GLOBALS['db']->getRow("select * from ".$GLOBALS['hhs']->table('weixin_config')."");


    $appid = $weixin_config_rows['appid'];


    $appsecret =$weixin_config_rows['appsecret'];



    $timestamp=gmtime();


    


	$class_weixin=new class_weixin($appid,$appsecret);


	$signature=$class_weixin->getSignature($timestamp);


	$access_token=$class_weixin->getAccessToken();




	$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;


	


	$query_sql = "SELECT openid FROM " . $hhs->table('users') . " WHERE user_id = '$user_id'";


	$ret_w = $db->getRow($query_sql);


	$wxid = $ret_w['openid'];








/*


	$sql = "SELECT * FROM hhs_wxch_order WHERE order_name = '$wxch_order_name'";


	$cfg_order = $db->getRow($sql);


	*/


	/**/


	$cfg_baseurl = $db->getOne("SELECT cfg_value FROM ".$hhs->table("weixin_cfg")."  WHERE cfg_name = 'baseurl'");


	//$cfg_murl = $db->getOne("SELECT cfg_value FROM hhs_weixin_cfg WHERE cfg_name = 'murl'");


	


	//$cfg_baseurl="http://" . $_SERVER['HTTP_HOST'] . "/";


	preg_match("/^(http:\/\/)?([^\/]+)/i", $cfg_baseurl , $matches);


	$cfg_baseurl=$matches[0]."/";


	if($wxch_order_name=='pay'){ 


        $w_title = '付款成功';//$cfg_order['title'];


        $w_url = $cfg_baseurl.'share.php?team_sign='.$team_sign;


        $w_description="恭喜您荣升为团长！马上叫小伙伴来参团，组团成功才能享受优惠哦~";


        $picurl='';


	}
	elseif($wxch_order_name=='teammem_suc'){


	    $w_title = '团购成功通知';


	    $w_url = $cfg_baseurl.'share.php?team_sign='.$team_sign;


	    $w_description="您参加的团组团成功！！！！";


	    $picurl='';


	}elseif($wxch_order_name=='warn'){


	    $w_title = '参团人数不足提醒';


	    $w_url = $cfg_baseurl.'share.php?team_sign='.$team_sign;


	    $w_description="您参加的 ".$goods_name."还剩10小时，目前人数不足，尚未组团成功！快去叫身边的小伙伴一起来参团吧";


	    $picurl='';


	}elseif($wxch_order_name=='refund'){
	    $w_title = '退款提醒';


	    $w_url = $cfg_baseurl.'user.php?act=order_detail&order_id='.$order_id;


	    $w_description="您的订单已经成功退款，记得常来看看哦";


	    $picurl='';


	}


	elseif($wxch_order_name=='send_order_bonus'){


	    $w_title = '发送优惠券提醒';


	    $w_url = $cfg_baseurl.'user.php?act=bonus';


	    $w_description='亲爱的'.$uname.'您好！恭喜您获得了'.$count.'个红包，金额分别为'.$money;


	    $picurl='';


	    if($bonus['free_all'])


	    {


	    	$goods = $db->getRow("SELECT goods_id,goods_name,goods_img FROM " . $hhs->table('goods') . " WHERE `bonus_free_all` = '".$bonus['type_id']."' ");


		    $w_title = '免单券';


	    	$w_url = $cfg_baseurl.'goods.php?id=' . $goods['goods_id'].'&bonus_free_all='.$bonus['type_id'];


		    $w_description='亲爱的'.$uname.'您好！恭喜您获得了免单券一张，赶紧去使用吧！';


		    $picurl= $cfg_baseurl . $goods['goods_img'];


	    }


	}


	elseif($wxch_order_name=='shipping'){


		$orders = $db->getRow("SELECT * FROM " . $hhs->table('order_info') . " WHERE `order_id` = '$order_id' ");


		$order_goods = $db->getAll("SELECT * FROM " . $hhs->table('order_goods') . "  WHERE `order_id` = '$order_id'");


		if(!empty($order_goods))


		{


			foreach($order_goods as $v)


			{


				if(empty($v['goods_attr']))


				{


					$shopinfo .= $v['goods_name'].'('.$v['goods_number'].'),';


				}


				else


				{


					$shopinfo .= $v['goods_name'].'（'.$v['goods_attr'].'）'.'('.$v['goods_number'].'),';


				}


			}


			$shopinfo = substr($shopinfo, 0, strlen($shopinfo)-1);


			$shopinfo = str_replace(PHP_EOL, '', $shopinfo);


		}


		if($orders['pay_status'] == 0)


		{


			$pay_status = '支付状态：未付款';


		}


		elseif($orders['pay_status'] == 1)


		{


			$pay_status = '支付状态：付款中';


		}


		elseif($orders['pay_status'] == 2)


		{


			$pay_status = '支付状态：已付款';


		}


		$wxch_address = "\r\n收件地址：".$orders['address'];


		$wxch_consignee = "\r\n收件人：".$orders['consignee'];


		$w_title = '发货提醒';


		if($orders['order_amount'] == '0.00')


		{


			$orders['order_amount'] = $orders['money_paid'];


		}


	


	    $w_url = $cfg_baseurl.'user.php?act=order_detail&order_id='.$order_id;


	    $w_description = '订单号：'.$orders['order_sn']."\r\n".'商品信息：'.$shopinfo."\r\n总金额：".$orders['order_amount']."\r\n".$pay_status.$wxch_consignee.$wxch_address;


	    $picurl='';


	}


	elseif($wxch_order_name=='send_checked_result'){


	    $w_title = '发送审核结果提醒';


	    $w_url = $shareurl;//$cfg_baseurl.'user.php?act=bonus';


	    $w_description='亲爱的'.$uname.'您好！您申请的商家审核'.($is_check == 1 ?'已通过，请通过PC端管理商品。':'暂未通过').'。' . $check_desc;


	    $picurl='';


	}


	elseif($wxch_order_name=='pay_msgs'){


		$pay_msgs = array(


			'1' => ' 已经产生，请核对结算信息',


			'3' => ' 审核通过',


			'4' => ' 准备结算，请核对银行账户',


			'6' => ' 已支付，请注意查收',


			'11' =>' 审核未过，请注意跟进联系',


		);


	    $w_title = '结款通知提醒';


	    $w_url = '';//$cfg_baseurl.'user.php?act=bonus';


	    $w_description= '结算单：' .$settlement_sn . $pay_msgs[$settlement_status];


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


	


	setcookie("access_token",$ret->access_token,time()+7000,'/');


	return $ret->access_token;


}/*


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


}*/


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