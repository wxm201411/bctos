<?php

namespace app\common\widget;

use app\common\controller\base;

/**
 * 级联菜单插件
 *
 * @author 凡星
 */
class Cascade extends base
{

    public $info = array(
        'name' => 'Cascade',
        'title' => '级联菜单',
        'description' => '支持无级级联菜单（当然也包括常见的一级下拉菜单也可以用此插件来实现），用于地区选择、多层分类选择等场景。菜单的数据来源支持查询数据库和直接用户按格式输入两种方式',
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
     * type=db&table=common_category&module=shop_category&value_field=id&custom_field=id,title,pid,sort&custom_pid=0&exclude=0
     */
    public function cascade($data)
    {
        // dump($data);
        $key = $data['name'] . '_' . get_wpid();
        $json = S($key);
        if ($json === false || true) {
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
            !isset($arr['type']) && $arr['type'] = 'db';
            $first_option = isset($arr['first_option']) ? $arr['first_option'] : '请选择';
            if (isset($arr['clicklast'])) {
                $this->assign('clicklast', $arr['clicklast']);
                unset($arr['clicklast']);
            } else {
                $this->assign('clicklast', true);
            }
            $max_level = 0;
            if (isset($arr['max_level'])) {
                $max_level = $arr['max_level'];
                unset($arr['max_level']);
            }

            if ($arr['type'] == 'db') {
                $table = isset($arr['table']) ? $arr['table'] : 'common_category';
                $value_field = isset($arr['value_field']) ? $arr['value_field'] : 'id';
                $custom_field = isset($arr['custom_field']) ? $arr['custom_field'] : 'id,title,pid,sort';
                $custom_pid = isset($arr['custom_pid']) ? $arr['custom_pid'] : 0;
                $exclude = isset($arr['exclude']) ? $arr['exclude'] : 0;
                if (!empty($exclude)) {
                    $arr[$value_field] = ['not in', $exclude];
                }
                if ($table == 'common_category' && !isset($arr['app'])) {
                    $arr['app'] = MODULE_NAME;
                }

                unset($arr['type'], $arr['table'], $arr['value_field'], $arr['custom_field'], $arr['custom_pid'], $arr['first_option'], $arr['exclude']);
                // dump ( $table );

                //dump($arr);exit;
                $list = find_data(M($table)->where(wp_where($arr))->field($custom_field)->order('pid asc, sort asc')->select());

                //
                //lastsql ();

                //默认值由最后一级ID转成完整的ID链
                if (is_numeric($data['value'])) {
                    $data['value'] = $this->getParentsIds($data['value'], $list, $value_field);
                }
                // exit ();
                $tree = $this->makeTree($list, $custom_pid, $value_field, $max_level);
                array_unshift($tree, ['value' => 0, 'label' => $first_option]);
            } elseif ($arr['type'] == 'extra') {
                $tree = $this->extra2json($arr['data']);
            } elseif ($arr['type'] == 'json') {
                $tree = $this->extra2json($arr['data']);
            } else {
                $tree = $this->str2json($arr['data']);
            }
            //dump($tree);
            $json = json_encode($tree);

            S($key, $json, 86400);
        }
        // dump($json);
        $this->assign('json', $json);

        $data['default_value'] = implode(',', array_filter(explode(',', $data['value'])));
        //dump($json);exit;
        $this->assign($data);

        $content = $this->fetch('common@widget/cascade');
        return $content;
    }

    private function getParentsIds($pid, $list, $value_field)
    {
        foreach ($list as $vo) {
            if ($vo[$value_field] == $pid) {
                if ($vo['pid'] == 0) {
                    return $pid;
                } else {
                    return $this->getParentsIds($vo['pid'], $list, $value_field) . ',' . $pid;
                }
            }
        }
    }

    public function makeTree($list, $pid = 0, $value_field = 'id', $max_level = 0, $now_level = 0)
    {
        $result = [];

        if ($max_level > 0 && $max_level <= $now_level)
            return $result;

        $now_level += 1;
        foreach ($list as $k => $vo) {

            if ($vo['pid'] == $pid) {
                $data['value'] = $vo[$value_field];
                $data['label'] = $vo['title'];
                unset($list[$k]);

                $d = $this->makeTree($list, $vo['id'], $value_field, $max_level, $now_level);
                empty($d) || $data['children'] = $d;

                $result[] = $data;
                unset($data);
            }
        }
        return $result;
    }

// $str = '[1:广西[3:南宁,4:桂林],5:123[6:456,7:789,asd], 2:广东[广州, 深圳[福田区, 龙岗区[板田,龙华], 宝安区]]]';
    public function str2json($str)
    {
        $str = str_replace('，', ',', $str);
        $str = str_replace('【', '[', $str);
        $str = str_replace('】', ']', $str);
        $str = str_replace('：', ':', $str);

        $arr = StringToArray($str);
        $str = '';
        foreach ($arr as $v) {
            if ($v == '[' || $v == ']' || $v == ',') {
                if ($str) {
                    $block = explode(':', trim($str));
                    $blockArr['value'] = $block[0];
                    $blockArr['label'] = isset($block[1]) ? $block[1] : $block[0];

                    $arr2[] = $blockArr;
                }
                $v == ',' || $arr2[] = $v;
                $str = '';
            } else {
                $str .= $v;
            }
        }
        if ($arr2[0] == '[') {
            unset($arr2[0]);
            array_pop($arr2);
        }
        // dump ( $arr2 );
        // 通过栈的原理把一维数组转成多维数据
        $wareroom = [];
        foreach ($arr2 as $k => $vo) {
            if ($vo == ']') {
                // 逆向出栈
                $count = count($wareroom) - 1;
                for ($i = $count; $i >= 0; $i--) {
                    if ($wareroom[$i] == '[') {
                        $parent = $i - 1;
                        array_pop($wareroom);
                        break;
                    } else {
                        $d[] = array_pop($wareroom);
                    }
                }

                $wareroom[$parent]['children'] = $d;
                unset($d);
            } else {
                // 入栈
                array_push($wareroom, $vo);
            }
        }
        // dump ( $wareroom );
        return $wareroom;
    }

// $str = '[1:广西[3:南宁,4:桂林],5:123[6:456,7:789,asd], 2:广东[广州, 深圳[福田区, 龙岗区[板田,龙华], 宝安区]]]';
    public function extra2json($str)
    {
        $str = str_replace('，', ',', $str);
        $str = str_replace('：', ':', $str);

        $arr = wp_explode($str);
        $res = [];
        foreach ($arr as $v) {
            if (false !== strpos($v, ':')) {
                $block = explode(':', trim($str));
                $blockArr['value'] = $block[0];
                $blockArr['label'] = isset($block[1]) ? $block[1] : $block[0];

                $res[] = $blockArr;
            } else {
                $res[] = [
                    'value' => $v,
                    'label' => $v
                ];
            }
        }

        return $res;
    }
}
