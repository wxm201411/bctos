<?php
/**
 * Site数据模型
 */
class SiteTable {
    // 数据表模型配置
    public $config = [
      'name' => 'site',
      'title' => '网站',
      'search_key' => 'title:网站搜索',
      'add_button' => 0,
      'del_button' => 0,
      'search_button' => 1,
      'check_all' => 0,
      'list_row' => 20,
      'addon' => 'admin',
      'inherit' => ''
  ];

    // 列表定义
    public $list_grid = [
      'title' => [
          'title' => '网站名',
          'is_sort' => 1
      ],
      'status' => [
          'title' => '状态',
          'is_sort' => 1
      ],
      'backup_at' => [
          'title' => '最近备份时间'
      ],
      'path' => [
          'title' => '网站目录'
      ],
      'remark' => [
          'title' => '备注'
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'href' => [
              '0' => [
                  'title' => '删除',
                  'url' => '[DELETE]'
              ]
          ]
      ]
  ];

    // 字段定义
    public $fields = [
      'domain' => [
          'title' => '域名',
          'type' => 'textarea',
          'field' => 'text NOT NULL',
          'is_show' => 1,
          'is_must' => 1
      ],
      'status' => [
          'title' => '状态',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:停用
1:运行',
          'value' => 1
      ],
      'remark' => [
          'title' => '备注',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'create_at' => [
          'title' => '创建时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'auto_rule' => 'time',
          'auto_time' => 1,
          'value' => 0,
          'auto_type' => 'function'
      ],
      'path' => [
          'title' => '网站目录',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'database' => [
          'title' => '数据库',
          'type' => 'select',
          'field' => 'char(50) NULL',
          'extra' => 'not:不创建
mysql:MySql',
          'value' => 'not',
          'is_show' => 1,
          'extra_array' => [
              'not' => '不创建',
              'mysql' => 'MySql'
          ],
          'extra_array_id' => [
              '0' => 'not',
              '1' => 'mysql',
              '2' => 'not',
              '3' => 'mysql'
          ],
          'extra_array_title' => [
              '0' => '不创建',
              '1' => 'MySql',
              '2' => '不创建',
              '3' => 'MySql'
          ]
      ],
      'db_user' => [
          'title' => '用户名',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1,
          'show_set' => [
              'database' => [
                  '0' => 'mysql'
              ]
          ]
      ],
      'db_passwd' => [
          'title' => '密码',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1,
          'show_set' => [
              'database' => [
                  '0' => 'mysql'
              ]
          ]
      ],
      'db_set' => [
          'title' => '字符集',
          'type' => 'select',
          'field' => 'char(50) NULL',
          'extra' => 'utf8mb4:utf8mb4
utf8:utf8
gbk:gbk
big5:big5',
          'value' => 'utf8mb4',
          'is_show' => 1,
          'show_set' => [
              'database' => [
                  '0' => 'mysql'
              ]
          ],
          'extra_array' => [
              'utf8mb4' => 'utf8mb4',
              'utf8' => 'utf8',
              'gbk' => 'gbk',
              'big5' => 'big5'
          ],
          'extra_array_id' => [
              '0' => 'utf8mb4',
              '1' => 'utf8',
              '2' => 'gbk',
              '3' => 'big5'
          ],
          'extra_array_title' => [
              '0' => 'utf8mb4',
              '1' => 'utf8',
              '2' => 'gbk',
              '3' => 'big5'
          ]
      ],
      'php_version' => [
          'title' => 'PHP版本',
          'type' => 'select',
          'field' => 'char(50) NULL',
          'extra' => 'php72:PHP7.2',
          'value' => 'php72',
          'is_show' => 1,
          'extra_array' => [
              'php72' => 'PHP7.2'
          ],
          'extra_array_id' => [
              '0' => 'php72',
              '1' => 'php72',
              '2' => 'php72',
              '3' => 'php72'
          ],
          'extra_array_title' => [
              '0' => 'PHP7.2',
              '1' => 'PHP7.2',
              '2' => 'PHP7.2',
              '3' => 'PHP7.2'
          ]
      ],
      'title' => [
          'title' => '网站名',
          'type' => 'string',
          'field' => 'varchar(255) NULL'
      ],
      'public_path' => [
          'title' => '运行目录',
          'type' => 'string',
          'field' => 'varchar(30) NULL'
      ],
      'ssl' => [
          'title' => '证书',
          'type' => 'textarea',
          'field' => 'text NULL',
          'validate_file_size' => 10485760
      ],
      'backup_at' => [
          'title' => '备份',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'value' => 0
      ],
      'open_basedir' => [
          'title' => '防跨站攻击',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0：关闭
1：开启',
          'value' => 1
      ],
      'recode_log' => [
          'title' => '写访问日志',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0：否
1：是',
          'value' => 1
      ],
      'rewrite_mod' => [
          'title' => '伪静态模板',
          'type' => 'string',
          'field' => 'varchar(30) NULL'
      ],
      'ssl_open' => [
          'title' => 'SSL状态',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0：关闭
1：开启',
          'value' => 0,
          'is_show' => 1
      ],
      'ssl_force' => [
          'title' => '强制HTTPS',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0：不强制
1：强制',
          'value' => 0,
          'is_show' => 1
      ]
  ];   
}