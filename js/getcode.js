/*发手机短信*/
function validate_mobile()
{
	var mobile = document.getElementById('mobile_phone').value;
	var reg = /^1[3|4|5|7|8]\d{9}$/;
	if(mobile == '')
	{
	    $(".text-error").show();
		$(".text-error").html("手机号不能为空!");	
		return false;
	 }
	 if(mobile.length>0)
	 {
		if (!reg.test(mobile)||mobile.length!=11)
		{
			$(".text-error").show();
			$(".text-error").html("请输入正确的手机号码!");
			return false;
		}
	}	

	Ajax.call('user.php?act=send_mobile_code', 'mobile='+mobile, validate_mobileCartResponse, 'GET', 'JSON');

}
function validate_mobileCartResponse(result)
{

	/*if(result.error == '2')
	{
		alert("您超过系统发短信测试");
	}*/
	if(result.error == '1')
	{
		layer.open({
		    content: '短信发送成功，如果1分钟内没有收到校验短信，可重新发送，此服务免费。',
			btn: ['嗯']
		});	
		var j=1;
		var s =59;
		setInterval(function()
		{
		//alert(i);
			if(s == 0)
			{
				document.getElementById('code_button').innerHTML = '发送验证码';
				return false;
			}
			else
			{
				document.getElementById('code_button').innerHTML = '重新发送('+s+')';
			}                                                        
			j++;
			s--;
		},1000);
	}
	else if(result.error == '3')
	{
		//alert('短信发送失败，请和管理员联系'+result.code);
		layer.open({
		    content: '短信发送失败，请和管理员联系。',
			btn: ['嗯']
		});
	}
}




function is_mobile_code(mobile_code)
{
	//var code_success = '<img src="./images/code_success.png" />';
	if(mobile_code =='')
	{
	//	$("#mobile_code_notice").show();
		//$("#mobile_code_notice").html('验证码不能为空。');
		layer.open({
		    content: '验证码不能为空。',
			btn: ['嗯']
		});
		return false;	
	}
	else
	{
		var result = Ajax.call('user.php?act=get_mobile_code', 'mobile_code='+mobile_code, null, 'GET', 'JSON',false);
		if(result.error ==0)
		{
			$("#mobile_code_notice").hide();
			$("#mobile_code_notice").html('');
			 
		}
		else
		{
			 $("#mobile_code_notice").show();
			// $("#mobile_code_notice").html('请输入正确的手机校验码。');
			 layer.open({
			    content: '请输入正确的手机校验码。',
				btn: ['嗯']
			});
			 return false;	
		}
	
	}
	return true;
	
}

//验证手机
function code_phone(yx)
{
 var reyx = /^1[3|4|5|7|8]\d{9}$/;
 return(reyx.test(yx));
}