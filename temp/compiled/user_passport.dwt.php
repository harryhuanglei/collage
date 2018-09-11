<!doctype html>
<html lang="zh-CN">
<head>
<meta name="Generator" content="haohaipt X_7.2" />
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<meta name="format-detection" content="telephone=no">
<title><?php echo $this->_var['page_title']; ?></title>
<link rel="shortcut icon" href="favicon.ico" />
<link href="<?php echo $this->_var['hhs_css_path']; ?>/style.css" rel="stylesheet" />
<link href="<?php echo $this->_var['hhs_css_path']; ?>/login.css" rel="stylesheet" />
<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.js,haohaios.js,user.js,getcode.js')); ?>
<body>

<?php if ($this->_var['action'] == 'login'): ?>
<div class="container">
	<form name="formLogin" action="user.php" method="post" onSubmit="return userLogin()">
		<div class="login_input">
			<div class="input_group">
				<label for="mobile_phone" id="icon1" class="phone_icon"></label>
				<input type="tel" id="mobile_phone" name="mobile_phone" value="" placeholder="手机号码">
			</div>
			<hr class="input_hr">
			<div class="input_group">
				<label for="user_mobile" id="icon2" class="phone_icon"></label>
				<input type="tel" id="code" name="code" value="" placeholder="验证码">
				<button type="button" id="code_button" onclick="validate_mobile()">发送验证码</button>
			</div>
		</div>
		<div class="error">
			<p class="text-error" style="display: none;"></p>
		</div>
		<input type="hidden" name="act" value="act_login" />
        <input type="hidden" name="back_act" value="<?php echo $this->_var['back_act']; ?>" />
        <input type="submit" name="submit" id="submit_button" value="登录" />
		<!--a href="user.php?act=register" class="submit_button_login">注册</a-->
		
	</form>
<?php endif; ?>

</body>
<script type="text/javascript">
var process_request = "<?php echo $this->_var['lang']['process_request']; ?>";
<?php $_from = $this->_var['lang']['passport_js']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
var username_exist = "<?php echo $this->_var['lang']['username_exist']; ?>";
</script>
</html>
