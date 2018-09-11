<?php

/**

 * ECSHOP 管理中心公用函数库

 * ============================================================================

 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。

 * 网站地址: http://www.ecshop.com；

 * ----------------------------------------------------------------------------

 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和

 * 使用；不允许对程序代码以任何形式任何目的的再发布。

 * ============================================================================

 * $Author: liubo $

 * $Id: lib_main.php 17217 2011-01-19 06:29:08Z liubo $

*/

if (!defined('IN_HHS'))

{

    die('Hacking attempt');

}

include_once(ROOT_PATH . 'business/includes/lib_goods.php');

/**

 *

 *

 * @access  public

 * @param

 * @return  void

 */

function make_json_result($content, $message='', $append=array())

{

    make_json_response($content, 0, $message, $append);

}



/**

 * 创建一个JSON格式的错误信息

 *

 * @access  public

 * @param   string  $msg

 * @return  void

 */

function make_json_error($msg)

{

    make_json_response('', 1, $msg);

}

/**

 * 取得站点列表

 * @param   int     $shipping_id    配送id

 */

function get_shipping_point_list($suppliers_id)

{

    $sql = "SELECT a.*,rp.region_name as province,rc.region_name as city,rd.region_name as district " .

                " FROM " . $GLOBALS['hhs']->table('shipping_point'). " AS a left join " .

                    $GLOBALS['hhs']->table('region') . " AS rp on a.province=rp.region_id left join ".

                    $GLOBALS['hhs']->table('region') . " as rc on a.city=rc.region_id left join ".

                    $GLOBALS['hhs']->table('region') ." as rd on a.district=rd.region_id  where a.suppliers_id='$suppliers_id'";

    $list=$GLOBALS['db']->getAll($sql);



    return $list;

}

/**

 * 创建一个JSON格式的数据

 *

 * @access  public

 * @param   string      $content

 * @param   integer     $error

 * @param   string      $message

 * @param   array       $append

 * @return  void

 */

function make_json_response($content='', $error="0", $message='', $append=array())

{

    include_once(ROOT_PATH . 'includes/cls_json.php');



    $json = new JSON;



    $res = array('error' => $error, 'message' => $message, 'content' => $content);



    if (!empty($append))

    {

        foreach ($append AS $key => $val)

        {

            $res[$key] = $val;

        }

    }



    $val = $json->encode($res);



    exit($val);

}



/**

 * 获得所有模块的名称以及链接地址

 *

 * @access      public

 * @param       string      $directory      插件存放的目录

 * @return      array

 */

function read_modules($directory = '.')

{

    global $_LANG;

    $dir = @opendir($directory);

    $set_modules = true;

    $modules = array();

    while (false !== ($file = @readdir($dir))) {

        if (preg_match('/^.*?\\.php$/', $file)) {

            include_once $directory . '/' . $file;

        }

    }

    @closedir($dir);

    unset($set_modules);

    foreach ($modules as $key => $value) {

        ksort($modules[$key]);

    }

    ksort($modules);

    return $modules;

}

/**

 * 取得配送区域列表

 * @param   int     $shipping_id    配送id

 */

function get_shipping_area_list($shipping_id, $suppliers_id)

{

    $sql = 'SELECT * FROM ' . $GLOBALS['hhs']->table('shipping_area');

    if ($shipping_id > 0) {

        $sql .= " WHERE shipping_id = '{$shipping_id}'";

    }

    if ($suppliers_id > 0) {

        $sql .= " AND supp_id = '{$suppliers_id}'";

    }

    $res = $GLOBALS['db']->query($sql);

    $list = array();

    while ($row = $GLOBALS['db']->fetchRow($res)) {

        $sql = 'SELECT r.region_name ' . 'FROM ' . $GLOBALS['hhs']->table('area_region') . ' AS a, ' . $GLOBALS['hhs']->table('region') . ' AS r ' . 'WHERE a.region_id = r.region_id ' . "AND a.shipping_area_id = '{$row['shipping_area_id']}'";

        $regions = join(', ', $GLOBALS['db']->getCol($sql));

        $row['shipping_area_regions'] = empty($regions) ? '<a href="shipping_area.php?act=region&amp;id=' . $row['shipping_area_id'] . '" style="color:red">' . $GLOBALS['_LANG']['empty_regions'] . '</a>' : $regions;

        $list[] = $row;

    }

    return $list;

}



/**

 * 获得商品类型的列表

 *

 * @access  public

 * @param   integer     $selected   选定的类型编号

 * @return  string

 */

function goods_type_list($selected)

{

    $sql = 'SELECT cat_id, cat_name FROM ' . $GLOBALS['hhs']->table('goods_type') . ' WHERE enabled = 1';

    $res = $GLOBALS['db']->query($sql);



    $lst = '';

    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        $lst .= "<option value='$row[cat_id]'";

        $lst .= ($selected == $row['cat_id']) ? ' selected="true"' : '';

        $lst .= '>' . htmlspecialchars($row['cat_name']). '</option>';

    }



    return $lst;

}

function get_pic_list($su_id,$size,$start)

{



	$where = "where id>0";



	if($_REQUEST['cat_id'])



	{



		$where .= " and cat_id='$_REQUEST[cat_id]'";



	}

		if($_REQUEST['keywords'])

	{

		$where .=" and pic_name like '%$_REQUEST[keywords]%'";	

	}



	$sql = $GLOBALS['db']->getAll("select * from ".$GLOBALS['hhs']->table('supp_pic_list')." $where and suppliers_id='$su_id' order by id desc limit $start,$size");



	foreach($sql as $idx=>$value)



	{



		//$sql[$idx]['shop_price'] = price_format($value['shop_price']);	



	}



	return $sql;

}







function get_suppliers_goods($su_id,$size,$start,$is_delete)

{

	$where = "where  suppliers_id='$su_id' and is_delete='$is_delete'";

	if($_REQUEST['goods_status'] != '')

	{

		$where .= " and is_on_sale='$_REQUEST[goods_status]' ";	

	}

	if($_REQUEST['is_check'] != '')

	{

		

		$where .= " and is_check='$_REQUEST[is_check]' ";	

	}

	if($_REQUEST['keywords']!='')

	{

		$where .= " and goods_name like '%%$_REQUEST[keywords]%%'";

	}

	if($_REQUEST['is_promote']!='')

	{

		$where .= " and is_promote='$_REQUEST[is_promote]'";

	}

	if($_REQUEST['is_season']!='')

	{

		$where .= " and is_season='$_REQUEST[is_season]'";

	}

	if($_REQUEST['is_supp_top']!='')

	{

		$where .= " and is_supp_top='$_REQUEST[is_supp_top]'";

	}

	

	

	$sql = $GLOBALS['db']->getAll("select * from ".$GLOBALS['hhs']->table('goods')." $where order by goods_id desc limit $start,$size");

	foreach($sql as $idx=>$value)

	{

		$sql[$idx]['shop_price'] = price_format($value['shop_price']);	

	}

	return $sql;

}



function create_html_editor_xaphp($input_name, $input_value = '',$supp_id)

{

    global $smarty;

	

    $kindeditor="<script charset='utf-8' src='includes/kindeditor/kindeditor-min.js'></script>

    <script>

        var editor;

		var SUPP_ID  = $supp_id;

            KindEditor.ready(function(K) {

                editor = K.create('textarea[name=\"$input_name\"]', {

					afterChange : function() {

						K('#desc_num').val(this.count());

					},

                    allowFileManager : true,

                    width : '700px',

                    height: '300px',

                    resizeType: 0    //固定宽高

                });

            });

    </script>

    <textarea id=\"$input_name\" name=\"$input_name\" style='width:700px;height:300px;'>$input_value</textarea>

    ";

    $smarty->assign('FCKeditor', $kindeditor);  //这里前面的 FCKEditor 不要变

}



function create_html_editor_xaphp1($input_name, $input_value = '',$supp_id)

{

    global $smarty;

    $kindeditor="<script charset='utf-8' src='includes/kindeditor/kindeditor-min.js'></script>

    <script>

        var editor;

		var SUPP_ID  = $supp_id;

            KindEditor.ready(function(K) {

                editor = K.create('textarea[name=\"$input_name\"]', {

                    allowFileManager : true,

                    width : '700px',

                    height: '300px',

                    resizeType: 0    //固定宽高

                });

            });

    </script>

    <textarea id=\"$input_name\" name=\"$input_name\" style='width:700px;height:300px;'>$input_value</textarea>

    ";

    $smarty->assign($input_name, $kindeditor);  //这里前面的 FCKEditor 不要变

}





function get_linked_goods_list($goods_id,$size,$start)



{



    $sql = "SELECT lg.link_goods_id AS goods_id,lg.id,lg.pass,g.shop_price,g.goods_sn, g.goods_name " .



            "FROM " . $GLOBALS['hhs']->table('agent_goods') . " AS lg, " .



                $GLOBALS['hhs']->table('goods') . " AS g " .



            "WHERE lg.suppliers_id = '$goods_id' " .



            "AND lg.link_goods_id = g.goods_id  limit $start,$size";



    $row = $GLOBALS['db']->getAll($sql);







    foreach ($row AS $key => $val)



    {







        $row[$key]['goods_name'] = $val['goods_name'];







        unset($row[$key]['is_double']);



    }



	







    return $row;



	



}











function get_linked_goods($goods_id)



{



    $sql = "SELECT lg.link_goods_id AS goods_id,g.shop_price, g.goods_name " .



            "FROM " . $GLOBALS['hhs']->table('agent_goods') . " AS lg, " .



                $GLOBALS['hhs']->table('goods') . " AS g " .



            "WHERE lg.suppliers_id = '$goods_id' " .



            "AND lg.link_goods_id = g.goods_id ";



    



    $row = $GLOBALS['db']->getAll($sql);







    foreach ($row AS $key => $val)



    {







        $row[$key]['goods_name'] = $val['goods_name'];







        unset($row[$key]['is_double']);



    }







    return $row;



}















function get_where_sql($filter)



{



    $time = date('Y-m-d');







    $where  = isset($filter->is_delete) && $filter->is_delete == '1' ?



        ' WHERE is_delete = 1 ' : ' WHERE is_delete = 0 ';



    $where .= (isset($filter->real_goods) && ($filter->real_goods > -1)) ? ' AND is_real = ' . intval($filter->real_goods) : '';



    $where .= isset($filter->cat_id) && $filter->cat_id > 0 ? ' AND ' . get_children($filter->cat_id) : '';



    $where .= isset($filter->brand_id) && $filter->brand_id > 0 ? " AND brand_id = '" . $filter->brand_id . "'" : '';



    $where .= isset($filter->intro_type) && $filter->intro_type != '0' ? ' AND ' . $filter->intro_type . " = '1'" : '';



    $where .= isset($filter->intro_type) && $filter->intro_type == 'is_promote' ?



        " AND promote_start_date <= '$time' AND promote_end_date >= '$time' " : '';



    $where .= isset($filter->keyword) && trim($filter->keyword) != '' ?



        " AND (goods_name LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_sn LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_id LIKE '%" . mysql_like_quote($filter->keyword) . "%') " : '';



    $where .= isset($filter->suppliers_id) && trim($filter->suppliers_id) != '' ?



        " AND (suppliers_id = '" . $filter->suppliers_id . "') " : '';







    $where .= isset($filter->in_ids) ? ' AND goods_id ' . db_create_in($filter->in_ids) : '';



    $where .= isset($filter->exclude) ? ' AND goods_id NOT ' . db_create_in($filter->exclude) : '';



    $where .= isset($filter->stock_warning) ? ' AND goods_number <= warn_number' : '';



    return $where;



}



function generate_goods_sn($goods_id)



{



    $goods_sn = $GLOBALS['_CFG']['sn_prefix'] . str_repeat('0', 6 - strlen($goods_id)) . $goods_id;







    $sql = "SELECT goods_sn FROM " . $GLOBALS['hhs']->table('goods') .



            " WHERE goods_sn LIKE '" . mysql_like_quote($goods_sn) . "%' AND goods_id <> '$goods_id' " .



            " ORDER BY LENGTH(goods_sn) DESC";



    $sn_list = $GLOBALS['db']->getCol($sql);



    if (in_array($goods_sn, $sn_list))



    {



        $max = pow(10, strlen($sn_list[0]) - strlen($goods_sn) + 1) - 1;



        $new_sn = $goods_sn . mt_rand(0, $max);



        while (in_array($new_sn, $sn_list))



        {



            $new_sn = $goods_sn . mt_rand(0, $max);



        }



        $goods_sn = $new_sn;



    }







    return $goods_sn;



}

//跳转到某地址



function getUrl($url,$timeout=0)



{



	echo "<meta http-equiv=\"refresh\" content=\"$timeout;url=$url\">";



}





function get_pic_cat_list($id)

{

	return $GLOBALS['db']->getAll("select * from ".$GLOBALS['hhs']->table('supp_pic_category')." where suppliers_id='$id'");

}













/**



 * 获取评论列表



 * @access  public



 * @return  array



 */



function get_comment_list($suppliers_id)

{

    /* 获取评论数据 */



    $arr = array();

    $sql  = "SELECT c.*,s.suppliers_id FROM " .$GLOBALS['hhs']->table('comment'). " as c left join ".$GLOBALS['hhs']->table('goods')." as s on c.id_value = s.goods_id WHERE c.parent_id = 0 and c.is_false < 1 and comment_type = 0 and  s.suppliers_id = ".$suppliers_id." order by c.add_time desc";

	$res  = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        $sql = ($row['comment_type'] == 0) ?

            "SELECT goods_name FROM " .$GLOBALS['hhs']->table('goods'). " WHERE goods_id='$row[id_value]'" :

            "SELECT title FROM ".$GLOBALS['hhs']->table('article'). " WHERE article_id='$row[id_value]'";

        $row['title'] = $GLOBALS['db']->getOne($sql);



        /* 标记是否回复过 */



//        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['hhs']->table('comment'). " WHERE parent_id = '$row[comment_id]'";



//        $row['is_reply'] =  ($GLOBALS['db']->getOne($sql) > 0) ?



//            $GLOBALS['_LANG']['yes_reply'] : $GLOBALS['_LANG']['no_reply'];



        $row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);

       $arr[] = $row;

    }

    return $arr;



}







/**



 * 返回某个订单可执行的操作列表，包括权限判断



 * @param   array   $order      订单信息 order_status, shipping_status, pay_status



 * @param   bool    $is_cod     支付方式是否货到付款



 * @return  array   可执行的操作  confirm, pay, unpay, prepare, ship, unship, receive, cancel, invalid, return, drop



 * 格式 array('confirm' => true, 'pay' => true)



 */



function operable_list($order)



{



    /* 取得订单状态、发货状态、付款状态 */



    $os = $order['order_status'];



    $ss = $order['shipping_status'];



    $ps = $order['pay_status'];



    /* 取得订单操作权限 */



    $actions = $_SESSION['action_list'];



//    if ($actions == 'all')

//

//    {

//

        $priv_list  = array('os' => true, 'ss' => true, 'ps' => true, 'edit' => true);



  //  }



    //else

//

//    {

//

//        $actions    = ',' . $actions . ',';

//

//        $priv_list  = array(

//

//            'os'    => strpos($actions, ',order_os_edit,') !== false,

//

//            'ss'    => strpos($actions, ',order_ss_edit,') !== false,

//

//            'ps'    => strpos($actions, ',order_ps_edit,') !== false,

//

//            'edit'  => strpos($actions, ',order_edit,') !== false

//

//        );

//

//    }

	









    /* 取得订单支付方式是否货到付款 */



    $payment = payment_info($order['pay_id']);



    $is_cod  = $payment['is_cod'] == 1;







    /* 根据状态返回可执行操作 */



    $list = array();



    if (OS_UNCONFIRMED == $os)



    {



        /* 状态：未确认 => 未付款、未发货 */



        if ($priv_list['os'])



        {



            $list['confirm']    = true; // 确认



            $list['invalid']    = true; // 无效



            $list['cancel']     = true; // 取消



            if ($is_cod)



            {



                /* 货到付款 */



                if ($priv_list['ss'])



                {



                    $list['prepare'] = true; // 配货



                    $list['split'] = true; // 分单



                }



            }



            else



            {



                /* 不是货到付款 */



                if ($priv_list['ps'])



                {



                    $list['pay'] = true;  // 付款



                }



            }



        }



    }



    elseif (OS_CONFIRMED == $os || OS_SPLITED == $os || OS_SPLITING_PART == $os)



    {



        /* 状态：已确认 */



        if (PS_UNPAYED == $ps)



        {



            /* 状态：已确认、未付款 */



            if (SS_UNSHIPPED == $ss || SS_PREPARING == $ss)



            {



                /* 状态：已确认、未付款、未发货（或配货中） */



                if ($priv_list['os'])



                {



                    $list['cancel'] = true; // 取消



                    $list['invalid'] = true; // 无效



                }



                if ($is_cod)



                {



                    /* 货到付款 */



                    if ($priv_list['ss'])



                    {



                        if (SS_UNSHIPPED == $ss)



                        {



                            $list['prepare'] = true; // 配货



                        }



                        $list['split'] = true; // 分单



                    }



                }



                else



                {



                    /* 不是货到付款 */



                    if ($priv_list['ps'])



                    {



                        $list['pay'] = true; // 付款



                    }



                }



            }



            /* 状态：已确认、未付款、发货中 */



            elseif (SS_SHIPPED_ING == $ss || SS_SHIPPED_PART == $ss)







            {



                // 部分分单



                if (OS_SPLITING_PART == $os)



                {



                    $list['split'] = true; // 分单



                }



                $list['to_delivery'] = true; // 去发货



            }



            else



            {



                /* 状态：已确认、未付款、已发货或已收货 => 货到付款 */



                if ($priv_list['ps'])



                {



                    $list['pay'] = true; // 付款



                }



                if ($priv_list['ss'])



                {



                    if (SS_SHIPPED == $ss)



                    {



                        //$list['receive'] = true; // 收货确认注释掉



                    }



                    $list['unship'] = true; // 设为未发货



                    if ($priv_list['os'])



                    {



                        $list['return'] = true; // 退货



                    }



                }



            }



        }



        else



        {



            /* 状态：已确认、已付款和付款中 */



            if (SS_UNSHIPPED == $ss || SS_PREPARING == $ss)



            {



                /* 状态：已确认、已付款和付款中、未发货（配货中） => 不是货到付款 */



                if ($priv_list['ss'])



                {



                    if (SS_UNSHIPPED == $ss)



                    {



                        $list['prepare'] = true; // 配货



                    }



                    $list['split'] = true; // 分单



                }



                if ($priv_list['ps'])



                {



                    $list['unpay'] = true; // 设为未付款



                    if ($priv_list['os'])



                    {



                        $list['cancel'] = true; // 取消



                    }



                }



            }



            /* 状态：已确认、未付款、发货中 */



            elseif (SS_SHIPPED_ING == $ss || SS_SHIPPED_PART == $ss)



            {



                // 部分分单



                if (OS_SPLITING_PART == $os)



                {



                    $list['split'] = true; // 分单



                }



                $list['to_delivery'] = true; // 去发货



            }



            else



            {



                /* 状态：已确认、已付款和付款中、已发货或已收货 */



                if ($priv_list['ss'])



                {



                    if (SS_SHIPPED == $ss)



                    {



                        $list['receive'] = true; // 收货确认



                    }



                    if (!$is_cod)



                    {



                        $list['unship'] = true; // 设为未发货



                    }



                }



                if ($priv_list['ps'] && $is_cod)



                {



                    $list['unpay']  = true; // 设为未付款



                }



                if ($priv_list['os'] && $priv_list['ss'] && $priv_list['ps'])



                {



                    $list['return'] = true; // 退货（包括退款）



                }



            }



        }



    }



    elseif (OS_CANCELED == $os)



    {



        /* 状态：取消 */



        if ($priv_list['os'])



        {



            $list['confirm'] = true;



        }



        if ($priv_list['edit'])



        {



            $list['remove'] = true;



        }



    }



    elseif (OS_INVALID == $os)



    {



        /* 状态：无效 */



        if ($priv_list['os'])



        {



            $list['confirm'] = true;



        }



        if ($priv_list['edit'])



        {



            $list['remove'] = true;



        }



    }



    elseif (OS_RETURNED == $os)



    {



        /* 状态：退货 */



        if ($priv_list['os'])



        {



            $list['confirm'] = true;



        }



    }







    /* 修正发货操作 */



    if (!empty($list['split']))



    {



        /* 如果是团购活动且未处理成功，不能发货 */



        if ($order['extension_code'] == 'group_buy')



        {



            include_once(ROOT_PATH . 'includes/lib_goods.php');



            $group_buy = group_buy_info(intval($order['extension_id']));



            if ($group_buy['status'] != GBS_SUCCEED)



            {



                unset($list['split']);



                unset($list['to_delivery']);



            }



        }







        /* 如果部分发货 不允许 取消 订单 */



        if (order_deliveryed($order['order_id']))



        {



            $list['return'] = true; // 退货（包括退款）



            unset($list['cancel']); // 取消



        }



    }







    /* 售后 */



    $list['after_service'] = true;







    return $list;



}

/**

 * 判断订单是否已发货（含部分发货）

 * @param   int     $order_id  订单 id

 * @return  int     1，已发货；0，未发货

 */

function order_deliveryed($order_id)

{

    $return_res = 0;



    if (empty($order_id))

    {

        return $return_res;

    }



    $sql = 'SELECT COUNT(delivery_id)

            FROM ' . $GLOBALS['hhs']->table('delivery_order') . '

            WHERE order_id = \''. $order_id . '\'

            AND status = 0';

    $sum = $GLOBALS['db']->getOne($sql);



    if ($sum)

    {

        $return_res = 1;

    }



    return $return_res;

}

function get_role($account_action_list)

{

	$list = $GLOBALS['db']->getAll("select * from ".$GLOBALS['hhs']->table('supp_action')." where parent_id=0");

	$account_action_list = explode(",",$account_action_list);

	foreach($list as $idx=>$value)

	{

		$action_list =$GLOBALS['db']->getAll("select * from ".$GLOBALS['hhs']->table('supp_action')." where parent_id='$value[id]'"); 

		foreach($action_list as $id=>$v)

		{

			if(in_array($v['action'],$account_action_list))	

			{

				$action_list[$id]['checked'] =1;

			}

		}

		$list[$idx]['action_list'] = $action_list;

	}

	return $list;

}



function get_action_list()

{

	$list = $GLOBALS['db']->getAll("select * from ".$GLOBALS['hhs']->table('supp_action')." where parent_id=0 ORDER BY sort");

	$role_id = $_SESSION['role_id'];

	if($role_id)

	{

		$role_action = $GLOBALS['db']->getOne("select role from ".$GLOBALS['hhs']->table('supp_account')." where account_id='$role_id' ORDER BY sort"); 

		$role_actions = explode(",",$role_action);

	

		foreach($list as $idx=>$value)

		{

				$action_lists = $GLOBALS['db']->getAll("select * from ".$GLOBALS['hhs']->table('supp_action')." where parent_id='$value[id]' ORDER BY sort");

				

				$i =0;

				foreach($action_lists as $id=>$v)

				{

					

					if(!in_array($v['action'],$role_actions))

					{

						unset($action_lists[$id]);

//						$new_list[$i]['action'] = $v['action'];

//						$new_list[$i]['id'] = $v['id'];

//						$new_list[$i]['action_name'] = $v['action_name'];

//						$new_list[$i]['action_link'] = $v['action_link'];	

//						$i++;

					}

					

				}

				$list[$idx]['action_lists'] = $action_lists;

		}

		return $list;

	}

	else

	{

		foreach($list as $idx=>$value)

		{

			$id = $value['id'];

			$list[$idx]['action_lists'] = $GLOBALS['db']->getAll("select * from ".$GLOBALS['hhs']->table('supp_action')." where parent_id='$id' ORDER BY sort ");

		}

		return $list;

	}

}



function delivery_order_info($delivery_id, $delivery_sn = '')



{



    $return_order = array();



    if (empty($delivery_id) || !is_numeric($delivery_id))



    {



        return $return_order;



    }







    $where = '';



  

    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('delivery_order');



    if ($delivery_id > 0)



    {



        $sql .= " WHERE delivery_id = '$delivery_id'";



    }



    else



    {



        $sql .= " WHERE delivery_sn = '$delivery_sn'";



    }







    $sql .= $where;



    $sql .= " LIMIT 0, 1";



    $delivery = $GLOBALS['db']->getRow($sql);



    if ($delivery)



    {



        /* 格式化金额字段 */



        $delivery['formated_insure_fee']     = price_format($delivery['insure_fee'], false);



        $delivery['formated_shipping_fee']   = price_format($delivery['shipping_fee'], false);







        /* 格式化时间字段 */



        $delivery['formated_add_time']       = local_date($GLOBALS['_CFG']['time_format'], $delivery['add_time']);



        $delivery['formated_update_time']    = local_date($GLOBALS['_CFG']['time_format'], $delivery['update_time']);







        $return_order = $delivery;



    }



    return $return_order;



}



/**



 * 判断订单的发货单是否全部发货







 * @param   int     $order_id  订单 id



 * @return  int     1，全部发货；0，未全部发货；-1，部分发货；-2，完全没发货；



 */



function get_all_delivery_finish($order_id)



{



    $return_res = 0;







    if (empty($order_id))



    {



        return $return_res;



    }







    /* 未全部分单 */



    if (!get_order_finish($order_id))



    {



        return $return_res;



    }



    /* 已全部分单 */



    else



    {



        // 是否全部发货



        $sql = "SELECT COUNT(delivery_id)



                FROM " . $GLOBALS['hhs']->table('delivery_order') . "



                WHERE order_id = '$order_id'



                AND status = 2 ";



        $sum = $GLOBALS['db']->getOne($sql);



        // 全部发货



        if (empty($sum))



        {



            $return_res = 1;



        }



        // 未全部发货



        else



        {



            /* 订单全部发货中时：当前发货单总数 */



            $sql = "SELECT COUNT(delivery_id)



            FROM " . $GLOBALS['hhs']->table('delivery_order') . "



            WHERE order_id = '$order_id'



            AND status <> 1 ";



            $_sum = $GLOBALS['db']->getOne($sql);



            if ($_sum == $sum)



            {



                $return_res = -2; // 完全没发货



            }



            else



            {



                $return_res = -1; // 部分发货



            }



        }



    }







    return $return_res;



}



/**



 * 订单中的商品是否已经全部发货



 * @param   int     $order_id  订单 id



 * @return  int     1，全部发货；0，未全部发货



 */



function get_order_finish($order_id)



{



    $return_res = 0;







    if (empty($order_id))



    {



        return $return_res;



    }







    $sql = 'SELECT COUNT(rec_id)



            FROM ' . $GLOBALS['hhs']->table('order_goods') . '



            WHERE order_id = \'' . $order_id . '\'



            AND goods_number > send_number';







    $sum = $GLOBALS['db']->getOne($sql);



    if (empty($sum))



    {



        $return_res = 1;



    }







    return $return_res;



}



//获得商家配置信息



function get_supp_config($id)

{

	$rows = $GLOBALS['db']->getRow("select * from ".$GLOBALS['hhs']->table('supp_config')." where suppliers_id='$id'");

	return $rows;

}



/**

 * 取得订单商品

 * @param   array     $order  订单数组

 * @return array

 */

function get_order_goods($order)

{

    $goods_list = array();

    $goods_attr = array();

    $sql = "SELECT o.*, g.suppliers_id AS suppliers_id,IF(o.product_id > 0, p.product_number, g.goods_number) AS storage, o.goods_attr, IFNULL(b.brand_name, '') AS brand_name, p.product_sn " .

            "FROM " . $GLOBALS['hhs']->table('order_goods') . " AS o ".

            "LEFT JOIN " . $GLOBALS['hhs']->table('products') . " AS p ON o.product_id = p.product_id " .

            "LEFT JOIN " . $GLOBALS['hhs']->table('goods') . " AS g ON o.goods_id = g.goods_id " .

            "LEFT JOIN " . $GLOBALS['hhs']->table('brand') . " AS b ON g.brand_id = b.brand_id " .

            "WHERE o.order_id = '$order[order_id]' ";

    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        // 虚拟商品支持

        if ($row['is_real'] == 0)

        {

            /* 取得语言项 */

            $filename = ROOT_PATH . 'plugins/' . $row['extension_code'] . '/languages/common_' . $GLOBALS['_CFG']['lang'] . '.php';

            if (file_exists($filename))

            {

                include_once($filename);

                if (!empty($GLOBALS['_LANG'][$row['extension_code'].'_link']))

                {

                    $row['goods_name'] = $row['goods_name'] . sprintf($GLOBALS['_LANG'][$row['extension_code'].'_link'], $row['goods_id'], $order['order_sn']);

                }

            }

        }



        $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);

        $row['formated_goods_price']    = price_format($row['goods_price']);



        $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组



        if ($row['extension_code'] == 'package_buy')

        {

            $row['storage'] = '';

            $row['brand_name'] = '';

            $row['package_goods_list'] = get_package_goods_list($row['goods_id']);

        }



        //处理货品id

        $row['product_id'] = empty($row['product_id']) ? 0 : $row['product_id'];



        $goods_list[] = $row;

    }



    $attr = array();

    $arr  = array();

    foreach ($goods_attr AS $index => $array_val)

    {

        foreach ($array_val AS $value)

        {

            $arr = explode(':', $value);//以 : 号将属性拆开

            $attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);

        }

    }



    return array('goods_list' => $goods_list, 'attr' => $attr);

}

/**

 * 订单单个商品或货品的已发货数量

 *

 * @param   int     $order_id       订单 id

 * @param   int     $goods_id       商品 id

 * @param   int     $product_id     货品 id

 *

 * @return  int

 */

function order_delivery_num($order_id, $goods_id, $product_id = 0)

{

    $sql = 'SELECT SUM(G.send_number) AS sums

            FROM ' . $GLOBALS['hhs']->table('delivery_goods') . ' AS G, ' . $GLOBALS['hhs']->table('delivery_order') . ' AS O

            WHERE O.delivery_id = G.delivery_id

            AND O.status = 0

            AND O.order_id = ' . $order_id . '

            AND G.extension_code <> "package_buy"

            AND G.goods_id = ' . $goods_id;



    $sql .= ($product_id > 0) ? " AND G.product_id = '$product_id'" : '';



    $sum = $GLOBALS['db']->getOne($sql);



    if (empty($sum))

    {

        $sum = 0;

    }



    return $sum;

}

/**

 * 更新订单商品信息

 * @param   int     $order_id       订单 id

 * @param   array   $_sended        Array(‘商品id’ => ‘此单发货数量’)

 * @param   array   $goods_list

 * @return  Bool

 */

function update_order_goods($order_id, $_sended, $goods_list = array())

{

    if (!is_array($_sended) || empty($order_id))

    {

        return false;

    }



    foreach ($_sended as $key => $value)

    {

        // 超值礼包

        if (is_array($value))

        {

            if (!is_array($goods_list))

            {

                $goods_list = array();

            }



            foreach ($goods_list as $goods)

            {

                if (($key != $goods['rec_id']) || (!isset($goods['package_goods_list']) || !is_array($goods['package_goods_list'])))

                {

                    continue;

                }



                $goods['package_goods_list'] = package_goods($goods['package_goods_list'], $goods['goods_number'], $goods['order_id'], $goods['extension_code'], $goods['goods_id']);

                $pg_is_end = true;



                foreach ($goods['package_goods_list'] as $pg_key => $pg_value)

                {

                    if ($pg_value['order_send_number'] != $pg_value['sended'])

                    {

                        $pg_is_end = false; // 此超值礼包，此商品未全部发货



                        break;

                    }

                }



                // 超值礼包商品全部发货后更新订单商品库存

                if ($pg_is_end)

                {

                    $sql = "UPDATE " . $GLOBALS['hhs']->table('order_goods') . "

                            SET send_number = goods_number

                            WHERE order_id = '$order_id'

                            AND goods_id = '" . $goods['goods_id'] . "' ";



                    $GLOBALS['db']->query($sql, 'SILENT');

                }

            }

        }

        // 商品（实货）（货品）

        elseif (!is_array($value))

        {

            /* 检查是否为商品（实货）（货品） */

            foreach ($goods_list as $goods)

            {

                if ($goods['rec_id'] == $key && $goods['is_real'] == 1)

                {

                    $sql = "UPDATE " . $GLOBALS['hhs']->table('order_goods') . "

                            SET send_number = send_number + $value

                            WHERE order_id = '$order_id'

                            AND rec_id = '$key' ";

                    $GLOBALS['db']->query($sql, 'SILENT');

                    break;

                }

            }

        }

    }



    return true;

}

function get_supp_account_list($suppliers_id)

{

	$sql = "select * from ".$GLOBALS['hhs']->table('supp_account')."where suppliers_id = ".$suppliers_id." and is_check=1 order by sort_order asc";

	$account_list = $GLOBALS['db']->getAll($sql);

	return $account_list;

}

function get_supp_account_name($supp_account_id)

{

	$sql = $GLOBALS['db']->getOne("select name from ".$GLOBALS['hhs']->table('supp_account')." where account_id='$supp_account_id'");

	return $sql;

}

function get_delivery_list($is_page=true,$shipping_id = null){

	$suppliers_id=$_SESSION['suppliers_id'];



	$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

	/* 过滤信息 */

	$filter['delivery_sn'] = empty($_REQUEST['delivery_sn']) ? '' : trim($_REQUEST['delivery_sn']);

	$filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);

	$filter['order_id'] = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);

	if ($aiax == 1 && !empty($_REQUEST['consignee']))

	{

		$_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);

	}

	$filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);

	$filter['status'] = isset($_REQUEST['status']) ? $_REQUEST['status'] : -1;

	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'update_time' : trim($_REQUEST['sort_by']);

	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

	$filter['supp_account_id'] = isset($_REQUEST['supp_account_id']) ? $_REQUEST['supp_account_id'] : -1;//$_SESSION['role_id'];

	

	if($_SESSION['role_id']&&$_SESSION['account_type'])

	{

		$filter['supp_account_id'] = $_SESSION['role_id'];

	}

	$filter['delivery_pic'] = $_REQUEST['delivery_pic'];

    $where = ' WHERE suppliers_id="'.$suppliers_id.'"';

	if ($filter['order_sn'])

	{

		$where .= " AND order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";

	}

	if ($filter['consignee'])

	{

		$where .= " AND consignee LIKE '%" . mysql_like_quote($filter['consignee']) . "%'";

	}

	if ($filter['status'] >= 0)

	{

		$where .= " AND status = '" . mysql_like_quote($filter['status']) . "'";

	}

	if ($filter['delivery_sn'])

	{

		$where .= " AND delivery_sn LIKE '%" . mysql_like_quote($filter['delivery_sn']) . "%'";

	}

	if ($filter['supp_account_id']>=0)

	{

		$where .=" AND supp_account_id=".$filter['supp_account_id'];

	}

	

	if($filter['delivery_pic']==1)

	{

		$where .=" AND delivery_pic!='' ";

	}

	if($filter['delivery_pic']==0)

	{

		$where .=" AND delivery_pic='' ";

	}



    /**

     * 增加deliver_id

     */

    if($shipping_id)

    {

        $where .=" AND shipping_id > 0 ";

    }

	/* 分页大小 */

	if($is_page){

		$filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

		

		if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)

		{

			$filter['page_size'] = intval($_REQUEST['page_size']);

		}

		

		elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)

		

		{

		

			$filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);

		

		}

		

		else

		

		{

		

			$filter['page_size'] = 15;

		

		}

	}

	

	/* 记录总数 */

	

	$sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('delivery_order') . $where;

	

	$record_count   = $GLOBALS['db']->getOne($sql);	

	if($is_page){

		$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

		$arr=$filter;

		unset($arr['page']);

        // $arr['act']='delivery_list';        

		$arr['act']= trim($_REQUEST['act']);

		$arr['op'] ='order';

		$pager  = get_pager('index.php', $arr,$record_count, $page,$filter['page_size']);

	}

	

	/* 查询 */

	$sql = "SELECT delivery_id,supp_account_id,delivery_pic,delivery_person, delivery_sn,invoice_no, order_sn, order_id, add_time, action_user, consignee, country,

	province, city, district, tel, status, update_time, email, suppliers_id

	FROM " . $GLOBALS['hhs']->table("delivery_order") . "

	$where

	ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order'];

	if($is_page){

		$sql.=" LIMIT $pager[start],$pager[size] ";

	}



	$row = $GLOBALS['db']->getAll($sql);

	/* 格式化数据 */

	foreach ($row AS $key => $value)

	{

	$row[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);

	$row[$key]['update_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['update_time']);

	$row[$key]['supp_account_name'] = get_supp_account_name($value['supp_account_id']);

	if ($value['status'] == 1)

	{

	$row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][1];

	}

	elseif ($value['status'] == 2)

	{

	$row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][2];

	}

	else

	{

	$row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][0];

	}

	$row[$key]['suppliers_name'] = isset($_suppliers_list[$value['suppliers_id']]) ? $_suppliers_list[$value['suppliers_id']] : '';

	}

	

	$arr = array('delivery' => $row, 'filter' => $filter,'pager'=>$pager, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

	return $arr;

}

function get_order_list($is_page=true,$action=null){

	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	/* 过滤信息 */

	

	$filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);

	

	$filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);

	

	$filter['email'] = empty($_REQUEST['email']) ? '' : trim($_REQUEST['email']);

	

	$filter['address'] = empty($_REQUEST['address']) ? '' : trim($_REQUEST['address']);

	

	$filter['zipcode'] = empty($_REQUEST['zipcode']) ? '' : trim($_REQUEST['zipcode']);

	

	$filter['tel'] = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);

	

	$filter['mobile'] = empty($_REQUEST['mobile']) ? 0 : intval($_REQUEST['mobile']);

	

	$filter['country'] = empty($_REQUEST['country']) ? 0 : intval($_REQUEST['country']);

	

	$filter['province'] = empty($_REQUEST['province']) ? 0 : intval($_REQUEST['province']);

	

	$filter['city'] = empty($_REQUEST['city']) ? 0 : intval($_REQUEST['city']);

	

	$filter['district'] = empty($_REQUEST['district']) ? 0 : intval($_REQUEST['district']);

	

	$filter['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);

	

	$filter['pay_id'] = empty($_REQUEST['pay_id']) ? 0 : intval($_REQUEST['pay_id']);

	

	$filter['order_status'] = isset($_REQUEST['order_status']) ? intval($_REQUEST['order_status']) : -1;

	

	$filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? intval($_REQUEST['shipping_status']) : -1;

	

	$filter['pay_status'] = isset($_REQUEST['pay_status']) ? intval($_REQUEST['pay_status']) : -1;

	

	$filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);

	

	$filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);

	

	$filter['composite_status'] = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;

	

	$filter['group_buy_id'] = isset($_REQUEST['group_buy_id']) ? intval($_REQUEST['group_buy_id']) : 0;

	

	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);

	

	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

	

	$filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);

	

	$filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);

	

	$filter['action'] = $action= empty($action) ? ($_REQUEST['action']) : $action;

	

	$filter['team_status'] = isset($_REQUEST['team_status']) ? trim($_REQUEST['team_status']) : '-1';

	

	$filter['type'] = empty($_REQUEST['type']) ? '' : trim($_REQUEST['type']);

	

	$filter['point_id'] = isset($_REQUEST['point_id']) ? trim($_REQUEST['point_id']) : '-1';

	

	$filter['checked_mobile'] = isset($_REQUEST['checked_mobile']) ? trim($_REQUEST['checked_mobile']) : '';

	

	$suppliers_id=$_SESSION['suppliers_id'];

	

	$where = 'WHERE o.suppliers_id='.$suppliers_id;

	

	if ($filter['order_sn'])

	

	{

		$where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";	

	}

	if ($filter['consignee'])

	

	{

		$where .= " AND o.consignee LIKE '%" . mysql_like_quote($filter['consignee']) . "%'";	

	}

	if ($filter['email'])

	{

	

		$where .= " AND o.email LIKE '%" . mysql_like_quote($filter['email']) . "%'";

	

	}

	

	if ($filter['address'])

	

	{

	

		$where .= " AND o.address LIKE '%" . mysql_like_quote($filter['address']) . "%'";

	

	}

	

	if ($filter['zipcode'])

	

	{

	

		$where .= " AND o.zipcode LIKE '%" . mysql_like_quote($filter['zipcode']) . "%'";

	

	}

	

	if ($filter['tel'])

	

	{

	

		$where .= " AND o.tel LIKE '%" . mysql_like_quote($filter['tel']) . "%'";

	

	}

	

	if($filter['team_status'] != '-1')

	{

		$where .=" AND team_status='$filter[team_status]' and extension_code='team_goods'";

	}

	

	

	if($filter['point_id'] != '-1')

	{

		$where .=" AND o.point_id='$filter[point_id]'";

	}

	

	if($filter['checked_mobile'] != '')

	{

		$where .=" AND o.checked_mobile='$filter[checked_mobile]'";

	}	

	if($filter['type']>0)

	{

		if($filter['type']==1)

		{

			$where .=" AND  extension_code='team_goods'";

		}

		else

		{

			$where .=" AND  extension_code=''";

		}

		

	}

	

	if ($filter['mobile'])

	

	{

	

		$where .= " AND o.mobile LIKE '%" .mysql_like_quote($filter['mobile']) . "%'";

	

	}

	

	if ($filter['country'])

	

	{

	

		$where .= " AND o.country = '$filter[country]'";

	

	}

	

	if ($filter['province'])

	

	{

	

		$where .= " AND o.province = '$filter[province]'";

	

	}

	

	if ($filter['city'])

	

	{

	

		$where .= " AND o.city = '$filter[city]'";

	

	}

	

	if ($filter['district'])

	

	{

	

		$where .= " AND o.district = '$filter[district]'";

	

	}

	

	if ($filter['shipping_id'])

	

	{

	

		$where .= " AND o.shipping_id  = '$filter[shipping_id]'";

	

	}

	

	if ($filter['pay_id'])

	

	{

	

		$where .= " AND o.pay_id  = '$filter[pay_id]'";

	

	}

	

	if ($filter['order_status'] != -1)

	

	{

	

		$where .= " AND o.order_status  = '$filter[order_status]'";

	

	}

	

	if ($filter['shipping_status'] != -1)

	

	{

	

		$where .= " AND o.shipping_status = '$filter[shipping_status]'";

	

	}

	

	if ($filter['pay_status'] != -1)

	

	{

	

		$where .= " AND o.pay_status = '$filter[pay_status]'";

	

	}

	

	if ($filter['user_id'])

	

	{

	

		$where .= " AND o.user_id = '$filter[user_id]'";

	

	}

	

	if ($filter['user_name'])

	

	{

	

		$where .= " AND u.uname LIKE '%" . mysql_like_quote($filter['user_name']) . "%'";

	

	}

	

	if ($filter['start_time'])

	

	{

	

		$where .= " AND o.add_time >= '$filter[start_time]'";

	

	}

	

	if ($filter['end_time'])

	

	{

	

		$where .= " AND o.add_time <= '$filter[end_time]'";

	

	}

	//综合状态

	

	switch($filter['composite_status'])

	{

		case CS_AWAIT_PAY :

			$where .= order_query_sql('await_pay');

			break;

		case CS_AWAIT_SHIP :

	

                $where.= " and ((o.extension_code='team_goods' and o.team_status=2  ".order_query_sql('await_ship').") or (o.extension_code!='team_goods' ".order_query_sql('await_ship').") )";

	

			break;

	

        case CS_SHIPPED :

            

            $where .= order_query_sql('shipped2');

            break;

	

		case CS_FINISHED :

	

			$where .= order_query_sql('finished');

	

			break;

	



        case PS_REFUNDED :

    

            $where .= " AND o.pay_status = 3 ";

    

            break;



        case CS_PAYED :

    

            if ($filter['composite_status'] != -1)

    

            {

    

                $where .= " AND o.pay_status = 2 ";

    

            }

    

            break;	

	

		case PS_PAYING :

	

			if ($filter['composite_status'] != -1)

	

			{

	

				$where .= " AND o.pay_status = '$filter[composite_status]' ";

	

			}

	

			break;

	

		case OS_SHIPPED_PART :

	

			if ($filter['composite_status'] != -1)

	

			{

	

				$where .= " AND o.shipping_status  = '$filter[composite_status]'-2 ";

	

			}

	

			break;

	

		default:

	

			if ($filter['composite_status'] != -1)

	

			{

	

				$where .= " AND o.order_status = '$filter[composite_status]' ";

	

			}

	}



	

	/* 团购订单 */

	

	if ($filter['group_buy_id'])

	{

		$where .= " AND o.extension_code = 'group_buy' AND o.extension_id = '$filter[group_buy_id]' ";

	}



	/* 如果管理员属于某个办事处，只列出这个办事处管辖的订单 */

	

	$sql = "SELECT agency_id FROM " . $GLOBALS['hhs']->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";

	

	$agency_id = $GLOBALS['db']->getOne($sql);

	

	if ($agency_id > 0)

	{

		$where .= " AND o.agency_id = '$agency_id' ";

	}

	/*

	if($ext){

	    $where .=$ext;

	}*/

	if($action=='goods_order'){

        // $ext=" and o.shipping_id<> " . offlineID;

	    $ext=" and o.point_id =0 ";

	}else{

        // $ext=" and o.shipping_id= " . offlineID;

	    $ext=" and o.point_id>0 ";

	}

	$where .=$ext;

	if($is_page){

		/* 分页大小 */

		$page = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

		/* 记录总数 */

		if ($filter['user_name'])

		{

			$sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('order_info') . " AS o ,".

		

					$GLOBALS['hhs']->table('users') . " AS u " . $where;

		

		}

		else

		{

			$sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('order_info') . " AS o ". $where;

		

		}

		

		$record_count   = $GLOBALS['db']->getOne($sql);

		$arr=$filter;

		unset($arr['page']);



		$arr['act']=$action;

		$arr['op']='order';

		$pager  = get_pager('index.php', $arr, $record_count, $page);

		

	}

	

	/* 查询 */

	

	$sql = "SELECT o.*, " .

	

			"(" . order_amount_field('o.') . ") AS total_fee, " .

	

			"IFNULL(u.uname, '" .$GLOBALS['_LANG']['anonymous']. "') AS buyer ".

	

			" FROM " . $GLOBALS['hhs']->table('order_info') . " AS o " .

	

			" LEFT JOIN " .$GLOBALS['hhs']->table('users'). " AS u ON u.user_id=o.user_id ". $where .

	

			" ORDER BY $filter[sort_by] $filter[sort_order] ";



	if($is_page){

		$sql.=" LIMIT $pager[start],$pager[size]";

	}



	foreach (array('order_sn', 'consignee', 'email', 'address', 'zipcode', 'tel', 'user_name') AS $val)

	

	{

		$filter[$val] = stripslashes($filter[$val]);

	

	}



	$row = $GLOBALS['db']->getAll($sql);

	/* 格式话数据 */

	foreach ($row AS $key => $value)

	{

		//统计商品数量

		$sql="select goods_number from ".$GLOBALS['hhs']->table('order_goods')." as og where og.order_id=".$value['order_id'];

		$goods_number=$GLOBALS['db']->getAll($sql);

		$row[$key]['goods_num']=0;	

		foreach($goods_number as $v){

			$row[$key]['goods_num']+=$v['goods_number'];

		}		

	

		$row[$key]['formated_order_amount'] = price_format($value['order_amount']);

	

		$row[$key]['formated_money_paid'] = price_format($value['money_paid']);

	

		$row[$key]['formated_total_fee'] = price_format($value['total_fee']);

	

        $row[$key]['short_order_time'] = local_date('m-d H:i', $value['add_time']);

		$row[$key]['short_pay_time'] = local_date('m-d H:i', $value['pay_time']);

	

		if ($value['order_status'] == OS_INVALID || $value['order_status'] == OS_CANCELED)

	

		{	

			/* 如果该订单为无效或取消则显示删除链接 */

	

			$row[$key]['can_remove'] = 1;

	

		}

	

		else

	

		{

			$row[$key]['can_remove'] = 0;

		}	

		$row[$key]['user_name']=$GLOBALS['db']->getOne('select uname from hhs_users where user_id='.$value['user_id']);

        if($value['point_id']){

            $row[$key]['consignee']=$GLOBALS['db']->getOne('select CONCAT("【自提】",`address`,",",`shop_name`) from hhs_shipping_point where id='.$value['point_id']);

            $row[$key]['address'] = '';

        }

	}

	

	$arr = array('orders' => $row,'pager'=>$pager, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

	return $arr;	

}



function get_account_list($is_page=true){

	

	$suppliers_id=$_SESSION['suppliers_id'];

	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$where=" where sa.suppliers_id=".$suppliers_id;

	$filter['settlement_status'] = empty($_REQUEST['settlement_status']) ? '' : intval($_REQUEST['settlement_status']);

	$filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);

	$filter['start_time'] = empty($_REQUEST['start_time']) ? '' : trim($_REQUEST['start_time']);

	$filter['end_time'] = empty($_REQUEST['end_time']) ? '' : trim($_REQUEST['end_time']);

	

	if($filter['settlement_status'] != '')

	{		

		$where .= " and sa.settlement_status=".$filter['settlement_status'];

	}

	if($filter['order_sn'] != '')

	{		

		$where .= " and sa.settlement_sn like '%".$filter['order_sn']."%'";

	}

	/*

	if($filter['start_time']!='')

	{		

		$start_time=local_strtotime($filter['start_time']);

	    $where .= " and sa.start_time>=".$start_time;

	}

	if($filter['end_time']!='')

	{

		$end_time=local_strtotime($filter['end_time']);

		$where .= " and sa.end_time<=".$end_time;

	}*/

	if($filter['start_time']!='')

	{

	    $start_time=local_strtotime($filter['start_time']);

	    $where .= " and sa.add_time>=".$start_time;

	}

	if($filter['end_time']!='')

	{

	    $end_time=local_strtotime($filter['end_time']);

	    $where .= " and sa.add_time<=".$end_time;

	}

	

	#echo $where;

	if($is_page){

		$sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('suppliers_accounts') . ' as sa '. $where;

		$record_count = $GLOBALS['db']->getOne($sql);

		$arr=$filter;

		$arr['act']='my_order';	

		$arr['op']='account';	

		$pager = get_pager('index.php',$arr , $record_count, $page);

		

	}

	//$time = local_strtotime('-'.$_CFG['agent_apply_day'].' day');

	

	$sql = "SELECT sa.* ".

			" FROM " . $GLOBALS['hhs']->table("suppliers_accounts") . " as sa ".

			$where." ORDER BY sa.id desc ";

	if($is_page){

		$res = $GLOBALS['db']->SelectLimit($sql, $pager['size'], $pager['start']);	

	}else{

		$res = $GLOBALS['db']->query($sql);

		

	}

	#echo $sql;

	$total_settlement_amount=0;

	while ($row = $GLOBALS['db']->fetchRow($res))

	{

		$total_settlement_amount+=$row['settlement_amount'];

		$row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);

		$row['settlement_amount'] = price_format($row['settlement_amount'],false);

		$row['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['start_time']);

		$row['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['end_time']);

		$row['commission'] = price_format($row['commission'],false);

	

		$account_list[] = $row;

	}

	$arr=array('account_list'=>$account_list,

			'filter'=>$filter,'pager'=>$pager,

			'record_count'=>$record_count,

			'total_settlement_amount'=>$total_settlement_amount

			);

	return $arr;

}



/*------------------------------------------------------ */

//--订单统计需要的函数

/*------------------------------------------------------ */

 /**

  * 取得订单概况数据(包括订单的几种状态)

  * @param       $start_date    开始查询的日期

  * @param       $end_date      查询的结束日期

  * @return      $order_info    订单概况数据

  */

 function get_orderinfo($start_date, $end_date,$suppliers_id)

 {

    $order_info = array();



    /* 未确认订单数 */

    $sql = 'SELECT COUNT(*) AS unconfirmed_num FROM ' .$GLOBALS['hhs']->table('order_info').

           " WHERE suppliers_id='$suppliers_id' and order_status = '" .OS_UNCONFIRMED. "' AND add_time >= '$start_date'".

           " AND add_time < '" . ($end_date + 86400) . "'";



    $order_info['unconfirmed_num'] = $GLOBALS['db']->getOne($sql);



    /* 已确认订单数 */

    $sql = 'SELECT COUNT(*) AS confirmed_num FROM ' .$GLOBALS['hhs']->table('order_info').

           " WHERE  suppliers_id='$suppliers_id' and order_status = '" .OS_CONFIRMED. "' AND shipping_status NOT ". db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . " AND pay_status NOT" . db_create_in(array(PS_PAYED, PS_PAYING)) ." AND add_time >= '$start_date'".

           " AND add_time < '" . ($end_date + 86400) . "'";

    $order_info['confirmed_num'] = $GLOBALS['db']->getOne($sql);



    /* 已成交订单数 */

    $sql = 'SELECT COUNT(*) AS succeed_num FROM ' .$GLOBALS['hhs']->table('order_info').

           " WHERE  suppliers_id='$suppliers_id' " . order_query_sql('finished') .

           " AND add_time >= '$start_date' AND add_time < '" . ($end_date + 86400) . "'";

    $order_info['succeed_num'] = $GLOBALS['db']->getOne($sql);



    /* 无效或已取消订单数 */

    $sql = "SELECT COUNT(*) AS invalid_num FROM " .$GLOBALS['hhs']->table('order_info').

           " WHERE suppliers_id='$suppliers_id' and order_status > '" .OS_CONFIRMED. "'".

           " AND add_time >= '$start_date' AND add_time < '" . ($end_date + 86400) . "'";

    $order_info['invalid_num'] = $GLOBALS['db']->getOne($sql);

    return $order_info;

 }



/*------------------------------------------------------ */

//--获取销售明细需要的函数

/*------------------------------------------------------ */

/**

 * 取得销售明细数据信息

 * @param   bool  $is_pagination  是否分页

 * @return  array   销售明细数据

 */

function get_sale_list($is_pagination = true,$suppliers_id){



    /* 时间参数 */

    $filter['start_date'] = empty($_REQUEST['start_date']) ? local_strtotime('-7 days') : local_strtotime($_REQUEST['start_date']);

    $filter['end_date'] = empty($_REQUEST['end_date']) ? local_strtotime('today') : local_strtotime($_REQUEST['end_date']);

  

    /* 查询数据的条件 */

    $where = " WHERE og.order_id = oi.order_id". order_query_sql('finished', 'oi.') .

             " AND oi.suppliers_id='$suppliers_id'  and oi.add_time >= '".$filter['start_date']."' AND oi.add_time < '" . ($filter['end_date']) . "'";



	

	

    $sql = "SELECT COUNT(og.goods_id) FROM " .

           $GLOBALS['hhs']->table('order_info') . ' AS oi,'.

           $GLOBALS['hhs']->table('order_goods') . ' AS og '.

           $where;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql);



    /* 分页大小 */

      

	$page = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

	$record_count   = $filter['record_count'];

	//$filter['end_date'] = $filter['end_date'];

	

	//echo $filter['start_date']."---------".$filter['end_date'];

	//echo $where;exit;

	$filter['start_date'] = local_date("Y-m-d",$filter['start_date']);

	$filter['end_date'] = local_date("Y-m-d",$filter['end_date']);

	$arr=$filter;

	unset($arr['page']);

	

	$arr['act']='sale_list';

	$arr['op']='statistical';

	

	

	

	$pager  = get_pager('index.php', $arr, $record_count, $page);	

	

	

	



    $sql = 'SELECT og.goods_id, og.goods_sn, og.goods_name, og.goods_number AS goods_num, og.goods_price '.

           'AS sales_price, oi.add_time AS sales_time, oi.order_id, oi.order_sn '.

           "FROM " . $GLOBALS['hhs']->table('order_goods')." AS og, ".$GLOBALS['hhs']->table('order_info')." AS oi ".

           $where. " ORDER BY sales_time DESC, goods_num DESC";

		//   echo $where;exit;

		

    if ($is_pagination)

    {

        $sql .= " LIMIT " . $pager['start'] . ', ' . $pager['size'];

    }

    $sale_list_data = $GLOBALS['db']->getAll($sql);

    foreach ($sale_list_data as $key => $item)

    {

        $sale_list_data[$key]['sales_price'] = price_format($sale_list_data[$key]['sales_price']);

        $sale_list_data[$key]['sales_time']  = local_date($GLOBALS['_CFG']['time_format'], $sale_list_data[$key]['sales_time']);

    }

    $arr = array('sale_list_data' => $sale_list_data,'pager'=>$pager, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;

}



/*------------------------------------------------------ */

//--排行统计需要的函数

/*------------------------------------------------------ */

/**

 * 取得销售排行数据信息

 * @param   bool  $is_pagination  是否分页

 * @return  array   销售排行数据

 */

function get_sales_order($is_pagination = true,$suppliers_id)

{

    $filter['start_date'] =$_REQUEST['start_date'];

    $filter['end_date'] = $_REQUEST['end_date'];

    $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'goods_num' : trim($_REQUEST['sort_by']);

    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    $where = " WHERE og.order_id = oi.order_id and oi.suppliers_id='$suppliers_id' ". order_query_sql('finished', 'oi.');



    if ($filter['start_date'])

    {

        $where .= " AND oi.add_time >= '" . $filter['start_date'] . "'";

    }

    if ($filter['end_date'])

    {

        $where .= " AND oi.add_time <= '" . $filter['end_date'] . "'";

    }

	$page = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);



    $sql = "SELECT COUNT(distinct(og.goods_id)) FROM " .

           $GLOBALS['hhs']->table('order_info') . ' AS oi,'.

           $GLOBALS['hhs']->table('order_goods') . ' AS og '.

           $where;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql);



    /* 分页大小 */

	$record_count   = $filter['record_count'];

	$filter['start_date'] = local_date("Y-m-d",$filter['start_date']);

	$filter['end_date'] = local_date("Y-m-d",$filter['end_date']);

	$arr=$filter;

	unset($arr['page']);

	$arr['act']='sale_order';

	$arr['op']='statistical';

	$pager  = get_pager('index.php', $arr, $record_count, $page);	



    $sql = "SELECT og.goods_id, og.goods_sn, og.goods_name, oi.order_status, " .

           "SUM(og.goods_number) AS goods_num, SUM(og.goods_number * og.goods_price) AS turnover ".

           "FROM ".$GLOBALS['hhs']->table('order_goods')." AS og, " .

           $GLOBALS['hhs']->table('order_info')." AS oi  " .$where .

           " GROUP BY og.goods_id ".

           ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] ;

		

    if ($is_pagination)

    {

        $sql .= " LIMIT " . $pager['start'] . ', ' . $pager['size'];

    }

    $sales_order_data = $GLOBALS['db']->getAll($sql);



    foreach ($sales_order_data as $key => $item)

    {

        $sales_order_data[$key]['wvera_price'] = price_format($item['goods_num'] ? $item['turnover'] / $item['goods_num'] : 0);

        $sales_order_data[$key]['short_name']  = sub_str($item['goods_name'], 30, true);

        $sales_order_data[$key]['turnover']    = price_format($item['turnover']);

        $sales_order_data[$key]['taxis']       = $key + 1;

    }



    $arr = array('sales_order_data' => $sales_order_data,'pager'=>$pager, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);



    return $arr;

}

/**

 * 获取优惠劵类型列表

 * @access  public

 * @return void

 */

function get_type_list($supp_id)

{

    /* 获得所有优惠劵类型的发放数量 */

    $sql = "SELECT bonus_type_id, COUNT(*) AS sent_count".

            " FROM " .$GLOBALS['hhs']->table('user_bonus') .

            " where suppliers_id='$supp_id' GROUP BY bonus_type_id";

    $res = $GLOBALS['db']->query($sql);

	$page = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

    $sent_arr = array();

    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        $sent_arr[$row['bonus_type_id']] = $row['sent_count'];

    }

    /* 获得所有优惠劵类型的发放数量 */

    $sql = "SELECT bonus_type_id, COUNT(*) AS used_count".

            " FROM " .$GLOBALS['hhs']->table('user_bonus') .

            " WHERE used_time > 0 and suppliers_id='$supp_id'".

            " GROUP BY bonus_type_id";

    $res = $GLOBALS['db']->query($sql);



    $used_arr = array();

    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        $used_arr[$row['bonus_type_id']] = $row['used_count'];

    }



    

        /* 查询条件 */

        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'type_id' : trim($_REQUEST['sort_by']);

        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);



        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['hhs']->table('bonus_type')."  where suppliers_id='$supp_id'";

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);



      

	  /* 分页大小 */

	  $record_count   = $filter['record_count'];

	  

	  $arr=$filter;

	  $arr['op']='bonus';

	  $arr['act']='bonus';

	  $pager  = get_pager('index.php', $arr, $record_count, $page);	

	  



	  

      $sql = "SELECT * FROM " .$GLOBALS['hhs']->table('bonus_type'). " where suppliers_id='$supp_id' ORDER BY $filter[sort_by] $filter[sort_order] limit $pager[start],$pager[size]";

	

    $arr = array();

    $res = $GLOBALS['db']->query($sql);





    while ($row = $GLOBALS['db']->fetchRow($res))

    {

		

        $row['send_by'] = $GLOBALS['_LANG']['send_by'][$row['send_type']];

        $row['send_count'] = isset($sent_arr[$row['type_id']]) ? $sent_arr[$row['type_id']] : 0;

        $row['use_count'] = isset($used_arr[$row['type_id']]) ? $used_arr[$row['type_id']] : 0;



        $arr[] = $row;

    }

    $arr = array('item' => $arr, 'filter' => $filter,'pager'=>$pager, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);



    return $arr;

}

/**

 * 取得优惠劵类型数组（用于生成下拉列表）

 *

 * @return  array       分类数组 bonus_typeid => bonus_type_name

 */

function get_bonus_type($suppliers_id = 0)

{

    $bonus = array();

    $sql = 'SELECT type_id, type_name, type_money FROM ' . $GLOBALS['hhs']->table('bonus_type') .

           ' WHERE suppliers_id = "'.$suppliers_id.'" AND send_type = 3';

    $res = $GLOBALS['db']->query($sql);



    while ($row = $GLOBALS['db']->fetchRow($res))

    {

        $bonus[$row['type_id']] = $row['type_name'].' [' .sprintf($GLOBALS['_CFG']['currency_format'], $row['type_money']).']';

    }



    return $bonus;

}

/**

 * 查询优惠劵类型的商品列表

 *

 * @access  public

 * @param   integer $type_id

 * @return  array

 */

function get_bonus_goods($type_id)

{

    $sql = "SELECT goods_id, goods_name,use_goods_sn FROM " .$GLOBALS['hhs']->table('goods').

            " WHERE bonus_type_id = '$type_id'";

    $row = $GLOBALS['db']->getAll($sql);



	foreach($row as $key => $val)

	{

		

		if(isset($val['use_goods_sn']) && strlen($val['use_goods_sn']) > 0)

		{ 

			$row[$key]['use_goods_name']  = $GLOBALS['db']->getOne("SELECT goods_name FROM " .$GLOBALS['hhs']->table('goods')." WHERE goods_sn = '".$val['use_goods_sn']."'");

		}

	}

	

    return $row;

}

/**

 * 取得商品列表：用于把商品添加到组合、关联类、赠品类

 * @param   object  $filters    过滤条件

 */

function get_goods_list($filter)

{

    $filter->keyword = json_str_iconv($filter->keyword);

	$where  = isset($filter->is_delete) && $filter->is_delete == '1' ?

        ' WHERE is_delete = 1 ' : ' WHERE is_delete = 0 ';

	if($filter->cat_id>0)

	{

		$where.=" and cat_id=".$filter->cat_id;

	}

	if($filter->suppliers_id!='')

	{

		$where.=" and suppliers_id=".$filter->suppliers_id." ";

	}

    $where .= isset($filter->keyword) && trim($filter->keyword) != '' ?

        " AND (goods_name LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_sn LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_id LIKE '%" . mysql_like_quote($filter->keyword) . "%') " : '';



    /* 取得数据 */

    $sql = 'SELECT goods_id, goods_name,suppliers_id, shop_price '.

           'FROM ' . $GLOBALS['hhs']->table('goods') . ' AS g ' . $where .

           'LIMIT 50';

    $row = $GLOBALS['db']->getAll($sql);

	foreach($row  as $id=>$v)

	{

		$row[$id]['goods_name'] = $v['goods_name'];	

	}



    return $row;

}

/**

 * 获取用户优惠劵列表

 * @access  public

 * @param   $page_param

 * @return void

 */

function get_bonus_list($supp_id)

{

    /* 查询条件 */

    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'bonus_type_id' : trim($_REQUEST['sort_by']);

    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    $filter['bonus_type'] = empty($_REQUEST['bonus_type']) ? 0 : intval($_REQUEST['bonus_type']);

	$page = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

    $where = empty($filter['bonus_type']) ? '' : " WHERE  bonus_type_id='$filter[bonus_type]'";

    $sql = "SELECT COUNT(*) FROM ".$GLOBALS['hhs']->table('user_bonus'). $where;

    $record_count = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */

	

	$arr=$filter;

    $arr['act']='bonus_list';

	$arr['op']='bonus';

	$pager  = get_pager('?', $arr, $record_count, $page);	



    $sql = "SELECT ub.*, u.user_name,u.uname, u.email, o.order_sn, bt.type_name ".

          " FROM ".$GLOBALS['hhs']->table('user_bonus'). " AS ub ".

          " LEFT JOIN " .$GLOBALS['hhs']->table('bonus_type'). " AS bt ON bt.type_id=ub.bonus_type_id ".

          " LEFT JOIN " .$GLOBALS['hhs']->table('users'). " AS u ON u.user_id=ub.user_id ".

          " LEFT JOIN " .$GLOBALS['hhs']->table('order_info'). " AS o ON o.order_id=ub.order_id $where ".

          " ORDER BY ".$filter['sort_by']." ".$filter['sort_order'].

          " LIMIT ". $pager['start'] .", $pager[size]";

    $row = $GLOBALS['db']->getAll($sql);



    foreach ($row AS $key => $val)

    {

        $row[$key]['used_time'] = $val['used_time'] == 0 ?

            $GLOBALS['_LANG']['no_use'] : local_date($GLOBALS['_CFG']['date_format'], $val['used_time']);

        $row[$key]['emailed'] = $GLOBALS['_LANG']['mail_status'][$row[$key]['emailed']];

    }



    $arr = array('item' => $row, 'filter' => $filter,'pager'=>$pager, 'page_count' => $pager['page_count'], 'record_count' => $pager['record_count']);



    return $arr;

}

/**

 * 取得优惠劵类型信息

 * @param   int     $bonus_type_id  优惠劵类型id

 * @return  array

 */

function bonus_type_info($bonus_type_id)

{

    $sql = "SELECT * FROM " . $GLOBALS['hhs']->table('bonus_type') .

            " WHERE type_id = '$bonus_type_id'";



    return $GLOBALS['db']->getRow($sql);

}

/**

 * 取得上次的过滤条件

 * @param   string  $param_str  参数字符串，由list函数的参数组成

 * @return  如果有，返回array('filter' => $filter, 'sql' => $sql)；否则返回false

 */

function get_filter($param_str = '')

{

    $filterfile = basename(PHP_SELF, '.php');

    if ($param_str)

    {

        $filterfile .= $param_str;

    }

    if (isset($_GET['uselastfilter']) && isset($_COOKIE['HHSCP']['lastfilterfile'])

        && $_COOKIE['HHSCP']['lastfilterfile'] == sprintf('%X', crc32($filterfile)))

    {

        return array(

            'filter' => unserialize(urldecode($_COOKIE['HHSCP']['lastfilter'])),

            'sql'    => base64_decode($_COOKIE['HHSCP']['lastfiltersql'])

        );

    }

    else

    {

        return false;

    }

}



/**

 * 退回余额、积分、优惠劵（取消、无效、退货时），把订单使用余额、积分、优惠劵设为0

 * @param   array   $order  订单信息

 */

function return_user_surplus_integral_bonus($order)

{

	/* 处理余额、积分、优惠劵 */

	if ($order['user_id'] > 0 && $order['surplus'] > 0)

	{

		$surplus = $order['money_paid'] < 0 ? $order['surplus'] + $order['money_paid'] : $order['surplus'];

		log_account_change($order['user_id'], $surplus, 0, 0, 0, sprintf($GLOBALS['_LANG']['return_order_surplus'], $order['order_sn']));

		$GLOBALS['db']->query("UPDATE ". $GLOBALS['hhs']->table('order_info') . " SET `order_amount` = '0' WHERE `order_id` =". $order['order_id']);

	}



	if ($order['user_id'] > 0 && $order['integral'] > 0)

	{

		log_account_change($order['user_id'], 0, 0, 0, $order['integral'], sprintf($GLOBALS['_LANG']['return_order_integral'], $order['order_sn']));

	}



	if ($order['bonus_id'] > 0)

	{

		unuse_bonus($order['bonus_id']);

	}



	/* 修改订单 */

	$arr = array(

			'bonus_id'  => 0,

			'bonus'     => 0,

			'integral'  => 0,

			'integral_money'    => 0,

			'surplus'   => 0

	);

	update_order($order['order_id'], $arr);

}





function get_goods_cat($data)

{

	if($data['cat_four'] == "" && $data['cat_three'] != "")

	{

		$data['cat_id']	= $data['cat_three'];

		

	}

	else if($data['cat_three'] == "" && $data['cat_two'] != "")

	{

		

		$data['cat_id']	= $data['cat_two'];

	}

	else if($data['cat_two'] == "" )

	{

		$data['cat_id']	= $data['cat_one'];

	}

	else

	{

		$data['cat_id']	= $data['cat_four'];

	}

	

	return $data['cat_id'];

}



function get_each_cat($cat_id){

    if($cat_id<=0){

    	return array(0);

    }

    $arr=array();

    $parent_id=$cat_id; 

    $arr[]=$parent_id;

    do{

        $sql="select parent_id from ".$GLOBALS['hhs']->table('category')." where cat_id = ".$parent_id;

        $parent_id=$GLOBALS['db']->getOne($sql);

        $arr[]=$parent_id;     

    }while($parent_id>0);

    return array_reverse($arr);  

}



/**

 * 获得指定分类下的子分类的数组

 *

 * @access  public

 * @param   int     $cat_id     分类的ID

 * @param   int     $selected   当前选中分类的ID

 * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组

 * @param   int     $level      限定返回的级数。为0时返回所有级数

 * @param   int     $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。

 * @return  mix

 */

function my_cat_list($suppliers_id,$cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)

{

	static $res = NULL;



    if ($res === NULL)

    {

        

            $sql = "SELECT c.cat_id, c.cat_name,  c.parent_id, c.is_show, c.show_in_nav, c.grade, c.sort_order, COUNT(s.cat_id) AS has_children ".

                'FROM ' . $GLOBALS['hhs']->table('goods_category') . " AS c ".

                "LEFT JOIN " . $GLOBALS['hhs']->table('goods_category') . " AS s ON s.parent_id=c.cat_id  where c.suppliers_id = ".$suppliers_id." ".

                "GROUP BY c.cat_id ".

                'ORDER BY c.parent_id, c.sort_order ASC';

		

            $res = $GLOBALS['db']->getAll($sql);





            $sql = "SELECT cat_id, COUNT(*) AS goods_num " .

                    " FROM " . $GLOBALS['hhs']->table('goods') .

                    " WHERE is_delete = 0 AND is_on_sale = 1 " .

                    " GROUP BY cat_id";

            $res2 = $GLOBALS['db']->getAll($sql);



            $sql = "SELECT gc.cat_id, COUNT(*) AS goods_num " .

                    " FROM " . $GLOBALS['hhs']->table('goods_cat') . " AS gc , " . $GLOBALS['hhs']->table('goods') . " AS g " .

                    " WHERE g.goods_id = gc.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 " .

                    " GROUP BY gc.cat_id";

            $res3 = $GLOBALS['db']->getAll($sql);



            $newres = array();

            foreach($res2 as $k=>$v)

            {

                $newres[$v['cat_id']] = $v['goods_num'];

                foreach($res3 as $ks=>$vs)

                {

                    if($v['cat_id'] == $vs['cat_id'])

                    {

                    $newres[$v['cat_id']] = $v['goods_num'] + $vs['goods_num'];

                    }

                }

            }



            foreach($res as $k=>$v)

            {

                $res[$k]['goods_num'] = !empty($newres[$v['cat_id']]) ? $newres[$v['cat_id']] : 0;

            }

          

    }



    if (empty($res) == true)

    {

        return $re_type ? '' : array();

    }



    $options = my_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组



    $children_level = 99999; //大于这个分类的将被删除

    if ($is_show_all == false)

    {

        foreach ($options as $key => $val)

        {

            if ($val['level'] > $children_level)

            {

                unset($options[$key]);

            }

            else

            {

                if ($val['is_show'] == 0)

                {

                    unset($options[$key]);

                    if ($children_level > $val['level'])

                    {

                        $children_level = $val['level']; //标记一下，这样子分类也能删除

                    }

                }

                else

                {

                    $children_level = 99999; //恢复初始值

                }

            }

        }

    }



    /* 截取到指定的缩减级别 */

    if ($level > 0)

    {

        if ($cat_id == 0)

        {

            $end_level = $level;

        }

        else

        {

            $first_item = reset($options); // 获取第一个元素

            $end_level  = $first_item['level'] + $level;

        }



        /* 保留level小于end_level的部分 */

        foreach ($options AS $key => $val)

        {

            if ($val['level'] >= $end_level)

            {

                unset($options[$key]);

            }

        }

    }



    if ($re_type == true)

    {

        $select = '';

        foreach ($options AS $var)



        {

            $select .= '<option value="' . $var['cat_id'] . '" ';

            $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';

            $select .= '>';

            if ($var['level'] > 0)

            {

                $select .= str_repeat('&nbsp;', $var['level'] * 4);

            }

            $select .= htmlspecialchars(addslashes($var['cat_name']), ENT_QUOTES) . '</option>';

        }



        return $select;

    }

    else

    {

        foreach ($options AS $key => $value)

        {

            $options[$key]['url'] = build_uri('category', array('cid' => $value['cat_id']), $value['cat_name']);

        }



        return $options;

    }

}



/**

 * 过滤和排序所有分类，返回一个带有缩进级别的数组

 *

 * @access  private

 * @param   int     $cat_id     上级分类ID

 * @param   array   $arr        含有所有分类的数组

 * @param   int     $level      级别

 * @return  void

 */

function my_cat_options($spec_cat_id, $arr)

{

    static $cat_options = array();



    if (isset($cat_options[$spec_cat_id]))

    {

        return $cat_options[$spec_cat_id];

    }



    if (!isset($cat_options[0]))

    {

        $level = $last_cat_id = 0;

        $options = $cat_id_array = $level_array = array();

       

            while (!empty($arr))

            {

                foreach ($arr AS $key => $value)

                {

                    $cat_id = $value['cat_id'];

                    if ($level == 0 && $last_cat_id == 0)

                    {

                        if ($value['parent_id'] > 0)

                        {

                            break;

                        }



                        $options[$cat_id]          = $value;

                        $options[$cat_id]['level'] = $level;

                        $options[$cat_id]['id']    = $cat_id;

                        $options[$cat_id]['name']  = $value['cat_name'];

                        unset($arr[$key]);



                        if ($value['has_children'] == 0)

                        {

                            continue;

                        }

                        $last_cat_id  = $cat_id;

                        $cat_id_array = array($cat_id);

                        $level_array[$last_cat_id] = ++$level;

                        continue;

                    }



                    if ($value['parent_id'] == $last_cat_id)

                    {

                        $options[$cat_id]          = $value;

                        $options[$cat_id]['level'] = $level;

                        $options[$cat_id]['id']    = $cat_id;

                        $options[$cat_id]['name']  = $value['cat_name'];

						

						

						

						

                        unset($arr[$key]);



                        if ($value['has_children'] > 0)

                        {

                            if (end($cat_id_array) != $last_cat_id)

                            {

                                $cat_id_array[] = $last_cat_id;

                            }

                            $last_cat_id    = $cat_id;

                            $cat_id_array[] = $cat_id;

                            $level_array[$last_cat_id] = ++$level;

                        }

						else

						{

							$options[$cat_id]['is_last']  = 1;

						}

                    }

                    elseif ($value['parent_id'] > $last_cat_id)

                    {

                        break;

                    }

                }



                $count = count($cat_id_array);

                if ($count > 1)

                {

                    $last_cat_id = array_pop($cat_id_array);

                }

                elseif ($count == 1)

                {

                    if ($last_cat_id != end($cat_id_array))

                    {

                        $last_cat_id = end($cat_id_array);

                    }

                    else

                    {

                        $level = 0;

                        $last_cat_id = 0;

                        $cat_id_array = array();

                        continue;

                    }

                }



                if ($last_cat_id && isset($level_array[$last_cat_id]))

                {

                    $level = $level_array[$last_cat_id];

                }

                else

                {

                    $level = 0;

                }

            }

            

        

        $cat_options[0] = $options;

    }

    else

    {

        $options = $cat_options[0];

    }



    if (!$spec_cat_id)

    {

        return $options;

    }

    else

    {

        if (empty($options[$spec_cat_id]))

        {

            return array();

        }



        $spec_cat_id_level = $options[$spec_cat_id]['level'];



        foreach ($options AS $key => $value)

        {

            if ($key != $spec_cat_id)

            {

                unset($options[$key]);

            }

            else

            {

                break;

            }

        }



        $spec_cat_id_array = array();

        foreach ($options AS $key => $value)

        {

            if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||

                ($spec_cat_id_level > $value['level']))

            {

                break;

            }

            else

            {

                $spec_cat_id_array[$key] = $value;

            }

        }

        $cat_options[$spec_cat_id] = $spec_cat_id_array;



        return $spec_cat_id_array;

    }

}



function account_detail_list(){



    $suppliers_accounts_id=$_REQUEST['suppliers_accounts_id'];

    

    $where=" where sat.suppliers_accounts_id=".$suppliers_accounts_id;

    

    $sql = "SELECT sat.*,(sat.amount-sat.commission-sat.fenxiao_money) as money,o.suppliers_id,o.consignee,o.pay_name,o.user_id " .

    

        " FROM " . $GLOBALS['hhs']->table("suppliers_accounts_detal") . " as sat left join " .

    

        $GLOBALS['hhs']->table("order_info") . " as o on sat.order_id=o.order_id " .

    

        $where . " ORDER BY sat.id desc" ;

    

    $row=$GLOBALS['db']->getAll($sql);

    foreach ($row as $idx => $value)

    

    {

    

        $row[$idx]['order_time'] = local_date('Y-m-d', $value['order_time']);

    

        $total_amount += $row[$idx]['amount'];

    

        $total_commission += $row[$idx]['commission'];

    

        $total_fenxiao += $row[$idx]['fenxiao_money'];



        $total_money += ($row[$idx]['amount'] - $row[$idx]['commission'] - $row[$idx]['fenxiao_money']);

    

        $row[$idx]['suppliers_name'] = get_suppliers_name($value['suppliers_id']);

        if($value['user_id']){

            $row[$idx]['user_name'] = $GLOBALS['db']->getOne("select user_name from hhs_users where user_id=".$value['user_id']);

        }

        $transaction_order_sn = $GLOBALS['db']->getOne("select order_sn from ".$GLOBALS['hhs']->table('order_info')." where order_id='$value[new_parent_id]'");

    

        $row[$idx]['transaction_order_sn'] = $transaction_order_sn;

    

        $temp=array('order_id'=>$value['order_id']);

        $order_goods=get_order_goods($temp);

    

        $row[$idx]['goods'] =$order_goods;

        /*

        $sql="select goods_name from ".$GLOBALS['hhs']->table('order_goods')." where order_id=".$value['order_id'];

        $goods_name=$db->getAll($sql);*/

        $str="";

        $total_goods_num=0;

        foreach($order_goods['goods_list'] as $v){

            $str.=$v['goods_name']."<br>";

            $total_goods_num+=$v['goods_number'];

        }

        

        $row[$idx]['goods_name']=$str;

        $row[$idx]['total_goods_num']=$total_goods_num;

    }

    

    return array('row'=>$row,'total_amount'=>$total_amount,'total_commission'=>$total_commission,'total_money'=>$total_money,'total_fenxiao'=>$total_fenxiao);



}



/**

 * 

 * 根据php的$_SERVER['HTTP_USER_AGENT'] 中各种浏览器访问时所包含各个浏览器特定的字符串来判断是属于PC还是移动端

 * @author           discuz3x

 * @lastmodify    2014-04-09

 * @return  BOOL

 */

function checkmobile() {

 global $_G;

 $mobile = array();

//各个触控浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组

 static $touchbrowser_list =array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',

    'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',

    'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',

    'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',

    'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',

    'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',

    'benq', 'haier', '^lct', '320x320', '240x320', '176x220');

//window手机浏览器数组【猜的】

 static $mobilebrowser_list =array('windows phone');

//wap浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组

 static $wmlbrowser_list = array('cect', 'compal', 'ctl', 'lg', 'nec', 'tcl', 'alcatel', 'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom',

   'pantech', 'dopod', 'philips', 'haier', 'konka', 'kejian', 'lenovo', 'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh',

   'sed', 'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte');

 $pad_list = array('pad', 'gt-p1000');

 $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);

 if(dstrpos($useragent, $pad_list)) {

  return false;

 }

 if(($v = dstrpos($useragent, $mobilebrowser_list, true))){

  $_G['mobile'] = $v;

  return '1';

 }

 if(($v = dstrpos($useragent, $touchbrowser_list, true))){

  $_G['mobile'] = $v;

  return '2';

 }

 if(($v = dstrpos($useragent, $wmlbrowser_list))) {

  $_G['mobile'] = $v;

  return '3'; //wml版

 }

 $brower = array('mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop');

 if(dstrpos($useragent, $brower)) return false;

 $_G['mobile'] = 'unknown';

//对于未知类型的浏览器，通过$_GET['mobile']参数来决定是否是手机浏览器

 if(isset($_G['mobiletpl'][$_GET['mobile']])) {

  return true;

 } else {

  return false;

 }

}

/**

 * 判断$arr中元素字符串是否有出现在$string中

 * @param  $string     $_SERVER['HTTP_USER_AGENT'] 

 * @param  $arr          各中浏览器$_SERVER['HTTP_USER_AGENT']中必定会包含的字符串

 * @param  $returnvalue 返回浏览器名称还是返回布尔值，true为返回浏览器名称，false为返回布尔值【默认】

 * @author           discuz3x

 * @lastmodify    2014-04-09

 */

function dstrpos($string, $arr, $returnvalue = false) {

 if(empty($string)) return false;

 foreach((array)$arr as $v) {

  if(strpos($string, $v) !== false) {

   $return = $returnvalue ? $v : true;

   return $return;

  }

 }

 return false;

}
/*
*
*
*获取指定商家的团购列表
*
*
*不知道怎样写
*
*
*
*
*
**/
function get_teammen_list($is_page=true,$action=null)
{
	/*初始化首页*/
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	
	/*过滤条件*/
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
	
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	
	$filter['team_status'] = isset($_REQUEST['team_status']) ? intval($_REQUEST['team_status']) : -1;
	
	$suppliers_id=$_SESSION['suppliers_id'];
	
	$where = "WHERE o.extension_code='team_goods' and o.team_first=1 and o.suppliers_id=".$suppliers_id;
	
	/* 如果管理员属于某个办事处，只列出这个办事处管辖的订单 */
	
	$sql = "SELECT agency_id FROM " . $GLOBALS['hhs']->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
	
	$agency_id = $GLOBALS['db']->getOne($sql);
	
	if ($agency_id > 0)
	{
	
		$where .= " AND o.agency_id = '$agency_id' ";
	
	}
	if ($filter['team_status'] != -1)
	{
	
		$where .= " AND o.team_status  = '$filter[team_status]'";
	
	}
	
	/*记录总数*/
	
	$sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('order_info') . " AS o ". $where;
	
	$record_count   = $GLOBALS['db']->getOne($sql);
	
	$arr=$filter;
	
	unset($arr['page']);

	$arr['act']=$action;
	
	$arr['op']='team_order';
	/*分页*/
	$pager  = get_pager('index.php', $arr, $record_count, $page);
	
	/* 查询 */
	
	$sql = "SELECT g.goods_sn,g.goods_name,g.goods_number,g.team_num as goods_team_num,o.user_id,o.discount_type,o.discount_amount,o.team_num,u.uname,o.teammen_num,(o.team_num-o.teammen_num) as team_lack_num, o.team_status,o.team_sign,o.team_first,o.extension_code, o.order_id, o.order_sn, o.add_time,o.pay_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid," .

            "o.pay_status, o.consignee, o.address, o.email, o.tel,  o.extension_id,u.openid,u.uname, " .
	
			"(" . order_amount_field('o.') . ") AS total_fee, " .
	
			"IFNULL(u.uname, '" .$GLOBALS['_LANG']['anonymous']. "') AS buyer ".
	
			" FROM " . $GLOBALS['hhs']->table('order_info') . " AS o " .
			
			" LEFT JOIN " .$GLOBALS['hhs']->table('goods'). " AS g ON g.goods_id=o.extension_id ".
	
			" LEFT JOIN " .$GLOBALS['hhs']->table('users'). " AS u ON u.user_id=o.user_id ". $where .
	
			" ORDER BY $filter[sort_by] $filter[sort_order] ";

	if($is_page)
	{
	
		$sql.=" LIMIT $pager[start],$pager[size]";
	
	}

	foreach (array('order_sn', 'consignee', 'email', 'address', 'zipcode', 'tel', 'user_name') AS $val)
	{
		
		$filter[$val] = stripslashes($filter[$val]);
	
	}
	
	$row = $GLOBALS['db']->getAll($sql);
	
	/*格式化数据*/
	
	foreach ($row AS $key => $value)
	{		
	
		$row[$key]['formated_order_amount'] = price_format($value['order_amount']);
	
		$row[$key]['formated_money_paid'] = price_format($value['money_paid']);
	
		$row[$key]['formated_total_fee'] = price_format($value['total_fee']);
	
        $row[$key]['short_order_time'] = local_date('m-d H:i', $value['add_time']);
		$row[$key]['short_pay_time'] = local_date('m-d H:i', $value['pay_time']);
	
		if ($value['order_status'] == OS_INVALID || $value['order_status'] == OS_CANCELED)
	
		{	
			/* 如果该订单为无效或取消则显示删除链接 */
	
			$row[$key]['can_remove'] = 1;	
		}
	
		else
		{
		
			$row[$key]['can_remove'] = 0;
		
		}	
		//开团时间结束时间
		if($value['team_sign'] ){

            $sql="select pay_time from ".$GLOBALS['hhs']->table('order_info')." where order_id=".$value['team_sign'];

            $team_start_time=$GLOBALS['db']->getOne($sql);

            if($team_start_time){

                $row[$key]['team_start_date'] = local_date('Y-m-d H:i:s', $team_start_time);

                $row[$key]['team_end_date'] = local_date('Y-m-d H:i:s', $team_start_time+$GLOBALS['_CFG']['team_suc_time']*24*3600);

            }

        }
		
		
		
		
	}
	
	$arr = array('orders' => $row,'pager'=>$pager, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	
	return $arr;

}
/*获取团标志的订单*/

function get_team_sign_list($team_sign,$action=null)
{
	
	$team_sign = intval($team_sign);
	
	/*初始化首页*/
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	
	/*过滤条件*/
	$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
	
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);	
	
	$suppliers_id=$_SESSION['suppliers_id'];
	
	$where = "WHERE o.extension_code='team_goods' and o.suppliers_id=".$suppliers_id." and o.team_sign = ".$team_sign;
	
	/* 如果管理员属于某个办事处，只列出这个办事处管辖的订单 */
	
	$sql = "SELECT agency_id FROM " . $GLOBALS['hhs']->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
	
	$agency_id = $GLOBALS['db']->getOne($sql);
	
	if ($agency_id > 0)
	{
	
		$where .= " AND o.agency_id = '$agency_id' ";
	
	}
	
	
	
	
	/*记录总数*/
	
	$sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('order_info') . " AS o ". $where;
	
	$record_count   = $GLOBALS['db']->getOne($sql);
	
	$arr=$filter;
	
	unset($arr['page']);

	$arr['act']=$action;
	
	$arr['op']='team_order';
	/*分页*/
	$pager  = get_pager('index.php', $arr, $record_count, $page);
	
	/* 查询 */

    $sql ="SELECT o.discount_type,o.discount_amount,o.team_num,o.transaction_id,u.uname,o.teammen_num,(o.team_num-o.teammen_num) as team_lack_num, o.team_status,o.team_sign,o.team_first,o.extension_code, o.order_id, o.order_sn, o.add_time,o.pay_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid," .

            "o.pay_status, o.consignee, o.address, o.email, o.tel,  o.extension_id, " .


            "(" . order_amount_field('o.') . ") AS total_fee, " .

            "IFNULL(u.uname, '" .$GLOBALS['_LANG']['anonymous']. "') AS buyer,u.openid,u.uname,u.headimgurl, ".

            "g.goods_name,g.goods_sn ,g.goods_id,g.shop_price ".


            " FROM " . $GLOBALS['hhs']->table('order_info') . " AS o " .

            " LEFT JOIN " .$GLOBALS['hhs']->table('users'). " AS u ON u.user_id=o.user_id ".

            " LEFT JOIN " .$GLOBALS['hhs']->table('goods'). " AS g ON g.goods_id=o.extension_id ".$where." ORDER BY $filter[sort_by] $filter[sort_order] LIMIT $pager[start],$pager[size] ";
	
	$row = $GLOBALS['db']->getAll($sql);

	/*格式化数据*/
	
	foreach ($row AS $key => $value)
	{		
	
		$row[$key]['formated_order_amount'] = price_format($value['order_amount']);
	
		$row[$key]['formated_money_paid'] = price_format($value['money_paid']);
	
		$row[$key]['formated_total_fee'] = price_format($value['total_fee']);
	
        $row[$key]['short_order_time'] = local_date('m-d H:i', $value['add_time']);
		$row[$key]['short_pay_time'] = local_date('m-d H:i', $value['pay_time']);
	
		
		//开团时间结束时间
		if($value['team_sign'] ){

            $sql="select pay_time from ".$GLOBALS['hhs']->table('order_info')." where order_id=".$value['team_sign'];

            $team_start_time=$GLOBALS['db']->getOne($sql);

            if($team_start_time){

                $row[$key]['team_start_date'] = local_date('Y-m-d H:i:s', $team_start_time);

                $row[$key]['team_end_date'] = local_date('Y-m-d H:i:s', $team_start_time+$GLOBALS['_CFG']['team_suc_time']*24*3600);

            }

        }
	
	}
	
	$arr = array('orders' => $row,'pager'=>$pager, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	
	return $arr;
}
/*获取商家添加的虚拟会员*/
function get_false_user_list($action=null)
{
    /*初始化首页*/
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    /*过滤条件*/
    $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'user_id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);    
    $suppliers_id=$_SESSION['suppliers_id'];
    $where = "WHERE is_false = 1 and sup_id=".$suppliers_id;
    if ($filter['keywords'])
    {
        $where .= " AND uname LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
    }
    /*记录总数*/
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['hhs']->table('users') . " AS o ". $where;
    $record_count   = $GLOBALS['db']->getOne($sql);
    $arr=$filter;
    unset($arr['page']);
    $arr['act']=$action;
    $arr['op']='false';
    /*分页*/
    $pager  = get_pager('index.php', $arr, $record_count, $page);
    /* 查询 */
    $sql = "SELECT comment_num,headimgurl,sex,user_id, user_name,is_subscribe,uname, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time ".
                " FROM " . $GLOBALS['hhs']->table('users') .$where." ORDER BY $filter[sort_by] $filter[sort_order] LIMIT $pager[start],$pager[size] ";
    $row = $GLOBALS['db']->getAll($sql);
    /*格式化数据*/
    foreach ($row AS $key => $value)
    {       
    
        $row[$key]['reg_time'] = local_date('Y-m-d H:i', $value['reg_time']);
        
        $row[$key]['headimgurl'] = '/data/headimgurl/'.$value['headimgurl'];
    
    }
    $arr = array('orders' => $row,'pager'=>$pager, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    return $arr;
}
?>