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
 * 后台用户控制器
 *
 */
class User extends Admin {

	/**
	 * 用户管理首页
	 *
	 */
	public function index() {
		$nickname = I ( 'nickname' );
		$group_id = I ( 'group_id/d', 0 );
		$map ['status'] = array (
				'>=',
				0
		);
		if (is_numeric ( $nickname )) {
			$map ['uid|nickname'] = array (
					intval ( $nickname ),
					array (
							'like',
							'%' . $nickname . '%'
					),
					'_multi' => true
			);
		} else {
			$map ['nickname'] = array (
					'like',
					'%' . ( string ) $nickname . '%'
			);
		}
		if ($group_id > 0) {
			$uids = M( 'auth_group_access' )->where("group_id='$group_id'" )->column( 'uid' );
			if (empty ( $uids )) {
				$map ['uid'] = 0;
			} else {
				$map ['uid'] = array (
						'in',
						$uids
				);
			}
		}
		$list = $this->lists_data ( 'User', $map );
		if (! empty ( $list )) {
			$group_list = find_data( M( 'auth_group' )->field ( 'id,title' )->select() );
			foreach ( $group_list as $vo ) {
				$groupArr [$vo ['id']] = $vo ['title'];
			}

			$uids = getSubByKey ( $list, 'uid' );
			$link_map ['uid'] = array (
					'in',
					$uids
			);
			$link = find_data( M( 'auth_group_access' )->where ( wp_where( $link_map ) )->select() );
                        $gidArr = [];
			foreach ( $link as $l ) {
				isset($groupArr [$l ['group_id']]) && $linkArr [$l ['uid']] [] =  $groupArr [$l ['group_id']];
				$gidArr [$l ['uid']] [] = $l ['group_id'];
			}
			//dump($list);exit;
			foreach ( $list as &$vo ) {
				$vo ['group'] = isset($linkArr [$vo ['uid']])?implode ( ', ', $linkArr [$vo ['uid']] ):'';
				$vo ['is_admin'] = (isset($gidArr [$vo ['uid']]) && (in_array ( 3, $gidArr [$vo ['uid']] ) || in_array ( 1, $gidArr [$vo ['uid']]) )) ? 1 : 0;
				$vo ['audit_text'] = $vo ['is_audit'] ? '通过' : '待审';
				$vo ['nickname'] = $vo ['nickname'];
			}
		}

		int_to_string ( $list );
		$this->assign ( '_list', $list );

		$auth_group = find_data( M( 'AuthGroup' )->where( 'status>0 and manager_id=0' )->field ( 'id,title' )->select() );
		$this->assign ( 'auth_group', $auth_group );
		$this->assign ( 'group_id', $group_id );
		$tags = find_data( M( 'auth_group' )->where('type',1)->select() );
		$this->assign('tags',$tags);

		$this->meta_title = '用户信息';
		return $this->fetch();
	}
	/**
	 * 修改昵称初始化
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function updateNickname() {
		$nickname = M( 'User' )->getFieldByUid ( session('mid'), 'nickname' );
		$this->assign ( 'nickname', $nickname );
		$this->meta_title = '修改昵称';
		return $this->fetch('update_nickname');
	}

	/**
	 * 修改昵称提交
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function submitNickname() {
		// 获取参数
		$nickname = I ( 'post.nickname' );
		$password = I ( 'post.password' );
		if(empty ( $nickname )) return $this->error ( '请输入昵称' );
		if(empty ( $password )) return $this->error ( '请输入密码' );

		// 密码验证
		$map ['nickname'] = I ( 'post.old_nickname' );
		$User = D('common/User' );
		$user = $User->where ( wp_where( $map ) )->find ();
		if (think_bctos_md5 ( $password ) !== $user ['password']) {
			return $this->error ( '密码不正确' );
		}

		$uid = $user ['uid'];
		$data = array (
				'nickname' => $nickname
		);

		$res = $User->where ( 'uid', $uid )->update( $data );

		if ($res) {
			$user = session ( 'user_auth' );
			$user ['username'] = $data ['nickname'];
			session ( 'user_auth', $user );
			session ( 'user_auth_sign', data_auth_sign ( $user ) );
			return $this->success ( '修改昵称成功！' );
		} else {
			return $this->error ( '修改昵称失败！' );
		}
	}

	/**
	 * 修改密码初始化
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function updatePassword() {
		$this->meta_title = '修改密码';
		return $this->fetch('update_password');
	}

	/**
	 * 修改密码提交
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function submitPassword() {
		// 获取参数
		$password = I ( 'post.old' );
		if(empty ( $password )) return $this->error ( '请输入原密码' );
		$data ['password'] = I ( 'post.password' );
		if(empty ( $data ['password'] )) return $this->error ( '请输入新密码' );
		$repassword = I ( 'post.repassword' );
		if(empty ( $repassword )) return $this->error ( '请输入确认密码' );

		if ($data ['password'] !== $repassword) {
			return $this->error ( '您输入的新密码与确认密码不一致' );
		}

		$res = D('common/User' )->updateUserFields ( session('mid'), $password, $data );
		if ($res !== false) {
			return $this->success ( '修改密码成功！' );
		} else {
			return $this->error ( $res ['info'] );
		}
	}

	/**
	 * 用户行为列表
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function action() {
		// 获取列表数据
		$Action = M( 'Action' )->where ( wp_where(array (
				'status' => array (
						'>',
						- 1
				)
		)) );
		$list = $this->lists_data ( $Action );
		int_to_string ( $list );
		// 记录当前列表页的cookie
		$forward = cookie('__forward__');
            empty( $forward ) && cookie('__forward__', $_SERVER['REQUEST_URI']);

		$this->assign ( '_list', $list );
		$this->meta_title = '用户行为';
		return $this->fetch();
	}

	/**
	 * 新增行为
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function addAction() {
		$this->meta_title = '新增行为';
		$this->assign ( 'data', null );
		return $this->fetch( 'editaction' );
	}

	/**
	 * 编辑行为
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function editAction() {
		$id = I ( 'id' );
		if(empty ( $id )) return $this->error ( '参数不能为空！' );
		$data = M( 'Action' )->field ( true )->where('id', $id)->find();

		$this->assign ( 'data', $data );
		$this->meta_title = '编辑行为';
		return $this->fetch( 'editaction' );
	}

	/**
	 * 更新行为
	 *
	 * @author huajie <banhuajie@163.com>
	 */
	public function saveAction() {
		$res = D ( 'Action' )->updateInfo ();
		if (! $res) {
			return $this->error ( D ( 'Action' )->getError () );
		} else {
			return $this->success ( $res ['id'] ? '更新成功！' : '新增成功！', cookie( '__forward__' ) );
		}
	}

	/**
	 * 会员状态修改
	 *
	 * @author 朱亚杰 <zhuyajie@topthink.net>
	 */
	public function changeStatus($method = null) {
		$id = array_unique ( ( array ) I ( 'id', 0 ) );
		if (in_array ( config( 'app.user_administrator' ), $id )) {
			return $this->error ( "不允许对超级管理员执行该操作!" );
		}
		$id = is_array ( $id ) ? implode ( ',', $id ) : $id;
		if (empty ( $id )) {
			return $this->error ( '请选择要操作的数据!' );
		}
		$map ['uid'] = array (
				'in',
				$id
		);
		switch (strtolower ( $method )) {
			case 'forbiduser' :
                return $this->forbid ( 'User', $map );
				break;
			case 'resumeuser' :
                return $this->resume ( 'User', $map );
				break;
			case 'deleteuser' :
                return $this->delete ( 'User', $map );
				break;
			case 'audit_1' :
                return $this->audit ( $map, 1, $id );
				break;
			case 'audit_0' :
                return $this->audit ( $map, 0, $id );
				break;
			default :
				return $this->error ( '参数非法' );
		}
	}
	function audit($map, $val, $ids = 0) {
		$savedata ['is_audit'] = $val;
		$savedata ['audit_time'] = time ();
		$res = M( 'user' )->where ( wp_where( $map ) )->update( $savedata );
		if ($res!==false) {
			if (is_array ( $ids )) {
				foreach ( $ids as $id ) {
					D('common/User' )->getUserInfo ( $id, true );
				}
			} else {
				D('common/User' )->getUserInfo ( $ids, true );
			}

			return $this->success ( '设置成功' );
		} else {
			return $this->error ( '设置失败' );
		}
	}
	public function changeGroup() {
		$id = array_unique ( ( array ) I ( 'id', 0 ) );

		if (empty ( $id )) {
			return $this->error ( '请选择用户!' );
		}
		$group_id = I ( 'group_id', 0 );
		if (empty ( $group_id )) {
			return $this->error ( '请选择用户组!' );
		}
		$data ['uid'] = array (
				'in',
				$id
		);
		$data ['group_id'] = $group_id;
		M( 'auth_group_access' )->where ( wp_where( $data ) )->delete ();

		$type = I ( 'type', 0 );
		if ($type != 0)
			die ( 1 );

		foreach ( $id as $uid ) {
			$data ['uid'] = $uid;
			$res = M( 'auth_group_access' )->insertGetId( $data );

			// 更新用户缓存
			D('common/User' )->getUserInfo ( $uid, true );
		}
		echo 1;
	}
	public function add($username = '', $password = '', $repassword = '', $email = '') {
		if (request()->isPost()) {
			/* 检测密码 */
			if ($password != $repassword) {
				return $this->error ( '密码和重复密码不一致！' );
			}

			/* 调用注册接口注册用户 */
			$uid = D('common/User' )->register ( $username, $password, $email,'' );
			if (0 < $uid) { // 注册成功
				return $this->success ( '用户添加成功！', U ( 'index' ) );
			} else { // 注册失败，显示错误信息
				return $this->error ( '用户添加失败！' );
			}
		} else {
			$this->meta_title = '新增用户';
			return $this->fetch();
		}
	}

	/**
	 * 获取用户注册错误信息
	 *
	 * @param integer $code
	 *        	错误编码
	 * @return string 错误信息
	 */
	private function showRegError($code = 0) {
		switch ($code) {
			case - 1 :
				$error = '用户名长度必须在16个字符以内！';
				break;
			case - 2 :
				$error = '用户名被禁止注册！';
				break;
			case - 3 :
				$error = '用户名被占用！';
				break;
			case - 4 :
				$error = '密码长度必须在6-30个字符之间！';
				break;
			case - 5 :
				$error = '邮箱格式不正确！';
				break;
			case - 6 :
				$error = '邮箱长度必须在1-32个字符之间！';
				break;
			case - 7 :
				$error = '邮箱被禁止注册！';
				break;
			case - 8 :
				$error = '邮箱被占用！';
				break;
			case - 9 :
				$error = '手机格式不正确！';
				break;
			case - 10 :
				$error = '手机被禁止注册！';
				break;
			case - 11 :
				$error = '手机号被占用！';
				break;
			default :
				$error = '未知错误';
		}
		return $error;
	}

	/**
	 * 显示用户公众号信息
	 */
	function showPublic() {
		$uid = I ( 'uid' );
		if (! empty ( $uid )) {
			$map ['uid'] = $uid;
			$ucanter = M( 'user' )->where ( wp_where( $map ) )->find ();
			isset ( $ucanter ['nickname'] ) && $ucanter ['nickname'] = $ucanter ['nickname'];
			$public = M( 'publics' )->where ( wp_where( $map ) )->find ();
			$this->assign ( 'ucanter', $ucanter );
			$this->assign ( 'public', $public );
		}
		return $this->fetch( 'show_member_public' );
	}
	// 修改审核
	function changeAudit($is_audit, $uid) {
		$data ['is_audit'] = $is_audit == 1 ? '0' : '1';
		$result = D('common/User' )->updateInfo ( $uid, $data );
		if ($result) {
			return $this->success ( '修改成功' );
		} else {
			return $this->error ( '修改失败' );
		}
	}
	function menu() {
		$this->disply ();
	}
}
