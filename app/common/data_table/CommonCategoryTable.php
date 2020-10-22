<?php
/**
 * CommonCategory数据模型
 */
class CommonCategoryTable {
    // 数据表模型配置
    public $config = [
      'name' => 'common_category',
      'title' => '通用分类',
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
      'title' => [
          'title' => '标题'
      ],
      'sort' => [
          'title' => '排序号'
      ],
      'is_show' => [
          'title' => '显示'
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'href' => [
              '0' => [
                  'title' => '删除',
                  'url' => '[DELETE]'
              ]
          ]
      ]
  ];

    // 字段定义
    public $fields = [
      'pid' => [
          'title' => '上一级分类',
          'field' => 'int(10) unsigned NULL',
          'type' => 'select',
          'remark' => '如果你要增加一级分类，这里选择“无”即可',
          'extra' => '0:无',
          'value' => 0
      ],
      'title' => [
          'title' => '分类标题',
          'field' => 'varchar(255) NOT NULL',
          'type' => 'string'
      ],
      'sort' => [
          'title' => '排序号',
          'field' => 'int(10) unsigned NULL',
          'type' => 'num',
          'remark' => '数值越小越靠前',
          'value' => 0
      ],
      'is_show' => [
          'title' => '是否显示',
          'field' => 'tinyint(2) NULL',
          'type' => 'bool',
          'is_show' => 1,
          'extra' => '0:不显示
1:显示',
          'value' => 1
      ],
      'slug' => [
          'title' => '分类标识',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'remark' => '只能使用英文'
      ],
      'app' => [
          'title' => '所属应用',
          'type' => 'string',
          'field' => 'varchar(100) NULL'
      ],
      'wpid' => [
          'title' => 'wpid',
          'field' => 'int(10) NOT NULL',
          'type' => 'string',
          'auto_rule' => 'get_wpid',
          'auto_time' => 3,
          'auto_type' => 'function',
          'value' => 0
      ]
  ];   
}