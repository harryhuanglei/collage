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
<link href="<?php echo $this->_var['hhs_css_path']; ?>/index.css" rel="stylesheet" />
<link href="<?php echo $this->_var['hhs_css_path']; ?>/font-awesome.min.css" rel="stylesheet" />
<link href="<?php echo $this->_var['hhs_css_path']; ?>/swiper.min.css" rel="stylesheet" />
<link href="//at.alicdn.com/t/font_1469771107_0897112.css" rel="stylesheet" />
<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.js,haohaios.js,index.js,swiper.min.js,jquery.lazyload.js')); ?>
<link href="/js/dropload/dropload.min.css" rel="stylesheet" >
<script src="/js/dropload/dropload.min.js"></script>
</head><body id="index">
<div id="loading"><?php echo $this->_var['loading']; ?></div>
<div class="container" id="container" style="display:none;">
    <div class="topbox">
        <header class="index-header"> 
            <div class="city_txt"><a href="javascript:void(0);"><i class="iconfont icon-weizhi"></i><b id="show_index_cityname"><?php if ($this->_var['site_name'] == ''): ?>加载..<?php else: ?><?php echo $this->_var['site_name']; ?><?php endif; ?></b></a> </div>
            <div class="index-search-box">
                <form id="searchForm" name="searchForm" method="get" action="search.php" onSubmit="return checkSearchForm()">
                    <input name="keywords" id="keyword" type="text" class="index-search-input" placeholder="请输入查找的商品" onclick="openSearch();">
                    <a href="javascript:openSearch();" class="submit">搜索</a>
                </form>
            </div>
        </header>
    </div>
    <div class="city">
        <div class="city-title">
            <p class="city_tit_l"><b>选择地区</b></p>
            <p class="city_tit_r"><a href="javascript:void(0);" class="close-city"><img src="themes/haohainew/images/city_close.gif"></a></p>
        </div>
        <div class="city-content">
            <dl>
                <dt><a href="?site_id=1"<?php if ($this->_var['site_id'] == 1): ?> class="cur"<?php endif; ?>>中国</a></dt>
                <dd>&nbsp;</dd>
            </dl>
            <?php $_from = $this->_var['site_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'site');$this->_foreach['site'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['site']['total'] > 0):
    foreach ($_from AS $this->_var['site']):
        $this->_foreach['site']['iteration']++;
?>
            <dl>
                <dt><a href="?site_id=<?php echo $this->_var['site']['city_id']; ?>"<?php if ($this->_var['site']['city_id'] == $this->_var['site_id']): ?> class="cur"<?php endif; ?>><?php echo $this->_var['site']['region_name']; ?></a></dt>
                <dd> <?php $_from = $this->_var['site']['dis']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'dis');$this->_foreach['dis'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['dis']['total'] > 0):
    foreach ($_from AS $this->_var['dis']):
        $this->_foreach['dis']['iteration']++;
?> <a href="?site_id=<?php echo $this->_var['dis']['region_id']; ?>"<?php if ($this->_var['dis']['region_id'] == $this->_var['site_id']): ?> class="cur"<?php endif; ?>><?php echo $this->_var['dis']['region_name']; ?></a> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> </dd>
            </dl>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> </div>
    </div>
    <div class="city-bg"></div>
<script>
function openSearch(){
    $("#searchForm").submit();
}
function checkSearchForm(){
    var val = $("#searchForm input").val();
    return val.length > 0 ? true : false;
}
$(function () {
    H_login = {};
    H_login.openLogin = function(){
        $('.city_txt a').click(function(){
            $('.city').show();
            $('.city-bg').show();
        });
    };
    H_login.closeLogin = function(){
        $('.close-city').click(function(){
            $('.city').hide();
            $('.city-bg').hide();
        });
    };
    H_login.run = function () {
        this.closeLogin();
        this.openLogin();
    };
    H_login.run();
});
</script>
<script>
var Tday = new Array();
var daysms = 24 * 60 * 60 * 1000
var hoursms = 60 * 60 * 1000
var Secondms = 60 * 1000
var microsecond = 1000
var DifferHour = -1
var DifferMinute = -1
var DifferSecond = -1
function clock(key)
  {
   var time = new Date()
   var hour = time.getHours()
   var minute = time.getMinutes()
   var second = time.getSeconds()
   var timevalue = ""+((hour > 12) ? hour-12:hour)
   timevalue +=((minute < 10) ? ":0":":")+minute
   timevalue +=((second < 10) ? ":0":":")+second
   timevalue +=((hour >12 ) ? " PM":" AM")
   var convertHour = DifferHour
   var convertMinute = DifferMinute
   var convertSecond = DifferSecond
   var Diffms = Tday[key].getTime() - time.getTime()
   DifferHour = Math.floor(Diffms / daysms)
   Diffms -= DifferHour * daysms
   DifferMinute = Math.floor(Diffms / hoursms)
   Diffms -= DifferMinute * hoursms
   DifferSecond = Math.floor(Diffms / Secondms)
   Diffms -= DifferSecond * Secondms
   var dSecs = Math.floor(Diffms / microsecond)
  
   if(convertHour != DifferHour) a="<b>"+DifferHour+"</b>天";
   if(convertMinute != DifferMinute) b="<b>"+DifferMinute+"</b>时";
   if(convertSecond != DifferSecond) c="<b>"+DifferSecond+"</b>分"
     d="<b>"+dSecs+"</b>秒"
     if (DifferHour>0) {a=a}
     else {a=''}
   document.getElementById("leftTime"+key).innerHTML =a + b + c + d; //显示倒计时信息
 
  }
</script>
  <script>
   $(function(){
    $('.app-tips span').click(function(){
      $('.app-tips').hide();
    });
   });
</script>
    
    <div class="app-tips">微营销原生app体验更优!
      <a href="">点击下载</a>
      <span></span>
    </div>
    

    
    <div class="swiper-container" style="margin-top:56px;">
        <div class="swiper-wrapper">
		    <?php $_from = $this->_var['banner']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'banner_0_34450300_1536031468');if (count($_from)):
    foreach ($_from AS $this->_var['banner_0_34450300_1536031468']):
?>
            <div class="swiper-slide"><a href="<?php echo $this->_var['banner_0_34450300_1536031468']['ad_link']; ?>"><img src="<?php echo $this->_var['banner_0_34450300_1536031468']['ad_code']; ?>" width="100%"/></a></div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</div>
        <div class="swiper-pagination m1"></div>
    </div>
    <?php if ($this->_var['categories']): ?>
    <div class="index-menu">
	    <div class="swiper-wrapper">
		    <div class="swiper-slide">
            <?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat');$this->_foreach['cat'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['cat']['total'] > 0):
    foreach ($_from AS $this->_var['cat']):
        $this->_foreach['cat']['iteration']++;
?>
			<?php if (($this->_foreach['cat']['iteration'] - 1) % 10 == 0 && ($this->_foreach['cat']['iteration'] - 1) > 0): ?>
			</div>
			<div class="swiper-slide">
			<?php endif; ?>
			<a href="tuan.php?cid=<?php echo $this->_var['cat']['id']; ?>"><img src="<?php echo $this->_var['cat']['img']; ?>"><p><?php echo $this->_var['cat']['name']; ?></p></a>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</div>
		</div>
		<div class="swiper-pagination m2"></div>
    </div>
    <?php endif; ?>
    <div class="blank"></div>
    <div class="ads5">
		<?php $_from = get_advlist_position_name(首页6图,6); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ad');if (count($_from)):
    foreach ($_from AS $this->_var['ad']):
?>
		<li><a href="<?php echo $this->_var['ad']['url']; ?>"><img src="<?php echo $this->_var['ad']['image']; ?>"></a></li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>

	<?php $_from = get_advlist_position_name(首页抽奖广告,1); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ad');if (count($_from)):
    foreach ($_from AS $this->_var['ad']):
?>
	<div class="ads"><a href="<?php echo $this->_var['ad']['url']; ?>"><img src="<?php echo $this->_var['ad']['image']; ?>"></a></div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

    <div class="blank"></div>
	<!--script>
       function GetRTime(){
           var EndTime= new Date('2016/08/20 00:00:00');
           var NowTime = new Date();
           var t =EndTime.getTime() - NowTime.getTime();
           var d=Math.floor(t/1000/60/60/24);
           var h=Math.floor(t/1000/60/60%24);
           var m=Math.floor(t/1000/60%60);
           var s=Math.floor(t/1000%60);
    
           document.getElementById("t_d").innerHTML = d + "天";
           document.getElementById("t_h").innerHTML = h;
           document.getElementById("t_m").innerHTML = m;
           document.getElementById("t_s").innerHTML = s;
       }
       setInterval(GetRTime,0);
    </script-->
	<?php if ($this->_var['miao_list']): ?>
    <div class="xxms">
        <h3 class="mod_tit">
            <i class="iconfont icon-miaosha"></i>限时秒杀<span><a href="spike.php">查看更多></a></span>
        </h3>
        <ul>
            <?php $_from = $this->_var['miao_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['goods']):
?>
            <li>
			    <font class="timesbg"></font>
				<font class="times" id="leftTime<?php echo $this->_var['key']; ?>"><?php echo $this->_var['lang']['please_waiting']; ?></font>
                <a href="<?php echo $this->_var['goods']['url']; ?>&uid=<?php echo $this->_var['uid']; ?>">
                <img data-original="<?php echo $this->_var['goods']['thumb']; ?>" src="themes/haohainew/images/loading.gif" class="lazy">
                <p><?php echo $this->_var['goods']['name']; ?></p>
                <p class="price">¥<?php echo $this->_var['goods']['promote_price']; ?> <span><?php echo $this->_var['goods']['price_discount']; ?>折</span></p>
                </a>
            </li>
<script>
Tday[<?php echo $this->_var['key']; ?>] = new Date("<?php echo $this->_var['goods']['gmt_end_time']; ?>");  
window.setInterval(function()    
{clock(<?php echo $this->_var['key']; ?>);}, 1000);    
</script>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </ul>
    </div>
    <div class="blank"></div>
	<?php endif; ?>
	<?php if ($this->_var['tuan_list']): ?>
    <div class="good_list">
        <h3 class="mod_tit">
        <i class="iconfont icon-pintuan"></i>拼团专区<span><a href="tuan.php">查看更多></a></span>
        </h3>
        <div class="tuan_list"> 
            <?php $_from = $this->_var['tuan_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
            <div class="tuan_g">
                <div class="tuan_g_img">
				    <a href="<?php echo $this->_var['goods']['url']; ?>&uid=<?php echo $this->_var['uid']; ?>"> <img data-original="<?php echo $this->_var['goods']['little_img']; ?>" src="themes/haohainew/images/loading640x350.gif" class="lazy"> </a>
					<?php if ($this->_var['uid']): ?>
                    <?php if ($this->_var['goods']['collect']): ?>
                    <div class="like_click_button" data-id="<?php echo $this->_var['goods']['goods_id']; ?>"> <img src="themes/haohainew/images/is_liked.png" data-isLiked="1"> </div>
                    <?php else: ?>
                    <div class="like_click_button" data-id="<?php echo $this->_var['goods']['goods_id']; ?>"> <img src="themes/haohainew/images/no_liked.png" data-isLiked="0"> </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($this->_var['goods']['goods_number'] < 1): ?> <span class="sell_f"></span> <?php elseif ($this->_var['goods']['is_miao'] = 1 && $this->_var['goods']['promote_end_date'] < $this->_var['nowtime'] && $this->_var['goods']['promote_end_date']): ?> <span class="sell_o"></span> <?php endif; ?>
						<?php if ($this->_var['goods']['ts_a'] || $this->_var['goods']['ts_b'] || $this->_var['goods']['ts_c']): ?>
                        <div class="tuan_g_img_text">
                            <?php if ($this->_var['goods']['ts_a']): ?>
                            <div class="tuan_g_img_item">
                                <div class="tuan_g_img_round"></div>
                                <div class="tuan_img_text_border"><span><?php echo $this->_var['goods']['ts_a']; ?></span></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($this->_var['goods']['ts_b']): ?>
                            <div class="tuan_g_img_item">
                                <div class="tuan_g_img_round"></div>
                                <div class="tuan_img_text_border"><span><?php echo $this->_var['goods']['ts_b']; ?></span></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($this->_var['goods']['ts_c']): ?>
                            <div class="tuan_g_img_item">
                                <div class="tuan_g_img_round"></div>
                                <div class="tuan_img_text_border"><span><?php echo $this->_var['goods']['ts_c']; ?></span></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
				</div>
                <div class="tuan_g_info">
                    <p class="tuan_g_name"><?php echo $this->_var['goods']['goods_name']; ?></p>
					<p class="tuan_g_cx"><?php echo $this->_var['goods']['goods_brief']; ?></p>
                </div>
                <div class="tuan_g_core">
                    <p class="tuan_g_num"><?php echo $this->_var['goods']['team_num']; ?>人团</p>
                    <div class="line"></div>
                    <div class="tuan_g_price"><i>¥</i><?php echo $this->_var['goods']['team_price']; ?></div>
                    <del class="tuan_g_mprice"><i>￥</i><?php echo $this->_var['goods']['shop_price']; ?></del> <a href="<?php if ($this->_var['goods']['goods_number'] > 0): ?><?php echo $this->_var['goods']['url']; ?>&uid=<?php echo $this->_var['uid']; ?><?php else: ?>javascript:void(0);<?php endif; ?>">
                    <div class="tuan_g_btn">立即开团</div>
                    </a> </div>
                <img src="themes/haohainew/images/shade.png" style="display: block;width: 100%"> </div>
            
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
        </div>
    </div>
    
    <div class="blank"></div>
	<?php endif; ?>

    <!--div class="good_list">
        <h3 class="mod_tit"><i class="iconfont icon-qingling"></i>0元专区<span><a href="zero.php">查看更多></a></span></h2>

        <?php $_from = $this->_var['zero_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_list']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_list']['iteration']++;
?>

        <dl class="list_A">

            <dt><a href="<?php echo $this->_var['goods']['url']; ?>&uid=<?php echo $this->_var['uid']; ?>"><img data-original="<?php echo $this->_var['goods']['goods_thumb']; ?>" src="themes/haohainew/images/loading.gif" class="lazy"></a></dt>

            <dd>

                <p><i  class="tips">0元专区</i></p>

                <p class="tit"><a href="<?php echo $this->_var['goods']['url']; ?>&uid=<?php echo $this->_var['uid']; ?>"><?php echo $this->_var['goods']['goods_name']; ?></a></p>

                <p class="brief"><?php echo $this->_var['goods']['goods_brief']; ?></p>

                <p><font class="price">¥<b>0</b></font><del>¥<?php echo $this->_var['goods']['market_price']; ?></del></p>

            </dd>

        </dl>

        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

    </div>
    
    <div class="blank"></div-->
	<?php if ($this->_var['tejia_list']): ?>
    <div class="good_list">
        <h3 class="mod_tit">
        <i class="iconfont icon-jingpin"></i>精品商城<span><a href="mall.php">查看更多></a></span>
        </h2>
        <ul class="list_B">
            
            <?php $_from = $this->_var['tejia_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['goods_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['goods_list']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['goods_list']['iteration']++;
?>
            <li> <a href="<?php echo $this->_var['goods']['url']; ?>&uid=<?php echo $this->_var['uid']; ?>"><img goods_id="<?php echo $this->_var['goods']['goods_id']; ?>" data-original="<?php echo $this->_var['goods']['goods_thumb']; ?>" src="themes/haohainew/images/loading.gif" class="lazy"></a>
                <p class="tit"><a href="<?php echo $this->_var['goods']['url']; ?>&uid=<?php echo $this->_var['uid']; ?>"><?php echo $this->_var['goods']['goods_name']; ?></a></p>
                <p>
				    <font class="price">¥<b class="shop_price"><?php echo $this->_var['goods']['shop_price']; ?></b></font> 
                    
                     <?php if ($this->_var['goods']['attr']): ?>
                     <a class="mai iproduct_<?php echo $this->_var['goods']['goods_id']; ?>" id="iproduct_<?php echo $this->_var['goods']['goods_id']; ?>" href="javascript:addToCart(<?php echo $this->_var['goods']['goods_id']; ?>,0,1,0,0,1)">买</a>
                     <?php else: ?>
					 <?php if ($this->_var['goods']['goods_number'] > 0): ?>
                     <a class="mai" id="iproduct_<?php echo $this->_var['goods']['goods_id']; ?>" href="javascript:addToCart(<?php echo $this->_var['goods']['goods_id']; ?>,0,1,0,0,1)">买</a>
					 <?php else: ?>
                     <a class="mai hui" href="javascript:;">缺货</a>
                     <?php endif; ?>
                     <?php endif; ?>
                     
				</p>
            </li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            
        </ul>
    </div>
	<?php endif; ?>
</div>
<div class="blank"></div>
<?php echo $this->fetch('library/footer.lbi'); ?>
<div class="back-top"><span uigs="wap_to_top">顶部</span></div>
<script>
	window.onload=function(){
		
		send_team_info();
		
		send_bouns();
		
		document.getElementById('loading').style.display='none';

		document.getElementById('container').style.display='';

		var swiper = new Swiper('.swiper-container', {

	        pagination: '.m1',

	        paginationClickable: true,

	        spaceBetween: 30,

	        centeredSlides: true,

	        autoplay: 2500,

	        autoplayDisableOnInteraction: false

	    });
		var swiper = new Swiper('.index-menu', {

	        pagination: '.m2',

	        paginationClickable: true,

	        spaceBetween: 30,

	        centeredSlides: true,

	        autoplayDisableOnInteraction: false

	    });
		$("img.lazy").lazyload({
                effect: "fadeIn",
                threshold : 200
        });
        $("img.lazy:eq(0)").attr('src',$("img.lazy:eq(0)").attr('data-original'));

      var user_id = <?php echo $this->_var['uid']; ?>;

    $(".like_click_button").on("click", function(e) {

        e.preventDefault();
        var goodsId = $(this).attr("data-id");
        var img = $(this).find("img");
        if (img.attr("data-isLiked") == "1") {
            $.get('user.php', {
                act: "del_collection",
                collection_id: goodsId,
                user_id: user_id
            }).done(function(e) {
                img.attr("src", "themes/haohainew/images/no_liked.png");
                img.attr("data-isLiked", 0)
            })
        } else {
            $.get('user.php', {
                act: "collect",
                id: goodsId,
                user_id: user_id
            }).done(function(e) {
                img.attr("src", "themes/haohainew/images/is_liked.png");
                img.attr("data-isLiked", 1)
            })

        }
    })
	}
	
var btn_buy = "<?php echo $this->_var['lang']['btn_buy']; ?>";
var btn_add_to_cart = "<?php echo $this->_var['lang']['btn_add_to_cart']; ?>";
var is_cancel = "<?php echo $this->_var['lang']['is_cancel']; ?>";
var select_spe = "<?php echo $this->_var['lang']['select_spe']; ?>";
</script> 
<script type="text/javascript">
function getElementLeft(element){
　　　　var actualLeft = element.offsetLeft;
　　　　var current = element.offsetParent;
        
　　　　while ( current !== null ){
　　　　　　actualLeft += current.offsetLeft;
　　　　　　current = current.offsetParent;

　　　　}

　　　　return actualLeft;
　　}

function getElementTop(element){
　　　　var actualTop = element.offsetTop;
　　　　var current = element.offsetParent;

　　　　while (current !== null){
　　　　　　actualTop += current.offsetTop;
　　　　　　current = current.offsetParent;
　　　　}

　　　　return actualTop;
　　}　　

    var Cart = {
      id: 'cat',
      addProduct: function(cpid, num, t ) {
        //添加商品
        var cat =document.getElementById('cat');  
        var catLeft=getElementLeft(cat);
        var catTop=getElementTop(cat);
        var sTop=document.body.scrollTop+document.documentElement.scrollTop;


        var op = $("[id=iproduct_"+cpid+"]").parents("li").find("img");
        var goods_id = $(op).attr("goods_id");

        if(op.length>0) {
            var np = op.clone().css({"position":"absolute", "top": op.offset().top, "left": op.offset().left, width: 50, height:50, "z-index": 999999999}).show();
            np.appendTo("body").animate({top:  catTop + sTop , left: $("#cat").offset().left +30 , width: 20, height:20}, {duration: 1000,
                    callback:function(){}, complete: function(){np.remove();addToCart(goods_id,0,1 ,0,0,1 );} });
        }
       }
    }

    $(function() {
        $('[id^=iproduct_]').click(function() {
            var id = $(this).attr("id");
            var tmp = id.split('_');
            var goods_id = tmp[1];

            //var cpid = this.id.replace('iproduct_'+goods_id,goods_id);

             Cart.addProduct(goods_id, 1, 0  );

            return false;
        });
     });
</script>
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
		<?php if ($this->_var['site_id'] == ''): ?>
	    wx.checkJsApi({
	    	
	        jsApiList: [
	            'getLocation'
	        ],
	        success: function (res) {
	             //alert(JSON.stringify(res));
	            // alert(JSON.stringify(res.checkResult.getLocation));
	            if (res.checkResult.getLocation == false) {
	                alert('你的微信版本太低，不支持微信JS接口，请升级到最新的微信版本！');
	                return;
	            }
	        }
	    });
	    wx.getLocation({
	        success: function (res) {
	            var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
	            var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
	            var speed = res.speed; // 速度，以米/每秒计
	            var accuracy = res.accuracy; // 位置精度
	            $.ajax({
	                type:"post",//请求类型
	                url:"lbs.php",//服务器页面地址
                    data:"act=save_location_baidu&lat="+latitude+"&lng="+longitude,
	                dataType:"json",//服务器返回结果类型(可有可无)
	                error:function(){//错误处理函数(可有可无)
	                    //alert("ajax出错啦");
	                },
	                success:function(data){
	                    if(data.error==1){
	                        //alert('错误'+data.message);
	                    }else{
							document.getElementById('show_index_cityname').innerHTML = data.city_name;
	                    	//document.getElementById('loading').style.display='none';
	                		
	                    }
	                }
	            });
	        },
	        cancel: function (res) {
	            alert('用户拒绝授权获取地理位置');
	        }
	    });		
		
		<?php endif; ?>  

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


//团购提醒
	function send_team_info()
	{
		$.ajax({
            type:"post",//请求类型
            url:"index.php",//服务器页面地址
            data:"act=send_team_info",
            dataType:"json",//服务器返回结果类型(可有可无)
            error:function(){//错误处理函数(可有可无)
                //alert("ajax出错啦");
            },
            success:function(data){
                
            }
        });
		//setTimeout("send_team_info()", 20000);
	}
function send_bouns(){
		$.ajax({
            type:"post",//请求类型
            url:"index.php",//服务器页面地址
            data:"act=send_bouns&share_status= 1",
            dataType:"json",//服务器返回结果类型(可有可无)
            error:function(){//错误处理函数(可有可无)
                //alert("ajax出错啦");
            },
            success:function(data){
                
            }
        });
		//setTimeout("send_bouns()",20000);
	}


</script>
</body>
</html>
