<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_payment`;");
E_C("CREATE TABLE `hhs_payment` (
  `pay_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `pay_code` varchar(20) NOT NULL DEFAULT '',
  `pay_name` varchar(120) NOT NULL DEFAULT '',
  `pay_fee` varchar(10) NOT NULL DEFAULT '0',
  `pay_desc` text NOT NULL,
  `pay_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pay_config` text NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_cod` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_online` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pay_id`),
  UNIQUE KEY `pay_code` (`pay_code`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_payment` values('1','balance','余额支付','0','使用帐户余额支付。只有会员才能使用，通过设置信用额度，可以透支。','2','a:0:{}','1','0','1');");
E_D("replace into `hhs_payment` values('2','bank','银行汇款/转帐','0','银行名称\n收款人信息：全称 ××× ；帐号或地址 ××× ；开户行 ×××。\n注意事项：办理电汇时，请在电汇单“汇款用途”一栏处注明您的订单号。','0','a:0:{}','0','0','0');");
E_D("replace into `hhs_payment` values('3','cod','货到付款','0','开通城市：×××\n货到付款区域：×××','0','a:0:{}','0','1','0');");
E_D("replace into `hhs_payment` values('4','wxpay','微信支付','0','微信支付(wx.qq.com) 是国内先进的网上支付平台。<!--br/>收款接口：在线即可开通，<font color=\"red\"><b>零预付，免年费</b></font>，单笔阶梯费率，无流量限制。<br/><a href=\"http://cloud.ecshop.com/payment_apply.php?mod=alipay\" target=\"_blank\"><font color=\"red\">立即在线申请</font></a-->','0','a:10:{i:0;a:3:{s:4:\"name\";s:12:\"wxpay_app_id\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:3:\"111\";}i:1;a:3:{s:4:\"name\";s:16:\"wxpay_app_secret\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:4:\"1111\";}i:2;a:3:{s:4:\"name\";s:11:\"wxpay_mchid\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:10:\"1283826401\";}i:3;a:3:{s:4:\"name\";s:9:\"wxpay_key\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:0:\"\";}i:4;a:3:{s:4:\"name\";s:16:\"app_wxpay_app_id\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:0:\"\";}i:5;a:3:{s:4:\"name\";s:15:\"app_wxpay_mchid\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:0:\"\";}i:6;a:3:{s:4:\"name\";s:13:\"app_wxpay_key\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:0:\"\";}i:7;a:3:{s:4:\"name\";s:13:\"app_notifyurl\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:51:\"http://111.seenne.com/wxpay/demo/app_notify_url.php\";}i:8;a:3:{s:4:\"name\";s:9:\"notifyurl\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:47:\"http://111.seenne.com/wxpay/demo/notify_url.php\";}i:9;a:3:{s:4:\"name\";s:10:\"successurl\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:57:\"http://111.seenne.com/user.php?act=order_detail&order_id=\";}}','1','0','1');");
E_D("replace into `hhs_payment` values('5','alipay','支付宝','0','支付宝网站(www.alipay.com) 是国内先进的网上支付平台。<br/>支付宝收款接口：在线即可开通 <a href=\"http://cloud.ecshop.com/payment_apply.php?mod=alipay\" target=\"_blank\"><font color=\"red\">立即在线申请</font></a>','1','a:3:{i:0;a:3:{s:4:\"name\";s:14:\"alipay_account\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:0:\"\";}i:1;a:3:{s:4:\"name\";s:10:\"alipay_key\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:0:\"\";}i:2;a:3:{s:4:\"name\";s:14:\"alipay_partner\";s:4:\"type\";s:4:\"text\";s:5:\"value\";s:0:\"\";}}','1','0','1');");

require("../../inc/footer.php");
?>