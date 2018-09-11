<?php
error_reporting(E_ALL ^ E_NOTICE);

if($_REQUEST['token']) {
    //2.x 第二步, Prism服务器根据token获取配置参数
    get_args_by_token($_REQUEST['token']);
}else{
    //1.x 第一步, 人工交互过程
    create_new_token();
}

function create_new_token(){
    switch($_REQUEST['step']){

        // 1.1 用户交互流程完毕,
        //     保存配置参数到临时存储, 产生一个对应的token. 并跳转回callback
        case 'finish':
            $args = $_POST['p'];
            $token = md5(time(). print_r($_SERVER, 1));

            file_put_contents('/tmp/'.$token, serialize($args));
            $redirect = $_REQUEST['callback'].'?token='.urlencode($token);
            header('Location: '.$redirect);
            break;

        //1.0 第一步: 展现页面. 
        //    可以放置身份认证等若干页面流程, 只要最后一步能跳转到callback即可.
        default :
            echo <<<EOF
<html>
    <h1>Set Params</h1>
    <hr />
    <form action="?step=finish" method="post">
        <input type="hidden" name="callback" value="{$_REQUEST['callback']}" />
        <pre>
        shop_id    <input type="text" name="p[shop_id]" value="testid123" />
        app_secret <input type="text" name="p[api_secret]" value="secret9527" />

        <input type="submit" />
        </pre>
    </form>
</html>
EOF;
    }

}


// 2.0 根据token获取配置参数, 生成json代码返回给prism
function get_args_by_token($token){
    $token_file = '/tmp/'.$token;
    if(file_exists($token_file)){
        $data = file_get_contents($token_file);
        if ($data) {
            $data = unserialize($data);
            echo json_encode($data);
        }
        unlink($token_file);
    }
}