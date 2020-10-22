<?php

namespace app\common\model;

use app\common\model\Base;


class UserTag extends Base {
	var $table = DB_PREFIX. 'auth_group';
	function addTags($uid, $tag_ids) {
		$ids = array_filter ( explode ( ',', $tag_ids ) );

		$data ['uid'] = $uid;
		foreach ( $ids as $id ) {
			$data ['group_id'] = $id;

			$has = M( 'auth_group_access' )->where ( wp_where( $data ) )->value( 'group_id' );
			if (! $has) {
				M( 'auth_group_access' )->insert( $data );
			}
		}
	}
	function getById($id)
	{
        $tag_info = $this->where('group_id', $id)->find();
        return $tag_info;
	}
}
