<?php
/**
 * AppShopWeb数据模型
 */
class AppShopWebTable {
    // 数据表模型配置
    public $config = [
      'name' => 'app_shop_web',
      'title' => '网站信息',
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
      'title' => [
          'title' => '站名',
          'type' => 'string',
          'field' => 'varchar(255) NOT NULL',
          'is_show' => 1,
          'is_must' => 1
      ],
      'logo' => [
          'title' => 'Logo',
          'type' => 'picture',
          'field' => 'varchar(100) NOT NULL',
          'value' => 0,
          'is_show' => 1,
          'is_must' => 1
      ],
      'url' => [
          'title' => '网址',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'wealth' => [
          'title' => '余额',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'value' => 0
      ],
      'web_key' => [
          'title' => '授权许可证',
          'type' => 'string',
          'field' => 'varchar(255) NULL'
      ],
      'uid' => [
          'title' => '用户',
          'type' => 'user',
          'field' => 'int(10) NULL',
          'auto_rule' => 'get_mid',
          'auto_time' => 1,
          'auto_type' => 'function',
          'value' => 0
      ]
  ];   
}