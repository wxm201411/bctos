<?php
/**
 * Images数据模型
 */
class ImagesTable {
    // 数据表模型配置
    public $config = [
      'name' => 'images',
      'title' => '镜像',
      'search_key' => '',
      'add_button' => 0,
      'del_button' => 0,
      'search_button' => 0,
      'check_all' => 0,
      'list_row' => 20,
      'addon' => 'docker',
      'inherit' => ''
  ];

    // 列表定义
    public $list_grid = [
      'REPOSITORY' => [
          'title' => '仓库名'
      ],
      'TAG' => [
          'title' => '标签'
      ],
      'IMAGE_ID' => [
          'title' => '镜像ID'
      ],
      'CREATED' => [
          'title' => '创建时间'
      ],
      'SIZE' => [
          'title' => '大小'
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
      'REPOSITORY' => [
          'title' => '仓库名',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'TAG' => [
          'title' => '标签',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'IMAGE_ID' => [
          'title' => '镜像ID',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'CREATED' => [
          'title' => '创建时间',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'is_show' => 1
      ],
      'SIZE' => [
          'title' => '大小',
          'type' => 'string',
          'field' => 'varchar(20) NULL',
          'is_show' => 1
      ]
  ];   
}