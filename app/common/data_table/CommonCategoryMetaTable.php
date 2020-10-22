<?php
/**
 * CommonCategoryMeta数据模型
 */
class CommonCategoryMetaTable {
    // 数据表模型配置
    public $config = [
      'name' => 'common_category_meta',
      'title' => '分类扩展表',
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
      'category_id' => [
          'title' => '分类ID',
          'type' => 'num',
          'field' => 'int(10) NOT NULL',
          'is_show' => 1,
          'is_must' => 1,
          'value' => 0
      ],
      'meta_key' => [
          'title' => 'key',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'meta_value' => [
          'title' => 'value',
          'type' => 'textarea',
          'field' => 'longtext NULL',
          'is_show' => 1
      ]
  ];   
}