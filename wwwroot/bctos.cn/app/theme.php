<?php

// bctos主题函数库（基库）

function db_query($sql)
{
    return M()::query($sql);
}

function db_ex($sql)
{
    return M()->db_ex($sql);
}

function get_exclude_ids($exclude, $exclude_field = 'id')
{
    $ids = [];
    foreach ($exclude as $e) {
        if (isset($e[$exclude_field])) {
            $ids[$e[$exclude_field]] = $e[$exclude_field];
        } else {
            $child_ids = get_exclude_ids($e, $exclude_field);
            $ids = array_merge($ids, $child_ids);
        }
    }
    return $ids;
}

function exclude_map($map, $exclude, $field = 'id', $exclude_field = 'id')
{
    if (!empty($exclude)) {
        $ids = get_exclude_ids($exclude, $exclude_field);
        if (!empty($ids)) {
            if (is_string($map)) {
                $map .= ' and ' . $field . ' not in (' . implode(',', $ids) . ') ';
            } else {
                $map[$field] = ['not in', $ids];
            }
        }
    }

    return $map;
}

function get_lists($table, $map = [], $limit = 0, $order = 'sort asc,id asc', $exclude = [])
{
    if (!is_object($table)) {
        $table = M($table);
    }
    $map = wp_where(exclude_map($map, $exclude));
    $lists = $table->where($map)->order($order)->field(true);
    if ($limit > 0) {
        $lists = $lists->limit($limit);
    }

    $lists = find_data($lists->select());

    return $lists;
}

function get_pages($table, $map = [], $row = 20, $order = 'sort asc,id asc', $exclude = [])
{
    if (!is_object($table)) {
        $table = M($table);
    }

    $map = wp_where(exclude_map($map, $exclude));
    $data = $table->where($map)->order($order)->field(true)->paginate($row);

    $data = dealPage($data);
    if (!empty($list_data)) {
        $list_data = array_merge($list_data, $data);
    } else {
        $list_data = $data;
    }

    return $list_data;
}

//素材库
function get_article($id, $field = true)
{
    $info = M('cms_posts')->where(['id' => $id])->field($field)->find();
    return $info;
}

//客服
function online_service()
{
    $lists = find_data(M('online_service')->select());
    return $lists;
}