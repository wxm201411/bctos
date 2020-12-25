<?php
/**
 * Menu数据模型
 */
class MenuTable {
    // 数据表模型配置
    public $config = [
      'name' => 'menu',
      'title' => '公众号管理员菜单',
      'search_key' => '',
      'add_button' => 1,
      'del_button' => 1,
      'search_button' => 1,
      'check_all' => 1,
      'list_row' => 65535,
      'addon' => 'core'
  ];

    // 列表定义
    public $list_grid = [
      'title' => [
          'title' => '菜单名',
          'can_edit' => 0
      ],
      'addon_name' => [
          'title' => '插件名',
          'can_edit' => 0
      ],
      'url' => [
          'title' => '外链',
          'can_edit' => 0
      ],
      'is_hide' => [
          'title' => '隐藏',
          'can_edit' => 1
      ],
      'sort' => [
          'title' => '排序号',
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
      'pid' => [
          'title' => '上级菜单',
          'type' => 'cascade',
          'field' => 'varchar(50) NULL',
          'extra' => 'type=db&table=menu&pid=0&first_option=一级菜单',
          'value' => 0,
          'is_show' => 1
      ],
      'title' => [
          'title' => '菜单名',
          'field' => 'varchar(50) NULL',
          'type' => 'string',
          'is_show' => 1
      ],
      'url_type' => [
          'title' => '链接类型',
          'field' => 'tinyint(2) NULL',
          'type' => 'bool',
          'is_show' => 1,
          'extra' => '0:插件
1:外链',
          'value' => 0
      ],
      'addon_name' => [
          'title' => '插件名',
          'field' => 'varchar(30) NULL',
          'type' => 'dynamic_select',
          'is_show' => 1,
          'extra' => 'table=apps&type=0&value_field=name&title_field=title&order=id asc',
          'show_set' => [
              'url_type' => [
                  '0' => 0
              ]
          ]
      ],
      'url' => [
          'title' => '外链',
          'field' => 'varchar(255) NULL',
          'type' => 'string',
          'is_show' => 1,
          'show_set' => [
              'url_type' => [
                  '0' => 1
              ]
          ]
      ],
      'target' => [
          'title' => '打开方式',
          'field' => 'varchar(50) NULL',
          'type' => 'select',
          'is_show' => 1,
          'extra' => '_self:当前窗口打开
_blank:在新窗口打开'
      ],
      'icon' => [
          'title' => '图标',
          'type' => 'icon',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'is_hide' => [
          'title' => '是否隐藏',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:否
1:是',
          'value' => 0,
          'is_show' => 1
      ],
      'sort' => [
          'title' => '排序号',
          'field' => 'int(10) NULL',
          'type' => 'num',
          'remark' => '值越小越靠前',
          'is_show' => 1,
          'value' => 0
      ],
      'place' => [
          'title' => '0：运营端，1：开发端',
          'field' => 'tinyint(1) NULL',
          'type' => 'string',
          'is_show' => 4,
          'value' => 0
      ],
      'rule' => [
          'title' => '权限',
          'type' => 'dynamic_checkbox',
          'field' => 'varchar(100) NULL',
          'extra' => 'table=auth_group&type=0',
          'value' => 2,
          'is_show' => 1
      ]
  ];   
}