<!-- $Id: wxch_config.html  2013-10-16 10:30:26Z djks $ -->



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



    <span class="action-span1"><a href="index.php?act=main"><?php echo $this->_var['lang']['cp_home']; ?></a> </span><span id="search_id" class="action-span1"> - 微信设置</span>



    <div style="clear:both"></div>



</h1>







<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,selectzone.js,colorselector.js')); ?>






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



<form enctype="multipart/form-data" action="" method="post" name="theForm" >



<!-- 通用信息 -->



<table width="90%" id="general-table" align="center">



    <?php $_from = $this->_var['wxchdata']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['list']):
?>



    <tr>



        <td class="label"><?php echo $this->_var['list']['title']; ?>：</td>



        <td>



            <label><input type="text" name="<?php echo $this->_var['list']['cfg_name']; ?>" value="<?php echo $this->_var['list']['cfg_value']; ?>" size=40 /></label>



        </td>



    </tr>



    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>



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







<script language="JavaScript">



var goodsId = '<?php echo $this->_var['goods']['goods_id']; ?>';



var elements = document.forms['theForm'].elements;



var sz1 = new SelectZone(1, elements['source_select1'], elements['target_select1']);



var sz2 = new SelectZone(2, elements['source_select2'], elements['target_select2'], elements['price2']);



var sz3 = new SelectZone(1, elements['source_select3'], elements['target_select3']);



var marketPriceRate = <?php echo empty($this->_var['cfg']['market_price_rate']) ? '1' : $this->_var['cfg']['market_price_rate']; ?>;



var integralPercent = <?php echo empty($this->_var['cfg']['integral_percent']) ? '0' : $this->_var['cfg']['integral_percent']; ?>;











onload = function()



{







    if (document.forms['theForm'].elements['auto_thumb'])



    {



        handleAutoThumb(document.forms['theForm'].elements['auto_thumb'].checked);



    }







    // 检查新订单



    startCheckOrder();



    



        <?php $_from = $this->_var['user_rank_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>



        set_price_note(<?php echo $this->_var['item']['rank_id']; ?>);



    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>



        



        document.forms['theForm'].reset();



    }







    function setAttrList(result, text_result)



    {



        document.getElementById('tbody-goodsAttr').innerHTML = result.content;



    }











            



</script>



<?php echo $this->fetch('pagefooter.htm'); ?>



