<?php
/**
 * CommonCategoryGroup数据模型
 */
class CommonCategoryGroupTable {
    // 数据表模型配置
    public $config = [
      'name' => 'common_category_group',
      'title' => '通用分类分组',
      'search_key' => 'title',
      'add_button' => 1,
      'del_button' => 1,
      'search_button' => 1,
      'check_all' => 1,
      'list_row' => 20,
      'addon' => 'core'
  ];

    // 列表定义
    public $list_grid = [
      'name' => [
          'title' => '分组标识'
      ],
      'title' => [
          'title' => '分组标题'
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'href' => [
              '0' => [
                  'title' => '数据管理',
                  'url' => 'cascade?target=_blank&module=[name]'
              ],
              '1' => [
                  'title' => '编辑',
                  'url' => '[EDIT]'
              ],
              '2' => [
                  'title' => '删除',
                  'url' => '[DELETE]'
              ]
          ]
      ]
  ];

    // 字段定义
    public $fields = [ ];   
}