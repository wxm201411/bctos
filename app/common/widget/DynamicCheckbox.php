<?php

namespace app\common\widget;

use app\common\controller\base;

/**
 * 动态多选菜单插件
 *
 * @author 凡星
 */
class DynamicCheckbox extends base {
	public $info = array (
			'name' => 'DynamicCheckbox',
			'title' => '动态多选菜单',
			'description' => '支持动态从数据库里取值显示',
			'status' => 1,
			'author' => '凡星',
			'version' => '0.1',
			'has_adminlist' => 0,
			'type' => 0
	);
	public function install() {
		return true;
	}
	public function uninstall() {
		return true;
	}

	/**
	 * 编辑器挂载的后台文档模型文章内容钩子
	 *
	 * table=addons&type=1&value_field=name&title_field=title&order=id desc
	 */
	public function dynamic_checkbox($data) {
        list($res, $first_option) = dynamic_data($data);
		// dump ( $json );
		$this->assign ( 'list', $res );

		$data ['default_value'] = $data ['value'] = is_array ( $data ['value'] ) ? $data ['value'] : explode ( ',', $data ['value'] );
		$this->assign ( $data );

		return $this->fetch( 'common@widget/dynamic_checkbox' );
	}
}