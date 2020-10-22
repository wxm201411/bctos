<?php
/**
 * Store数据模型
 */
class StoreTable {
    // 数据表模型配置
    public $config = [
      'name' => 'store',
      'title' => '应用商店',
      'search_key' => 'title',
      'check_all' => 1,
      'search_button' => 1,
      'del_button' => 1,
      'add_button' => 1,
      'list_row' => 20,
      'addon' => 'core'
  ];

    // 列表定义
    public $list_grid = [ ];

    // 字段定义
    public $fields = [ ];   
}