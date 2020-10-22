<?php
/**
 * PublicLink数据模型
 */
class PublicLinkTable {
    // 数据表模型配置
    public $config = [
      'name' => 'public_link',
      'title' => '公众号与管理员的关联关系',
      'search_key' => '',
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