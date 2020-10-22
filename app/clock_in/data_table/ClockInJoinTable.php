<?php
/**
 * ClockInJoin数据模型
 */
class ClockInJoinTable {
    // 数据表模型配置
    public $config = [
      'name' => 'clock_in_join',
      'title' => '参与活动',
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
      'uid' => [
          'title' => '粉丝',
          'type' => 'user',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'create_at' => [
          'title' => '参与时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'pay_money' => [
          'title' => '支付金额',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'is_pay' => [
          'title' => '是否支付',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:未支付
1:已支付',
          'is_show' => 1,
          'value' => 0
      ],
      'status' => [
          'title' => '打卡进度',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:进行中
1:挑战成功
2:挑战失败',
          'is_show' => 1,
          'value' => 0
      ],
      'pay_type' => [
          'title' => '支付方式',
          'type' => 'radio',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:余额支付
1:微信支付',
          'is_show' => 1,
          'value' => 0
      ],
      'finish_at' => [
          'title' => '打卡成功的时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'out_trade_no' => [
          'title' => '支付单号',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ]
  ];   
}