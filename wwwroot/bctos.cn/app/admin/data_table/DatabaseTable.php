<?php
/**
 * Database数据模型
 */
class DatabaseTable {
    // 数据表模型配置
    public $config = [
      'name' => 'database',
      'title' => '数据库',
      'search_key' => 'title:数据库搜索',
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
      'db_name' => [
          'title' => '数据库名',
          'can_edit' => 0,
          'is_sort' => 1
      ],
      'db_user' => [
          'title' => '用户名',
          'can_edit' => 0,
          'is_sort' => 1
      ],
      'db_passwd' => [
          'title' => '密码',
          'can_edit' => 0,
          'is_sort' => 1
      ],
      'power' => [
          'title' => '权限',
          'can_edit' => 0,
          'is_sort' => 1
      ],
      'backup_at' => [
          'title' => '备份',
          'can_edit' => 0
      ],
      'remark' => [
          'title' => '备注',
          'can_edit' => 0
      ],
      'ip' => [
          'title' => '指定IP',
          'can_edit' => 0
      ],
      'database' => [
          'title' => '版本',
          'can_edit' => 0
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'can_edit' => 0,
          'href' => [
              '0' => [
                  'title' => '删除',
                  'url' => '[DELETE]',
                  'show_set' => [ ]
              ]
          ]
      ]
  ];

    // 字段定义
    public $fields = [
      'db_name' => [
          'title' => '数据库名',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
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
              '3' => 'big5',
              '4' => 'utf8mb4',
              '5' => 'utf8',
              '6' => 'gbk',
              '7' => 'big5'
          ],
          'extra_array_title' => [
              '0' => 'utf8mb4',
              '1' => 'utf8',
              '2' => 'gbk',
              '3' => 'big5',
              '4' => 'utf8mb4',
              '5' => 'utf8',
              '6' => 'gbk',
              '7' => 'big5'
          ]
      ],
      'db_user' => [
          'title' => '用户名',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'db_passwd' => [
          'title' => '密码',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'power' => [
          'title' => '权限',
          'type' => 'select',
          'field' => 'char(50) NULL',
          'extra' => '%:所有人(%)
ip:IP',
          'value' => 'utf8mb4',
          'is_show' => 1,
          'extra_array' => [
              '%' => '所有人(%)',
              'ip' => '指定IP'
          ],
          'extra_array_id' => [
              '0' => '%',
              '1' => 'ip'
          ],
          'extra_array_title' => [
              '0' => '所有人(不安全)',
              '1' => '指定IP'
          ]
      ],
      'ip' => [
          'title' => '指定IP',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1,
          'show_set' => [
              'power' => [
                  '0' => 'ip'
              ]
          ]
      ],
      'backup_at' => [
          'title' => '备份',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'value' => 0
      ],
      'remark' => [
          'title' => '备注',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'database' => [
          'title' => '数据库版本',
          'type' => 'string',
          'field' => 'varchar(50) NULL'
      ]
  ];   
}