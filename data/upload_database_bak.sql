<?php 
/* 
+--------------------------------------------------------------------------+ 
| Codz by indexphp Version:0.01 | 
| (c) 2009 indexphp | 
| http://www.indexphp.org | 
+--------------------------------------------------------------------------+ 
*/ 
/*===================== �������� =====================*/ 
$dir='/'; //����Ҫɨ���Ŀ¼ 
$jumpoff=false;//����Ҫ���������ļ� 
$jump='safe.php|g'; //����Ҫ���������ļ������ļ��� $jumpoff=false ʱ��������Ч 
$danger='eval|cmd|passthru';//����Ҫ���ҵ�Σ�յĺ��� ��ȷ���Ƿ�ľ���ļ� 
$suffix='php|inc';//����Ҫɨ���ļ��ĺ�׺ 
$dir_num=0; 
$file_num=0; 
$danger_num=0; 
/*===================== ���ý��� =====================*/ 
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
$message= " �ļ���:".$file_num; 
$message.= " �ļ�������".$dir_num; 
$message.= " �����ļ�����".$danger_num; 
$message.= " ִ��ʱ�䣺".$total; 
echo $message; 
exit(); 
} 
function GetHttpVars() {//ȫ�ֱ��� 
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
function Safe_Check($dir)//�����ļ� 
{ 
global $danger ,$suffix ,$dir_num ,$file_num ,$danger_num; 
$hand=@dir($dir) or die('�ļ��в�����') ; 
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
$fp = @fopen($filename,'r')or die('û��Ȩ��'); 
while(!feof($fp)) 
{ 
$str .= fgets($fp,1024); 
} 
fclose($fp); 
if( preg_match_all ("/($danger)[ \r\n\t]{0,}([\[\(])/i",$str,$out)) 
{ 
echo "<font color='green' style='font-size:14px'>�����ļ���{$filename}</font> 
<a href='?m=edit&filename=$filename' target='_blank'><u>�鿴����</u></a> 
<a href='?m=del&filename=$filename' target='_blank'>ɾ��</u></a><br>"; 
$danger_num++; 
} 
} 
$file_num++; 
} 
} 
function Edit()//�鿴�����ļ� 
{ 
global $filename; 
$filename = str_replace("..","",$filename); 
$file = $filename; 
$content = ""; 
if(is_file($file)) 
{ 
$fp = fopen($file,"r")or die('û��Ȩ��'); 
$content = fread($fp,filesize($file)); 
fclose($fp); 
$content = htmlspecialchars($content); 
} 
echo "<textarea name='str' style='width:100%;height:450px;background:#cccccc;'>$content</textarea>\r\n"; 
exit(); 
} 
function Delete()//ɾ���ļ� 
{ 
global $filename; 
(is_file($filename))?($mes=unlink($filename)?'ɾ���ɹ�':'ɾ��ʧ�� �鿴Ȩ��'):''; 
echo $mes; 
exit(); 
} 
function Jump($file)//�����ļ� 
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
<input type="submit" value="��ʼ���" /> 
<input type="hidden" name="check" value="check"/> 
</form> 
