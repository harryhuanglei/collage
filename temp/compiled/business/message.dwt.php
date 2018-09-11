<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" content="haohaipt X_7.2" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>店铺管理平台</title>
	<link href="templates/css/login.css" rel="stylesheet" type="text/css" />
<?php if ($this->_var['auto_redirect']): ?>
	<meta http-equiv="refresh" content="3;URL=<?php echo $this->_var['message']['back_url']; ?>" />
<?php endif; ?>	
</head>
<body style="background-color: #001E54;">
	<div class="loginbox loginbox-mess">
  		<div align="center">
          <div style="margin:50px auto 0;">
          <p style="font-size: 16px; font-weight:bold; color: #fff;"><?php echo $this->_var['message']['content']; ?></p>
            <div class="blank"></div>
            <div class="blank"></div>
            <?php if ($this->_var['message']['url_info']): ?>
              <?php $_from = $this->_var['message']['url_info']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('info', 'url');if (count($_from)):
    foreach ($_from AS $this->_var['info'] => $this->_var['url']):
?>
              <p style="margin-top: 15px;"><a href="<?php echo $this->_var['url']; ?>" style="color:#fff;"><?php echo $this->_var['info']; ?></a></p><br />
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <?php endif; ?>
            </div>
        </div>
	</div>
	<div class="loginfoot" style="margin:340px auto 20px;">
		<p style="color:#fff;"> copyright&copy;2005-2014 版权所有 www.xaphp.cn &nbsp; </p>
	</div>


</body>
</html>