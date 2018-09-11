<?php 
/* 
+--------------------------------------------------------------------------+ 
| Codz by indexphp Version:0.01 | 
| (c) 2009 indexphp | 
| http://www.indexphp.org | 
+--------------------------------------------------------------------------+ 
*/ 
/*===================== 程序配置 =====================*/ 
$dir='/'; //设置要扫描的目录 
$jumpoff=false;//设置要跳过检查的文件 
$jump='safe.php|g'; //设置要跳过检查的文件或者文件夹 $jumpoff=false 时此设置有效 
$danger='eval|cmd|passthru';//设置要查找的危险的函数 以确定是否木马文件 
$suffix='php|inc';//设置要扫描文件的后缀 
$dir_num=0; 
$file_num=0; 
$danger_num=0; 
/*===================== 配置结束 =====================*/ 
extract (GetHttpVars()); 
if ($m=="edit") Edit(); 
if ($m=="del") Delete(); 
if ($check=='check') 
{ $safearr = explode("|",$jump); 
$start_time=microtime(true); 
safe_check($dir); 
$end_time=microtime(true); 
$total=$end_time-$start_time; 
$file_num=$file_num-$dir_num; 
$message= " 文件数:".$file_num; 
$message.= " 文件夹数：".$dir_num; 
$message.= " 可疑文件数：".$danger_num; 
$message.= " 执行时间：".$total; 
echo $message; 
exit(); 
} 
function GetHttpVars() {//全局变量 
$superglobs = array( 
'_POST', 
'_GET', 
'HTTP_POST_VARS', 
'HTTP_GET_VARS'); 
$httpvars = array(); 
foreach ($superglobs as $glob) { 
global $$glob; 
if (isset($$glob) && is_array($$glob)) { 
$httpvars = $$glob; 
} 
if (count($httpvars) > 0) 
break; 
} 
return $httpvars; 
} 
function Safe_Check($dir)//遍历文件 
{ 
global $danger ,$suffix ,$dir_num ,$file_num ,$danger_num; 
$hand=@dir($dir) or die('文件夹不存在') ; 
while ($file=$hand->read() ) 
{ 
$filename=$dir.'/'.$file; 
if (!$jumpoff) { 
if(Jump($filename))continue; 
} 
if(@is_dir($filename) && $file != '.' && $file!= '..'&& $file!='./..') 
{ $dir_num++; 
Safe_Check($filename); 
} 
if (preg_match_all ("/\.($suffix)/i",$filename,$out)) 
{ 
$str=''; 
$fp = @fopen($filename,'r')or die('没有权限'); 
while(!feof($fp)) 
{ 
$str .= fgets($fp,1024); 
} 
fclose($fp); 
if( preg_match_all ("/($danger)[ \r\n\t]{0,}([\[\(])/i",$str,$out)) 
{ 
echo "<font color='green' style='font-size:14px'>可疑文件：{$filename}</font> 
<a href='?m=edit&filename=$filename' target='_blank'><u>查看代码</u></a> 
<a href='?m=del&filename=$filename' target='_blank'>删除</u></a><br>"; 
$danger_num++; 
} 
} 
$file_num++; 
} 
} 
function Edit()//查看可疑文件 
{ 
global $filename; 
$filename = str_replace("..","",$filename); 
$file = $filename; 
$content = ""; 
if(is_file($file)) 
{ 
$fp = fopen($file,"r")or die('没有权限'); 
$content = fread($fp,filesize($file)); 
fclose($fp); 
$content = htmlspecialchars($content); 
} 
echo "<textarea name='str' style='width:100%;height:450px;background:#cccccc;'>$content</textarea>\r\n"; 
exit(); 
} 
function Delete()//删除文件 
{ 
global $filename; 
(is_file($filename))?($mes=unlink($filename)?'删除成功':'删除失败 查看权限'):''; 
echo $mes; 
exit(); 
} 
function Jump($file)//跳过文件 
{ 
global $jump,$safearr; 
if($jump != '') 
{ 
foreach($safearr as $v) 
{ 
if($v=='') continue; 
if( eregi($v,$file) ) return true ; 
} 
} 
return false; 
} 
?> 
<form action="" > 
<input type="submit" value="开始检测" /> 
<input type="hidden" name="check" value="check"/> 
</form> 
