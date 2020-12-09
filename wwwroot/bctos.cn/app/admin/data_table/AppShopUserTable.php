<?php
/**
 * AppShopUser数据模型
 */
class AppShopUserTable {
    // 数据表模型配置
    public $config = [
      'name' => 'app_shop_user',
      'title' => '用户下载记录',
      'search_key' => '',
      'add_button' => 1,
      'del_button' => 1,
      'search_button' => 1,
      'check_all' => 1,
      'list_row' => 20,
      'addon' => 'admin'
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
      'status' => [
          'title' => '支付状态',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:未支付
1:已支付',
          'is_show' => 1,
          'value' => 0
      ],
      'app_id' => [
          'title' => '扩展ID',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'value' => 0
      ],
      'price' => [
          'title' => '支付积分',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'value' => 0
      ]
  ];   
}