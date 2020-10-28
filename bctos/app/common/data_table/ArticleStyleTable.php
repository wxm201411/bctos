<?php
/**
 * ArticleStyle数据模型
 */
class ArticleStyleTable {
    // 数据表模型配置
    public $config = [
      'name' => 'article_style',
      'title' => '图文样式',
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