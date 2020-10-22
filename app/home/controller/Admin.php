<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\home\controller;

/**
 * 后台用户控制器
 */
class Admin extends Home
{

    protected $addon, $model;

    public function initialize()
    {
        parent::initialize();
        
        $this->assign('check_all', false);
        $this->assign('search_url', U('lists'));
        
        $this->model = M('model')->getByName('user');
        $this->assign('model', $this->model);
        // dump ( $this->model );
        
        $res['title'] = '公众号管理';
        $res['url'] = U('weixin/publics/lists');
        $res['class'] = '';
        $nav[] = $res;
        
        $res['title'] = '管理员配置';
        $res['url'] = U('home/admin/lists');
        $res['class'] = 'current';
        $nav[] = $res;
        
        $this->assign('nav', $nav);
    }

    protected function _display()
    {
        $this->view->display(ACTION_NAME);
    }

    /**
     * 显示指定模型列表数据
     */
    public function lists()
    {
        // 获取模型信息
        $model = $this->model;
        
        $page = I('p', 1, 'intval');
        // 解析列表规则
        $list_data = $this->listGrid($model);
        $grids = $list_data['list_grids'];
        $fields = $list_data['fields'];
        
        $map = $this->searchMap($model, $list_data['db_fields']);
        $map['uid'] = array(
            'not in',
            array(
                $this->mid
            )
        );
        $row = empty($model['list_row']) ? 20 : $model['list_row'];
        
        // 读取模型数据列表
        $name = parse_name($model['name'], true);
        
        $page_data = M($name)->where(wp_where($map))
            ->order('uid DESC')
            ->paginate($row);
        $page = $page_data->render();
        $page_data = $page_data->toArray();
        $list_data['count'] = $page_data['total'];
        
        foreach ($page_data['data'] as &$v) {
            $v['public_ids'] = $this->_get_public_name($v['public_ids']);
        }
        
        $this->assign('_page', $page);
        $this->assign('list_grids', $grids);
        $this->assign('list_data', $page_data['data']);
        
        $this->_display();
    }

    public function del()
    {
        $ids = I('ids');
        $model = $this->model;
        ! empty($ids) || $ids = I('id');
        ! empty($ids) || $ids = array_filter(array_unique((array) I('ids', 0)));
        if(empty($ids)) return $this->error('请选择要操作的数据!');
        
        $Model = M($model['name']);
        $map[] = array(
            'uid',
            'in',
            $ids
        );
        
        // 插件里的操作自动加上Token限制
        $wpid = get_wpid();
        if (! empty($wpid)) {
            $map[] = [
                'wpid',
                '=',
                $wpid
            ];
        }
        
        if ($Model->where(wp_where($map))->delete()) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败！');
        }
    }

    public function edit()
    {
        $model = $this->model;
        $id = I('id');
        
        // 获取数据
        $data = M($model['name'])->where('id', $id)->find();
        if(empty($data)) return $this->error('数据不存在！');
        $data['id'] = $data['uid'];
        
        if (request()->isPost()) {
			$data = input('post.');
            $data['uid'] = input('post.id');
            $Model = D($model['name']);
            $data = $this->checkData($data, $model);
            if ($Model->where('id', $id)->save($data)!==false) {
                return $this->success('保存' . $model['title'] . '成功！', U('lists?model=' . $model['name'], $this->get_param));
            } else {
                return $this->error($Model->getError());
            }
        } else {
            $this->_getField($model);
            
            $this->assign('data', $data);
            
            $this->_display();
        }
    }

    public function add()
    {
        $model = $this->model;
        if (request()->isPost()) {
			$data = input('post.');
            $data['status'] = 1;
            /* 调用注册接口注册用户 */
            $uid = D('common/User')->register(input('post.nickname'), input('post.password'), input('post.nickname') . NOW_TIME . '@www.bctos.cn');
            if (0 < $uid) { // 注册成功
                $data['uid'] = $uid;
                $Model = D($model['name']);                
                $data = $this->checkData($data, $model);
                if (false !== ($id = $Model->save($data))) {
                    return $this->success('添加' . $model['title'] . '成功！', U('lists?model=' . $model['name'], $this->get_param));
                } else {
                    //
                    return $this->error($Model->getError());
                }
            } else { // 注册失败，显示错误信息
                return $this->error($this->showRegError($uid));
            }
        } else {
            $this->_getField($model);
            $this->_display();
        }
    }

    function _getField($model)
    {
        $fields = get_model_attribute ( $model );
        
        $list = find_data( M('publics')->select() );
        $extra = '';
        foreach ($list as $vo) {
            $extra .= $vo['id'] . ":" . $vo['public_name'] . "\r\n";
        }
        $extra = rtrim($extra, "
");
        $fields['public_ids']['extra'] = $extra;
        
        $this->assign('fields', $fields);
    }

    function _get_public_name($ids)
    {
        if (empty($ids))
            return '';
        
        static $_public_list;
        if (empty($_public_list)) {
            $list = find_data( M('publics')->select() );
            foreach ($list as $v) {
                $_public_list[$v['id']] = $v['public_name'];
            }
        }
        
        $ids = explode(',', $ids);
        foreach ($ids as $id) {
            $res[$id] = $_public_list[$id];
        }
        
        return implode(', ', $res);
    }

    /**
     * 获取用户注册错误信息
     *
     * @param integer $code
     *            错误编码
     * @return string 错误信息
     */
    private function showRegError($code = 0)
    {
        switch ($code) {
            case - 1:
                $error = '用户名长度必须在16个字符以内！';
                break;
            case - 2:
                $error = '用户名被禁止注册！';
                break;
            case - 3:
                $error = '用户名被占用！';
                break;
            case - 4:
                $error = '密码长度必须在6-30个字符之间！';
                break;
            case - 5:
                $error = '邮箱格式不正确！';
                break;
            case - 6:
                $error = '邮箱长度必须在1-32个字符之间！';
                break;
            case - 7:
                $error = '邮箱被禁止注册！';
                break;
            case - 8:
                $error = '邮箱被占用！';
                break;
            case - 9:
                $error = '手机格式不正确！';
                break;
            case - 10:
                $error = '手机被禁止注册！';
                break;
            case - 11:
                $error = '手机号被占用！';
                break;
            default:
                // $error = '未知错误';
                $error = '用户名被占用！';
        }
        return $error;
    }
}
