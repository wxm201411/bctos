<?php
/**
 * Docker数据模型
 */
class DockerTable {
    // 数据表模型配置
    public $config = [
      'name' => 'docker',
      'title' => '容器管理',
      'search_key' => '',
      'add_button' => 0,
      'del_button' => 0,
      'search_button' => 0,
      'check_all' => 0,
      'list_row' => 1,
      'addon' => 'docker',
      'inherit' => ''
  ];

    // 列表定义
    public $list_grid = [
      'CONTAINER_ID' => [
          'title' => '容器ID'
      ],
      'IMAGE' => [
          'title' => '镜像'
      ],
      'COMMAND' => [
          'title' => '命令'
      ],
      'CREATED' => [
          'title' => '创建时间'
      ],
      'STATUS' => [
          'title' => '状态'
      ],
      'PORTS' => [
          'title' => '端口'
      ],
      'NAMES' => [
          'title' => '容器名'
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'href' => [
              '0' => [
                  'title' => '停止',
                  'url' => 'preview?id=[id]'
              ],
              '1' => [
                  'title' => '启动',
                  'url' => 'preview?id=[id]'
              ],
              '2' => [
                  'title' => '删除',
                  'url' => '[DELETE]'
              ]
          ]
      ]
  ];

    // 字段定义
    public $fields = [
      'CONTAINER_ID' => [
          'title' => '容器ID',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'IMAGE' => [
          'title' => '镜像',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'COMMAND' => [
          'title' => '命令',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'CREATED' => [
          'title' => '创建时间',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'STATUS' => [
          'title' => '状态',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'PORTS' => [
          'title' => '端口',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'NAMES' => [
          'title' => '容器名',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ]
  ];   
}