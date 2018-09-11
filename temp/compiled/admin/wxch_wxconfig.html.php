<!-- $Id: wxch_config.htm 14216 2013-10-13 14:27:21Z djks $ -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $this->_var['lang']['cp_home']; ?><?php if ($this->_var['ur_here']): ?> - <?php echo $this->_var['ur_here']; ?> <?php endif; ?></title>
<meta name="robots" content="noindex, nofollow">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="styles/general.css" rel="stylesheet" type="text/css" />
<link href="styles/main.css" rel="stylesheet" type="text/css" />
<?php echo $this->smarty_insert_scripts(array('files'=>'transport.js,common.js')); ?>
<script language="JavaScript">
<!--
// 这里把JS用到的所有语言都赋值到这里
<?php $_from = $this->_var['lang']['js_languages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
//-->
</script>
</head>
<body>
<h1>
<?php if ($this->_var['action_link']): ?>
<span class="action-span"><a href="<?php echo $this->_var['action_link']['href']; ?>"><?php echo $this->_var['action_link']['text']; ?></a></span>
<?php endif; ?>
<?php if ($this->_var['action_link2']): ?>
<span class="action-span"><a href="<?php echo $this->_var['action_link2']['href']; ?>"><?php echo $this->_var['action_link2']['text']; ?></a>&nbsp;&nbsp;</span>
<?php endif; ?>
<span class="action-span1"><a href="index.php?act=main"><?php echo $this->_var['lang']['cp_home']; ?></a> </span><span id="search_id" class="action-span1"> - 微信接口</span>
<div style="clear:both"></div>
</h1>
<div class="main-div">
  <form name="theForm" method="post" action="wxch.php?act=wxconfig" >
  <table width="100%" >
 <tr>
    <td class="label">Url:</td>
    <td>http://你的网址/wechat/weixin.php</td>
  </tr>
  <tr>
    <td class="label">Token:</td>
    <td><input type="text" name="token" size="20" value="<?php echo $this->_var['config']['token']; ?>"/></td>
  </tr>
  <tr>
    <td class="label">AppId :</td>
    <td><input type="text " name="appid" size="20" value="<?php echo $this->_var['config']['appid']; ?>"/></td>
  </tr>
 <tr>
    <td class="label">AppSecret :</td>
    <td><input type="text " name="appsecret" size="20" value="<?php echo $this->_var['config']['appsecret']; ?>"/> * 
</td>
  </tr>
 <tr>
    <td class="label"></td>
    <td>分销红包提现设置</td>
  </tr>
  <tr>
    <td class="label">服务器IP :</td>
    <td><input type="text " name="client_ip" size="20" value="<?php echo $this->_var['config']['client_ip']; ?>"/>* </td>
  </tr>
  <tr>
    <td class="label">提供方名称 :</td>
    <td><input type="text " name="nick_name" size="20" value="<?php echo $this->_var['config']['nick_name']; ?>"/>* </td>
  </tr>
  <tr>
    <td class="label">红包发送者名称 :</td>
    <td><input type="text " name="send_name" size="20" value="<?php echo $this->_var['config']['send_name']; ?>"/>* </td>
  </tr>
  <tr>
    <td class="label">红包祝福 :</td>
    <td><input type="text " name="wishing" size="20" value="<?php echo $this->_var['config']['wishing']; ?>"/>* </td>
  </tr>
  <tr>
    <td class="label">活动名称 :</td>
    <td><input type="text " name="act_name" size="20" value="<?php echo $this->_var['config']['act_name']; ?>"/>* 活动名称不超过16个字</td>
  </tr>
  <tr>
    <td class="label">备注信息 :</td>
    <td><input type="text " name="remark" size="20" value="<?php echo $this->_var['config']['remark']; ?>"/>* </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" value="<?php echo $this->_var['lang']['button_submit']; ?>" class="button" />
      <input type="reset" value="<?php echo $this->_var['lang']['button_reset']; ?>" class="button" />
    </td>
  </tr>
</table>
  </form>
</div>
<?php echo $this->fetch('pagefooter.htm'); ?>