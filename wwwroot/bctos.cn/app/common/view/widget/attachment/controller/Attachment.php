<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------

namespace Addons\Attachment\Controller;
use app\common\controller\WebBase;

class Attachment extends WebBase{
	
	/* 附件下载 */
	public function download(){
		/* 获取附件ID */
		$id = input('id');
		if(empty($id) || !is_numeric($id)){
			$this->error('附件ID无效！');
		}

		/* 下载附件 */
		$Attachment = D('Attachment/Attachment');
		if(false === $Attachment->download($id)){
			$this->error($Attachment->getError());
		}

	}

	/* 上传附件 */
	public function upload(){
		/* 返回标准数据 */
		$return  = array('status' => 1, 'info' => '上传成功', 'data' => '');

		/* 获取当前分类附件配置信息 */
		$default  = config('ATTACHMENT_DEFAULT');
		$category = get_category(input('category'));

		/* 分类正确性检测 */
		if(empty($category)){
			$return['status'] = 0;
			$return['info']   = '没有指定分类或分类不正确；';
		} else {
			$config = $category['extend']['attachment'];
			$config = empty($config) ? $default : array_merge($default, $config);

			/* 检测并上传附件 */
			if(in_array('2', str2arr($config['allow_type']))){
				$setting = config('ATTACHMENT_UPLOAD');

				/* 调用文件上传组件上传文件 */
				$File = D('File');
				$info = $File->upload($_FILES, $setting, $config['driver'], $config['driver_config']);
				/* 记录附件信息 */
				if($info){
					$return['data'] = think_encrypt(json_encode($info['attachment']));
				} else {
					$return['status'] = 0;
					$return['info']   = $File->getError();
				}
			} else {
				$return['info']   = '该分类不允许上传文件附件！';
				$return['status'] = 0;
			}
		}

		/* 返回JSON数据 */
		return json_encode($return);
	}

}
