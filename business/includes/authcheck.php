<?php

$suppliers_id = intval($_SESSION['suppliers_id']);
/* 未登录处理 */
if (empty($suppliers_id)) {
    if (!in_array($action, $not_login_arr)) {
        if (in_array($action, $ui_arr)) {
            if (!empty($_SERVER['QUERY_STRING'])) {
                $back_act = 'suppliers.php?' . strip_tags($_SERVER['QUERY_STRING']);
            }
            $action = 'login';
        } else {
            //未登录提交数据。非正常途径提交数据！
            ob_clean();
            header("location:/business/suppliers.php");
            die($_LANG['require_login']);
        }
    }
}