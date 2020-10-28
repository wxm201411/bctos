<?php
/**
 * Hisroty数据模型
 */
class HistoryTable {
    // 数据表模型配置
    public $config = [
      'name' => 'history',
      'title' => '历史操作',
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
      'chaincode' => [
          'title' => '链码名称',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'func' => [
          'title' => '查询类型',
          'type' => 'string',
          'field' => 'varchar(30) NULL',
          'is_show' => 1
      ],
      'param' => [
          'title' => '输入参数',
          'type' => 'textarea',
          'field' => 'text NULL',
          'is_show' => 1
      ],
      'uid' => [
          'title' => '用户',
          'type' => 'user',
          'field' => 'int(10) NULL',
          'auto_rule' => 'get_mid',
          'auto_time' => 1,
          'value' => 0,
          'auto_type' => 'function'
      ],
      'update_at' => [
          'title' => '更新时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'auto_rule' => 'time',
          'auto_time' => 2,
          'value' => 0,
          'auto_type' => 'function'
      ]
  ];   
}