// JavaScript Document

function winopen(url, w, h){

	url = fix_url(url);

	$("html,body").css("overflow", "hidden");

	$("div.shade").show();

	var _body = $("body").eq(0);

	if ($("#dialog").length == 0) {

		if (!is_mobile()) {

			_body.append("<div id=\"dialog\"><iframe src='" + url + "' style='width:" + w + "px;height:100%;border:3px solid #42AE1D;border-radius:5px;background:#fff' scrolling='auto' ></iframe></div>");

			$("#dialog").css({

				width : w,

				height : h,

				position : "fixed",

				"z-index" : "2000",

				top : ($(window).height() / 2 - h / 2),

				left : (_body.width() / 2 - w / 2),

				"background-color" : "#ffffff"

			});

		} else {

			$("div.shade").css("width", _body.width());

			_body.append("<div id=\"dialog\"><iframe src='" + url + "' style='width:100%;height:100%;border:3px solid #42AE1D;border-radius:5px;background:#fff' scrolling='auto' ></iframe></div>");

			$("#dialog").css({

				width : _body.width(),

				height : h,

				position : "fixed",

				"z-index" : "2000",

				top : 0,

				left : 0,

				"background-color" : "#ffffff"

			});

		}

	} else {

		$("#dialog").show();

	}

}









function is_mobile() {

	return navigator.userAgent.match(/mobile/i);

}

function fix_url(url) {

	var ss = url.split('?');

	url = ss[0] + "?";

	for (var i = 1; i < ss.length; i++) {

		url += ss[i] + "&";

	}

	if (ss.length > 0) {

		url = url.substring(0, url.length - 1);

	}

	return url;

}

/* 关闭弹出窗口*/

function myclose() {

	parent.winclose();

}

function winclose() {

	$("html,body").css("overflow", "auto");

	$("div.shade").hide();

	$("#dialog").html("");

	$("#dialog").remove();

}

function get_cat_piclist_goods(value,img_id='goods_img_url') 
{

	if(value=='0')

	{

		alert('请选择分类');

		return false;	

	}
	getStatusUrl = 'index.php?op=goods&img_id='+img_id+'&act=get_pic&cat_id='+value;
 	window.location.href=getStatusUrl;
//	$.ajax(
//
//	{
//
//		  url: getStatusUrl,
//
//		  dataType: 'json',
//
//		  global: false,
//
//		  success: function(data)
//
//		  {
//
//			 document.getElementById('photo').innerHTML =data.pic_list;
//
//			 document.getElementById('pages').innerHTML =data.pages;
//
//		  },
//
//		  error: function(XMLHttpRequest,textStatus, errorThrown){
//
//		  }
//
//	});		

}


function get_cat_piclist(value)
{

	if(value=='0')

	{

		alert('请选择分类');

		return false;	

	}

	getStatusUrl = 'index.php?op=goods&act=get_cat_piclist&cat_id='+value;

	$.ajax(

	{

		  url: getStatusUrl,

		  dataType: 'json',

		  global: false,

		  success: function(data)

		  {

			 document.getElementById('photo').innerHTML =data.pic_list;

			 document.getElementById('pages').innerHTML =data.pages;

		  },

		  error: function(XMLHttpRequest,textStatus, errorThrown){

		  }

	});		

}

function show_html(id,num)

{

		for(i=1;i<=num;i++)

		{

			if(i==id)

			{

				document.getElementById("show_html"+i).style.display= "";

				document.getElementById("nav"+i).className='act';

			}

			else

			{

				document.getElementById("show_html"+i).style.display = "none";

				document.getElementById("nav"+i).className='';

			}

		}

}



