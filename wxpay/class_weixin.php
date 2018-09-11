<?php
class class_weixin
{
    var $appid ;
    var $appsecret ;
    var $tokenFile ;
    var $ticketFile;
    //构造函数，获取Access Token
    public function __construct($appid = NULL, $appsecret = NULL)
    {
        if($appid && $appsecret){
            
            $this->appid = $appid;
            
            $this->appsecret = $appsecret;
// 添加文件夹，0777权限，很重要
// 如果你是复制文件夹，记得清空文件夹里面所有的东西
            $this->tokenFile = dirname(__FILE__) . '/token/token.txt';
            $this->ticketFile = dirname(__FILE__) . '/token/ticket.txt';
            
        }else{
             
            echo "appid或appsecret为空";
            
            return ;
        }
        
    }
    public function getAccessToken(){
        
        if(! is_file($this->tokenFile)){
            return $this->getToken();
        }
        $now_time = time();
        $last_time = filemtime($this->tokenFile);
        if(($now_time - $last_time) > 5000)
        {
            return $this->getToken();
        }
        // echo date("Y-m-d H:i:s") . '/' . date("Y-m-d H:i:s",$last_time) . '<br>';
        // echo ($now_time - $last_time) . '<br>';
        $_SESSION['access_token'] = file_get_contents($this->tokenFile);
        return $_SESSION['access_token'];
        // if(empty($_SESSION['access_token']) ||  (time()-intval($_SESSION['access_token'])>7000) ){
         
        //     $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;
            
        //     $tmpInfo=$this->httpGet($url);
            
        //     $info=json_decode($tmpInfo,true);
            
        //     //setcookie("access_token",$info['access_token'],time()+7000,'/');            
        //     $_SESSION['access_token']=$info['access_token'];
        //     $_SESSION['access_token_time']=time();
            
        //     return $info['access_token'];         
        // }else{
            
        //  return $_SESSION['access_token'];
            
        // }
        
    }
    private function getToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;
        
        $tmpInfo=$this->httpGet($url);
        
        $info=json_decode($tmpInfo,true);
        
        file_put_contents($this->tokenFile, $info['access_token']);
        //setcookie("access_token",$info['access_token'],time()+7000,'/');            
        $_SESSION['access_token']=$info['access_token'];
        $_SESSION['access_token_time']=time();
        @unlink($this->ticketFile);
        return $info['access_token'];         
    }
    public function getSignature($timestamp='1499992323' ) {
        
        $jsapiTicket = $this->getJsApiTicket();
    
        // 注意 URL 一定要动态获取，不能 hardcode.
        //$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        /*
        if(($pos=strrpos($_SERVER[REQUEST_URI], "from"))!==false){
            $uri=substr($_SERVER[REQUEST_URI],0,$pos-1);
        }else{
            $uri=$_SERVER[REQUEST_URI];
        }*/
        
        $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."";
        
        $nonceStr = $timestamp;//$this->createNonceStr();
    
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        return $signature;
    }
    public function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $accessToken = $this->getAccessToken();
        if(! is_file($this->ticketFile)){
            return $this->getTicket();
        }
                
        $now_time = time();
        $last_time = filemtime($this->ticketFile);
        if(($now_time - $last_time) > 5000)
        {
            return $this->getTicket();
        }  
        return file_get_contents($this->ticketFile);      
    }
    public function getTicket()
    {
        $accessToken = $this->getAccessToken();
            
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
        
        $res = json_decode($this->httpGet($url));
        // echo "<pre>";
        // print_r($res);
        // die();
        $jsapi_ticket = $res->ticket;
        if ($jsapi_ticket) {
            file_put_contents($this->ticketFile, $jsapi_ticket);
            return $jsapi_ticket;
        }
        else{
            $this->getToken();
        }
        return false;
    }
    public function createNoncestr( $length = 32 )
    
    {
    
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    
        $str ="";
    
        for ( $i = 0; $i < $length; $i++ )  {
    
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
    
        }
    
        return $str;
    
    }
    public function send_wxmsg($openid,$w_title,$w_url,$w_description,$picurl=''  )
    {
        
        $accessToken = $this->getAccessToken();
        
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$accessToken;
        $cfg_baseurl = $GLOBALS['db']->getOne("SELECT cfg_value FROM ".$GLOBALS['hhs']->table("weixin_cfg")."  WHERE cfg_name = 'baseurl'");
        preg_match("/^(http:\/\/)?([^\/]+)/i", $cfg_baseurl , $matches);
        $cfg_baseurl=$matches[0]."/";
        $w_url = strstr($w_url,'mp.weixin.qq.com') ? $w_url: ($w_url?$cfg_baseurl.$w_url:'');
        // $picurl = strstr($picurl,'qpic.cn') ? $picurl: ($picurl ? $cfg_baseurl.$picurl: '');
        $picurl = $picurl ? $cfg_baseurl.$picurl: '';
        $post_msg = '{
           "touser":"'.$openid.'",
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
       $ret_json = $this->httpPost($url, $post_msg);
       $ret = json_decode($ret_json);
       if(isset($ret->errcode) && $ret->errcode > 0 && $ret->errcode != 45015 ){
            @unlink($this->ticketFile);
            @unlink($this->tokenFile);
       }
       // print_r($ret). '<br>';
       return $ret->errmsg ;
       
    }		public function send_wxmsgdemo($openid,$template_id,$w_url,$w_description,$picurl='',$data)    {        $accessToken = $this->getAccessToken();	    $urlt = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$accessToken;        $post_msg = '{           "touser":"'.$openid.'",           "template_id":"'.$template_id.'",		   "url":"'.$w_url.'",		    "data":{                   "first": {                      "value":"'.$data['title'].'",                       "color":"#FF3333"                   },                   "keyword1":{                       "value":"'.$data['order_sn'].'",                       "color":"#173177"                  },                  "keyword2": {                      "value":"'.$data['add_time'].'",                      "color":"#173177"                  },                 "keyword3": {                       "value":"'.$data['order_status'].'",                      "color":"#173177"                 },				 "keyword4": {                       "value":"'.$data['point_address'].'",                      "color":"#173177"                 },                   "remark":{                      "value":"'.$data['desc'].'",                       "color":"#FF3333"                   }           }         }';	   $template = $this->httpPost($urlt, $post_msg);       $ret = json_decode($template);       if(isset($ret->errcode) && $ret->errcode > 0 && $ret->errcode != 45015 ){            @unlink($this->ticketFile);            @unlink($this->tokenFile);      }      return $ret->errmsg ;    }				
    //获取网站关注二维码，并且存入本地
    public function getWxCode($user_id)
    {
        $accessToken = $this->getAccessToken();
        
        //临时二维码
        $post_msg = '{"expire_seconds": 2592000, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$user_id.'}}}';
    
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$accessToken;
        $ret_json = $this->httpPost($url, $post_msg);
        $ret_json = json_decode($ret_json);
        if(!empty($ret_json->ticket))
        {
            $wx_code_url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ret_json->ticket);          
            #return $wx_code_url;
            //存入本地
            $url = ROOT_PATH . 'data/share/u_'.$user_id.'.jpg';
            $img = $this->httpGet($wx_code_url);
            $res = file_put_contents($url,$img);
            if($res > 0)
            {
                return $url;
            }
            else
            {
                return false;   
            }
                
        }
        else
        {
            @unlink($this->ticketFile);
            @unlink($this->tokenFile);            
            return false;
        }
    }
        
    public function httpGet($url, $data = null)
    {
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $tmpInfo = curl_exec($ch);
        
        if(curl_errno($ch))
        
        {
        
            return curl_error($ch);
        
        }
        
        curl_close($ch);
        
        return $tmpInfo;
    }
    
    public function httpPost($url, $data = null)
    {
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $temp=curl_exec ($ch);
        curl_close ($ch);
        return $temp; 
    }
}
?>