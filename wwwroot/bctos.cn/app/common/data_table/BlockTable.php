<?php
/**
 * Block数据模型
 */
class BlockTable {
    // 数据表模型配置
    public $config = [
      'name' => 'block',
      'title' => '块元素',
      'search_key' => '',
      'add_button' => 1,
      'del_button' => 1,
      'search_button' => 1,
      'check_all' => 1,
      'list_row' => 20,
      'addon' => 'core'
  ];

    // 列表定义
    public $list_grid = [ ];

    // 字段定义
    public $fields = [
      'page' => [
          'title' => '页面',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'block_id' => [
          'title' => '块元素',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'content' => [
          'title' => '配置参数',
          'type' => 'textarea',
          'field' => 'text NULL',
          'is_show' => 1
      ]
  ];   
}