<!doctype html>
<html lang="zh-CN">
<head>
<meta name="Generator" content="haohaipt X_7.2" />
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<meta name="format-detection" content="telephone=no">
<title>商品分类</title>
<link rel="shortcut icon" href="favicon.ico" />
<link href="<?php echo $this->_var['hhs_css_path']; ?>/style.css" rel="stylesheet" />
<link href="<?php echo $this->_var['hhs_css_path']; ?>/catall.css" rel="stylesheet" />
<link href="<?php echo $this->_var['hhs_css_path']; ?>/font-awesome.min.css" rel="stylesheet" />
<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.js,haohaios.js,jquery.nicescroll.min.js,catall.js')); ?>
<script type="text/javascript">
    var process_request = "<?php echo $this->_var['lang']['process_request']; ?>";
    function checkSearchForm()
    {
        if(document.getElementById('keyword').value)
        {
            return true;
        }
        else
        {
            alert("<?php echo $this->_var['lang']['no_keywords']; ?>");
            return false;
        }
    }
</script>
</head>
<body id='catall' class="catall">
<div class="classification">
    <div class="searchbox">
        <div class="search">
            <form id="searchForm" name="searchForm" method="get" action="search.php" onSubmit="return checkSearchForm()">
                <!--input type="search" name="keywords" id="keyword" style="max-width:90%" class="ud" placeholder="搜索商品" required>
                <input type="submit" value="" class="submit" /-->
                <input name="keywords" id="keyword" type="text" class="index-search-input" placeholder="请输入查找的商品" onclick="openSearch();">
                <a href="javascript:openSearch();" class="submit">搜索</a>
            </form>
        </div>
    </div>
    
    <div class="leftmenu">
        <ul id="cat_menu">
            <?php $_from = $this->_var['pcat_array']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'category');$this->_foreach['category'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['category']['total'] > 0):
    foreach ($_from AS $this->_var['category']):
        $this->_foreach['category']['iteration']++;
?>
            <li id="cat_<?php echo $this->_var['category']['id']; ?>"<?php if (($this->_foreach['category']['iteration'] - 1) == 0): ?> class="cur"<?php endif; ?>><?php echo $this->_var['category']['name']; ?></li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    <div id="cat_list" class="content"> <?php $_from = $this->_var['arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'category');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['category']):
        $this->_foreach['name']['iteration']++;
?>
        <dl id="c_<?php echo $this->_var['key']; ?>"<?php if (($this->_foreach['name']['iteration'] - 1) != 0): ?> style="display:none;"<?php endif; ?>>
            <?php $_from = $this->_var['category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat');if (count($_from)):
    foreach ($_from AS $this->_var['cat']):
?>
            <?php if ($this->_var['cat']['is_level']): ?>
            <?php $_from = $this->_var['cat']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat_two');if (count($_from)):
    foreach ($_from AS $this->_var['cat_two']):
?>
            <dt><a href="mall.php?cid=<?php echo $this->_var['cat_two']['id']; ?>"><?php echo $this->_var['cat_two']['name']; ?><i></i></a><span><a href="mall.php?cid=<?php echo $this->_var['cat_two']['id']; ?>">查看全部 ></a></span></dt>
            <dd> <?php $_from = $this->_var['cat_two']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat_three');if (count($_from)):
    foreach ($_from AS $this->_var['cat_three']):
?> <a href="mall.php?cid=<?php echo $this->_var['cat_three']['id']; ?>"><img src="<?php if ($this->_var['cat_three']['cat_img']): ?><?php echo $this->_var['site_url']; ?><?php echo $this->_var['cat_three']['cat_img']; ?><?php else: ?><?php echo $this->_var['site_url']; ?>images/no_picture.gif<?php endif; ?>"><span><?php echo $this->_var['cat_three']['name']; ?></span></a> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> </dd>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <?php else: ?>
            <dt><a href="mall.php?cid=<?php echo $this->_var['cat']['id']; ?>"><?php echo $this->_var['cat']['name']; ?><i></i></a><span><a href="mall.php?cid=<?php echo $this->_var['cat']['id']; ?>">查看全部 ></a></span></dt>
            <dd> <?php $_from = $this->_var['cat']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat_two');$this->_foreach['cat_two'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['cat_two']['total'] > 0):
    foreach ($_from AS $this->_var['cat_two']):
        $this->_foreach['cat_two']['iteration']++;
?> <a href="mall.php?cid=<?php echo $this->_var['cat_two']['id']; ?>"><img src="<?php if ($this->_var['cat_two']['cat_img']): ?><?php echo $this->_var['site_url']; ?><?php echo $this->_var['cat_two']['cat_img']; ?><?php else: ?><?php echo $this->_var['site_url']; ?>images/no_picture.jpg<?php endif; ?>"><span><?php echo $this->_var['cat_two']['name']; ?></span></a> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> </dd>
            <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <?php $_from = $this->_var['category']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat');if (count($_from)):
    foreach ($_from AS $this->_var['cat']):
?>
            <div class="brand_list">
            <h3>热门品牌</h3>
            <ul>
            <?php $_from = $this->_var['cat']['brands']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'brands');if (count($_from)):
    foreach ($_from AS $this->_var['brands']):
?>
            <li><a href="<?php echo $this->_var['brands']['url']; ?>"><?php if ($this->_var['brands']['brand_logo']): ?><img src="data/brandlogo/<?php echo $this->_var['brands']['brand_logo']; ?>" alt="<?php echo $this->_var['brands']['brand_name']; ?>" /><?php else: ?><p><?php echo $this->_var['brands']['brand_name']; ?></p><?php endif; ?></a></li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </dl>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>
</div>
<?php echo $this->fetch('library/footer.lbi'); ?>
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script> 
<script language="javascript" type="text/javascript">
    wx.config({

        debug: false,//这里是开启测试，如果设置为true，则打开每个步骤，都会有提示，是否成功或者失败

        appId: '<?php echo $this->_var['appid']; ?>',

        timestamp: '<?php echo $this->_var['timestamp']; ?>',//这个一定要与上面的php代码里的一样。

        nonceStr: '<?php echo $this->_var['timestamp']; ?>',//这个一定要与上面的php代码里的一样。

        signature: '<?php echo $this->_var['signature']; ?>',

        jsApiList: [

          // 所有要调用的 API 都要加到这个列表中

            'onMenuShareTimeline',

            'onMenuShareAppMessage',

            'onMenuShareQQ',

            'onMenuShareWeibo',

            'checkJsApi',

            'openLocation',

            'getLocation'

        ]

    });

    

    var title="<?php echo $this->_var['title']; ?>";

    var link= "<?php echo $this->_var['link']; ?>";

    var imgUrl="<?php echo $this->_var['imgUrl']; ?>";

    var desc= "<?php echo $this->_var['desc']; ?>";

    wx.ready(function () {

        wx.onMenuShareTimeline({//朋友圈

            title: title, // 分享标题

            link: link, // 分享链接

            imgUrl: imgUrl, // 分享图标

            success: function () { 

                // 用户确认分享后执行的回调函数

                statis(2,1);

            },

            cancel: function () { 

                // 用户取消分享后执行的回调函数

                statis(2,2);

            }

        });

        wx.onMenuShareAppMessage({//好友

            title: title, // 分享标题

            desc: desc, // 

            link: link, // 分享链接

            imgUrl: imgUrl, // 分享图标

            type: '', // 分享类型,music、video或link，不填默认为link

            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空

            success: function () { 

                // 用户确认分享后执行的回调函数

                statis(1,1);    

            },

            cancel: function () { 

                // 用户取消分享后执行的回调函数

                statis(1,2);

            }

        });

      

        wx.onMenuShareQQ({

            title: title, // 分享标题

            desc: desc, // 分享描述

            link: link, // 分享链接

            imgUrl: imgUrl, // 分享图标

            success: function () { 

               // 用户确认分享后执行的回调函数

                statis(4,1);

            },

            cancel: function () { 

               // 用户取消分享后执行的回调函数

                statis(4,2);

            }

        });

        wx.onMenuShareWeibo({

            title: title, // 分享标题

            desc: desc, // 分享描述

            link: link, // 分享链接

            imgUrl: imgUrl, // 分享图标

            success: function () { 

               // 用户确认分享后执行的回调函数

                statis(3,1);

            },

            cancel: function () { 

                // 用户取消分享后执行的回调函数

                statis(3,2);

            }

        });      

    });

    function statis(share_type,share_status){

        $.ajax({

            type:"post",//请求类型

            url:"share.php",//服务器页面地址

            data:"act=link&share_status="+share_status+"&share_type="+share_type+"&link_url=<?php echo $this->_var['link2']; ?>",

            dataType:"json",//服务器返回结果类型(可有可无)

            error:function(){//错误处理函数(可有可无)

                //alert("ajax出错啦");

            },
            success:function(data){
           }

        });
    }
</script>

<script type="text/javascript">
    function openSearch(){
        $("#searchForm").submit();
    }
    function checkSearchForm(){
        var val = $("#searchForm input").val();
        return val.length > 0 ? true : false;
    }
</script>
</body>
</html>
