<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\common\model;

use app\common\model\Base;


/**
 * 分类模型
 */
class Category extends Base
{
    var $table = DB_PREFIX . 'common_category';


    function getAppCategory($meta_table, $app = '')
    {
        empty($app) && $app = MODULE_NAME;
        $data = $this->getList($meta_table, "c.app='$app'");
        $data = treeList($data, 0, '', false);

        return $data;
    }

    function getList($meta_table, $having = '')
    {
        if (is_string($meta_table)) {
            $meta_table = D('common/Models')->getFileInfo($meta_table);
        }
        //基本字段
        $common_table = D('common/Models')->getFileInfo('common_category');

        //扩展字段
        $all_fields = array_diff_key($meta_table->fields, $common_table->fields);

        return $this->getListData($having, $all_fields);
    }

    function getListData($having, $all_fields)
    {
        $fields = 'c.*';
        foreach ($all_fields as $key => $f) {
            $fields .= ",max(if(`meta_key`='{$key}', `meta_value`,'')) as `{$key}`";
        }

        $having = wp_having($having);
        if (empty($having)) {
            $having = "wpid=" . get_wpid();
        } else {
            $having .= " and wpid=" . get_wpid();
        }

        $data = find_data(M('common_category')->alias('c')
            ->join('common_category_meta m', 'm.category_id=c.id')
            ->field($fields)
            ->group('m.category_id')->having($having)
            ->order('sort asc, id asc')
            ->select());

        return $data;
    }

    function getMultList($having = '')
    {
        $cms_table = D('common/Models')->getFileInfo('cms_category');
        $shop_table = D('common/Models')->getFileInfo('shop_goods_category');
        //基本字段
        $common_table = D('common/Models')->getFileInfo('common_category');

        $all_fields1 = array_diff_key($cms_table->fields, $common_table->fields);
        $all_fields2 = array_diff_key($shop_table->fields, $common_table->fields);
        $all_fields = array_merge($all_fields1, $all_fields2);

        $data = $this->getListData($having, $all_fields);
        foreach ($data as &$d) {
            $param['cate_id'] = $d['id'];
            if (isset($d['jump_url']) && !empty($d['jump_url'])) {
                $d['url'] = $d['jump_url'];
            } else if ($d['app'] == 'shop') {
                if ($d['pid'] > 0) {
                    $param = ['cate_id' => $d['pid'], 'sub_cate_id' => $d['id']];
                }
                $d['url'] = U('lists', $param);
            } else {
                $d['url'] = U('news', $param);
            }
        }

        return $data;
    }

    // 保存商品分类
    public function saveCategoryLink($data_id, $category_ids, $app = '')
    {
        if (empty($data_id) || empty($category_ids)) {
            return false;
        }
        empty($app) && $app = MODULE_NAME;

        $gmap['data_id'] = $data_id;
        $gmap['app'] = $app;
        $has = M('common_category_link')->where(wp_where($gmap))->column('id', 'id');

        is_array($category_ids) || $category_ids = explode(',', $category_ids);
        $ids = [];
        foreach ($category_ids as $val) {
            if (empty($val)) {
                continue;
            }
            if (isset($has[$val])) {
                $ids[] = $has[$val];
            } else {
                $gmap['category_id'] = intval($val);
                $ids[] = M('common_category_link')->insertGetId($gmap);
            }
        }
        $has = isset($has) ? $has : [];
        $diff = array_diff($has, $ids);
        if (!empty($diff)) {
            M('common_category_link')->whereIn('id', $diff)->delete();
        }
    }

    public function getCategoryLink($data_id, $app = '')
    {
        if (empty($data_id)) {
            return [];
        }
        empty($app) && $app = MODULE_NAME;

        $gmap['data_id'] = $data_id;
        $gmap['app'] = $app;
        $ids = M('common_category_link')->where(wp_where($gmap))->column('category_id');
        return $ids;
    }

    function updateById($id, $data, $model)
    {
        if (is_string($model)) {
            $model = $this->getModel($model);
        }
        $res = D('common/Category')->updateInfo($model, $data, $id);
    }

    function updateInfo($model, $data, $id = 0)
    {
        //基本字段
        $common_table = D('common/Models')->getFileInfo('common_category');

        //扩展字段
        $meta_table = D('common/Models')->getFileInfo($model);
        $meta_table->fields = array_diff_key($meta_table->fields, $common_table->fields);

        $common_data = $meta_data = [];
        foreach ($data as $k => $d) {
            if (isset($common_table->fields[$k])) {
                $common_data[$k] = $d;
            } else if (isset($meta_table->fields[$k])) {
                $meta_data[$k] = $d;
            }
        }

        if ($id > 0) {
            //更新基本信息
            $res = M('common_category')->where('id', $id)->update($common_data);
        } else {
            //增加基本信息
            $common_data['wpid'] = get_wpid();
            $res = $id = M('common_category')->insertGetId($common_data);
        }

        if (empty($meta_data)) {
            //定义meta_null为特殊的空值填充变量，防止meta没值时列表使用join和group时无法取到值
            $meta_data['meta_null'] = 0;
        }

        //获取已有的扩展信息
        $has = M('common_category_meta')->where('category_id', $id)->column('meta_value', 'meta_key');

        //更新扩展信息
        foreach ($meta_data as $key => $value) {
            if (isset($has[$key])) {
                M('common_category_meta')->where('category_id', $id)->where('meta_key', $key)->update(['meta_value' => $value]);
                unset($meta_data[$key]);
                unset($has[$key]);
            }
        }

        //增加新的扩展信息
        if (!empty($meta_data)) {
            $allData = [];
            foreach ($meta_data as $key => $value) {
                $allData[] = ['category_id' => $id, 'meta_key' => $key, 'meta_value' => $value];
            }
            M('common_category_meta')->insertAll($allData);
        }

        return $res;
    }

}
