<?php
/**
 * Xterm数据模型
 */
class XtermTable {
    // 数据表模型配置
    public $config = [
      'name' => 'xterm',
      'title' => '终端历史操作',
      'search_key' => '',
      'add_button' => 0,
      'del_button' => 0,
      'search_button' => 0,
      'check_all' => 0,
      'list_row' => 200,
      'addon' => 'admin'
  ];

    // 列表定义
    public $list_grid = [
      'command' => [
          'title' => '历史常用命令',
          'width' => 80,
          'can_edit' => 0
      ],
      'status' => [
          'title' => '点击后立即执行',
          'can_edit' => 0
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'width' => 15,
          'href' => [
              '0' => [
                  'title' => '删除',
                  'url' => '[DELETE]',
                  'show_set' => [ ]
              ]
          ],
          'can_edit' => 0
      ]
  ];

    // 字段定义
    public $fields = [
      'command' => [
          'title' => '命令',
          'type' => 'textarea',
          'field' => 'text NULL',
          'is_show' => 1
      ],
      'update_at' => [
          'title' => '更新时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'auto_rule' => 'time',
          'auto_time' => 2,
          'value' => 0,
          'auto_type' => 'function'
      ],
      'count' => [
          'title' => '使用次数',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'value' => 0
      ],
      'status' => [
          'title' => '点击后立即执行',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:否
1:是',
          'value' => 1,
          'is_show' => 1
      ]
  ];   
}