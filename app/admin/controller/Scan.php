<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\controller;

/**
 * 模型数据管理控制器
 *
 * @author 凡星
 */
class Scan extends Admin {
	public function index() {
		if (IS_POST) {
			$Config = D ( 'Config' );
			
			$wp_id = I ( 'wp_id' );
			$status = I ( 'status' );
			
			if ($status == 1 && empty ( $wp_id )) {
				return $this->error ( '140261:请先选择服务号' );
			}
			
			$Config->update([ 'SCAN_LOGIN_PUBLIC'=> $wp_id ]);
			$Config->update([ 'SCAN_LOGIN'=> $status ]);
			
			$where ['id'] = $wp_id;
			$token = M ( 'publics' )->where ( $where )->value ( 'token' );
			$Config->update([ 'SCAN_LOGIN_TOKEN'=> $token ]);
			
			return $this->success ( '操作成功！' );
		} else {
			$this->assign ( 'status', config( 'app.SCAN_LOGIN' ) );
			$this->assign ( 'wp_id', config( 'app.SCAN_LOGIN_PUBLIC' ) );
			
			return $this->fetch();
		}
	}
}