<?php
/**
 * ClockInKeyword数据模型
 */
class ClockInKeywordTable {
    // 数据表模型配置
    public $config = [
      'name' => 'clock_in_keyword',
      'title' => '打卡口令',
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
      'event_id' => [
          'title' => '活动ID',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'keyword' => [
          'title' => '口令',
          'type' => 'string',
          'field' => 'varchar(30) NULL',
          'is_show' => 1
      ],
      'day' => [
          'title' => '日期',
          'type' => 'string',
          'field' => 'varchar(30) NULL',
          'is_show' => 1
      ],
      'start_time' => [
          'title' => '开始时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'end_time' => [
          'title' => '结束时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ]
  ];   
}