<?php
require("../../inc/header.php");

/*
		SoftName : EmpireBak Version 2010
		Author   : wm_chief
		Copyright: Powered by www.phome.net
*/

DoSetDbChar('utf8');
E_D("DROP TABLE IF EXISTS `hhs_mail_templates`;");
E_C("CREATE TABLE `hhs_mail_templates` (
  `template_id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `template_code` varchar(30) NOT NULL DEFAULT '',
  `is_html` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `template_subject` varchar(200) NOT NULL DEFAULT '',
  `template_content` text NOT NULL,
  `last_modify` int(10) unsigned NOT NULL DEFAULT '0',
  `last_send` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(10) NOT NULL,
  PRIMARY KEY (`template_id`),
  UNIQUE KEY `template_code` (`template_code`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8");
E_D("replace into `hhs_mail_templates` values('1','send_password','1','密码找回','<p>{\$user_name}您好！<br />\r\n<br />\r\n您已经进行了密码重置的操作，请点击以下链接(或者复制到您的浏览器):<br />\r\n<br />\r\n<a href=\"{\$reset_email}\" target=\"_blank\">{\$reset_email}</a><br />\r\n<br />\r\n以确认您的新密码重置操作！<br />\r\n<br />\r\n{\$shop_name}<br />\r\n{\$send_date}&nbsp;</p>','1430177200','0','template');");
E_D("replace into `hhs_mail_templates` values('2','order_confirm','1','订单确认通知','<p>亲爱的{\$order.consignee}，你好！   我们已经收到您于 {\$order.formated_add_time} 提交的订单，该订单编号为：{\$order.order_sn} 请记住这个编号以便日后的查询。  {\$shop_name} {\$sent_date}</p>','1430177207','0','template');");
E_D("replace into `hhs_mail_templates` values('3','deliver_notice','1','发货通知','亲爱的{\$order.consignee}。你好！</br></br>\n\n您的订单{\$order.order_sn}已于{\$send_time}按照您预定的配送方式给您发货了。</br>\n</br>\n{if \$order.invoice_no}发货单号是{\$order.invoice_no}。</br>{/if}\n</br>\n在您收到货物之后请点击下面的链接确认您已经收到货物：</br>\n<a href=\"{\$confirm_url}\" target=\"_blank\">{\$confirm_url}</a></br></br>\n如果您还没有收到货物可以点击以下链接给我们留言：</br></br>\n<a href=\"{\$send_msg_url}\" target=\"_blank\">{\$send_msg_url}</a></br>\n<br>\n再次感谢您对我们的支持。欢迎您的再次光临。 <br>\n<br>\n{\$shop_name} </br>\n{\$send_date}','1194823291','0','template');");
E_D("replace into `hhs_mail_templates` values('4','order_cancel','1','订单取消','<p>亲爱的{\$order.consignee}，你好！   您的编号为：{\$order.order_sn}的订单已取消。  {\$shop_name} {\$send_date}</p>','1430177217','0','template');");
E_D("replace into `hhs_mail_templates` values('5','order_invalid','1','订单无效','<p>亲爱的{\$order.consignee}，你好！  您的编号为：{\$order.order_sn}的订单无效。  {\$shop_name} {\$send_date}</p>','1430177224','0','template');");
E_D("replace into `hhs_mail_templates` values('6','send_bonus','1','发红包','<p>亲爱的{\$user_name}您好！  恭喜您获得了{\$count}个红包，金额{if \$count &gt; 1}分别{/if}为{\$money}  {\$shop_name} {\$send_date}</p>','1430177235','0','template');");
E_D("replace into `hhs_mail_templates` values('7','group_buy','1','团购商品','亲爱的{\$consignee}，您好！<br>\n<br>\n您于{\$order_time}在本店参加团购商品活动，所购买的商品名称为：{\$goods_name}，数量：{\$goods_number}，订单号为：{\$order_sn}，订单金额为：{\$order_amount}<br>\n<br>\n此团购商品现在已到结束日期，并达到最低价格，您现在可以对该订单付款。<br>\n<br>\n请点击下面的链接：<br>\n<a href=\"{\$shop_url}\" target=\"_blank\">{\$shop_url}</a><br>\n<br>\n请尽快登录到用户中心，查看您的订单详情信息。 <br>\n<br>\n{\$shop_name} <br>\n<br>\n{\$send_date}','1194824668','0','template');");
E_D("replace into `hhs_mail_templates` values('8','register_validate','1','邮件验证','{\$user_name}您好！<br><br>\r\n\r\n这封邮件是 {\$shop_name} 发送的。你收到这封邮件是为了验证你注册邮件地址是否有效。如果您已经通过验证了，请忽略这封邮件。<br>\r\n请点击以下链接(或者复制到您的浏览器)来验证你的邮件地址:<br>\r\n<a href=\"{\$validate_email}\" target=\"_blank\">{\$validate_email}</a><br><br>\r\n\r\n{\$shop_name}<br>\r\n{\$send_date}','1162201031','0','template');");
E_D("replace into `hhs_mail_templates` values('9','virtual_card','0','虚拟卡片','亲爱的{\$order.consignee}\r\n你好！您的订单{\$order.order_sn}中{\$goods.goods_name} 商品的详细信息如下:\r\n{foreach from=\$virtual_card item=card}\r\n{if \$card.card_sn}卡号：{\$card.card_sn}{/if}{if \$card.card_password}卡片密码：{\$card.card_password}{/if}{if \$card.end_date}截至日期：{\$card.end_date}{/if}\r\n{/foreach}\r\n再次感谢您对我们的支持。欢迎您的再次光临。\r\n\r\n{\$shop_name} \r\n{\$send_date}','1162201031','0','template');");
E_D("replace into `hhs_mail_templates` values('10','attention_list','0','关注商品','亲爱的{\$user_name}您好~\n\n您关注的商品 : {\$goods_name} 最近已经更新,请您查看最新的商品信息\n\n{\$goods_url}\r\n\r\n{\$shop_name} \r\n{\$send_date}','1183851073','0','template');");
E_D("replace into `hhs_mail_templates` values('11','remind_of_new_order','0','新订单通知','亲爱的店长，您好：\n   快来看看吧，又有新订单了。\n    订单号:{\$order.order_sn} \n 订单金额:{\$order.order_amount}，\n 用户购买商品:{foreach from=\$goods_list item=goods_data}{\$goods_data.goods_name}(货号:{\$goods_data.goods_sn})    {/foreach} \n\n 收货人:{\$order.consignee}， \n 收货人地址:{\$order.address}，\n 收货人电话:{\$order.tel} {\$order.mobile}, \n 配送方式:{\$order.shipping_name}(费用:{\$order.shipping_fee}), \n 付款方式:{\$order.pay_name}(费用:{\$order.pay_fee})。\n\n               系统提醒\n               {\$send_date}','1196239170','0','template');");
E_D("replace into `hhs_mail_templates` values('12','goods_booking','1','缺货回复','亲爱的{\$user_name}。你好！</br></br>{\$dispose_note}</br></br>您提交的缺货商品链接为</br></br><a href=\"{\$goods_link}\" target=\"_blank\">{\$goods_name}</a></br><br>{\$shop_name} </br>{\$send_date}','0','0','template');");
E_D("replace into `hhs_mail_templates` values('13','user_message','1','留言回复','亲爱的{\$user_name}。你好！</br></br>对您的留言：</br>{\$message_content}</br></br>店主作了如下回复：</br>{\$message_note}</br></br>您可以随时回到店中和店主继续沟通。</br>{\$shop_name}</br>{\$send_date}','0','0','template');");
E_D("replace into `hhs_mail_templates` values('14','recomment','1','用户评论回复','亲爱的{\$user_name}。你好！</br></br>对您的评论：</br>“{\$comment}”</br></br>店主作了如下回复：</br>“{\$recomment}”</br></br>您可以随时回到店中和店主继续沟通。</br>{\$shop_name}</br>{\$send_date}','0','0','template');");

require("../../inc/footer.php");
?>