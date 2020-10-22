<?php
/**
 * Sucai数据模型
 */
class SucaiTable {
    // 数据表模型配置
    public $config = [
      'name' => 'sucai',
      'title' => '素材管理',
      'search_key' => 'name',
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