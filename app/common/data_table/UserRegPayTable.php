<?php
/**
 * UserRegPay数据模型
 */
class UserRegPayTable {
    // 数据表模型配置
    public $config = [
      'name' => 'user_reg_pay',
      'title' => '用户注册支付记录',
      'search_key' => '',
      'add_button' => 1,
      'del_button' => 1,
      'search_button' => 1,
      'check_all' => 1,
      'list_row' => 20,
      'addon' => 'core'
  ];

    // 列表定义
    public $list_grid = [ ];

    // 字段定义
    public $fields = [
      'uid' => [
          'title' => '用户uid',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'pay_money' => [
          'title' => '支付金额',
          'type' => 'num',
          'field' => 'decimal(10,2) NULL',
          'is_show' => 1
      ],
      'end_time' => [
          'title' => '到期时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'out_trade_no' => [
          'title' => '订单号',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'is_pay' => [
          'title' => '是否支付',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'cTime' => [
          'title' => '创建时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ]
  ];   
}