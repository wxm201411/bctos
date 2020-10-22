<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\common\widget;

use app\common\controller\WapBase;

/**
 * 编辑器插件
 *
 * @author yangweijie <yangweijiester@gmail.com>
 */
class Editor extends WapBase
{
    public $info = array(
        'name' => 'EditorForAdmin',
        'title' => '后台编辑器',
        'description' => '用于增强整站长文本的输入和显示',
        'status' => 1,
        'author' => 'thinkphp',
        'version' => '0.2'
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
     * @param
     *            array('name'=>'表单name','value'=>'表单对应的值')
     */
    public function show($data)
    {
        $uploadDriver = strtolower(config("app.EDITOR_PICTURE_UPLOAD_DRIVER"));
        if ($uploadDriver == 'qiniu') {
            $driverfile = 'ueditor_qiniu';
        } else {
            $driverfile = 'ueditor';
        }
        $this->assign('driver_file', $driverfile);
        $data['is_mult'] = isset($data['is_mult']) ? intval($data['is_mult']) : 0; // 默认不传时为0
        if (!isset($data['style'])) {
            $data['style'] = 'height:680px;';//默认宽高
        }
        $this->assign('data', $data);//dump($data);

        static $editor_load = 0;
        $this->assign('editor_load_count', $editor_load);
        if ($editor_load == 0) {
            $editor_load++;
        }

        $addons_config = [
            "editor_height" => "500px",
        ];
        $this->assign('addons_config', $addons_config);
        return $this->fetch('common@widget/edit');
    }

    /**
     * 编辑器挂载的后台文档模型文章内容钩子
     *
     * @param
     *            array('name'=>'表单name','value'=>'表单对应的值')
     */
    public function uploadImg($data)
    {
        $this->assign('addons_data', $data);
        $this->assign('addons_config', $this->getConfig());
        $uploadDriver = strtolower(config("app.EDITOR_PICTURE_UPLOAD_DRIVER"));
        if ($uploadDriver == 'qiniu') {
            $driverfile = 'ueditor_qiniu';
        } else {
            $driverfile = 'ueditor';
        }
        $this->assign('driver_file', $driverfile);

        return $this->fetch('common@widget/edit_upload_btn');
    }
}
