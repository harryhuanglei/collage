<?php

/*
提示：如果您需要的公司不在以下列表，请按以下方法自行添加或修改，快递公司名称区分大小写
case "与【shopex后台-商店配置-物流公司】下的公司名称一致":
$postcom '中的名称与【http://code.google.com/p/kuaidi-api/wiki/Open_API_API_URL】下的【快递公司代码】一致’;
*/
switch ($getcom){
	case "EMS"://ecshop后台中显示的快递公司名称
	    $postcom = 'ems';//快递公司代码
	    break;
	case "中国邮政":
	    $postcom = 'ems';
	    break;
	case "申通快递":
	    $postcom = 'sto';
	    break;
    case "圆通速递":
        $postcom = 'yt';
        break;
    case "顺丰速运":
        $postcom = 'sf';
        break;
    case "天天快递":
        $postcom = 'tt';
        break;
    case "韵达快递":
        $postcom = 'yd';
        break;
    case "中通速递":
        $postcom = 'zto';
        break;
    case "汇通快递":
        $postcom = 'ht';
        break;
    case "全峰速递":
        $postcom = 'qf';
        break;
    default:
        $postcom = '';
}