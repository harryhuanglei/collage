<?php

if (!defined('IN_HHS'))

{

    die('Hacking attempt');

}



/**



 * 根据属性数组创建属性的表单



 *



 * @access  public



 * @param   int     $cat_id     分类编号



 * @param   int     $goods_id   商品编号



 * @return  string



 */



function build_attr_html($cat_id, $goods_id = 0)



{



    $attr = get_attr_list($cat_id, $goods_id);



    $html = '<table id="attrTable">';



    $spec = 0;







    foreach ($attr AS $key => $val)



    {



        $html .= "<tr><td class='label'>";



        if ($val['attr_type'] == 1 || $val['attr_type'] == 2)



        {



            $html .= ($spec != $val['attr_id']) ?



                "<a href='javascript:;' onclick='addSpec(this)'>[+]</a>" :



                "<a href='javascript:;' onclick='removeSpec(this)'>[-]</a>";



            $spec = $val['attr_id'];



        }







        $html .= "$val[attr_name]</td><td><input type='hidden' class='input' name='attr_id_list[]' value='$val[attr_id]' />";







        if ($val['attr_input_type'] == 0)



        {



            $html .= '<input name="attr_value_list[]" class="input" type="text" value="' .htmlspecialchars($val['attr_value']). '" size="40" /> ';



        }



        elseif ($val['attr_input_type'] == 2)



        {



            $html .= '<textarea name="attr_value_list[]" rows="3" cols="40">' .htmlspecialchars($val['attr_value']). '</textarea>';



        }



        else



        {



            $html .= '<select name="attr_value_list[]">';



            $html .= '<option value="">请选择...</option>';







            $attr_values = explode("\n", $val['attr_values']);







            foreach ($attr_values AS $opt)



            {



                $opt    = trim(htmlspecialchars($opt));







                $html   .= ($val['attr_value'] != $opt) ?



                    '<option value="' . $opt . '">' . $opt . '</option>' :



                    '<option value="' . $opt . '" selected="selected">' . $opt . '</option>';



            }



            $html .= '</select> ';



        }







        $html .= ($val['attr_type'] == 1 || $val['attr_type'] == 2) ?



            '属性价格'.' <input type="text" name="attr_price_list[]" class="input" value="' . $val['attr_price'] . '" size="5" maxlength="10" />' :



            ' <input type="hidden" name="attr_price_list[]" value="0" />';



        $html .= ($val['attr_type'] == 1 || $val['attr_type'] == 2) ?



            '团购属性价格'.' <input type="text" name="attr_team_price_list[]" class="input" value="' . $val['attr_team_price'] . '" size="5" maxlength="10" />' :



            ' <input type="hidden" name="attr_team_price_list[]" value="0" />';
        if($val['attr_img'])
        {
            $html .= '属性图片： <img src="../'.$val['attr_img'].'" width="50" height="50" align="absmiddle"><input type="file" name="attr_img_list[]" />';
        }
        else
        {
            $html .= '属性图片：<input type="file" name="attr_img_list[]" />';
        }
        $html .= '</td></tr>';



    }







    $html .= '</table>';







    return $html;



}











/**



 * 取得通用属性和某分类的属性，以及某商品的属性值



 * @param   int     $cat_id     分类编号



 * @param   int     $goods_id   商品编号



 * @return  array   规格与属性列表



 */



function get_attr_list($cat_id, $goods_id = 0)



{



    if (empty($cat_id))



    {



        return array();



    }







    // 查询属性值及商品的属性值



    $sql = "SELECT v.attr_img,a.attr_id, a.attr_name, a.attr_input_type, a.attr_type, a.attr_values, v.attr_value, v.attr_price, v.attr_team_price ".



            "FROM " .$GLOBALS['hhs']->table('attribute'). " AS a ".



            "LEFT JOIN " .$GLOBALS['hhs']->table('goods_attr'). " AS v ".



            "ON v.attr_id = a.attr_id AND v.goods_id = '$goods_id' ".



            "WHERE a.cat_id = " . intval($cat_id) ." OR a.cat_id = 0 ".



            "ORDER BY a.sort_order, a.attr_type, a.attr_id, v.attr_price, v.goods_attr_id";







    $row = $GLOBALS['db']->GetAll($sql);







    return $row;



}

/**

 * 获取商品类型中包含规格的类型列表

 *

 * @access  public

 * @return  array

 */

function get_goods_type_specifications()

{

    // 查询

    $sql = "SELECT DISTINCT cat_id

            FROM " .$GLOBALS['hhs']->table('attribute'). "

            WHERE attr_type = 1";

    $row = $GLOBALS['db']->GetAll($sql);



    $return_arr = array();

    if (!empty($row))

    {

        foreach ($row as $value)

        {

            $return_arr[$value['cat_id']] = $value['cat_id'];

        }

    }

    return $return_arr;

}

/**

 * 获得商品的货品列表

 *

 * @access  public

 * @params  integer $goods_id

 * @params  string  $conditions

 * @return  array

 */

function product_list($goods_id, $conditions = '')

{

    /* 过滤条件 */

    $param_str = '-' . $goods_id;

    $result = get_filter($param_str);

    if ($result === false)

    {

        $day = getdate();

        $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);



        $filter['goods_id']         = $goods_id;

        $filter['keyword']          = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);

        $filter['stock_warning']    = empty($_REQUEST['stock_warning']) ? 0 : intval($_REQUEST['stock_warning']);



        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)

        {

            $filter['keyword'] = json_str_iconv($filter['keyword']);

        }

        $filter['sort_by']          = empty($_REQUEST['sort_by']) ? 'product_id' : trim($_REQUEST['sort_by']);

        $filter['sort_order']       = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['extension_code']   = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);

        $filter['page_count'] = isset($filter['page_count']) ? $filter['page_count'] : 1;



        $where = '';



        /* 库存警告 */

        if ($filter['stock_warning'])

        {

            $where .= ' AND goods_number <= warn_number ';

        }



        /* 关键字 */

        if (!empty($filter['keyword']))

        {

            $where .= " AND (product_sn LIKE '%" . $filter['keyword'] . "%')";

        }



        $where .= $conditions;



        /* 记录总数 */

        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['hhs']->table('products'). " AS p WHERE goods_id = $goods_id $where";

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);



        $sql = "SELECT product_id, goods_id, goods_attr, product_sn, product_number

                FROM " . $GLOBALS['hhs']->table('products') . " AS g

                WHERE goods_id = $goods_id $where

                ORDER BY $filter[sort_by] $filter[sort_order]";



        $filter['keyword'] = stripslashes($filter['keyword']);

        //set_filter($filter, $sql, $param_str);

    }

    else

    {

        $sql    = $result['sql'];

        $filter = $result['filter'];

    }

    $row = $GLOBALS['db']->getAll($sql);



    /* 处理规格属性 */

    $goods_attr = product_goods_attr_list($goods_id);

    foreach ($row as $key => $value)

    {

        $_goods_attr_array = explode('|', $value['goods_attr']);

        if (is_array($_goods_attr_array))

        {

            $_temp = '';

            foreach ($_goods_attr_array as $_goods_attr_value)

            {

                 $_temp[] = $goods_attr[$_goods_attr_value];

            }

            $row[$key]['goods_attr'] = $_temp;

        }

    }



    return array('product' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

}

/**

 * 获得商品的规格属性值列表

 *

 * @access      public

 * @params      integer         $goods_id

 * @return      array

 */

function product_goods_attr_list($goods_id)

{

    if (empty($goods_id))

    {

        return array();  //$goods_id不能为空

    }



    $sql = "SELECT goods_attr_id, attr_value FROM " . $GLOBALS['hhs']->table('goods_attr') . " WHERE goods_id = '$goods_id'";

    $results = $GLOBALS['db']->getAll($sql);



    $return_arr = array();

    foreach ($results as $value)

    {

        $return_arr[$value['goods_attr_id']] = $value['attr_value'];

    }



    return $return_arr;

}

/**

 * 获得商品的货品总库存

 *

 * @access      public

 * @params      integer     $goods_id       商品id

 * @params      string      $conditions     sql条件，AND语句开头

 * @return      string number

 */

function product_number_count($goods_id, $conditions = '')

{

    if (empty($goods_id))

    {

        return -1;  //$goods_id不能为空

    }



    $sql = "SELECT SUM(product_number)

            FROM " . $GLOBALS['hhs']->table('products') . "

            WHERE goods_id = '$goods_id'

            " . $conditions;

    $nums = $GLOBALS['db']->getOne($sql);

    $nums = empty($nums) ? 0 : $nums;



    return $nums;

}



/**

 * 插入或更新商品属性

 *

 * @param   int     $goods_id           商品编号

 * @param   array   $id_list            属性编号数组

 * @param   array   $is_spec_list       是否规格数组 'true' | 'false'

 * @param   array   $value_price_list   属性值数组

 * @return  array                       返回受到影响的goods_attr_id数组

 */

function handle_goods_attr($goods_id, $id_list, $is_spec_list, $value_price_list)

{

    $goods_attr_id = array();



    /* 循环处理每个属性 */

    foreach ($id_list AS $key => $id)

    {

        $is_spec = $is_spec_list[$key];

        if ($is_spec == 'false')

        {

            $value = $value_price_list[$key];

            $price = '';

        }

        else

        {

            $value_list = array();

            $price_list = array();

            if ($value_price_list[$key])

            {

                $vp_list = explode(chr(13), $value_price_list[$key]);

                foreach ($vp_list AS $v_p)

                {

                    $arr = explode(chr(9), $v_p);

                    $value_list[] = $arr[0];

                    $price_list[] = $arr[1];

                }

            }

            $value = join(chr(13), $value_list);

            $price = join(chr(13), $price_list);

        }



        // 插入或更新记录

        $sql = "SELECT goods_attr_id FROM " . $GLOBALS['hhs']->table('goods_attr') . " WHERE goods_id = '$goods_id' AND attr_id = '$id' AND attr_value = '$value' LIMIT 0, 1";

        $result_id = $GLOBALS['db']->getOne($sql);

        if (!empty($result_id))

        {

            $sql = "UPDATE " . $GLOBALS['hhs']->table('goods_attr') . "

                    SET attr_value = '$value'

                    WHERE goods_id = '$goods_id'

                    AND attr_id = '$id'

                    AND goods_attr_id = '$result_id'";



            $goods_attr_id[$id] = $result_id;

        }

        else

        {

            $sql = "INSERT INTO " . $GLOBALS['hhs']->table('goods_attr') . " (goods_id, attr_id, attr_value, attr_price) " .

                    "VALUES ('$goods_id', '$id', '$value', '$price')";

        }



        $GLOBALS['db']->query($sql);



        if ($goods_attr_id[$id] == '')

        {

            $goods_attr_id[$id] = $GLOBALS['db']->insert_id();

        }

    }



    return $goods_attr_id;

}

/**

 * 商品的货品规格是否存在

 *

 * @param   string     $goods_attr        商品的货品规格

 * @param   string     $goods_id          商品id

 * @param   int        $product_id        商品的货品id；默认值为：0，没有货品id

 * @return  bool                          true，重复；false，不重复

 */

function check_goods_attr_exist($goods_attr, $goods_id, $product_id = 0)

{

    $goods_id = intval($goods_id);

    if (strlen($goods_attr) == 0 || empty($goods_id))

    {

        return true;    //重复

    }



    if (empty($product_id))

    {

        $sql = "SELECT product_id FROM " . $GLOBALS['hhs']->table('products') ."

                WHERE goods_attr = '$goods_attr'

                AND goods_id = '$goods_id'";

    }

    else

    {

        $sql = "SELECT product_id FROM " . $GLOBALS['hhs']->table('products') ."

                WHERE goods_attr = '$goods_attr'

                AND goods_id = '$goods_id'

                AND product_id <> '$product_id'";

    }



    $res = $GLOBALS['db']->getOne($sql);



    if (empty($res))

    {

        return false;    //不重复

    }

    else

    {

        return true;    //重复

    }

}

/**

 * 商品货号是否重复

 *

 * @param   string     $goods_sn        商品货号；请在传入本参数前对本参数进行SQl脚本过滤

 * @param   int        $goods_id        商品id；默认值为：0，没有商品id

 * @return  bool                        true，重复；false，不重复

 */

function check_goods_sn_exist($goods_sn, $goods_id = 0)

{

    $goods_sn = trim($goods_sn);

    $goods_id = intval($goods_id);

    if (strlen($goods_sn) == 0)

    {

        return true;    //重复

    }



    if (empty($goods_id))

    {

        $sql = "SELECT goods_id FROM " . $GLOBALS['hhs']->table('goods') ."

                WHERE goods_sn = '$goods_sn'";

    }

    else

    {

        $sql = "SELECT goods_id FROM " . $GLOBALS['hhs']->table('goods') ."

                WHERE goods_sn = '$goods_sn'

                AND goods_id <> '$goods_id'";

    }



    $res = $GLOBALS['db']->getOne($sql);



    if (empty($res))

    {

        return false;    //不重复

    }

    else

    {

        return true;    //重复

    }



}

/**

 * 修改商品库存

 * @param   string  $goods_id   商品编号，可以为多个，用 ',' 隔开

 * @param   string  $value      字段值

 * @return  bool

 */

function update_goods_stock($goods_id, $value)

{

    if ($goods_id)

    {

        /* $res = $goods_number - $old_product_number + $product_number; */

        $sql = "UPDATE " . $GLOBALS['hhs']->table('goods') . "

                SET goods_number = goods_number + $value,

                    last_update = '". gmtime() ."'

                WHERE goods_id = '$goods_id'";

        $result = $GLOBALS['db']->query($sql);



        /* 清除缓存 */

        clear_cache_files();



        return $result;

    }

    else

    {

        return false;

    }

}

/**

 * 商品的货品货号是否重复

 *

 * @param   string     $product_sn        商品的货品货号；请在传入本参数前对本参数进行SQl脚本过滤

 * @param   int        $product_id        商品的货品id；默认值为：0，没有货品id

 * @return  bool                          true，重复；false，不重复

 */

function check_product_sn_exist($product_sn, $product_id = 0)

{

    $product_sn = trim($product_sn);

    $product_id = intval($product_id);

    if (strlen($product_sn) == 0)

    {

        return true;    //重复

    }

    $sql="SELECT goods_id FROM ". $GLOBALS['hhs']->table('goods')."WHERE goods_sn='$product_sn'";

    if($GLOBALS['db']->getOne($sql))

    {

        return true;    //重复

    }





    if (empty($product_id))

    {

        $sql = "SELECT product_id FROM " . $GLOBALS['hhs']->table('products') ."

                WHERE product_sn = '$product_sn'";

    }

    else

    {

        $sql = "SELECT product_id FROM " . $GLOBALS['hhs']->table('products') ."

                WHERE product_sn = '$product_sn'

                AND product_id <> '$product_id'";

    }



    $res = $GLOBALS['db']->getOne($sql);



    if (empty($res))

    {

        return false;    //不重复

    }

    else

    {

        return true;    //重复

    }

}

/**

 * 取货品信息

 *

 * @access  public

 * @param   int         $product_id     货品id

 * @param   int         $filed          字段

 * @return  array

 */

function get_product_info($product_id, $filed = '')

{

    $return_array = array();



    if (empty($product_id))

    {

        return $return_array;

    }



    $filed = trim($filed);

    if (empty($filed))

    {

        $filed = '*';

    }



    $sql = "SELECT $filed FROM  " . $GLOBALS['hhs']->table('products') . " WHERE product_id = '$product_id'";

    $return_array = $GLOBALS['db']->getRow($sql);



    return $return_array;

}

/**

 * 获得商品已添加的规格列表

 *

 * @access      public

 * @params      integer         $goods_id

 * @return      array

 */

function get_goods_specifications_list($goods_id)

{

    if (empty($goods_id))

    {

        return array();  //$goods_id不能为空

    }



    $sql = "SELECT g.goods_attr_id, g.attr_value, g.attr_id, a.attr_name

            FROM " . $GLOBALS['hhs']->table('goods_attr') . " AS g

                LEFT JOIN " . $GLOBALS['hhs']->table('attribute') . " AS a

                    ON a.attr_id = g.attr_id

            WHERE goods_id = '$goods_id'

            AND a.attr_type = 1

            ORDER BY g.attr_id ASC";

    $results = $GLOBALS['db']->getAll($sql);



    return $results;

}



/**

 * 取得重量单位列表

 * @return  array   重量单位列表

 */

function get_unit_list()

{

    return array(

        '1'     => '千克',

        '0.001' => '克',

    );

}









?>