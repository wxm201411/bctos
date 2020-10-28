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
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class Dev extends Home
{
    // 开发者
    public function index()
    {
        $GLOBALS['where']['uid'] = $this->mid;
        $GLOBALS['forbit_audit'] = false;
        $model = $this->getModel('app_shop');

        return $this->commonLists($model, 'index');
    }

    public function submitApp()
    {
        $this->assign('info', $GLOBALS['myinfo']);

        $model = $this->getModel('app_shop');
        $this->assign('model', $model);
        $id = I('id', 0);

        if (IS_POST) {
            $Model = M($model ['name']);

            $post = input('post.');
            $post ['price'] = intval($post ['price']);

            $post = $this->checkData($post, $model);
            if ($id > 0) {
                $res = $Model->where('id', $id)->update($post);
            } else {
                if (isset($post['id'])) unset($post['id']);
                $res = $id = $Model->insertGetId($post);
            }
            if ($res) {
                $url = U('index');
                return $this->success('保存成功,应用已提交给管理员审核！', $url);
            } else {
                return $this->error($Model->getError());
            }
        } else {
            $fields = get_model_attribute($model ['id']);
            // dump($fields);
            unset ($fields [1] [10], $fields [1] [11], $fields [1] [12]);
            $this->assign('fields', $fields);

            // 获取数据
            if ($id) {
                $data = M($model ['name'])->where('id', $id)->find();
                $data || $this->error('数据不存在！');
                $this->assign('data', $data);
            }

            $this->meta_title = '编辑' . $model ['title'];
            $this->assign('post_url', U('submitApp'));
        }

        return $this->fetch('submitApp');
    }

    public function appDetail()
    {
        $map ['id'] = I('id', 0);
        $data = M('app_shop')->where($map)->find();

        $model = $this->getModel('app_shop');
        $res = $this->parseListData([$data], $model);
        $data = $res[0];

        $data ['logo'] = empty ($data ['logo']) ? __ROOT__ . '/home/images/app_no_pic.png' : $data ['logo'];

        $this->assign('data', $data);

        return $this->fetch('appDetail');
    }

    function download()
    {
        /* 获取附件ID */
        $id = input('attach/d', 0);
        if (empty ($id)) {
            $this->error('附件ID无效！');
        }

        $map ['id'] = input('id/d', 0);
        is_administrator() || $map ['uid'] = $this->mid;
        $info = M('app_shop')->where($map)->find();

        /* 下载附件 */
        $Attachment = D('File');
        $config = config('app.DOWNLOAD_UPLOAD');
        if (false === $Attachment->download($config ['rootPath'], $id)) {
            return $this->error($Attachment->getError());
        }
    }
}