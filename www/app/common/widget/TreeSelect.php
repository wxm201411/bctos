<?php

namespace app\common\widget;

use app\common\controller\base;

/**
 * 分类选择菜单插件
 *
 * @author 凡星
 */
class TreeSelect extends base
{

    public $info = array(
        'name' => 'TreeSelect',
        'title' => '分类选择',
        'description' => '',
        'status' => 1,
        'author' => '凡星',
        'version' => '0.1',
        'has_adminlist' => 0,
        'type' => 0
    );

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    /**
     * 编辑器挂载的后台文档模型文章内容钩子
     *
     * type=db&table=common_category&module=shop_category&value_field=id&custom_field=id,title,pid,sort&custom_pid=0
     * type=text&data=[广西[南宁,桂林], 广东[广州, 深圳[福田区, 龙岗区, 宝安区]]]
     */
    public function show($data)
    {
        $manager_id = $GLOBALS['uid'];
        $wpid = get_wpid();
        $data['extra'] = str_replace(array(
            '[manager_id]',
            '[wpid]'
        ), array(
            $manager_id,
            $wpid
        ), $data['extra']);

        parse_str($data['extra'], $arr);

        $table = isset($arr['table']) ? $arr['table'] : 'common_category';
        $value_field = isset($arr['value_field']) ? $arr['value_field'] : 'id';
        $custom_field = isset($arr['custom_field']) ? $arr['custom_field'] : 'id,title,pid,sort';
        $custom_pid = isset($arr['custom_pid']) ? $arr['custom_pid'] : 0;
        $exclude = isset($arr['exclude']) ? $arr['exclude'] : 0;
        if (!empty($exclude)) {
            $arr = exclude_map($arr, $exclude, $value_field, $value_field);
        }
        isset($arr['app']) || $arr['app'] = MODULE_NAME;

        unset($arr['table'], $arr['value_field'], $arr['custom_field'], $arr['custom_pid'], $arr['exclude']);
        // dump ( $table );

        //dump($arr);exit;
        $list = find_data(M($table)->where(wp_where($arr))
            ->field($custom_field)
            ->order('pid asc, sort asc')
            ->select());
        //lastsql ();
        $titles = $titleArr = [];
        if (!empty($data['value'])) {
            if (is_array($data['value'])) {
                $value = array_filter($data['value']);
                $data['value'] = implode(',', $value);
            } else {
                $value = array_filter(explode(',', $data['value']));
            }

            foreach ($list as $vo) {
                $titleArr[$vo[$value_field]] = $vo['title'];
            }
            foreach ($value as $v) {
                isset($titleArr[$v]) && $titles[] = $titleArr[$v];
            }
        } elseif (is_array($data['value'])) {
            $data['value'] = '';
        }
        $data['titles'] = implode(',', $titles);
        // exit ();
        $data['cate_data'] = treeList($list, $custom_pid, '', false);

        $this->assign($data);

        $content = $this->fetch('common@widget/tree_select');
        return $content;
    }
}
