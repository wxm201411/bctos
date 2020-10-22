<?php
/**
 * Notice数据模型
 */
class NoticeTable {
    // 数据表模型配置
    public $config = [
      'name' => 'notice',
      'title' => '微信支付回调',
      'search_key' => '',
      'add_button' => 1,
      'del_button' => 1,
      'search_button' => 1,
      'check_all' => 1,
      'list_row' => 20,
      'addon' => 'notice'
  ];

    // 列表定义
    public $list_grid = [ ];

    // 字段定义
    public $fields = [ ];   
}