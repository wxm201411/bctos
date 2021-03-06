<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\common\model\Base;

/**
 * 日志模型
 */
class Logs extends Base {
	public function __construct(){
	}
	/**
	 * 查找后置操作
	 */
	protected function _after_find(&$result, $options) {
	}
	protected function _after_select(&$result, $options) {
	}

	/**
	 * 判断日志类型
	 *
	 * @param int $type,1-接口日志
	 */
	public function getLogs($type = '') {
		if($type == ''){

		}
		switch ($type){
			case 1:
				break;
			default:;
		}
		return $this->getList();
	}
	/**
	 * 获取日志
	 */
	public function getList(){
		$rows = config('app.LIST_ROWS') > 0 ? config('app.LIST_ROWS') : 20;
		$list = $this->order('cTime desc')-> paginate($rows);
		return $list;
	}

}
