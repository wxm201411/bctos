<?php
/**
 * ClockInCash数据模型
 */
class ClockInCashTable {
    // 数据表模型配置
    public $config = [
      'name' => 'clock_in_cash',
      'title' => '提现',
      'search_key' => '',
      'add_button' => 0,
      'del_button' => 0,
      'search_button' => 1,
      'check_all' => 0,
      'list_row' => 20,
      'addon' => 'clock_in'
  ];

    // 列表定义
    public $list_grid = [
      'uid' => [
          'title' => '用户'
      ],
      'qrcode' => [
          'title' => '收款码'
      ],
      'money' => [
          'title' => '提现金额'
      ],
      'truename' => [
          'title' => '真实姓名'
      ],
      'mobile' => [
          'title' => '手机号'
      ],
      'status' => [
          'title' => '进度'
      ],
      'create_at' => [
          'title' => '创建时间'
      ]
  ];

    // 字段定义
    public $fields = [
      'wpid' => [
          'title' => '平台账号ID',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'remark' => '区分平台账号之间的数据',
          'auto_rule' => 'get_wpid',
          'auto_time' => 1,
          'auto_type' => 'function',
          'value' => 0
      ],
      'create_at' => [
          'title' => '创建时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'auto_rule' => 'time',
          'auto_time' => 1,
          'auto_type' => 'function',
          'value' => 0
      ],
      'update_at' => [
          'title' => '更新时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'auto_rule' => 'time',
          'auto_time' => 2,
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
      'status' => [
          'title' => '进度',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:待处理
1:已处理',
          'is_show' => 1,
          'value' => 0
      ],
      'qrcode' => [
          'title' => '收款码',
          'type' => 'picture',
          'field' => 'varchar(100) NOT NULL',
          'is_show' => 1,
          'is_must' => 1,
          'value' => 0
      ],
      'remark' => [
          'title' => '备注',
          'type' => 'textarea',
          'field' => 'text NULL',
          'is_show' => 1
      ],
      'truename' => [
          'title' => '真实姓名',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'pay_type' => [
          'title' => '提现方式',
          'type' => 'radio',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:微信
1:支付宝',
          'is_show' => 1,
          'value' => 0
      ],
      'mobile' => [
          'title' => '手机号',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'money' => [
          'title' => '提现金额',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ]
  ];   
}