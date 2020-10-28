<?php
/**
 * CommonCategoryLink数据模型
 */
class CommonCategoryLinkTable {
    // 数据表模型配置
    public $config = [
      'name' => 'common_category_link',
      'title' => '分类关联表',
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
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'app' => [
          'title' => '应用名',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'data_id' => [
          'title' => '应用数据ID',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ]
  ];   
}