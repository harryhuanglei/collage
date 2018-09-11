
<div class="footer">

    <ul>

        <li><a href="index.php" class="nav-index"><i class="ico_index"></i>首页</a></li>
        
        <li><a href="<?php if ($this->_var['cat_type'] == 1): ?>category.php<?php else: ?>catall.php<?php endif; ?>" class="nav-catall"><i class="ico_catall"></i>分类</a></li>

	    <li><a href="flows.php?step=cart" id="cat" class="nav-cart"><i class="ico_cart"></i>购物车</a><span id="HHS_CARTINFO"><?php 
$k = array (
  'name' => 'cart_num',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?></span></li>

        <li><a href="square.php" class="nav-square"><i class="ico_square"></i>广场</a></li>

        <li><a href="user.php"  class="nav-user"><i class="ico_user"></i>个人中心</a></li>

    </ul>

</div>
<script>
    var serv_time = '<?php echo $this->_var['serv_time']; ?>';

    $(function() {
        queryOrder();
		
		queryRemaind();
    });
    function queryOrder(){
        Ajax.call('api.php?act=queryOrder','serv_time='+serv_time, queryOrderResponse, 'GET', 'JSON');
    }

    function queryOrderResponse(result){
        if(result.error)
        {
            //alert(result.message)
        }
        else
        {
            if (result.content.length > 0)
            do_what_you_want(result.content);
            serv_time = result.serv_time;
            window.setTimeout("queryOrder()", 3*1000); 
        }
    }
    function do_what_you_want(result) {
        $('.ws-for-push').remove();
        $('body').append(result);
        $('.ws-for-push').fadeOut(5000);
    }
	
	function queryRemaind()
	{
		
		Ajax.call('api.php?act=queryRemaind','serv_time='+serv_time, queryRemaindResponse, 'GET', 'JSON');
	
	}
	function queryRemaindResponse(result)
	{
	
		if(result.error)
        {
            window.setTimeout("queryRemaind()", 1000);
        }
	
	}
	
	
	
</script>