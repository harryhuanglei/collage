<?php 


/**
 * Created by PhpStorm.
 * User: StandOpen
 * Date: 15-1-7
 * Time: 9:41
 */
class OrderPush
{
    protected $appid;
    protected $secrect;
    protected $accessToken;
    function __construct()
    {
        $appid='wx92970832ecad238a';
        $secrect='37c36d9099ee7635fe1f4ea5230412db';
        $this->appid = $appid;
        $this->secrect = $secrect;
        $this->accessToken = $this->getToken($appid, $secrect);
    }
    /**
     * 发送post请求
     * @param string $url
     * @param string $param
     * @return bool|mixed
     */
    function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }
    /**
     * 发送get请求
     * @param string $url
     * @return bool|mixed
     */
    function request_get($url = '')
    {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    /**
     * @param $appid
     * @param $appsecret
     * @return mixed
     * 获取token
     */
    protected function getToken($appid, $appsecret)
    {
        /*
         if (S($appid)) {
        $access_token = S($appid);
        } else {*/
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
        $token = $this->request_get($url);
        $token = json_decode(stripslashes($token));
        $arr = json_decode(json_encode($token), true);
        $access_token = $arr['access_token'];
         
        // S($appid, $access_token, 720);
        //}
        return $access_token;

}
/**
 * 发送自定义的模板消息
 * @param $touser
 * @param $template_id
 * @param $url
 * @param $data
 * @param string $topcolor
 * @return bool
 */
public function doSend($touser, $template_id, $url, $data, $topcolor = '#7B68EE')
{
    /*
     * data=>array(
         'first'=>array('value'=>urlencode("您好,您已购买成功"),'color'=>"#743A3A"),
         'name'=>array('value'=>urlencode("商品信息:微时代电影票"),'color'=>'#EEEEEE'),
         'remark'=>array('value'=>urlencode('永久有效!密码为:1231313'),'color'=>'#FFFFFF'),
     )
    */
    $template = array(
        'touser' => $touser,
        'template_id' => $template_id,
        'url' => $url,
        'topcolor' => $topcolor,
        'data' => $data
    );
    $json_template = json_encode($template);
    $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->accessToken;


    $dataRes = $this->request_post($url, urldecode($json_template));
    if ($dataRes['errcode'] == 0) {
        return true;
    } else {
        return false;
    }
}
}

?>