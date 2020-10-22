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
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */
class File extends Home
{

    /* 文件上传 */
    public function upload()
    {
    	//用户登录检测
    	if (!is_login()){
    		$return = array(
    				'code'=>1,
    				'info' => '上传失败，请先登录',
    				'msg' => '上传失败，请先登录',
    				'data' => ''
    		);
    		return json_encode($return);
    	}
        $return = array(
            'info' => '上传成功',
            'data' => ''
        );
        /* 调用文件上传组件上传文件 */
        $File = D('home/File');
        $file_driver = strtolower(config('app.picture_upload_driver'));
        $info = $File->upload(config('app.download_upload'), config('app.picture_upload_driver'), config("app.upload_{$file_driver}_config"));
        /* 记录附件信息 */
        if ($info) {
            $return['code'] = $return['status'] = 0;
            $return = array_merge($info['download'], $return);
        } else {
            $return['code'] = $return['status'] = 1;
            $return['msg'] = $return['info'] = $File->getError();
        }
        /* 返回JSON数据 */
        return json_encode($return);
    }

    /* 下载文件 */
    public function download($id = null)
    {
        if (empty($id) || ! is_numeric($id)) {
            return $this->error('参数错误！');
        }
        
        $logic = D('Download', 'Logic');
        if (! $logic->download($id)) {
            return $this->error($logic->getError());
        }
    }

    /**
     * 上传图片
     *
     * @author huajie <banhuajie@163.com>
     */
    public function upload_picture()
    {
        /* 返回标准数据 */
        $return = array(
            'status' => 1,
            'info' => '上传成功',
            'data' => ''
        );
        /* 调用文件上传组件上传文件 */
        $Picture = D('home/Picture');
        $pic_driver = strtolower(config('app.picture_upload_driver'));
        
        $info = $Picture->upload(config('app.picture_upload'), config('app.picture_upload_driver'), config("app.upload_{$pic_driver}_config")); // TODO:上传到远程服务器
        /* 记录图片信息 */
        if ($info) {
            $return = array_merge($info['download'], $return);
        } else {
            $return['status'] = 0;
            $return['info'] = $Picture->getError();
        }
        /* 返回JSON数据 */
        return json($return);
    }

    // 图片选择器
    function upload_dialog()
    {
        return $this->fetch();
    }

    function user_pics()
    {
        $map['wpid'] = get_wpid();
        $picList = M('Picture')->where(wp_where($map))
            ->order('id desc')
            ->select();
        $this->assign('picList', $picList);
        exit($this->fetch());
    }

    // 系统图标
    function system_pics()
    {
        $dir = I('dir');
        $cateList = $this->_getLocalCate();
        if (! $dir) {
            $dir = $cateList[0]['dir'];
        }
        foreach ($cateList as &$ca) {
            if ($dir == $ca['dir']) {
                $ca['current'] = 1;
                break;
            }
        }
        $picList = $this->_getCatePicList($dir);
        $picDao = D('home/Picture');
        foreach ($picList as &$p) {
            $pInfo = $picDao->where('path', $p['path'])->find();
            if ($pInfo) {
                $p['id'] = $pInfo['id'];
                continue;
            } else {
                $data['path'] = $p['path'];
                $data['system'] = 1;
                $data['status'] = 1;
                $data['create_time'] = time();
                $id = $picDao->removeOption('data')->insertGetId($data);
                if ($id) {
                    $p['id'] = $id;
                } else {
                    unset($p);
                }
            }
        }
        $this->assign('cateList', $cateList);
        $this->assign('picList', $picList);
        exit($this->fetch());
    }

    // 获取系统图标分类
    function _getLocalCate()
    {
        $dir = SITE_PATH . '/public/static/icon';
        $dirObj = opendir($dir);
        while (false !== ($file = readdir($dirObj))) {
            if ($file === '.' || $file == '..' || $file == '.svn' || is_file($dir . '/' . $file)) {
                continue;
            }
            $res['cate'] = $file;
            $res['dir'] = $file;
            // 获取配置文件
            if (file_exists($dir . '/' . $file . '/info.php')) {
                $info = require_once $dir . '/' . $file . '/info.php';
                $res = array_merge($res, $info);
            }
            $cateList[] = $res;
            unset($res);
        }
        closedir($dirObj);
        return $cateList;
    }

    function _getCatePicList($dirName)
    {
        $dir = SITE_PATH . '/public/static/icon/' . $dirName;
        $dirObj = opendir($dir);
        while (false !== ($file = readdir($dirObj))) {
            if ($file === '.' || $file == '..' || $file == '.svn' || $file == 'info.php') {
                continue;
            }
            $res['path'] = '/static/icon/' . $dirName . '/' . $file;
            $res['url'] = SITE_URL . '/static/icon/' . $dirName . '/' . $file;
            $picList[] = $res;
            
            unset($res);
        }
        closedir($dirObj);
        return $picList;
    }

    // 图片管理
    public function picLists()
    {
        $picModel = $this->getModel('picture');
        $wpid = get_wpid();
        $map['wpid'] = array(
            'in',
            $wpid . ',1'
        );
        $add_url = U('editPic', array(
            'mdm' => I('mdm')
        ));
        $del_url = U('delPic', array(
            'mdm' => I('mdm')
        ));
        $this->assign('add_url', $add_url);
        $this->assign('del_url', $del_url);
        $map['wpid'] = get_wpid();
        $page_data = D('home/Picture')->where(wp_where($map))->paginate(30);
        $list = dealPage($page_data);
        
        $list['fields'] = array(
            'id',
            'pic',
            'cate'
        );
        $list['list_grids']['id']['field'] = 'id';
        $list['list_grids']['id']['title'] = '图片编号';
        $list['list_grids']['pic']['field'] = 'pic';
        $list['list_grids']['pic']['title'] = '图片';
        $list['list_grids']['ids']['field'] = 'ids';
        $list['list_grids']['ids']['title'] = '操作';
        $list['list_grids']['urls']['href'] = "editPic&id=[id]|编辑,delPic&id=[id]|删除";
        foreach ($list['list_data'] as &$v) {
            $v['pic'] = '<img src="' . get_cover_url($v['id']) . '" width="100" height="100" />';
        }
        $this->assign($list);
        return $this->fetch(SITE_PATH . '/app/common/view/base/lists.html');
    }

    public function delPic()
    {
        ! empty($ids) || $ids = I('id');
        ! empty($ids) || $ids = array_filter(array_unique((array) I('ids', 0)));
        if(empty($ids)) return $this->error('请选择要操作的数据!');
        
        $res = D('home/Picture')->whereIn('id', $ids)->delete();
        if ($res) {
            return $this->success('删除成功', U('home/File/picLists'));
        } else {
            return $this->success('删除失败');
        }
    }

    // 新增图片
    public function editPic()
    {
        $fields['id']['type'] = 'picture';
        $fields['id']['name'] = 'id';
        $fields['id']['title'] = '图片';
        $fields['id']['is_show'] = 1;
        
        $id = $_GET['id'];
        if ($id) {
            $info = D('home/Picture')->where('id', $id)->find();
            $this->assign('data', $info);
        }
        if (request()->isPost()) {
            $data['id'] = input('post.id');
            $res = D('home/Picture')->where('id', $data['id'])->update($data);
            if ($res!==false) {
                return $this->success('保存成功', U('home/File/picLists'));
            } else {
                return $this->success('保存失败');
            }
        }
        $this->assign('fields', $fields);
        $this->assign('post_url', U('home/File/editPic', array(
            'id' => $id,
            'mdm' => I('mdm')
        )));
        return $this->fetch(SITE_PATH . '/app/common/view/base/edit.html');
    }
    
    /* 文件上传 到根目录 */
    public function upload_root() {
    	$return = array(
    			'status' => 1,
    			'info' => '上传成功',
    			'data' => ''
    	);
    	/* 调用文件上传组件上传文件 */
    	$File = D('home/File');
    	$file_driver = strtolower(config('app.picture_upload_driver'));
    	$setting = array (
    			'rootPath' => './' ,
    	);
    	$info = $File->upload($setting, config('app.picture_upload_driver'), config("app.upload_{$file_driver}_config"));
//     	$info = $File->upload(config('app.download_upload'), config('app.picture_upload_driver'), config("app.upload_{$file_driver}_config"));
    	/* 记录附件信息 */
    	if ($info) {
    		$return['status'] = 1;
    		$return = array_merge($info['download'], $return);
    	} else {
    		$return['status'] = 0;
    		$return['info'] = $File->getError();
    	}
    	/* 返回JSON数据 */
    	return json_encode($return);
    	
    }
    // layui图标
    function layui_icon()
    {
        $dir = I('dir');
        $cateList = $this->_getLocalCate();
        if (! $dir) {
            $dir = $cateList[0]['dir'];
        }
        foreach ($cateList as &$ca) {
            if ($dir == $ca['dir']) {
                $ca['current'] = 1;
                break;
            }
        }
        $picList = $this->_getCatePicList($dir);
        $picDao = D('home/Picture');
        foreach ($picList as &$p) {
            $pInfo = $picDao->where('path', $p['path'])->find();
            if ($pInfo) {
                $p['id'] = $pInfo['id'];
                continue;
            } else {
                $data['path'] = $p['path'];
                $data['system'] = 1;
                $data['status'] = 1;
                $data['create_time'] = time();
                $id = $picDao->removeOption('data')->insertGetId($data);
                if ($id) {
                    $p['id'] = $id;
                } else {
                    unset($p);
                }
            }
        }
        $this->assign('cateList', $cateList);
        $this->assign('picList', $picList);
        exit($this->fetch());
    }
}
