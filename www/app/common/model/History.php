<?php

namespace app\common\model;

use app\common\model\Base;

/**
 * Shop模型
 */
class History extends Base
{
    function saveData($uid, $data)
    {
        $data['uid'] = $uid;
        $id = $this->where(wp_where($data))->value('id');
        if ($id > 0) {
            $this->where('id', $id)->update(['update_at' => NOW_TIME]);
        } else {
            $data['update_at'] = NOW_TIME;
            return $this->insert($data);
        }
    }

    function getList($uid)
    {
        return find_data($this->where('uid', $uid)->order('update_at desc')->select());
    }

    function del($id)
    {
        return $this->where('id', $id)->delete();
    }
}
