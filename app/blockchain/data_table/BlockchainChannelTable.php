<?php
/**
 * BlockchainChannel数据模型
 */
class BlockchainChannelTable {
    // 数据表模型配置
    public $config = [
      'name' => 'blockchain_channel',
      'title' => '通道管理',
      'search_key' => '',
      'add_button' => 0,
      'del_button' => 0,
      'search_button' => 0,
      'check_all' => 0,
      'list_row' => 20,
      'addon' => 'blockchain',
      'inherit' => ''
  ];

    // 列表定义
    public $list_grid = [
      'channel_name' => [
          'title' => '通道名称'
      ],
      'version' => [
          'title' => '通道版本号'
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
      'channel_name' => [
          'title' => '通道名称',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'version' => [
          'title' => '通道版本号',
          'type' => 'string',
          'field' => 'varchar(20) NULL',
          'is_show' => 1
      ]
  ];   
}