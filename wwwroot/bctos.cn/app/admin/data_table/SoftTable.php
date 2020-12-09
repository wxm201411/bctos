<?php
/**
 * Soft数据模型
 */
class SoftTable {
    // 数据表模型配置
    public $config = [
      'name' => 'soft',
      'title' => '应用商城',
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
          'can_edit' => 0
      ],
      'dev' => [
          'title' => '开发商',
          'can_edit' => 0
      ],
      'intro' => [
          'title' => '介绍',
          'can_edit' => 0
      ],
      'image' => [
          'title' => '对应镜像',
          'can_edit' => 0
      ],
      'status' => [
          'title' => '状态',
          'can_edit' => 0
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'can_edit' => 0,
          'href' => [
              '0' => [
                  'title' => '安装',
                  'url' => 'install?id=[id]',
                  'show_set' => [
                      'status' => [
                          '0' => 0
                      ]
                  ],
                  'class' => 'status-set'
              ],
              '1' => [
                  'title' => '启动',
                  'url' => 'server?type=start&id=[id]',
                  'show_set' => [
                      'status' => [
                          '1' => 1,
                          '3' => 3
                      ]
                  ],
                  'class' => 'status-set'
              ],
              '2' => [
                  'title' => '停止',
                  'url' => 'server?type=stop&id=[id]',
                  'show_set' => [
                      'status' => [
                          '2' => 2
                      ]
                  ],
                  'class' => 'status-set'
              ],
              '3' => [
                  'title' => '设置',
                  'url' => 'config_set?only_body=1&id=[id]',
                  'show_set' => [
                      'can_edit' => [
                          '1' => 1
                      ],
                      'status' => [
                          '1' => 1,
                          '2' => 2,
                          '3' => 3
                      ]
                  ],
                  'class' => 'config-set'
              ],
              '4' => [
                  'title' => '卸载',
                  'url' => 'uninstall?id=[id]',
                  'show_set' => [
                      'status' => [
                          '1' => 1,
                          '3' => 3
                      ]
                  ],
                  'class' => 'status-set'
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
          'title' => '状态',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:未安装
1:已安装
2:运行中
3:已停止',
          'value' => 0,
          'is_show' => 1
      ],
      'image' => [
          'title' => '对应镜像',
          'type' => 'string',
          'field' => 'varchar(100) NOT NULL',
          'is_show' => 1,
          'is_must' => 1
      ],
      'docker' => [
          'title' => '容器名',
          'type' => 'string',
          'field' => 'varchar(100) NOT NULL',
          'is_show' => 1,
          'is_must' => 1
      ],
      'can_edit' => [
          'title' => '可配置',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:否
1:是',
          'value' => 0,
          'is_show' => 1,
          'extra_array' => [
              '0' => '否',
              '1' => '是'
          ],
          'extra_array_id' => [
              '0' => 0,
              '1' => 1,
              '2' => 0,
              '3' => 1
          ],
          'extra_array_title' => [
              '0' => '否',
              '1' => '是',
              '2' => '否',
              '3' => '是'
          ]
      ],
      'can_del' => [
          'title' => '可删除',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:否
1:是',
          'value' => 1,
          'is_show' => 1,
          'extra_array' => [
              '0' => '否',
              '1' => '是'
          ],
          'extra_array_id' => [
              '0' => 0,
              '1' => 1,
              '2' => 0,
              '3' => 1
          ],
          'extra_array_title' => [
              '0' => '否',
              '1' => '是',
              '2' => '否',
              '3' => '是'
          ]
      ],
      'config_html' => [
          'title' => '配置模板',
          'field' => 'varchar(50) NULL',
          'type' => 'string'
      ]
  ];   
}