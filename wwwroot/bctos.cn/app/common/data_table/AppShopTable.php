<?php
/**
 * AppShop数据模型
 */
class AppShopTable {
    // 数据表模型配置
    public $config = [
      'name' => 'app_shop',
      'title' => '扩展商城',
      'search_key' => '',
      'add_button' => 1,
      'del_button' => 1,
      'search_button' => 1,
      'check_all' => 1,
      'list_row' => 20,
      'addon' => 'core'
  ];

    // 列表定义
    public $list_grid = [
      'update_at' => [
          'title' => '更新时间',
          'can_edit' => 0
      ],
      'status' => [
          'title' => '状态',
          'can_edit' => 0
      ],
      'type' => [
          'title' => '扩展类型',
          'can_edit' => 0
      ],
      'title' => [
          'title' => '扩展名',
          'can_edit' => 0
      ],
      'attach' => [
          'title' => '安装包',
          'can_edit' => 0
      ],
      'is_top' => [
          'title' => '置顶',
          'can_edit' => 0
      ],
      'view_count' => [
          'title' => '浏览数',
          'can_edit' => 0
      ],
      'logo' => [
          'title' => '扩展封面',
          'can_edit' => 0
      ],
      'price' => [
          'title' => '价格',
          'can_edit' => 0
      ],
      'download_count' => [
          'title' => '下载数',
          'can_edit' => 0
      ]
  ];

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
      'update_at' => [
          'title' => '更新时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'auto_type' => 'function',
          'auto_rule' => 'time',
          'auto_time' => 3,
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
          'title' => '状态',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:待审核
1:审核通过
2:审核不通过',
          'value' => 0
      ],
      'type' => [
          'title' => '扩展类型',
          'type' => 'radio',
          'field' => 'tinyint(2) NOT NULL',
          'extra' => '0:应用
1:插件
2:皮肤',
          'is_show' => 1,
          'is_must' => 1,
          'value' => 0
      ],
      'title' => [
          'title' => '扩展名',
          'type' => 'string',
          'field' => 'varchar(255) NOT NULL',
          'is_show' => 1,
          'is_must' => 1
      ],
      'attach' => [
          'title' => '安装包',
          'type' => 'file',
          'field' => 'int(10) unsigned NOT NULL',
          'is_show' => 1,
          'validate_file_size' => 10485760,
          'is_must' => 1,
          'value' => 0
      ],
      'is_top' => [
          'title' => '置顶',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'value' => 0
      ],
      'view_count' => [
          'title' => '浏览数',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'value' => 0
      ],
      'logo' => [
          'title' => '扩展封面',
          'type' => 'picture',
          'field' => 'varchar(100) NOT NULL',
          'is_show' => 1,
          'is_must' => 1,
          'value' => 0
      ],
      'img' => [
          'title' => '截图',
          'type' => 'mult_picture',
          'field' => 'text NOT NULL',
          'is_show' => 1,
          'is_must' => 1
      ],
      'content' => [
          'title' => '内容',
          'type' => 'editor',
          'field' => 'text NOT NULL',
          'is_show' => 1,
          'is_must' => 1
      ],
      'price' => [
          'title' => '价格',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'remark' => '需要多少积分购买，0表示免费',
          'is_show' => 1,
          'value' => 0
      ],
      'download_count' => [
          'title' => '下载数',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'value' => 0
      ]
  ];   
}