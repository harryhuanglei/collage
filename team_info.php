<?php
define('IN_HHS', true);
require('includes/init2.php');
require('includes/lib_payment.php');$result = array('error' => 0,'message'=>'', 'content' => '');$order_id=$_REQUEST['order_id'];$team_sign=$_REQUEST['team_sign'];if(!empty($team_sign)){    $sql="select team_num from ".$hhs->table('order_info') ." where order_id=".$team_sign;        $team_num=$db->getOne($sql);        //实际人数    $sql="select count(*) from ".$hhs->table('order_info')." where team_sign=".$team_sign." and team_status>0 ";        $rel_num=$db->getOne($sql);        if($team_num<=$rel_num){        $result['error']=1;        $result['url']="share.php?team_sign=".$team_sign;    }else{        $result['error']=0;    }        echo json_encode($result);    exit();    }			
		
?>