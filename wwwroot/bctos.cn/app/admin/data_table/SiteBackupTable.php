<?php
/**
 * SiteBackup数据模型
 */
class SiteBackupTable {
    // 数据表模型配置
    public $config = [
      'name' => 'site_backup',
      'title' => '网站备份',
      'search_key' => '',
      'add_button' => 0,
      'del_button' => 0,
      'search_button' => 0,
      'check_all' => 0,
      'list_row' => 20,
      'addon' => 'admin'
  ];

    // 列表定义
    public $list_grid = [
      'title' => [
          'title' => '文件名',
          'width' => 40,
          'can_edit' => 0
      ],
      'file_size' => [
          'title' => '大小',
          'width' => 10,
          'can_edit' => 0
      ],
      'backup_at' => [
          'title' => '备份时间',
          'width' => 30,
          'can_edit' => 0
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'width' => 20,
          'href' => [
              '0' => [
                  'title' => '恢复',
                  'url' => 'recovery?id=[id]',
                  'class' => 'recovery',
                  'show_set' => [ ]
              ],
              '1' => [
                  'title' => '下载',
                  'url' => 'download?id=[id]',
                  'show_set' => [ ]
              ],
              '2' => [
                  'title' => '删除',
                  'url' => '[DELETE]',
                  'show_set' => [ ]
              ]
          ],
          'can_edit' => 0
      ]
  ];

    // 字段定义
    public $fields = [
      'backup_at' => [
          'title' => '备份时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'value' => 0,
          'auto_type' => 'function',
          'auto_rule' => 'time',
          'auto_time' => 1
      ],
      'title' => [
          'title' => '标题',
          'type' => 'string',
          'field' => 'varchar(255) NOT NULL',
          'is_show' => 1,
          'is_must' => 1
      ],
      'file_size' => [
          'title' => '文件大小',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'value' => 0,
          'is_show' => 1
      ],
      'site_id' => [
          'title' => '网站ID',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'value' => 0
      ],
      'type' => [
          'title' => '类型',
          'field' => 'tinyint(2) NULL',
          'type' => 'string',
          'value' => 0
      ]
  ];   
}