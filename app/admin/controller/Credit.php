<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星
// +----------------------------------------------------------------------
namespace app\admin\controller;

/**
 * 模型数据管理控制器
 *
 * @author 凡星
 */
class Credit extends Admin {

	/**
	 * 显示指定模型列表数据
	 *
	 * @param String $model
	 *        	模型标识
	 * @author 凡星
	 */
	public function lists() {
		$model = $this->getModel ( 'credit_config' );

		$map ['wpid'] = '0';
		session ( 'common_condition', $map );
		$list_data = $this->getModelList ( $model );

		$this->assign ( $list_data );

		$this->meta_title = $model ['title'] . '列表';

		return $this->fetch( 'think/lists' );
	}
	public function edit() {
        $id = I('id');
		D('common/Credit' )->clearCache(0);
		$model = $this->getModel ( 'credit_config' );
		$this->meta_title = '编辑' . $model ['title'];
		return parent::commonEdit ( $model, $id, 'Think:edit' );
	}
	public function add() {
		$model = $this->getModel ( 'credit_config' );
		$this->meta_title = '新增' . $model ['title'];
		return parent::commonAdd ( $model, 'Think:add' );
	}
	public function del() {
		D('common/Credit' )->clearCache(0);
		$model = $this->getModel ( 'credit_config' );
		$ids = I('ids');
		return parent::commonDel ( $model, $ids );
	}
}