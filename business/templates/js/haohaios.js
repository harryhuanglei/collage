var Ajax=new Object;
Ajax.call = function(url,args,_callback,method,responseType,asyn){
	var result;
	asyn = asyn === false?false:true;
	responseType = responseType.toLowerCase();
	method = method.toLowerCase() == 'post'?'post':'get';
	if(args == undefined) {
		args = {};
	} else {
		if(args.substr(0,1) === '&'){
			args = args.substr(1,args.length-1);
		}
		try{
			args=args.replace(/&/g,'\',').replace(/=/g,':\'');
			eval('args = {'+args+'\'}');
		}catch(ex){
			alert(ex.message);
		}
	}

	if(_callback == null){
		if(asyn !== false){
			throw 'Ajax.call error: the param \'asyn\' is undefind!';
			return false;
		}
		_callback = function(rs){
			result = rs;
		};
	}

		$.ajax({
			url:url,
			data:args,
			type:method,
			dataType:responseType,
			async:asyn,
			success:_callback
		});
	if(result)
		return result;
}


if ( ! Object.prototype.toJSONString) {
    Array.prototype.toJSONString = function () {
        var a = ['['], // The array holding the text fragments.
            b,         // A boolean indicating that a comma is required.
            i,         // Loop counter.
            l = this.length,
            v;         // The value to be stringified.

        function p(s) {

            // p accumulates text fragments in an array. It inserts a comma before all
            // except the first fragment.

            if (b) {
              a.push(',');
            }
            a.push(s);
            b = true;
        }

        // For each value in this array...

        for (i = 0; i < l; i ++) {
            v = this[i];
            switch (typeof v) {

            // Values without a JSON representation are ignored.

            case 'undefined':
            case 'function':
            case 'unknown':
                break;

            // Serialize a JavaScript object value. Ignore objects thats lack the
            // toJSONString method. Due to a specification error in ECMAScript,
            // typeof null is 'object', so watch out for that case.

            case 'object':
                if (v) {
                    if (typeof v.toJSONString === 'function') {
                        p(v.toJSONString());
                    }
                } else {
                    p("null");
                }
                break;

            // Otherwise, serialize the value.

            default:
                p(v.toJSONString());
            }
        }

        // Join all of the fragments together and return.

        a.push(']');
        return a.join('');
    };

    Boolean.prototype.toJSONString = function () {
        return String(this);
    };

    Date.prototype.toJSONString = function () {

        // Ultimately, this method will be equivalent to the date.toISOString method.

        function f(n) {

            // Format integers to have at least two digits.

            return n < 10 ? '0' + n : n;
        }

        return '"' + this.getFullYear() + '-' +
                f(this.getMonth() + 1) + '-' +
                f(this.getDate()) + 'T' +
                f(this.getHours()) + ':' +
                f(this.getMinutes()) + ':' +
                f(this.getSeconds()) + '"';
    };

    Number.prototype.toJSONString = function () {

        // JSON numbers must be finite. Encode non-finite numbers as null.

        return isFinite(this) ? String(this) : "null";
    };
/*
    Object.prototype.toJSONString = function () {
		//alert(this.toString());
		if(typeof this !== 'function') {
			var a = ['{'],  // The array holding the text fragments.
				b,          // A boolean indicating that a comma is required.
				k,          // The current key.
				v;          // The current value.

			function p(s) {

				// p accumulates text fragment pairs in an array. It inserts a comma before all
				// except the first fragment pair.

				if (b) {
					a.push(',');
				}
				a.push(k.toJSONString(), ':', s);
				b = true;
			}

			// Iterate through all of the keys in the object, ignoring the proto chain.

			for (k in this) {
				if (this.hasOwnProperty(k)) {
					v = this[k];
					switch (typeof v) {

					// Values without a JSON representation are ignored.

					case 'undefined':
					case 'function':
					case 'unknown':
						break;

					// Serialize a JavaScript object value. Ignore objects that lack the
					// toJSONString method. Due to a specification error in ECMAScript,
					// typeof null is 'object', so watch out for that case.

					case 'object':
						if (v) {
							if (typeof v.toJSONString === 'function') {
								p(v.toJSONString());
							}
						} else {
							p("null");
						}
						break;
					default:
						p(v.toJSONString());
					}
				}
			}

			  // Join all of the fragments together and return.

			a.push('}');
			return a.join('');
		}else{
			
		}
    };
*/


/*
*	replace Object.prototype.toJSONString()
*	2009/04/17/11:29:38
*/

function obj2str(o){
    var r = [];
    if(typeof o =="string") return "\""+o.replace(/([\'\"\\])/g,"\\$1").replace(/(\n)/g,"\\n").replace(/(\r)/g,"\\r").replace(/(\t)/g,"\\t")+"\"";
    if(typeof o =="undefined") return "undefined";
    if(typeof o == "object"){
        if(o===null) return "null";
        else if(!o.sort){
            for(var i in o)
                r.push("\""+i+"\""+":"+obj2str(o[i]));
            r="{"+r.join()+"}";
        }else{
            for(var i =0;i<o.length;i++)
                r.push(obj2str(o[i]));
            r="["+r.join()+"]";
        }
        return r;
    }
    return o.toString();
}

    (function (s) {

        // Augment String.prototype. We do this in an immediate anonymous function to
        // avoid defining global variables.

        // m is a table of character substitutions.

        var m = {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        };

        s.parseJSON = function (filter) {

            // Parsing happens in three stages. In the first stage, we run the text against
            // a regular expression which looks for non-JSON characters. We are especially
            // concerned with '()' and 'new' because they can cause invocation, and '='
            // because it can cause mutation. But just to be safe, we will reject all
            // unexpected characters.

            try {
                if (/^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/.
                        test(this)) {

                    // In the second stage we use the eval function to compile the text into a
                    // JavaScript structure. The '{' operator is subject to a syntactic ambiguity
                    // in JavaScript: it can begin a block or an object literal. We wrap the text
                    // in parens to eliminate the ambiguity.

                    var j = eval('(' + this + ')');

                    // In the optional third stage, we recursively walk the new structure, passing
                    // each name/value pair to a filter function for possible transformation.

                    if (typeof filter === 'function') {

                        function walk(k, v) {
                            if (v && typeof v === 'object') {
                                for (var i in v) {
                                    if (v.hasOwnProperty(i)) {
                                        v[i] = walk(i, v[i]);
                                    }
                                }
                            }
                            return filter(k, v);
                        }

                        j = walk('', j);
                    }
                    return j;
                }
            } catch (e) {

            // Fall through if the regexp test fails.

            }
            throw new SyntaxError("parseJSON");
        };

        s.toJSONString = function () {

          // If the string contains no control characters, no quote characters, and no
          // backslash characters, then we can simply slap some quotes around it.
          // Otherwise we must also replace the offending characters with safe
          // sequences.

          // add by weberliu @ 2007-4-2
          var _self = this.replace("&", "%26");

          if (/["\\\x00-\x1f]/.test(this)) {
              return '"' + _self.replace(/([\x00-\x1f\\"])/g, function(a, b) {
                  var c = m[b];
                  if (c) {
                      return c;
                  }
                  c = b.charCodeAt();
                  return '\\u00' +
                      Math.floor(c / 16).toString(16) +
                      (c % 16).toString(16);
              }) + '"';
          }
          return '"' + _self + '"';
        };
    })(String.prototype);
}

$(function(){
	//顶部菜单
	$(".quick-menu dl").hover(function() {
		$(this).addClass("hover");
	},
	function() {
		$(this).removeClass("hover");
	});
	$(".head-user-menu dl").hover(function() {
		$(this).addClass("hover");
	},
	function() {
		$(this).removeClass("hover");
	});
	//左侧分类菜单
	$(".category ul.menu").find("li").each(
		function() {
			$(this).hover(
				function() {
				    var cat_id = $(this).attr("cat_id");
					var menu = $(this).find("div[cat_menu_id='"+cat_id+"']");
					menu.show();
					$(this).addClass("hover");					
					var menu_height = menu.height();
					if (menu_height < 60) menu.height(80);
					menu_height = menu.height();
					var li_top = $(this).position().top;
					$(menu).css("top",-li_top + 38);
				},
				function() {
					$(this).removeClass("hover");
				    var cat_id = $(this).attr("cat_id");
					$(this).find("div[cat_menu_id='"+cat_id+"']").hide();
				}
			);
		}
	);
});

function setTab(name,cursel,n)
{
    for(i=1;i<=n;i++)
	{
        var menu=document.getElementById(name+i);
        var con=document.getElementById("con_"+name+"_"+i);
        menu.className=i==cursel?"hover":"";
        con.style.display=i==cursel?"block":"none";
		//document.getElementById('sel_tab').value= 'ten'+cursel;
    }
}

/* *
 * 添加商品到购物车 
 */
function addToCart(goodsId, parentId, is_ajax,rec_type,team_sign,type)
{
	  
	/* */ 
  var goods        = new Object();
  var spec_arr     = new Array();
  var fittings_arr = new Array();
  var number       = 1;
  var formBuy      = document.forms['HHS_FORMBUY'];
  var quick		   = 0;

  // 检查是否有商品规格 
  if (formBuy)
  {
    spec_arr = getSelectedAttributes(formBuy);

    if (formBuy.elements['number'])
    {
      number = formBuy.elements['number'].value;
    }

	quick = 1;
  }

  goods.quick    = quick;
  goods.spec     = spec_arr;
  goods.goods_id = goodsId;
  goods.number   = number;
  goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);
  goods.rec_type   = (typeof(rec_type) == "undefined") ? 0 : parseInt(rec_type);
  goods.team_sign   = (typeof(team_sign) == "undefined") ? 0 : parseInt(team_sign);
  goods.type   = (typeof(type) == "undefined") ? 0 : parseInt(type);
  
  if((typeof(is_ajax) == "undefined"))
  {
	  is_ajax=0;
  }
  
  // if(is_ajax == 1){
  if(goods.rec_type == 0){
	Ajax.call('flows.php?step=add_to_cart', 'goods=' + obj2str(goods), addToCartResponse, 'POST', 'JSON');
  }else{
	Ajax.call('flow.php?step=add_to_cart', 'goods=' + obj2str(goods), addToCartResponse, 'POST', 'JSON');
  }
}

/**
 * 获得选定的商品属性
 */
function getSelectedAttributes(formBuy)
{
  var spec_arr = new Array();
  var j = 0;

  for (i = 0; i < formBuy.elements.length; i ++ )
  {
    var prefix = formBuy.elements[i].name.substr(0, 5);

    if (prefix == 'spec_' && (
      ((formBuy.elements[i].type == 'radio' || formBuy.elements[i].type == 'checkbox') && formBuy.elements[i].checked) ||
      formBuy.elements[i].tagName == 'SELECT'))
    {
      spec_arr[j] = formBuy.elements[i].value;
      j++ ;
    }
  }

  return spec_arr;
}

/* *
 * 处理添加商品到购物车的反馈信息
 */
function addToCartResponse_ajax(result)
{
  if (result.error > 0)
  {
    // 如果需要缺货登记，跳转
    if (result.error == 2)
    {
      if (confirm(result.message))
      {
        location.href = 'user.php?act=add_booking&id=' + result.goods_id + '&spec=' + result.product_spec;
      }
    }
    // 没选规格，弹出属性选择框
    else if (result.error == 6)
    {
      //alert('此商品为多属性商品，请到详情页购买');
      openSpeDiv(result.message, result.goods_id, result.parent);
    }
    else
    {
      alert(result.message);
    }
  }
  else
  {
    var cartInfo = document.getElementById('HHS_CARTINFO');
    var cart_url = 'flow.php?step=checkout';
    if (cartInfo)
    {
      cartInfo.innerHTML = result.content;
    }
	opencartDiv(result.shop_price,result.goods_name,result.goods_thumb,result.goods_brief,result.goods_id,result.goods_price,result.goods_number);
  }
}

/* *
 * 处理添加商品到购物车的反馈信息
 */
function addToCartResponse(result)
{
  if (result.error > 0)
  {
    // 如果需要缺货登记，跳转
    if (result.error == 2)
    {
      alert(result.message);
      return;
      if (confirm(result.message))
      {
        location.href = 'user.php?act=add_booking&id=' + result.goods_id + '&spec=' + result.product_spec;
      }
    }
    // 没选规格，弹出属性选择框
    else if (result.error == 6)
    {
      getGoodsMeta(result.goods_id);
      //alert('此商品为多属性商品，请到详情页购买');
      openSpeDiv(result.message, result.goods_id, result.parent);
    }
    else if (result.error == 7)
    {
       if(confirm(String(result.message) )){
    	   window.location=result.url;
       }else{
    	   window.location=result.url2;
       }
    }
    else if (result.error == 8)
    {
       if(confirm(String(result.message))){
    	   window.location=result.url;
       }
    }
    else if (result.error == 3)
    {
      alert(result.message);
      location.href = result.url;
    }
    else
    {
      alert(result.message);
    }
  }
  else
  {
    var cartInfo = document.getElementById('HHS_CARTINFO');
    var cart_url = 'flow.php?step=checkout';
    if (cartInfo)
    {
      cartInfo.innerHTML = result.content;
    }

    if(result.rec_type !=5)
    {
      var cart_url = 'flows.php?step=cart';
      
    }
    

    if(result.type){
      if(result.message !='')
        alert(result.message)
    }else{
      location.href = cart_url;
    }
	  
  }
}

/* *
 * 添加商品到收藏夹
 */
function collect(goodsId)
{
  Ajax.call('user.php?act=collect', 'id=' + goodsId, collectResponse, 'GET', 'JSON');
}

/* *
 * 处理收藏商品的反馈信息
 */
function collectResponse(result)
{
  alert(result.message);
}

/* *
 * 添加店铺到收藏夹
 */
function collect_store(goodsId)
{
  Ajax.call('user.php?act=collect_store', 'id=' + goodsId, collect_storeResponse, 'GET', 'JSON');
}

/* *
 * 处理收藏店铺的反馈信息
 */
function collect_storeResponse(result)
{
	if(result.error == 1)
	{
		alert(result.message);
		location.href='user.php';	
	}
	else
	{
		alert(result.message);
	}
  
}

/* *
 * 处理会员登录的反馈信息
 */
function signInResponse(result)
{
  toggleLoader(false);

  var done    = result.substr(0, 1);
  var content = result.substr(2);

  if (done == 1)
  {
    document.getElementById('member-zone').innerHTML = content;
  }
  else
  {
    alert(content);
  }
}

/* *
 * 评论的翻页函数
 */
function gotoPage(page, id, type)
{
  Ajax.call('comment.php?act=gotopage', 'page=' + page + '&id=' + id + '&type=' + type, gotoPageResponse, 'GET', 'JSON');
}

function gotoPageResponse(result)
{
  document.getElementById("HHS_COMMENT").innerHTML = result.content;
}

/* *
 * 商品购买记录的翻页函数
 */
function gotoBuyPage(page, id)
{
  Ajax.call('goods.php?act=gotopage', 'page=' + page + '&id=' + id, gotoBuyPageResponse, 'GET', 'JSON');
}

function gotoBuyPageResponse(result)
{
  document.getElementById("HHS_BOUGHT").innerHTML = result.result;
}

/* *
 * 取得格式化后的价格
 * @param : float price
 */
function getFormatedPrice(price)
{
  if (currencyFormat.indexOf("%s") > - 1)
  {
    return currencyFormat.replace('%s', advFormatNumber(price, 2));
  }
  else if (currencyFormat.indexOf("%d") > - 1)
  {
    return currencyFormat.replace('%d', advFormatNumber(price, 0));
  }
  else
  {
    return price;
  }
}

/* *
 * 夺宝奇兵会员出价
 */

function bid(step)
{
  var price = '';
  var msg   = '';
  if (step != - 1)
  {
    var frm = document.forms['formBid'];
    price   = frm.elements['price'].value;
    id = frm.elements['snatch_id'].value;
    if (price.length == 0)
    {
      msg += price_not_null + '\n';
    }
    else
    {
      var reg = /^[\.0-9]+/;
      if ( ! reg.test(price))
      {
        msg += price_not_number + '\n';
      }
    }
  }
  else
  {
    price = step;
  }

  if (msg.length > 0)
  {
    alert(msg);
    return;
  }

  Ajax.call('snatch.php?act=bid&id=' + id, 'price=' + price, bidResponse, 'POST', 'JSON')
}

/* *
 * 夺宝奇兵会员出价反馈
 */

function bidResponse(result)
{
  if (result.error == 0)
  {
    document.getElementById('HHS_SNATCH').innerHTML = result.content;
    if (document.forms['formBid'])
    {
      document.forms['formBid'].elements['price'].focus();
    }
    newPrice(); //刷新价格列表
  }
  else
  {
    alert(result.content);
  }
}

/* *
 * 夺宝奇兵最新出价
 */

function newPrice(id)
{
  Ajax.call('snatch.php?act=new_price_list&id=' + id, '', newPriceResponse, 'GET', 'TEXT');
}

/* *
 * 夺宝奇兵最新出价反馈
 */

function newPriceResponse(result)
{
  document.getElementById('HHS_PRICE_LIST').innerHTML = result;
}

/* *
 *  返回属性列表
 */
function getAttr(cat_id)
{
  var tbodies = document.getElementsByTagName('tbody');
  for (i = 0; i < tbodies.length; i ++ )
  {
    if (tbodies[i].id.substr(0, 10) == 'goods_type')tbodies[i].style.display = 'none';
  }

  var type_body = 'goods_type_' + cat_id;
  try
  {
    document.getElementById(type_body).style.display = '';
  }
  catch (e)
  {
  }
}

/* *
 * 截取小数位数
 */
function advFormatNumber(value, num) // 四舍五入
{
  var a_str = formatNumber(value, num);
  var a_int = parseFloat(a_str);
  if (value.toString().length > a_str.length)
  {
    var b_str = value.toString().substring(a_str.length, a_str.length + 1);
    var b_int = parseFloat(b_str);
    if (b_int < 5)
    {
      return a_str;
    }
    else
    {
      var bonus_str, bonus_int;
      if (num == 0)
      {
        bonus_int = 1;
      }
      else
      {
        bonus_str = "0."
        for (var i = 1; i < num; i ++ )
        bonus_str += "0";
        bonus_str += "1";
        bonus_int = parseFloat(bonus_str);
      }
      a_str = formatNumber(a_int + bonus_int, num)
    }
  }
  return a_str;
}

function formatNumber(value, num) // 直接去尾
{
  var a, b, c, i;
  a = value.toString();
  b = a.indexOf('.');
  c = a.length;
  if (num == 0)
  {
    if (b != - 1)
    {
      a = a.substring(0, b);
    }
  }
  else
  {
    if (b == - 1)
    {
      a = a + ".";
      for (i = 1; i <= num; i ++ )
      {
        a = a + "0";
      }
    }
    else
    {
      a = a.substring(0, b + num + 1);
      for (i = c; i <= b + num; i ++ )
      {
        a = a + "0";
      }
    }
  }
  return a;
}

/* *
 * 根据当前shiping_id设置当前配送的的保价费用，如果保价费用为0，则隐藏保价费用
 *
 * return       void
 */
function set_insure_status()
{
  // 取得保价费用，取不到默认为0
  var shippingId = getRadioValue('shipping');
  var insure_fee = 0;
  if (shippingId > 0)
  {
    if (document.forms['theForm'].elements['insure_' + shippingId])
    {
      insure_fee = document.forms['theForm'].elements['insure_' + shippingId].value;
    }
    // 每次取消保价选择
    if (document.forms['theForm'].elements['need_insure'])
    {
      document.forms['theForm'].elements['need_insure'].checked = false;
    }

    // 设置配送保价，为0隐藏
    if (document.getElementById("hhs_insure_cell"))
    {
      if (insure_fee > 0)
      {
        document.getElementById("hhs_insure_cell").style.display = '';
        setValue(document.getElementById("hhs_insure_fee_cell"), getFormatedPrice(insure_fee));
      }
      else
      {
        document.getElementById("hhs_insure_cell").style.display = "none";
        setValue(document.getElementById("hhs_insure_fee_cell"), '');
      }
    }
  }
}

/* *
 * 当支付方式改变时出发该事件
 * @param       pay_id      支付方式的id
 * return       void
 */
function changePayment(pay_id)
{
  // 计算订单费用
  calculateOrderFee();
}

function getCoordinate(obj)
{
  var pos =
  {
    "x" : 0, "y" : 0
  }

  pos.x = document.body.offsetLeft;
  pos.y = document.body.offsetTop;

  do
  {
    pos.x += obj.offsetLeft;
    pos.y += obj.offsetTop;

    obj = obj.offsetParent;
  }
  while (obj.tagName.toUpperCase() != 'BODY')

  return pos;
}

function showCatalog(obj)
{
  var pos = getCoordinate(obj);
  var div = document.getElementById('HHS_CATALOG');

  if (div && div.style.display != 'block')
  {
    div.style.display = 'block';
    div.style.left = pos.x + "px";
    div.style.top = (pos.y + obj.offsetHeight - 1) + "px";
  }
}

function hideCatalog(obj)
{
  var div = document.getElementById('HHS_CATALOG');

  if (div && div.style.display != 'none') div.style.display = "none";
}

function sendHashMail()
{
  Ajax.call('user.php?act=send_hash_mail', '', sendHashMailResponse, 'GET', 'JSON')
}

function sendHashMailResponse(result)
{
  alert(result.message);
}

/* 订单查询 */
function orderQuery()
{
  var order_sn = document.forms['hhsOrderQuery']['order_sn'].value;

  var reg = /^[\.0-9]+/;
  if (order_sn.length < 10 || ! reg.test(order_sn))
  {
    alert(invalid_order_sn);
    return;
  }
  Ajax.call('user.php?act=order_query&order_sn=s' + order_sn, '', orderQueryResponse, 'GET', 'JSON');
}

function orderQueryResponse(result)
{
  if (result.message.length > 0)
  {
    alert(result.message);
  }
  if (result.error == 0)
  {
    var div = document.getElementById('HHS_ORDER_QUERY');
    div.innerHTML = result.content;
  }
}

function display_mode(str)
{
    document.getElementById('display').value = str;
    setTimeout(doSubmit, 0);
    function doSubmit() {document.forms['listform'].submit();}
}

function display_mode_wholesale(str)
{
    document.getElementById('display').value = str;
    setTimeout(doSubmit, 0);
    function doSubmit() 
    {
        document.forms['wholesale_goods'].action = "wholesale.php";
        document.forms['wholesale_goods'].submit();
    }
}

/* 修复IE6以下版本PNG图片Alpha */
function fixpng()
{
  var arVersion = navigator.appVersion.split("MSIE")
  var version = parseFloat(arVersion[1])

  if ((version >= 5.5) && (document.body.filters))
  {
     for(var i=0; i<document.images.length; i++)
     {
        var img = document.images[i]
        var imgName = img.src.toUpperCase()
        if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
        {
           var imgID = (img.id) ? "id='" + img.id + "' " : ""
           var imgClass = (img.className) ? "class='" + img.className + "' " : ""
           var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
           var imgStyle = "display:inline-block;" + img.style.cssText
           if (img.align == "left") imgStyle = "float:left;" + imgStyle
           if (img.align == "right") imgStyle = "float:right;" + imgStyle
           if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle
           var strNewHTML = "<span " + imgID + imgClass + imgTitle
           + " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
           + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
           + "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>"
           img.outerHTML = strNewHTML
           i = i-1
        }
     }
  }
}

function hash(string, length)
{
  var length = length ? length : 32;
  var start = 0;
  var i = 0;
  var result = '';
  filllen = length - string.length % length;
  for(i = 0; i < filllen; i++)
  {
    string += "0";
  }
  while(start < string.length)
  {
    result = stringxor(result, string.substr(start, length));
    start += length;
  }
  return result;
}

function stringxor(s1, s2)
{
  var s = '';
  var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  var max = Math.max(s1.length, s2.length);
  for(var i=0; i<max; i++)
  {
    var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
    s += hash.charAt(k % 52);
  }
  return s;
}

var evalscripts = new Array();
function evalscript(s)
{
  if(s.indexOf('<script') == -1) return s;
  var p = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/ig;
  var arr = new Array();
  while(arr = p.exec(s)) appendscript(arr[1], '', arr[2], arr[3]);
  return s;
}

function $$(id)
{
    return document.getElementById(id);
}

function appendscript(src, text, reload, charset)
{
  var id = hash(src + text);
  if(!reload && in_array(id, evalscripts)) return;
  if(reload && $$(id))
  {
    $$(id).parentNode.removeChild($$(id));
  }
  evalscripts.push(id);
  var scriptNode = document.createElement("script");
  scriptNode.type = "text/javascript";
  scriptNode.id = id;
  //scriptNode.charset = charset;
  try
  {
    if(src)
    {
      scriptNode.src = src;
    }
    else if(text)
    {
      scriptNode.text = text;
    }
    $$('append_parent').appendChild(scriptNode);
  }
  catch(e)
  {}
}

function in_array(needle, haystack)
{
  if(typeof needle == 'string' || typeof needle == 'number')
  {
    for(var i in haystack)
    {
      if(haystack[i] == needle)
      {
        return true;
      }
    }
  }
  return false;
}

var pmwinposition = new Array();

var userAgent = navigator.userAgent.toLowerCase();
var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);
function pmwin(action, param)
{
  var objs = document.getElementsByTagName("OBJECT");
  if(action == 'open')
  {
    for(i = 0;i < objs.length; i ++)
    {
      if(objs[i].style.visibility != 'hidden')
      {
        objs[i].setAttribute("oldvisibility", objs[i].style.visibility);
        objs[i].style.visibility = 'hidden';
      }
    }
    var clientWidth = document.body.clientWidth;
    var clientHeight = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
    var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;
    var pmwidth = 800;
    var pmheight = clientHeight * 0.9;
    if(!$$('pmlayer'))
    {
      div = document.createElement('div');div.id = 'pmlayer';
      div.style.width = pmwidth + 'px';
      div.style.height = pmheight + 'px';
      div.style.left = ((clientWidth - pmwidth) / 2) + 'px';
      div.style.position = 'absolute';
      div.style.zIndex = '999';
      $$('append_parent').appendChild(div);
      $$('pmlayer').innerHTML = '<div style="width: 800px; background: #666666; margin: 5px auto; text-align: left">' +
        '<div style="width: 800px; height: ' + pmheight + 'px; padding: 1px; background: #FFFFFF; border: 1px solid #7597B8; position: relative; left: -6px; top: -3px">' +
        '<div onmousedown="pmwindrag(event, 1)" onmousemove="pmwindrag(event, 2)" onmouseup="pmwindrag(event, 3)" style="cursor: move; position: relative; left: 0px; top: 0px; width: 800px; height: 30px; margin-bottom: -30px;"></div>' +
        '<a href="###" onclick="pmwin(\'close\')"><img style="position: absolute; right: 20px; top: 15px" src="images/close.gif" title="关闭" /></a>' +
        '<iframe id="pmframe" name="pmframe" style="width:' + pmwidth + 'px;height:100%" allowTransparency="true" frameborder="0"></iframe></div></div>';
    }
    $$('pmlayer').style.display = '';
    $$('pmlayer').style.top = ((clientHeight - pmheight) / 2 + scrollTop) + 'px';
    if(!param)
    {
        pmframe.location = 'pm.php';
    }
    else
    {
        pmframe.location = 'pm.php?' + param;
    }
  }
  else if(action == 'close')
  {
    for(i = 0;i < objs.length; i ++)
    {
      if(objs[i].attributes['oldvisibility'])
      {
        objs[i].style.visibility = objs[i].attributes['oldvisibility'].nodeValue;
        objs[i].removeAttribute('oldvisibility');
      }
    }
    hiddenobj = new Array();
    $$('pmlayer').style.display = 'none';
  }
}

var pmwindragstart = new Array();
function pmwindrag(e, op)
{
  if(op == 1)
  {
    pmwindragstart = is_ie ? [event.clientX, event.clientY] : [e.clientX, e.clientY];
    pmwindragstart[2] = parseInt($$('pmlayer').style.left);
    pmwindragstart[3] = parseInt($$('pmlayer').style.top);
    doane(e);
  }
  else if(op == 2 && pmwindragstart[0])
  {
    var pmwindragnow = is_ie ? [event.clientX, event.clientY] : [e.clientX, e.clientY];
    $$('pmlayer').style.left = (pmwindragstart[2] + pmwindragnow[0] - pmwindragstart[0]) + 'px';
    $$('pmlayer').style.top = (pmwindragstart[3] + pmwindragnow[1] - pmwindragstart[1]) + 'px';
    doane(e);
  }
  else if(op == 3)
  {
    pmwindragstart = [];
    doane(e);
  }
}

function doane(event)
{
  e = event ? event : window.event;
  if(is_ie)
  {
    e.returnValue = false;
    e.cancelBubble = true;
  }
  else if(e)
  {
    e.stopPropagation();
    e.preventDefault();
  }
}

/* *
 * 添加礼包到购物车
 */
function addPackageToCart(packageId)
{
  var package_info = new Object();
  var number       = 1;

  package_info.package_id = packageId
  package_info.number     = number;

  Ajax.call('flow.php?step=add_package_to_cart', 'package_info=' + obj2str(package_info), addPackageToCartResponse, 'POST', 'JSON');
}

/* *
 * 处理添加礼包到购物车的反馈信息
 */
function addPackageToCartResponse(result)
{
  if (result.error > 0)
  {
    if (result.error == 2)
    {
      if (confirm(result.message))
      {
        location.href = 'user.php?act=add_booking&id=' + result.goods_id;
      }
    }
    else
    {
      alert(result.message);    
    }
  }
  else
  {
    var cartInfo = document.getElementById('HHS_CARTINFO');
    var cart_url = 'flow.php?step=cart';
    if (cartInfo)
    {
      cartInfo.innerHTML = result.content;
    }
    location.href = cart_url;
  }
}

function setSuitShow(suitId)
{
    var suit    = document.getElementById('suit_'+suitId);

    if(suit == null)
    {
        return;
    }
    if(suit.style.display=='none')
    {
        suit.style.display='';
    }
    else
    {
        suit.style.display='none';
    }
}


/* 以下四个函数为属性选择弹出框的功能函数部分 */
//检测层是否已经存在
function docEle() 
{
  return document.getElementById(arguments[0]) || false;
}

//生成属性选择层
function openSpeDiv(message, goods_id, parent) 
{
  var _id = "speDiv";
  var m = "mask";
  if (docEle(_id)) document.removeChild(docEle(_id));
  if (docEle(m)) document.removeChild(docEle(m));
  //计算上卷元素值
  var scrollPos; 
  if (typeof window.pageYOffset != 'undefined') 
  { 
    scrollPos = window.pageYOffset; 
  } 
  else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') 
  { 
    scrollPos = document.documentElement.scrollTop; 
  } 
  else if (typeof document.body != 'undefined') 
  { 
    scrollPos = document.body.scrollTop; 
  }

  var i = 0;
  var sel_obj = document.getElementsByTagName('select');
  while (sel_obj[i])
  {
    sel_obj[i].style.visibility = "hidden";
    i++;
  }

  // 新激活图层
  var newDiv = document.createElement("form");
  newDiv.id = _id;
newDiv.setAttribute('name','HHS_FORMBUY');

  //生成层内内容  
  newDiv.innerHTML = '<a href="javascript:cancel_div()"><img id="sku-quit" src="http://pinduoduo.img-cn-shanghai.aliyuncs.com/sku/quit_button.png"></a><div id="sku-head"><img id="sku-image" class="image" src="'+goods_thumb+'"><div id="sku-detail"><div class="sku-name"><span class="goodsName" id="goodsName">'+goods_name+'</span><div class="sku-price-depends">￥'+goods_price+'</div></div><div><span id="sku-msg">' + select_spe + '</span></div></div></div>';

var specDiv = document.createElement("div");
specDiv.setAttribute('class','sku-info');
  for (var spec = 0; spec < message.length; spec++)
  {
	  specDiv.innerHTML += '<div class="sku-type">' +  message[spec]['name'] + '</div>';

        for (var val_arr = 0; val_arr < message[spec]['values'].length; val_arr++)
        {
          if (val_arr == 0)
          {
            specDiv.innerHTML += "<input class='goods' type='radio' name='spec_" + message[spec]['attr_id'] + "' value='" + message[spec]['values'][val_arr]['id'] + "' id='spec_value_" + message[spec]['values'][val_arr]['id'] + "' checked /><label for='spec_value_" + message[spec]['values'][val_arr]['id'] + "' data-price='"+ message[spec]['values'][val_arr]['price'] +"'>" + message[spec]['values'][val_arr]['label'] + '</label>';      
          }
          else
          {
            specDiv.innerHTML += "<input class='goods' type='radio' name='spec_" + message[spec]['attr_id'] + "' value='" + message[spec]['values'][val_arr]['id'] + "' id='spec_value_" + message[spec]['values'][val_arr]['id'] + "' /><label for='spec_value_" + message[spec]['values'][val_arr]['id'] + "' data-price='"+ message[spec]['values'][val_arr]['price'] +"'>" + message[spec]['values'][val_arr]['label'] + '</label>';      
          }
        } 
        specDiv.innerHTML += "<input type='hidden' name='spec_list' value='" + val_arr + "' />";
  }
  newDiv.appendChild(specDiv);
  
  newDiv.innerHTML += '<div class="sku-amount"><div class="sku-text"><a>购买数量</a><div class="sku-buy-amount-increase" onclick="jia();"><span>+</span></div><input id="number" name="number" value="1" class="sku-input-text" type="text" maxlength="5"><div class="sku-buy-amount-reduce" onclick="jian();"><span>−</span></div></div></div>';
  
  newDiv.innerHTML += "<div id='sku-decide' class='sku-button'><a href='javascript:submit_div(" + goods_id + "," + parent + ")' id='sku-buy'>" + btn_add_to_cart + "</a></div>";
  
  document.body.appendChild(newDiv);


  // mask图层
  var newMask = document.createElement("div");
  newMask.id = m;
  newMask.style.position = "absolute";
  newMask.style.zIndex = "99999";
  newMask.style.width = document.body.scrollWidth + "px";
  newMask.style.height = document.body.scrollHeight + "px";
  newMask.style.top = "0px";
  newMask.style.left = "0px";
  newMask.style.background = "rgba(0,0,0,0.4)";
  document.body.appendChild(newMask);
} 

//获取选择属性后，再次提交到购物车
function submit_div(goods_id, parentId) 
{
  var goods        = new Object();
  var spec_arr     = new Array();
  var fittings_arr = new Array();
  var number       = $("#number").val() || 1;
  var input_arr      = document.getElementsByTagName('input'); 
  var quick		   = 1;

  var spec_arr = new Array();
  var j = 0;

  for (i = 0; i < input_arr.length; i ++ )
  {
    var prefix = input_arr[i].name.substr(0, 5);

    if (prefix == 'spec_' && (
      ((input_arr[i].type == 'radio' || input_arr[i].type == 'checkbox') && input_arr[i].checked)))
    {
      spec_arr[j] = input_arr[i].value;
      j++ ;
    }
  }

  goods.quick    = quick;
  goods.spec     = spec_arr;
  goods.goods_id = goods_id;
  goods.number   = number;
  goods.rec_type = 0;
  goods.type = 1;
  goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);

  Ajax.call('flows.php?step=add_to_cart', 'goods=' + obj2str(goods), addToCartResponse, 'POST', 'JSON');

  document.body.removeChild(docEle('speDiv'));
  document.body.removeChild(docEle('mask'));

  var i = 0;
  var sel_obj = document.getElementsByTagName('select');
  while (sel_obj[i])
  {
    sel_obj[i].style.visibility = "";
    i++;
  }

}

// 关闭mask和新图层
function cancel_div() 
{
  document.body.removeChild(docEle('speDiv'));
  document.body.removeChild(docEle('mask'));

  var i = 0;
  var sel_obj = document.getElementsByTagName('select');
  while (sel_obj[i])
  {
    sel_obj[i].style.visibility = "";
    i++;
  }
}


//加入购物车弹窗
function opencartDiv(price,name,pic,goods_brief,goods_id,total,number)
{
var _id = "speDiv";
var m = "mask";
if (docEle(_id)) document.removeChild(docEle(_id));
if (docEle(m)) document.removeChild(docEle(m));
//计算上卷元素值
var scrollPos; 
if (typeof window.pageYOffset != 'undefined') 
{ 
scrollPos = window.pageYOffset; 
} 
else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') 
{ 
scrollPos = document.documentElement.scrollTop; 
} 
else if (typeof document.body != 'undefined') 
{ 
scrollPos = document.body.scrollTop; 
}
var i = 0;
var sel_obj = document.getElementsByTagName('select');
while (sel_obj[i])
{
sel_obj[i].style.visibility = "hidden";
i++;
}
// 新激活图层
var newDiv = document.createElement("div");
newDiv.id = _id;
newDiv.style.position = "absolute";
newDiv.style.zIndex = "10000";
newDiv.style.width = "480px";
newDiv.style.height = "200px";
newDiv.style.top = (parseInt(scrollPos + 200)) + "px";
newDiv.style.left = (parseInt(document.body.offsetWidth) - 400) / 2 + "px"; // 屏幕居中
var html = '';
//生成层内内容
html = '<div class="add_box"><div class="add_shop"><div class="add_shop_dh"><span class="gwlogo">加入购物车</span><span class="close"><a href="javascript:cancel_div()"><b>关闭</b></a></span></div><div class="clear"></div><div class="add_shop_con"><div class="add_shop_conl"><img src="'+pic+'" /></div><div class="add_shop_conr"><ul><li class="add_shopwz">'+name+'</li><li>已成功加入购物车!</li><li class="add_shopwz2">购物车当前共有<span class="add_shopwz3">'+number+'</span>件商品，合计金额：<span class="add_shopwz4">'+total+'</span></li><li><span class="add_shopwz5"><a href="javascript:cancel_div()"><< 继续购物</a></span><span class="add_shopwz6"><a href="flow.php">去结算</a></span></li></ul></div></div></div></div>';
newDiv.innerHTML = html;
document.body.appendChild(newDiv);
// mask图层
var newMask = document.createElement("div");
newMask.id = m;
newMask.style.position = "absolute";
newMask.style.zIndex = "9999";
newMask.style.width = document.body.scrollWidth + "px";
newMask.style.height = document.body.scrollHeight + "px";
newMask.style.top = "0px";
newMask.style.left = "0px";
newMask.style.background = "#FFF";
newMask.style.filter = "alpha(opacity=30)";
newMask.style.opacity = "0.40";
document.body.appendChild(newMask);
}

   //回到页面顶部
    jQuery.fn.goToTop = function(){
        if($(window).scrollTop() < 1) {
        }
        $(window).scroll(function(){
                if($(this).scrollTop()>50){
                    $(".back-top").fadeIn();
                } else {
                    $(".back-top").fadeOut();
                }
            });
        this.bind('click',function(){
            $('html,body').animate({scrollTop:0},'slow');
            return false;
        });        
    }
	
	function jia() {
	      var inum = $("#number").val();
          ++inum;
          $("#number").val(inum);
	}
	function jian() {
          var inum = $("#number").val();
          if(inum<=1)
            return;
          --inum;
          $("#number").val(inum);
	}


var goods_name = '';
var goods_thumb = '';
var goods_price = 0;

    function getGoodsMeta(goods_id){
      var box = $('.iproduct_'+goods_id).closest('li');
      goods_name = box.find('.tit').text();
      goods_thumb = box.find('img').eq(0).data('original');
      goods_price = box.find('.price b').text();
    }

    function showhide(id){
      fuck_team = true;
      if (id == undefined) {id = '';fuck_team=false}
      if ($('#speDiv').hasClass('show')) {
		    $('#btn-pre-buy'+id).show();
        $('#speDiv').hide();
        $('#btn-buy'+id).hide();
      }
      else
      {
        changePrice();
		    $('#btn-pre-buy'+id).hide();
        $('#speDiv').show();
        $('#btn-buy'+id).show();
      }
      $("#speDiv").find('a').eq(0).attr('href', 'javascript:showhide('+id+');');
      $('#speDiv').toggleClass('show');
    }