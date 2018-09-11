<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $this->_var['shop_name']; ?>管理后台</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/login.css" rel="stylesheet" type="text/css" />
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,validator.js')); ?>
<script language="JavaScript">
<!--
// 这里把JS用到的所有语言都赋值到这里
<?php $_from = $this->_var['lang']['js_languages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

if (window.parent != window)
{
  window.top.location.href = location.href;
}

//-->
</script>
</head>
<body>
<div class="logo"></div>
<div class="loginbox">
    <h3>管理中心登录</h3>
    <form method="post" action="privilege.php" name='theForm' onsubmit="return validate()">
        <ul>
        <li><i class="username"></i><input type="text" name="username" size="35" /></li>
        <li><i class="password"></i><input type="password" name="password" size="35" /></li>
        <?php if ($this->_var['gd_version'] > 0): ?>
        <li><label><?php echo $this->_var['lang']['label_captcha']; ?></label><input type="text" name="captcha" class="capital" /><img src="index.php?act=captcha&<?php echo $this->_var['random']; ?>" width="145" height="20" alt="CAPTCHA" border="1" onclick= this.src="index.php?act=captcha&"+Math.random() style="cursor: pointer;" title="<?php echo $this->_var['lang']['click_for_another']; ?>" /></li>
        <?php endif; ?>
        <li><label>&nbsp;</label><input type="checkbox" value="1" name="remember" id="remember" /><font><?php echo $this->_var['lang']['remember']; ?></font><span><a href="get_password.php?act=forget_pwd"><?php echo $this->_var['lang']['forget_pwd']; ?></a></span></li>
        <li><input class="signin" type="submit" value="<?php echo $this->_var['lang']['signin_now']; ?>" /></li>
        </ul>
        <input type="hidden" name="act" value="signin" />
    </form>
</div>
<script language="JavaScript">
<!--
  document.forms['theForm'].elements['username'].focus();
  
  /**
   * 检查表单输入的内容
   */
  function validate()
  {
    var validator = new Validator('theForm');
    validator.required('username', user_name_empty);
    //validator.required('password', password_empty);
    if (document.forms['theForm'].elements['captcha'])
    {
      validator.required('captcha', captcha_empty);
    }
    return validator.passed();
  }
  
//-->
</script>
</body>