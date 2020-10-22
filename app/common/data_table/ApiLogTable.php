<?php
/**
 * ApiLog数据模型
 */
class ApiLogTable {
    // 数据表模型配置
    public $config = [
      'name' => 'api_log',
      'title' => 'API日志',
      'search_key' => '',
      'add_button' => 1,
      'del_button' => 1,
      'search_button' => 1,
      'check_all' => 1,
      'list_row' => 20,
      'addon' => 'core',
      'inherit' => ''
  ];

    // 列表定义
    public $list_grid = [ ];

    // 字段定义
    public $fields = [
      'create_at' => [
          'title' => '创建时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'auto_rule' => 'time',
          'auto_time' => 1,
          'value' => 0,
          'auto_type' => 'function'
      ],
      'url' => [
          'title' => 'url',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'param' => [
          'title' => 'param',
          'type' => 'textarea',
          'field' => 'text NULL',
          'is_show' => 1
      ],
      'server_ip' => [
          'title' => 'server_ip',
          'type' => 'string',
          'field' => 'varchar(30) NULL',
          'is_show' => 1
      ]
  ];   
}