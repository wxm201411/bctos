<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ouyangessen
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\AuthRule;
use app\admin\model\AuthGroup;
use app\ask\Info;

/**
 * 权限管理控制器
 * Class AuthManagerController
 */
class Rules extends Admin
{
    /*
     * 新增/编辑角色
     */
    public function createTag()
    {
        return $this->redirect('editTag');
    }

    public function editTag()
    {
        $id = input('id');
        if (request()->isPost()) {
            $title = input('title');
            if (empty($title)) return $this->error('角色名不能为空');
            $data = array(
                'title' => $title,
                'mTime' => NOW_TIME,
                'type' => 0
            );

            if ($id) {
                $re = M('auth_group')->where('id', $id)->update($data);
            } else {
                $re = M('auth_group')->insert($data);
            }
            if ($re !== false) {
                $url = U('index');
                return $this->success('添加成功', $url);
            } else {
                return $this->error('添加失败' . M('auth_group')->getError());
            }
        } else {

            if ($id) {
                $tagInfo = D('Rules')->getTagInfo($id);
                $this->assign('id', $id);
                $this->assign('tagInfo', $tagInfo);
            }

            return $this->fetch('edit');
        }
    }

    /*
     * 删除角色
     */
    public function deleteTag()
    {
        $ids = (array)input('ids');
        if (empty($ids)) return $this->error('参数错误');
        $re = M('auth_group')->where(wp_where(array(array('id', 'in', $ids))))->delete();
        if ($re) {
            $url = U('index');
            return $this->success('删除成功', $url);
        } else {
            return $this->error('删除失败' . M('auth_group')->getError());
        }
    }

    /*
     * 删除用户角色关系
     */
    public function deleteUserTag()
    {
        $ids = (array)input('ids');
        $id = input('id');
        if (empty($ids)) return $this->error('参数错误');
        $re = M('auth_group_access')->where(wp_where(array(array('uid', 'in', $ids))))->delete();
        if ($re) {
            $url = U('members', array('id' => $id));
            return $this->success('删除成功', $url);
        } else {
            return $this->error('删除失败' . M('auth_group_access')->getError());
        }
    }

    /*
     * 角色成员
     */
    public function members()
    {
        $id = input('id');
        if (empty($id)) return $this->error('参数错误');
        $tagInfo = D('rules')->getTagInfo($id);
        $this->assign('tagInfo', $tagInfo);
        $users = find_data(M('auth_group_access')->field('uid,group_id')->where('group_id', $id)->select());
        if (!empty($users)) {
            foreach ($users as $v) {
                $tmp = getUserInfo($v['uid']);
                $tmp['group_id'] = $v['group_id'];
                $usersInfo [] = $tmp;
            }
            $this->assign('_list', $usersInfo);
        }
        $this->assign('id', $id);
        return $this->fetch();
    }

    /*
     * 角色成员编辑
     */
    public function changeTag()
    {
        $type = input('type');
        $tag_id = input('tag_id');

        $id = array_unique(( array )I('id', 0));

        if (empty ($id)) {
            return $this->error('请选择用户!');
        }
        $group_id = I('group_id', 0);
        if (empty ($tag_id)) {
            return $this->error('请选择角色!');
        }
        $data ['uid'] = array(
            'in',
            $id
        );
        $data ['group_id'] = $tag_id;
        M('auth_group_access')->where(wp_where($data))->delete();

        $type = I('type', 0);
        if ($type != 0)
            die (1);
        $data['cTime'] = NOW_TIME;
        foreach ($id as $uid) {
            $data ['uid'] = $uid;
            $res = M('auth_group_access')->insertGetId($data);

            // 更新用户缓存
            D('common/User')->getUserInfo($uid, true);
        }
        echo 1;
    }

    /*
     * 角色权限节点显示
     */
    public function rules()
    {
        $id = input('id');
        if (empty($id)) return $this->error('参数错误');
        //查找应用以及子权限节点
        $map['status'] = 1;
        $data = M('apps')->where(wp_where($map))
            ->order('id DESC')
            ->select();
        $this->initInfo($data);
        $list = D('common/AuthRule')->getAll();
        foreach ($data as &$v) {
            foreach ($list as $k => $lv) {
                if (strtolower($v['name']) == strtolower($lv['mod'])) {
                    $v['auth'][] = $lv;
                    unset($list[$k]);
                }
            }
        }//dump($data);
        $this->assign('data', $data);
        $tagInfo = M('auth_group')->where('id', $id)->find();
        $this->assign('tagInfo', $tagInfo);
        $this->assign('rule_arr', explode(',', $tagInfo['rule']));
        return $this->fetch();
    }

    /*
     * 更新权限节点
     */
    public function updateRule()
    {
        $rid = input('rid');
        if (empty($rid)) return $this->error('参数错误');
        $flag = input('flag');
        $id = input('id');
        $mod = input('mod');
        D('common/AuthRule')->updateRule($rid, $flag, $id, $mod);
        //清空缓存
        S('AuthRule_getList', null);
    }

    /*
     * 初始化自定义权限配置
     */
    private function initInfo($data)
    {
        return false;
    }

    /**
     * 后台节点配置的url作为规则存入auth_rule
     * 执行新节点的插入,已有节点的更新,无效规则的删除三项任务
     *
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function updateRules()
    {
        // 需要新增的节点必然位于$nodes
        $nodes = $this->returnNodes(false);

        // 需要更新和删除的节点必然位于$rules
        $rules = find_data(M('AuthRule')->order('name')->select());

        // 构建insert数据
        $data = []; // 保存需要插入和更新的新节点
        foreach ($nodes as $value) {
            $temp ['name'] = $value ['url'];
            $temp ['title'] = $value ['title'];
            $temp ['status'] = 1;
            $data [strtolower($temp ['name'])] = $temp; // 去除重复项
        }

        $update = []; // 保存需要更新的节点
        $ids = []; // 保存需要删除的节点的id
        foreach ($rules as $index => $rule) {
            $key = strtolower($rule ['name']);
            if (isset ($data [$key])) { // 如果数据库中的规则与配置的节点匹配,说明是需要更新的节点
                $data [$key] ['id'] = $rule ['id']; // 为需要更新的节点补充id值
                $update [] = $data [$key];
                unset ($data [$key]);
                unset ($rules [$index]);
                unset ($rule ['condition']);
                $diff [$rule ['id']] = $rule;
            } elseif ($rule ['status'] == 1) {
                $ids [] = $rule ['id'];
            }
        }
        if (count($update)) {
            foreach ($update as $k => $row) {
                if ($row != $diff [$row ['id']]) {
                    M('AuthRule')->where('id', $row ['id'])->update($row);
                }
            }
        }
        if (count($ids)) {
            M('AuthRule')->whereIn('id', $ids)->update(array(
                'status' => -1
            ));
            // 删除规则是否需要从每个用户组的访问授权表中移除该规则?
        }
        if (count($data)) {
            M('AuthRule')->insertAll(array_values($data));
        }
        if (M('AuthRule')->getDbError()) {
            trace('[' . __METHOD__ . ']:' . M('AuthRule')->getDbError());
            return false;
        } else {
            return true;
        }
    }

    /**
     * 权限管理首页--角色列表
     */
    public function index()
    {
        $map ['type'] = 0;
        $list = $this->lists_data('AuthGroup', $map, 'id desc');
        $this->assign('_list', $list);
        $this->assign('_use_tip', true);
        $this->meta_title = '权限管理';
        return $this->fetch();
    }

    /**
     * 微信接口节点管理首页
     */
    public function wechat()
    {
        $list = $this->lists_data('PublicAuth', '');
        $list = int_to_string($list);
        $this->assign('_list', $list);
        $this->assign('_use_tip', true);
        $this->meta_title = '微信接口';
        return $this->fetch();
    }

    /**
     * 创建新规则
     */
    public function createRule()
    {
        $this->meta_title = '新增规则';
        $temp = $_GET ['group'] == 'wechat' ? 'wechat_edit' : 'edit';
        return $this->fetch($temp);
    }

    /**
     * 编辑规则
     */
    public function editRule()
    {
        $auth_group = M('AuthRule')->find(input('id'));
        $this->assign('auth_rule', $auth_group);
        $this->meta_title = '编辑规则';
        return $this->fetch('edit');
    }

    /**
     * 访问授权页面
     *
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function access()
    {
        $this->updateRules();
        $auth_group = M('AuthGroup')->where('status', '>=', 0)->field('id,title,rules')->find();
        $node_list = $this->returnNodes();
        $map = array(
            'status' => 1
        );
        $main_rules = M('AuthRule')->where(wp_where($map))->column('id', 'name');
        $map = array(
            'status' => 1
        );
        $child_rules = M('AuthRule')->where(wp_where($map))->column('id', 'name');

        $this->assign('main_rules', $main_rules);
        $this->assign('auth_rules', $child_rules);
        $this->assign('node_list', $node_list);
        $this->assign('auth_group', $auth_group);
        $gid = input('group_id/d', 0);
        $this->assign('this_group', $auth_group [$gid]);
        $this->meta_title = '访问授权';
        return $this->fetch('managergroup');
    }

    /**
     * 规则数据写入/更新
     */
    public function writeRule()
    {
        $dao = D('AuthRule');
        $data = input('post.');
        if ($data) {
            if (empty ($data ['id'])) {
                $r = $dao->insertGetId($data);
            } else {
                $r = $dao->save($data);
            }
            if ($r === false) {
                return $this->error('操作失败' . $dao->getError());
            } else {
                $url = $data ['group'] == 'wechat' ? U('wechat') : U('index');
                return $this->success('操作成功!', $url);
            }
        } else {
            return $this->error('操作失败' . $dao->getError());
        }
    }

    /**
     * 状态修改
     *
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function changeStatus($method = null)
    {
        if (empty (input('id'))) {
            return $this->error('请选择要操作的数据!');
        }
        switch (strtolower($method)) {
            case 'forbidrule' :
                return $this->forbid('AuthRule');
                break;
            case 'resumerule' :
                return $this->resume('AuthRule');
                break;
            case 'deleterule' :
                return $this->delete('AuthRule');
                break;
            default :
                return $this->error($method . '参数非法');
        }
    }

    /**
     * 用户组授权用户列表
     *
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function user($group_id)
    {
        if (empty ($group_id)) {
            return $this->error('参数错误');
        }

        $auth_group = M('AuthGroup')->where('status', '>=', 0)->field('id,title,rules')->find();
        $prefix = DB_PREFIX;
        $l_table = $prefix . (AuthGroup::MEMBER);
        $r_table = $prefix . (AuthGroup::AUTH_GROUP_ACCESS);
        $model = M()::table($l_table)->alias('m')->join($r_table . ' a', 'm.uid=a.uid');
        $_REQUEST = [];
        $list = $this->lists_data($model, array(
            'a.group_id' => $group_id,
            'm.status' => array(
                '>=',
                0
            )
        ), 'm.uid asc', 'm.uid,m.nickname,m.last_login_time,m.last_login_ip,m.status');
        int_to_string($list);
        $this->assign('_list', $list);
        $this->assign('auth_group', $auth_group);
        $gid = input('group_id/d', 0);
        $this->assign('this_group', $auth_group [$gid]);
        $this->meta_title = '成员授权';
        return $this->fetch();
    }

    public function tree($tree = null)
    {
        $this->assign('tree', $tree);
        return $this->fetch('tree');
    }

    /**
     * 将用户添加到用户组的编辑页面
     *
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function group()
    {
        $uid = I('uid');
        $auth_groups = D('AuthGroup')->getGroups();
        $user_groups = AuthGroup::getUserGroup($uid);
        $ids = [];
        foreach ($user_groups as $value) {
            $ids [] = $value ['group_id'];
        }
        $nickname = D('common/User')->getNickName($uid);
        $this->assign('nickname', $nickname);
        $this->assign('auth_groups', $auth_groups);
        $this->assign('user_groups', implode(',', $ids));
        $this->meta_title = '用户组授权';
        return $this->fetch();
    }

    /**
     * 将用户添加到用户组,入参uid,group_id
     *
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function addToGroup()
    {
        $uid = I('uid');
        $gid = I('group_id');
        if (empty ($uid)) {
            return $this->error('参数有误');
        }
        $AuthGroup = D('AuthGroup');
        if (is_numeric($uid)) {
            if (is_administrator($uid)) {
                return $this->error('该用户为超级管理员');
            }
            if (!M('User')->where('uid', $uid)->find()) {
                return $this->error('用户不存在');
            }
        }

        if ($gid && !$AuthGroup->checkGroupId($gid)) {
            return $this->error($AuthGroup->error);
        }
        if ($AuthGroup->addToGroup($uid, $gid)) {
            return $this->success('操作成功');
        } else {
            return $this->error($AuthGroup->getError());
        }
    }

    /**
     * 将用户从用户组中移除 入参:uid,group_id
     *
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function removeFromGroup()
    {
        $uid = I('uid');
        $gid = I('group_id');
        if ($uid == session('mid')) {
            return $this->error('不允许解除自身授权');
        }
        if (empty ($uid) || empty ($gid)) {
            return $this->error('参数有误');
        }
        $AuthGroup = D('AuthGroup');
        if (!$AuthGroup->where('id', $gid)->find()) {
            return $this->error('用户组不存在');
        }
        if ($AuthGroup->removeFromGroup($uid, $gid)) {
            return $this->success('操作成功');
        } else {
            return $this->error('操作失败');
        }
    }

    /**
     * 将分类添加到用户组 入参:cid,group_id
     *
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function addToCategory()
    {
        $cid = I('cid');
        $gid = I('group_id');
        if (empty ($gid)) {
            return $this->error('参数有误');
        }
        $AuthGroup = D('AuthGroup');
        if (!$AuthGroup->where('id', $gid)->find()) {
            return $this->error('用户组不存在');
        }
        if ($cid && !$AuthGroup->checkCategoryId($cid)) {
            return $this->error($AuthGroup->error);
        }
        if ($AuthGroup->addToCategory($gid, $cid)) {
            return $this->success('操作成功');
        } else {
            return $this->error('操作失败');
        }
    }

    /**
     * 将模型添加到用户组 入参:mid,group_id
     *
     * @author 朱亚杰 <xcoolcc@gmail.com>
     */
    public function addToModel()
    {
        $mid = I('id');
        $gid = I('group_id');
        if (empty ($gid)) {
            return $this->error('参数有误');
        }
        $AuthGroup = D('AuthGroup');
        if (!$AuthGroup->where('id', $gid)->find()) {
            return $this->error('用户组不存在');
        }
        if ($mid && !$AuthGroup->checkModelId($mid)) {
            return $this->error($AuthGroup->error);
        }
        $res = $AuthGroup->addToModel($gid, $mid);
        return $this->msg($res);
    }

    function set_status()
    {
        $status = 1 - input('status/d', 0);
        $id = id();
        $res = D('AuthGroup')->where('id', $id)->update(['status' => $status]);

        return $this->msg($res);
    }

    function menu()
    {
        $model = $this->getModel('menu');
        $id = id();

        if (IS_POST) {
            $ids = implode(',', input('post.ids'));
            $res = D('AuthGroup')->where('id', $id)->update(['menu_rule' => $ids]);

            return $this->msg($res);
        } else {
            $GLOBALS['where_forbit_field'] = 'id';
            $list_data = $this->getModelList($model, 'sort asc,id asc', true);

            $list_data ['list_data'] = $this->treeList($list_data ['list_data']);
            $list_data ['_page'] = '';

            $this->assign($list_data);

            $menu_rule = D('AuthGroup')->where('id', $id)->value('menu_rule');
            if (empty($menu_rule)) {
                $this->assign('menu_rule', []);
            } else {
                $this->assign('menu_rule', explode(',', $menu_rule));
            }

            return $this->fetch();
        }
    }

    function treeList($data, $pid = 0, $level = '')
    {
        $tree = $two = [];

        //二级菜单
        foreach ($data as $vo) {
            if (0 != $vo['pid']) {
                $two[$vo['pid']][] = $vo;
            }
        }

        //一级菜单
        foreach ($data as $vo) {
            if (0 == $vo['pid']) {
                $vo['child'] = isset($two[$vo['id']]) ? $two[$vo['id']] : [];
                $tree[] = $vo;
            }
        }
        return $tree;
    }
}
