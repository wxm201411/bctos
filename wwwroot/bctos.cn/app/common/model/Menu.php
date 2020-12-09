<?php

namespace app\common\model;

use app\common\model\Base;

/**
 * 插件配置操作集成
 */
class Menu extends Base
{

    protected $table = DB_PREFIX . 'menu';

    // 取后台管理对当前用户配置的菜单
    private function _get_menu($addonList)
    {
        $map['is_hide'] = 0;

        //非超级管理员增加权限判断，只有后台授权的菜单才能查看
        if ($GLOBALS['mid'] > 0) {
            $menu_rule = [];
            foreach ($GLOBALS['myinfo']['groups'] as $g) {
                $rule = explode(',', $g['menu_rule']);

                $menu_rule = array_merge($menu_rule, $rule);
            }
            $menu_rule = array_filter(array_unique($menu_rule));
            $map['id'] = ['in', $menu_rule];
        }
        //dump($map);
        $menus = find_data($this->where(wp_where($map))->order('sort asc, id asc')->select());
        // 组装数据
        foreach ($menus as $k => &$me) {
            if ($me['url_type'] == 0) {
                $me['url'] = isset($addonList[$me['addon_name']]['addons_url']) ? $addonList[$me['addon_name']]['addons_url'] : '###';
            } elseif (strpos($me['url'], 'http://') !== false || strpos($me['url'], 'https://') !== false) {
                $me['url'] = $me['url'];
            } else {
                $me['url'] = U($me['url']);
            }
        }

        // 侧边栏数据
        foreach ($menus as $k => $m) {
            if ($m['pid'] == 0) {
                continue;
            }
            $param['side'] = $cate['id'] = $m['id'];
            $cate['title'] = $m['title'];
            $param['top'] = $cate['pid'] = intval($m['pid']);

            $cate['url'] = $m['url'];
            $cate['url'] .= '?&mdm=' . $cate['pid'] . '_' . $cate['id'];
            $cate['addon_name'] = $m['addon_name'];
            $cate['target'] = $m['target'];

            $res['core_side_menu'][$cate['pid']][] = $cate;
            $res['default_data'][$cate['url']] = $param;
            empty($m['addon_name']) || $res['default_data'][$cate['addon_name']] = $param;
        }
        // 顶部栏数据
        foreach ($menus as $k => $m) {
            if ($m['pid'] != 0) {
                continue;
            }
            // dump($m);
            $param['top'] = $cate['id'] = $m['id'];
            $cate['title'] = $m['title'];
            $cate['pid'] = 0;

            $cate['url'] = $m['url'];

            if ($m['url_type'] == 0) {
                $cate['url'] = $m['url'];

                if (empty($cate['url']) && !empty($res['core_side_menu'][$m['id']])) {
                    $cate['url'] = $res['core_side_menu'][$m['id']][0]['url'];
                }
                $cate['url'] .= '?&mdm=' . $cate['id'];
            } else {
                $cate['url'] = $m['url'];
                if (isset($res['core_side_menu'][$m['id']][0]['id'])) {
                    $cate['url'] .= '?&mdm=' . $m['id'] . '_' . $res['core_side_menu'][$m['id']][0]['id'];
                } else {
                    $cate['url'] .= '?&mdm=' . $m['id'];
                }
            }

            $cate['addon_name'] = $m['addon_name'];
            $cate['target'] = $m['target'];

            $res['core_top_menu'][] = $cate;

            $param['side'] = isset($res['core_side_menu'][$m['id']][0]['id']) ? $res['core_side_menu'][$m['id']][0]['id'] : '';
            $res['default_data'][$cate['url']] = $param;
            empty($m['addon_name']) || $res['default_data'][$cate['addon_name']] = $param;
        }

        if (!isset($res['core_side_menu']) || empty($res['core_side_menu'])) {
            $res['core_side_menu'] = [];
        }

        return $res;
    }

    public function getMenu()
    {
        $key = cache_key('is_hide:0', $this->table);
        $menus = S($key);
        if ($menus === false || config('app.debug') || true) {
            // 第一步：获取所有微信插件的入口URL
            $addonList = D('home/Addons')->getWeixinList(false);
            // dump($addonList);
            // 第二步：获取导航数据
            $menus = $this->_get_menu($addonList);
            // dump($menus);exit;
            // 第三步：获取用户登录进入时的初始化URL
            $menus['init_url'] = '';
            foreach ($menus['core_top_menu'] as $t) {
                $menus['init_url'] = $t['url'];
                break;
            }
            // S($key, $menus, 86400);
        }

        // 第四步：初始化导航高亮参数
        $default = session('menu_default');
        $only_body = input('only_body');
        if (IS_GET && empty($only_body) && (!isset($_SERVER['HTTP_REFERER']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'uploadify.swf') === false))) {
            if (isset($_GET['mdm'])) {
                $mdm = explode('_', input('mdm'));
                $default['top'] = intval($mdm[0]);
                $default['side'] = isset($mdm[1]) ? intval($mdm[1]) : '';
            } else {
                $current_url = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
                foreach ($menus['default_data'] as $k => $v) {
                    if (stripos($k, $current_url) !== false) {
                        $default = $v;
                    }
                }
            }
            if (empty($default['top']) && !empty($menus['core_top_menu'])) {
                $default['top'] = intval($menus['core_top_menu'][0]['id']);
            }
            if (empty($default['side']) && !empty($menus['core_side_menu'][$default['top']])) {
                $default['side'] = $menus['core_side_menu'][$default['top']][0]['id'];
            }
            $default['top'] = intval($default['top']);

            session('menu_default', $default);
        }

        // 第五步：设置导航高亮
        $menus['now_top_menu_name'] = '';
        foreach ($menus['core_top_menu'] as &$top) {
            $top['class'] = '';

            if ($top['id'] == $default['top']) {
                $top['class'] = 'active';
                $menus['now_top_menu_name'] = $top['title'];
            }
        }
        foreach ($menus['core_side_menu'] as &$side) {
            foreach ($side as &$s) {
                $s['class'] = '';
                if (isset($default['side']) && $s['id'] == $default['side']) {
                    $s['class'] = 'active';
                }
            }
        }
        if (isset($menus['core_side_menu'][$default['top']]) && !empty($menus['core_side_menu'][$default['top']])) {
            $menus['core_side_menu'] = $menus['core_side_menu'][$default['top']];
        } else {
            $menus['core_side_menu'] = '';
        }
        // dump($menus);exit;
        $new_menu = $menus;
        $new_menu['core_top_menu'] = $menus['core_side_menu'];
        $new_menu['core_side_menu'] = $menus['core_top_menu'];

        return $new_menu;
    }

    public function updateMenuData($data, $map)
    {
        $res = $this->where(wp_where($map))->save($data);
        // 清空缓存
        D('common/Menu')->clearCache(0);
        if (isset($data['rule'])) {
            $id = $this->where(wp_where($map))->value('id');
            D('AuthGroup')->saveMenuRule($id, $data['rule']);
        }

        return $res;
    }

    public function addData($data)
    {
        $id = $this->insertGetId($data);
        D('common/Menu')->clearCache(0);
        if (!isset($data['rule'])) {
            $data['rule'] = [2];
        }
        D('AuthGroup')->saveMenuRule($id, $data['rule']);
        return $id;
    }

    public function delData($ids = [])
    {
        D('AuthGroup')->delMenuRule($ids);
        $res = $this->whereIn('id', $ids)->delete();
        D('common/Menu')->clearCache(0);
        return $res;
    }

    public function clearCache($id, $act_type = '', $uid = 0, $more_param = [])
    {
        $key = cache_key('is_hide:0', $this->table);
        S($key, null);
    }

    //当app应用禁用或启用时
    public function appStatusChang($app_id, $status = 'disable')
    {
        $info = M('apps')->where('id', $app_id)->find();
        if (!isset($info['name'])) return true;

        $key = cache_key(['name' => $info['name']], 'apps', 'id,status');
        S($key, null);

        $ids = $this->where("addon_name='{$info['name']}' OR url like '{$info['name']}/%'")->column('id');
        if (empty($ids)) return true;

        if ($status == 'del') {
            return $this->delData($ids);
        } else {
            $data['is_hide'] = $status == 'disable' ? 1 : 0;
            $map['id'] = ['in', $ids];
            return $this->updateMenuData($data, $map);
        }
    }
}
