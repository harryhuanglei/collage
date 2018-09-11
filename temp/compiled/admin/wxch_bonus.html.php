<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<html xmlns="http://www.w3.org/1999/xhtml">


<head>


    <title><?php echo $this->_var['wxch_lang']['cp_home']; ?><?php if ($this->_var['wxch_lang']['ur_here']): ?> - <?php echo $this->_var['wxch_lang']['ur_here']; ?> <?php endif; ?></title>


    <meta name="robots" content="noindex, nofollow">


    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


    <link href="styles/general.css" rel="stylesheet" type="text/css" />


    <link href="styles/main.css" rel="stylesheet" type="text/css" />


    <?php echo $this->smarty_insert_scripts(array('files'=>'../js/transport_old.js,common.js')); ?>


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


    <span class="action-span1"><a href="index.php?act=main"><?php echo $this->_var['wxch_lang']['cp_home']; ?></a> </span><span id="search_id" class="action-span1"> - <?php echo $this->_var['wxch_lang']['ur_here']; ?></span>


    <div style="clear:both"></div>


</h1>





<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,selectzone.js,colorselector.js')); ?>


<script type="text/javascript" src="../js/calendar.php?lang=<?php echo $this->_var['cfg_lang']; ?>"></script>


<link href="../js/calendar/calendar.css" rel="stylesheet" type="text/css" />





<?php if ($this->_var['warning']): ?>


<ul style="padding:0; margin: 0; list-style-type:none; color: #CC0000;">


    <li style="border: 1px solid #CC0000; background: #FFFFCC; padding: 10px; margin-bottom: 5px;" ><?php echo $this->_var['warning']; ?></li>


</ul>


<?php endif; ?>





<!-- start goods form -->


<div class="tab-div">


<!-- tab bar -->


<div id="tabbar-div">


    <p>


        <span class="tab-front" id="general-tab">设置</span>


    </p>


</div>





<!-- tab body -->


<div id="tabbody-div">


<form action="" method="post" name="form1" >


<!-- 通用信息 -->


<table width="90%" id="general-table" align="center">


    <tr>


        <td class="label">选择红包类型：</td>


        <td>


            <select name="bonus">


                <option <?php if ($this->_var['type_id'] == 0): ?>selected<?php endif; ?>>关注不发送优惠券</option>


                <?php $_from = $this->_var['w_data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'bonus');if (count($_from)):
    foreach ($_from AS $this->_var['bonus']):
?>


                <option name="type_id" value="<?php echo $this->_var['bonus']['type_id']; ?>" <?php if ($this->_var['type_id'] == $this->_var['bonus']['type_id']): ?>selected<?php endif; ?>><?php echo $this->_var['bonus']['type_name']; ?>=>金额：<?php echo $this->_var['bonus']['type_money']; ?></option>


                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>


            </select>


        </td>


    </tr>


</table>











<div class="button-div">


    <input type="submit" value="<?php echo $this->_var['lang']['button_submit']; ?>" class="button" />


    <input type="reset" value="<?php echo $this->_var['lang']['button_reset']; ?>" class="button" />


</div>


<input type="hidden" name="act" value="<?php echo $this->_var['form_act']; ?>" />


</form>


</div>


</div>


<!-- end goods form -->


<?php echo $this->smarty_insert_scripts(array('files'=>'validator.js,tab.js')); ?>





<?php echo $this->fetch('wxch_pagefooter.htm'); ?>