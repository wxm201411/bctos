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
      'path' => [
          'title' => '网站目录'
      ],
      'remark' => [
          'title' => '备注'
      ],
      'ssl_pem' => [
          'title' => '证书'
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'href' => [
              '0' => [
                  'title' => '设置',
                  'url' => '[EDIT]'
              ],
              '1' => [
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
          'extra' => '0:禁用
1:启用',
          'value' => 0,
          'extra_array' => [
              '0' => '禁用',
              '1' => '启用'
          ],
          'extra_array_id' => [
              '0' => 0,
              '1' => 1,
              '2' => 0,
              '3' => 1,
              '4' => 0,
              '5' => 1,
              '6' => 0,
              '7' => 1
          ],
          'extra_array_title' => [
              '0' => '禁用',
              '1' => '启用',
              '2' => '禁用',
              '3' => '启用',
              '4' => '禁用',
              '5' => '启用',
              '6' => '禁用',
              '7' => '启用'
          ]
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
          'extra' => 'php-72:PHP7.2',
          'value' => 'php-72',
          'is_show' => 1,
          'extra_array' => [
              'php-72' => 'PHP7.2'
          ],
          'extra_array_id' => [
              '0' => 'php-72',
              '1' => 'php-72',
              '2' => 'php-72',
              '3' => 'php-72'
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
          'field' => 'varchar(255) NULL'
      ],
      'ssl_key' => [
          'title' => '密钥(KEY)',
          'type' => 'file',
          'field' => 'int(10) UNSIGNED NULL',
          'value' => 0,
          'validate_file_size' => 10485760
      ],
      'ssl_pem' => [
          'title' => '证书(PEM格式)',
          'type' => 'file',
          'field' => 'int(10) UNSIGNED NULL',
          'value' => 0,
          'validate_file_size' => 10485760
      ],
      'backup_at' => [
          'title' => '备份',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'value' => 0
      ]
  ];   
}