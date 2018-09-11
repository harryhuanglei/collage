<?php
define('IN_HHS', true);
if($action  =='false_user')
{
    $user_list = get_false_user_list($action);
    $smarty->assign('pager', $user_list['pager']);
    $smarty->assign('user_list',$user_list['orders']);
    $smarty->assign('filter',$user_list['filter']);
    $smarty->assign('action',$action);
    $smarty->display('false_user.dwt');
}
elseif($action  =='my_user_batch'){
    if (!empty($_POST['checkbox']))
    {
        foreach($_POST['checkbox'] as $v)
        {
            //删除会员评论
            drop_user_false_comment($v);
            //删除用户照片
            $user_info = $db->getOne("SELECT headimgurl FROM " . $hhs->table('users') . " WHERE user_id = '" . $v. "'");
            if($user_info)
            {
                @unlink(ROOT_PATH.'/data/headimgurl/' . $user_info);
            }
        }
        $sql = "SELECT user_name FROM " . $hhs->table('users') . " WHERE user_id " . db_create_in($_POST['checkbox']);
        $col = $db->getCol($sql);
        $usernames = implode(',',addslashes_deep($col));
        $count = count($col);
        /* 通过插件来删除用户 */
        $users =& init_users();
        $users->remove_user($col);
        show_message('操作成功','返回列表', "index.php?op=false&act=false_user&page=$page", 'info');  
    }
    else
    {
        show_message("请先选择");
    }
}
/*添加虚拟会员*/
elseif($action =='insert_user')
{
    /*会员基本信息*/
    $province = empty($_POST['province']) ? 0 : intval($_POST['province']);
    $city = empty($_POST['city']) ? 0 : intval($_POST['city']);
    $district = empty($_POST['district']) ? 0 : intval($_POST['district']);
    $addr = empty($_POST['address']) ? '' : trim($_POST['address']);
    $username = empty($_POST['username']) ? '' : trim($_POST['username']);
    $sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
    /*会员图片处理*/
    $user_img_arr = getimagesize($_FILES['headimgurl']['tmp_name']);
    if($user_img_arr[0] > 64 || $goods_img_arr[1] > 64)
    {
        show_message('会员图片上传宽高64*64');   
    }
    $img_name = $image->upload_image($_FILES['headimgurl'],'headimgurl');
    $img_name = str_replace('data/headimgurl/','',$img_name);
    /*判会员昵称*/
    if(empty($username))
    {
        show_message('会员昵称不能为空！');
    }
    if(empty($img_name)){
        show_message('会员图片不能为空！');
    }
	/* 更新会员的其它信息 */
    $other =  array();
    $other['uname'] = $username;
    $other['user_name'] = 'wxf'.mt_rand(0,999);
    $other['is_false'] = 1;
    $other['headimgurl']  = $img_name;
    $other['reg_time']  = gmtime();
    $other['sex']  = $sex;
    $other['sup_id']  = $_SESSION['suppliers_id'];
    $db->autoExecute($hhs->table('users'), $other, 'INSERT');
    $user_id = $db->insert_id();
    /*更新会员的收货地址*/
    $address = array();
    $address['consignee'] = $username;
    $address['user_id'] = $user_id;
    $address['province'] = $province;
    $address['city'] = $city;
    $address['district'] = $district;
    $address['address'] = $addr;
    $address['country'] = 1;
    $db->autoExecute($hhs->table('user_address'), $address, 'INSERT');
    $address_id = $db->insert_id();
    $db->autoExecute($hhs->table('users'), array('address_id' => $address_id), 'UPDATE',"user_id = '$user_id'");
	show_message('保存成功','会员列表', 'index.php?op=false&act=false_user', 'info');
}
/*编辑会员信息*/
elseif($action =='update_user')
{
    /*用户基本信息*/
    $user_id = empty($_POST['id']) ? 0 : intval($_POST['id']);
    $address_id = empty($_POST['address_id']) ? 0 : intval($_POST['address_id']);
    $username = empty($_POST['username']) ? '' : trim($_POST['username']);
    $sex = empty($_POST['sex']) ? 0 : intval($_POST['sex']);
    $province = empty($_POST['province']) ? 0 : intval($_POST['province']);
    $city = empty($_POST['city']) ? 0 : intval($_POST['city']);
    $district = empty($_POST['district']) ? 0 : intval($_POST['district']);
    $addr = empty($_POST['address']) ? '' : trim($_POST['address']);
    $user_img_arr = getimagesize($_FILES['headimgurl']['tmp_name']);
    if($user_img_arr[0] > 64 || $goods_img_arr[1] > 64)
    {
        show_message('会员图片上传宽高64*64');   
    }
    /*会员图片处理*/
    $img_name = $image->upload_image($_FILES['headimgurl'],'headimgurl');
    $img_name = str_replace('data/headimgurl/','',$img_name);
    /* 更新会员的其它信息 */
    $other =  array();
    if(!empty($img_name))
    {
        //删除用户照片
        $user_info = $db->getOne("SELECT headimgurl FROM " . $hhs->table('users') . " WHERE user_id = '" . $user_id . "'");
        if($user_info)
        {
            @unlink(ROOT_PATH.'/data/headimgurl/' . $user_info);
        }
        $other['headimgurl']  = $img_name;
    }
    $other['uname'] = $username;
    $other['is_false'] = 1;
    $other['reg_time']  = gmtime();
    $other['sex']  = $sex;
    $db->autoExecute($hhs->table('users'), $other, 'UPDATE',"user_id = '$user_id'");
    /*更新会员的收货地址*/
    $address = array();
    $address['consignee'] = $username;
    $address['user_id'] = $user_id;
    $address['province'] = $province;
    $address['city'] = $city;
    $address['district'] = $district;
    $address['address'] = $addr;
    $address['country'] = 1;
    $db->autoExecute($hhs->table('user_address'), $address, 'UPDATE',"address_id = '$address_id'");
    clear_all_files();
	show_message('编辑成功','会员列表', 'index.php?op=false&act=false_user', 'info');	
}
//编辑会员
elseif($action =='edit_false_user')
{
    /*获取虚拟会员信息*/
    $user_id = intval($_REQUEST['user_id']);
    $sql = "SELECT u.address_id,u.user_id,u.user_name,u.uname,u.headimgurl, u.sex,ua.province,ua.city,ua.district,ua.address FROM " .$hhs->table('users'). " u LEFT JOIN " . $hhs->table('user_address') . " ua ON u.address_id = ua.address_id WHERE u.user_id=".$user_id;
    $user_info = $db->GetRow($sql);
    $province_list = get_regions(1, 1);
    $city_list = get_regions(2, $user_info['province']);
    $district_list = get_regions(3, $user_info['city']);
    $smarty->assign('user_info', $user_info);
    $smarty->assign('province_list',    $province_list);
    $smarty->assign('city_list',    $city_list);
    $smarty->assign('district_list',    $district_list);
    $smarty->assign('form_act',    "update_user");
    $smarty->assign('action',$action);
	$smarty->display("false_user.dwt");	
}
elseif($action =='add_false_user')
{
    /* 载入国家 */
    $province_list = get_regions(1, 1);
    $smarty->assign('province_list',    $province_list);
	$smarty->assign('form_act','insert_user');
	$smarty->display("false_user.dwt");
}
/*添加虚拟评论*/
elseif($action =='add_false_message')
{
    $user_list = get_sup_user_list($_SESSION['suppliers_id']);
    $smarty->assign('form_act','insert_message');
    $smarty->assign('user_list',$user_list);
    $smarty->display("false_message.dwt");
}
/*添加虚拟评论*/
elseif($action =='insert_message')
{
    /*初始化数据*/
    $user_id         = !empty($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $id_value         = !empty($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;
    $content        = !empty($_POST['content']) ? trim($_POST['content']) : '';
    $comment_rank = !empty($_POST['comment_rank']) ? intval($_POST['comment_rank']) : 0;
    $is_false = 1;
    $status = 1;
    $add_time = gmtime();
    $ip_address = real_ip();
    $user_name = $GLOBALS['db']->getOne("SELECT uname ".
                " FROM " . $GLOBALS['hhs']->table('users')." WHERE user_id = ".$user_id );
    /* 插入数据库*/
    $sql = "INSERT INTO ".$hhs->table('comment')." (user_id,content,id_value,user_name, add_time,status,is_false,comment_rank,ip_address)
    VALUES ('$user_id','$content','$id_value','$user_name','$add_time','$status','$is_false','$comment_rank','$ip_address')";
    $db->query($sql);
    $com_id = $db->insert_id();
    //更新用户评论次数
    if($com_id > 0)
    {
        $sql = "UPDATE " .$hhs->table('users'). " SET comment_num = comment_num+1 WHERE user_id = '$user_id'";   
        $db->query($sql);
    }
    show_message('评论成功','虚拟评论列表', 'index.php?op=false&act=false_message', 'info');
}
/*虚拟评论列表*/
elseif($action =='false_message')
{
    $false_message = get_false_message_list($action);
    $smarty->assign('pager', $false_message['pager']);
    $smarty->assign('comment_list',$false_message['message']);
    $smarty->assign('filter',$false_message['filter']);
    $smarty->assign('action',$action);
    $smarty->display('false_message.dwt');
}
/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'search_goods')
{
    /*据说会坑人，我们总会试*/
    require('../includes/init2.php');
    $keywords = json_str_iconv(trim($_GET['keywords']));
    if($keywords){
        $sql = "SELECT goods_id,goods_name FROM " . $hhs->table('goods') .
        " WHERE goods_name LIKE '%" . mysql_like_quote($keywords) . "%' and is_delete=0 and is_on_sale=1 and suppliers_id = ".$_SESSION['suppliers_id'];
    }else{
        $sql = "SELECT goods_id,goods_name FROM " . $hhs->table('goods')." where is_delete=0 and is_on_sale=1 and suppliers_id = ".$_SESSION['suppliers_id'];
    }
    $row = $db->getAll($sql);
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
    $res = array('error' => 0, 'message' => '', 'content' => $row);
    echo $json->encode($res);
    exit();
}
/*查询判断指定用户是否有评论并删除*/
function drop_user_false_comment($user_id)
{
    $user_id = intval($user_id);   
    $user_num = $GLOBALS['db']->getRow(" select count(*) from ". $GLOBALS['hhs']->table('comment') . " where user_id = ".$user_id);
    if($user_num > 0)
    {
        $sql = "DELETE FROM " . $GLOBALS['hhs']->table('comment') ." WHERE user_id = '$user_id'";
        $GLOBALS['db']->query($sql);
    }
}
/*获取商家添加的虚拟用户*/
function get_sup_user_list($suppliers_id)
{
    $suppliers_id = intval($suppliers_id);
    if($suppliers_id > 0)
    {
        return $GLOBALS['db']->getAll("SELECT u.sex,u.headimgurl,u.user_id, u.user_name,u.uname,ua.province,ua.city,ua.district,ua.address ".
                " FROM " . $GLOBALS['hhs']->table('users')." AS u LEFT JOIN ".$GLOBALS['hhs']->table('user_address')." AS ua ON u.address_id = ua.address_id WHERE u.is_false = 1 AND u.sup_id = '$suppliers_id' ");
    }
}
/*获取商家添加的虚拟会员*/
function get_false_message_list($action=null)
{
    /*初始化首页*/
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    /*过滤条件*/
    $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'c.add_time' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);    
    $suppliers_id=$_SESSION['suppliers_id'];
    $where = " WHERE c.is_false = 1 and c.parent_id = 0 and comment_type = 0 and  s.suppliers_id = '$suppliers_id'";
    if ($filter['keywords'])
    {
        $where .= " AND uname LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
    }
    /*记录总数*/
    $sql = "SELECT count(*) FROM " .$GLOBALS['hhs']->table('comment'). " as c left join ".$GLOBALS['hhs']->table('goods')." as s on c.id_value = s.goods_id ". $where;
    $record_count   = $GLOBALS['db']->getOne($sql);
    $arr=$filter;
    unset($arr['page']);
    $arr['act']=$action;
    $arr['op']='false';
    /*分页*/
    $pager  = get_pager('index.php', $arr, $record_count, $page);
    /* 查询 */
    $sql  = "SELECT c.*,s.suppliers_id FROM " .$GLOBALS['hhs']->table('comment'). " as c left join ".$GLOBALS['hhs']->table('goods')." as s on c.id_value = s.goods_id ".$where." ORDER BY $filter[sort_by] $filter[sort_order] LIMIT $pager[start],$pager[size] ";
    $row = $GLOBALS['db']->getAll($sql);
    /*格式化数据*/
    foreach ($row AS $key => $value)
    {       
        $sql = ($row['comment_type'] == 0) ?
        "SELECT goods_name FROM " .$GLOBALS['hhs']->table('goods'). " WHERE goods_id='$value[id_value]'" :
        "SELECT title FROM ".$GLOBALS['hhs']->table('article'). " WHERE article_id='$value[id_value]'";
        $row[$key]['title'] = $GLOBALS['db']->getOne($sql);
        $row[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
    }
    $arr = array('message' => $row,'pager'=>$pager, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    return $arr;
}
?>