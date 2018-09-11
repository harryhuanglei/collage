<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $this->_var['shop_name']; ?>管理后台</title>
<link rel="stylesheet" href="css/admincp.css" type="text/css" media="all" />
<script src="js/admin.js" type="text/javascript"></script>
<script type="text/javascript" src="js/transport.js"></script>
<script type="text/javascript" src="js/common.js"></script>
</head>
<body>
<table id="frametable" cellpadding="0" cellspacing="0" width="100%" height="100%">
    <tr>
        <td colspan="2" height="154" valign="top">
                <div class="headermenu" id="topmenu">
                    <div class="head">
                        <div class="logo"></div>
                        <div class="logo_text"><?php echo $this->_var['shop_name']; ?>管理后台 <?php echo $this->_var['hhs_version']; ?></div>
                        <div></div>
                        <div class="uinfo">
                            <a href="javascript:;beforeSendMessage();">推送文章</a>
                            <a href="javascript:window.top.frames['main'].document.location.reload();window.top.frames['header-frame'].document.location.reload()"><?php echo $this->_var['lang']['refresh']; ?></a>
                            <a href="index.php?act=clear_cache" target="main"><?php echo $this->_var['lang']['clear_cache']; ?></a>
                            <a href="privilege.php?act=logout"><?php echo $this->_var['lang']['signout']; ?></a>
                        </div>
                    </div>
                    <ul>
                        <li><h3><a href="index.php?act=main" id="header_index" hidefocus="true" onClick="toggleMenu('index', 'index' ,0);doane(event);"><i class="iconfont">&#xe600;</i><?php echo $this->_var['lang']['admin_home']; ?></a></h3></li>
                        <?php $_from = $this->_var['menus']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'menu');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['menu']):
?>
                        <li>
                            <h3><a href="javascript:;" id="header_<?php echo $this->_var['k']; ?>" hidefocus="true" onClick="toggleMenu('<?php echo $this->_var['k']; ?>', '<?php echo $this->_var['k']; ?>' ,0);doane(event);"><i class="iconfont"><?php echo $this->_var['menu']['font']; ?></i><?php echo $this->_var['menu']['label']; ?></a></h3>
                        </li>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </ul>
                </div>
        </td>
    </tr>
    <tr>
        <td valign="top" width="180" class="menutd">
            <div id="leftmenu" class="menu">
                <div class="menu_top"></div>
                <ul id="menu_index" style="display:none">
                    <li><a href="index.php?act=main" hidefocus="true" target="main" class="tabon"><?php echo $this->_var['lang']['admin_home']; ?></a></li>
                    <li class="line"></li>
                    <li><a href="privilege.php?act=modif" hidefocus="true" target="main"><?php echo $this->_var['lang']['set_navigator']; ?></a></li>
                    <li class="line"></li>
                    <?php $_from = $this->_var['nav_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
                    <li><a href="<?php echo $this->_var['key']; ?>" hidefocus="true" target="main"><em onClick="menuNewwin(this)"></em><?php echo $this->_var['item']; ?></a></li>
                    <li class="line"></li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
                <?php $_from = $this->_var['menus']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'menu');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['menu']):
?>
                <?php if ($this->_var['menu']['children']): ?>
                <ul id="menu_<?php echo $this->_var['k']; ?>" style="display:none">
                    <?php $_from = $this->_var['menu']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');if (count($_from)):
    foreach ($_from AS $this->_var['child']):
?>
                    <li><a href="<?php echo $this->_var['child']['action']; ?>" hidefocus="true" target="main"><?php echo $this->_var['child']['label']; ?></a></li>
                    <li class="line"></li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
                <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </div>
            </td>
        <td valign="top" width="100%"><iframe src="index.php?act=main" id="main" name="main" width="99%" height="98%" frameborder="0" scrolling="yes"></iframe></td>
    </tr>
</table>
<script type="text/JavaScript">
    var headers = new Array('index'<?php $_from = $this->_var['menus']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'menu');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['menu']):
?>,'<?php echo $this->_var['k']; ?>'<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>);
    function switchheader(key) {
        if(!key || !$('header_' + key)) {
            return;
        }
        for(var k in top.headers) {
            if($('menu_' + headers[k])) {
                $('menu_' + headers[k]).style.display = headers[k] == key ? '' : 'none';
            }
        }
        var lis = $('topmenu').getElementsByTagName('li');
        for(var i = 0; i < lis.length; i++) {
            if(lis[i].className == 'navon') lis[i].className = '';
        }
        $('header_' + key).parentNode.parentNode.className = 'navon';
    }

    function toggleMenu(key, url, indexq) {
        menukey = key;
        switchheader(key);
        if(url) {
            var hrefs = $('menu_' + key).getElementsByTagName('a');
            for(var j = 0; j < hrefs.length; j++) {
                hrefs[j].className = j == indexq ? 'tabon' : '';
                if(hrefs[j].className =='tabon' ){
                    var tar_url = hrefs[j].getAttribute("href");
                    $('main').setAttribute("src",tar_url);
                }
            }
        }
    }
    function initCpMenus(menuContainerid) {
        var key = '', lasttabon1 = null, lasttabon2 = null, hrefs = $(menuContainerid).getElementsByTagName('a');
        for(var i = 0; i < hrefs.length; i++) {
            if(!hrefs[i].getAttribute('ajaxtarget')) hrefs[i].onclick = function() {
                if(menuContainerid != 'custommenu') {
                    var lis = $(menuContainerid).getElementsByTagName('li');
                    for(var k = 0; k < lis.length; k++) {
                        if(lis[k].firstChild && lis[k].firstChild.className != 'menulink') {
                            if(lis[k].firstChild.tagName != 'DIV') {
                                lis[k].firstChild.className = '';
                            } else {
                                var subid = lis[k].firstChild.getAttribute('sid');
                                if(subid) {
                                    var sublis = $(subid).getElementsByTagName('li');
                                    for(var ki = 0; ki < sublis.length; ki++) {
                                        if(sublis[ki].firstChild && sublis[ki].firstChild.className != 'menulink') {
                                            sublis[ki].firstChild.className = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if(this.className == '') this.className = menuContainerid == 'leftmenu' ? 'tabon' : '';
                }
            }
        }
        return key;
    }
    var header_key = initCpMenus('leftmenu');
    toggleMenu(header_key ? header_key : 'index');

    changeteamstatus();
    sendCancelOrder();
    function changeteamstatus(){
        Ajax.call('index.php?act=changeteamstatus','', changeteamstatusResponse, 'GET', 'JSON');
    }
    function changeteamstatusResponse(result){
        if(result.content)
            window.setTimeout("changeteamstatus()", 30*1000);   
    }

    function sendlatestnews(){
        Ajax.call('index.php?act=sendlatestnews','', sendlatestnewsResponse, 'GET', 'JSON');
    }
    function sendlatestnewsResponse(result){
        if(result.error)
        {
            alert(result.message);
        }
        else
        {
            window.setTimeout("sendlatestnews()", 30*1000); 
        }
    }
    function sendCancelOrder(){
        Ajax.call('index.php?act=cancel_order_remind','', sendCancelOrderResponse, 'GET', 'JSON');
    }
    function sendCancelOrderResponse(result){
        if(result.content)
        {
            window.setTimeout("sendCancelOrder()", 50*1000); 
        }
    }
    function beforeSendMessage()
    {
        if(confirm("确定要推送消息吗？"))
        {
            sendlatestnews();
            alert('安心的干活去吧');
        }
    }
</script>
</body>
</html>