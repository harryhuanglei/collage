<?php
require_once('../lib/provider.php');

//定义api
$provider = new prism_provider();

$provider->add("get_id_by_domain", 
        prism_api("id",
            prism_params("arg1", "useinput"),
            prism_params("arg2", "useinput"),
            prism_params("arg3", "useinput"),
            prism_params("arg4", "useinput")
        )
    );

//本例我们在下面会调用distach, 因此api的后端设为当前访问地址
$provider->set_url($_SERVER['DOCUMENT_URI']);

//设置签名方式
// $provider->set_validation("prism_sign_validation", "get_secret_by_key");

//设置具体的api调用
$provider->handler(new api_handler);

if(array_key_exists('show_api_json', $_GET)){
    $provider->output_json();
}elseif(array_key_exists("method", $_REQUEST)){
    $provider->dispatch($_REQUEST["method"]);
}else{
    echo <<<EOF
        <a href="?show_api_json">json</a>
EOF;
}

class api_handler{

    function get_id_by_domain($params){
        return $_SERVER;
    }

}
