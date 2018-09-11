<?php

define('IN_HHS', true);

require(dirname(__FILE__) . '/includes/init.php');



$_REQUEST['step'] = isset($_REQUEST['step']) ? $_REQUEST['step'] : '';

$cat_id           = isset($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : 0;



if ($_REQUEST['step'] == 'selectCat')

{

		$cat_id = $db->GetOne("select cat_id from ".$hhs->table('category')." where cat_id = ".$cat_id." ");

		if(empty($cat_id))

		{

			hhs_header("Location: index.php\n");

			exit;	

		}

		

		include_once('includes/cls_json.php');

		$json = new JSON;

		$result = array('error' => '', 'content' => '', 'cat_id' => 0);

		

		$pcat_array = get_cat_tree($cat_id);

		

		foreach($pcat_array as $key => $value)

		{

			foreach($value['cat_id'] as $val)

			{

				foreach($val['cat_id'] as $v)

				{

					if(empty($v['cat_id']))

					{

						$pcat_array[$key]['is_level'] = 1;

						break;

					}

				}

			}	

		}

		

		$smarty->assign('category',$pcat_array);    // 页面标题

		$result['content']    = $smarty->fetch('cat_list.dwt');

		echo $json->encode($result);

		exit;

	

}





/**

 * 获得指定分类同级的所有分类以及该分类下的子分类

 *

 * @access  public

 * @param   integer     $cat_id     分类编号

 * @return  array

 */

function get_cat_tree($cat_id = 0)

{

    /*

     判断当前分类中全是是否是底级分类，

     如果是取出底级分类上级分类，

     如果不是取当前分类及其下的子分类

    */

    $sql = 'SELECT count(*) FROM ' . $GLOBALS['hhs']->table('category') . " WHERE parent_id = '$cat_id' AND is_show = 1 ";

    if ($GLOBALS['db']->getOne($sql))

    {

        /* 获取当前分类及其子分类 */

        $sql = 'SELECT cat_id,cat_name ,parent_id,is_show,cat_img ' .

                'FROM ' . $GLOBALS['hhs']->table('category') .

                "WHERE cat_id = '$cat_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";

		

		$res = $GLOBALS['db']->getAll($sql);



        foreach ($res AS $key => $row)

        {

            if ($row['is_show'])

            {

                $cat_arr[$key]['id']   = $row['cat_id'];

                $cat_arr[$key]['name'] = $row['cat_name'];

                $cat_arr[$key]['cat_img'] = $row['cat_img'];

				if (isset($row['cat_id']) != NULL)

				{

				   $cat_arr[$key]['cat_id'] = get_child_tree_xaphp($row['cat_id']);

				}

            }

        }

    }

	



    if(isset($cat_arr))

    {

        return $cat_arr;

    }

}





function get_child_tree_xaphp($tree_id = 0)

{

    $three_arr = array();

    $sql = 'SELECT count(*) FROM ' . $GLOBALS['hhs']->table('category') . " WHERE parent_id = '$tree_id' AND is_show = 1 ";

    if ($GLOBALS['db']->getOne($sql) || $tree_id == 0)

    {

        $child_sql = 'SELECT cat_id, cat_name, parent_id, is_show,cat_img ' .

                'FROM ' . $GLOBALS['hhs']->table('category') .

                "WHERE parent_id = '$tree_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";

        $res = $GLOBALS['db']->getAll($child_sql);

        foreach ($res AS $key => $row)

        {

            if ($row['is_show'])

			{



               $three_arr[$key]['id']   = $row['cat_id'];

               $three_arr[$key]['name'] = $row['cat_name'];

               $three_arr[$key]['cat_img'] = $row['cat_img'];

               if (isset($row['cat_id']) != NULL)

               {

                  $three_arr[$key]['cat_id'] = get_child_tree_xaphp($row['cat_id']);

			   }

            }

        }

    }

    return $three_arr;

}







?>



