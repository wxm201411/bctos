<?php
/**
 * ClockInRecode数据模型
 */
class ClockInRecodeTable {
    // 数据表模型配置
    public $config = [
      'name' => 'clock_in_recode',
      'title' => '打卡记录',
      'search_key' => '',
      'add_button' => 1,
      'del_button' => 1,
      'search_button' => 1,
      'check_all' => 1,
      'list_row' => 20,
      'addon' => 'clock_in'
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
          'auto_type' => 'function',
          'value' => 0
      ],
      'uid' => [
          'title' => '用户',
          'type' => 'user',
          'field' => 'int(10) NULL',
          'auto_rule' => 'get_mid',
          'auto_time' => 1,
          'auto_type' => 'function',
          'value' => 0
      ],
      'event_id' => [
          'title' => '活动ID',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'keyword' => [
          'title' => '打卡口令',
          'type' => 'string',
          'field' => 'varchar(30) NULL',
          'is_show' => 1
      ]
  ];   
}