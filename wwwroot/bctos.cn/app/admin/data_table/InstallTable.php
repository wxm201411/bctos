<?php
/**
 * Install数据模型
 */
class InstallTable {
    // 数据表模型配置
    public $config = [
      'name' => 'install',
      'title' => '一键部署',
      'search_key' => '',
      'add_button' => 1,
      'del_button' => 0,
      'search_button' => 0,
      'check_all' => 0,
      'list_row' => 20,
      'addon' => 'admin',
      'inherit' => ''
  ];

    // 列表定义
    public $list_grid = [
      'title' => [
          'title' => '软件名称',
          'width' => '20%',
          'can_edit' => 0
      ],
      'dev' => [
          'title' => '开发商',
          'width' => '100px',
          'can_edit' => 0
      ],
      'admin_user' => [
          'title' => '登录账号',
          'width' => '100px',
          'can_edit' => 0
      ],
      'admin_passwd' => [
          'title' => '登录密码',
          'width' => '100px',
          'can_edit' => 0
      ],
      'intro' => [
          'title' => '介绍',
          'width' => '60%',
          'can_edit' => 0
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'width' => '100px',
          'can_edit' => 0,
          'href' => [
              '0' => [
                  'title' => '编辑',
                  'url' => '[EDIT]',
                  'show_set' => [
                      'is_audit' => [
                          '0' => 0
                      ]
                  ]
              ],
              '1' => [
                  'title' => '一键安装',
                  'url' => 'install?id=[id]&domain=555123',
                  'show_set' => [ ],
                  'class' => 'config-set'
              ],
              '2' => [
                  'title' => '删除',
                  'url' => '[DELETE]',
                  'show_set' => [
                      'is_audit' => [
                          '0' => 0
                      ]
                  ]
              ]
          ]
      ]
  ];

    // 字段定义
    public $fields = [
      'title' => [
          'title' => '软件名称',
          'type' => 'string',
          'field' => 'varchar(100) NOT NULL',
          'is_show' => 1,
          'is_must' => 1
      ],
      'dev' => [
          'title' => '开发商',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'intro' => [
          'title' => '介绍',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'status' => [
          'title' => '运行状态',
          'type' => 'radio',
          'field' => 'char(10) NULL',
          'extra' => '0:待安装
1:已安装
2:运行中',
          'value' => 0,
          'is_show' => 1
      ],
      'is_audit' => [
          'title' => '审核状态',
          'type' => 'radio',
          'field' => 'char(10) NULL',
          'extra' => '0:待审核
1:审核通过
2:审核未通过',
          'value' => 0,
          'is_show' => 1
      ],
      'is_del' => [
          'title' => '是否已删除',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:否
1:是',
          'value' => 0,
          'is_show' => 1
      ],
      'download_url' => [
          'title' => '下载地址',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'database' => [
          'title' => '数据库版本',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'db_set' => [
          'title' => 'db_set',
          'type' => 'string',
          'field' => 'varchar(30) NULL',
          'is_show' => 1
      ],
      'db_file' => [
          'title' => 'db_file',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'redis' => [
          'title' => 'redis',
          'type' => 'string',
          'field' => 'varchar(30) NULL',
          'is_show' => 1
      ],
      'redis_file' => [
          'title' => 'redis_file',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'memcached' => [
          'title' => 'memcached',
          'type' => 'string',
          'field' => 'varchar(30) NULL',
          'is_show' => 1
      ],
      'memcached_file' => [
          'title' => 'memcached_file',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'php_version' => [
          'title' => 'php_version',
          'type' => 'string',
          'field' => 'varchar(30) NULL',
          'is_show' => 1
      ],
      'php_func' => [
          'title' => 'php_func',
          'type' => 'checkbox',
          'field' => 'varchar(200) NULL',
          'is_show' => 1
      ],
      'php_ext' => [
          'title' => 'php_ext',
          'type' => 'checkbox',
          'field' => 'varchar(200) NULL',
          'is_show' => 1
      ],
      'rewrite_mod' => [
          'title' => 'rewrite_mod',
          'type' => 'string',
          'field' => 'varchar(30) NULL',
          'is_show' => 1
      ],
      'rewrite' => [
          'title' => 'rewrite',
          'type' => 'textarea',
          'field' => 'text NULL',
          'is_show' => 1
      ],
      'public_path' => [
          'title' => 'public_path',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'index_file' => [
          'title' => 'index_file',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'admin_user' => [
          'title' => 'admin_user',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'admin_passwd' => [
          'title' => 'admin_passwd',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'rm_file' => [
          'title' => 'rm_file',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'download_type' => [
          'title' => 'download_type',
          'type' => 'radio',
          'field' => 'char(10) NULL',
          'extra' => 'git:从git下载
composer:composer安装
wget:下载zip代码包',
          'value' => 'git',
          'is_show' => 1
      ]
  ];   
}