<?php
/**
 * Cron数据模型
 */
class CronTable {
    // 数据表模型配置
    public $config = [
      'name' => 'cron',
      'title' => '计划任务',
      'search_key' => '',
      'add_button' => 0,
      'del_button' => 0,
      'search_button' => 0,
      'check_all' => 0,
      'list_row' => 2000,
      'addon' => 'admin'
  ];

    // 列表定义
    public $list_grid = [
      'title' => [
          'title' => '任务名称',
          'can_edit' => 0
      ],
      'update_at' => [
          'title' => '最近执行',
          'can_edit' => 0
      ],
      'time_type' => [
          'title' => '执行周期',
          'can_edit' => 0
      ],
      'max_keep' => [
          'title' => '保存数量',
          'can_edit' => 0
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'href' => [
              '0' => [
                  'title' => '执行',
                  'url' => 'execute?id=[id]',
                  'class' => 'exec-cron',
                  'show_set' => [ ]
              ],
              '1' => [
                  'title' => '编辑',
                  'url' => '[EDIT]/only_body/1',
                  'class' => 'edit-cron',
                  'show_set' => [ ]
              ],
              '2' => [
                  'title' => '日志',
                  'url' => 'log?id=[id]',
                  'class' => 'log-cron',
                  'show_set' => [ ]
              ],
              '4' => [
                  'title' => '清空日志',
                  'url' => 'delLog?id=[id]',
                  'class' => 'log-del',
                  'show_set' => [ ]
              ],
              '3' => [
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
      'type' => [
          'title' => '任务类型',
          'type' => 'select',
          'field' => 'char(50) NULL',
          'extra' => '备份数据库
备份网站
Shell脚本
缓存清理
释放内存
访问URL
备份目录
同步时间',
          'is_show' => 1,
          'extra_array' => [
              '0' => '备份数据库',
              '1' => '备份网站',
              '2' => 'Shell脚本',
              '3' => '缓存清理',
              '4' => '释放内存',
              '5' => '访问URL',
              '6' => '备份目录',
              '7' => '同步时间'
          ],
          'extra_array_id' => [
              '0' => 0,
              '1' => 1,
              '2' => 2,
              '3' => 3,
              '4' => 4,
              '5' => 5,
              '6' => 6,
              '7' => 7,
              '8' => 0,
              '9' => 1,
              '10' => 2,
              '11' => 3,
              '12' => 4,
              '13' => 5,
              '14' => 6,
              '15' => 7
          ],
          'extra_array_title' => [
              '0' => '备份数据库',
              '1' => '备份网站',
              '2' => 'Shell脚本',
              '3' => '缓存清理',
              '4' => '释放内存',
              '5' => '访问URL',
              '6' => '备份目录',
              '7' => '同步时间',
              '8' => '备份数据库',
              '9' => '备份网站',
              '10' => 'Shell脚本',
              '11' => '缓存清理',
              '12' => '释放内存',
              '13' => '访问URL',
              '14' => '备份目录',
              '15' => '同步时间'
          ]
      ],
      'title' => [
          'title' => '任务名称',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1,
          'show_set' => [
              'type' => [
                  '0' => 2
              ]
          ]
      ],
      'time_type' => [
          'title' => '执行周期',
          'type' => 'select',
          'field' => 'char(50) NULL',
          'extra' => 'day:每天
day-n:N天
hour:每小时
hour-n:N小时
minute:每分钟
minute-n:N分钟
week:每星期
month:每月',
          'value' => 'day',
          'is_show' => 1,
          'extra_array' => [
              'day' => '每天',
              'day-n' => 'N天',
              'hour' => '每小时',
              'hour-n' => 'N小时',
              'minute' => '每分钟',
              'minute-n' => 'N分钟',
              'week' => '每星期',
              'month' => '每月'
          ],
          'extra_array_id' => [
              '0' => 'day',
              '1' => 'day-n',
              '2' => 'hour',
              '3' => 'hour-n',
              '4' => 'minute',
              '5' => 'minute-n',
              '6' => 'week',
              '7' => 'month',
              '8' => 'day',
              '9' => 'day-n',
              '10' => 'hour',
              '11' => 'hour-n',
              '12' => 'minute',
              '13' => 'minute-n',
              '14' => 'week',
              '15' => 'month',
              '16' => 'day',
              '17' => 'day-n',
              '18' => 'hour',
              '19' => 'hour-n',
              '20' => 'minute',
              '21' => 'minute-n',
              '22' => 'week',
              '23' => 'month',
              '24' => 'day',
              '25' => 'day-n',
              '26' => 'hour',
              '27' => 'hour-n',
              '28' => 'minute',
              '29' => 'minute-n',
              '30' => 'week',
              '31' => 'month',
              '32' => 'day',
              '33' => 'day-n',
              '34' => 'hour',
              '35' => 'hour-n',
              '36' => 'minute',
              '37' => 'minute-n',
              '38' => 'week',
              '39' => 'month',
              '40' => 'day',
              '41' => 'day-n',
              '42' => 'hour',
              '43' => 'hour-n',
              '44' => 'minute',
              '45' => 'minute-n',
              '46' => 'week',
              '47' => 'month',
              '48' => 'day',
              '49' => 'day-n',
              '50' => 'hour',
              '51' => 'hour-n',
              '52' => 'minute',
              '53' => 'minute-n',
              '54' => 'week',
              '55' => 'month',
              '56' => 'day',
              '57' => 'day-n',
              '58' => 'hour',
              '59' => 'hour-n',
              '60' => 'minute',
              '61' => 'minute-n',
              '62' => 'week',
              '63' => 'month',
              '64' => 'day',
              '65' => 'day-n',
              '66' => 'hour',
              '67' => 'hour-n',
              '68' => 'minute',
              '69' => 'minute-n',
              '70' => 'week',
              '71' => 'month'
          ],
          'extra_array_title' => [
              '0' => '每天',
              '1' => 'N天',
              '2' => '每小时',
              '3' => 'N小时',
              '4' => '每分钟',
              '5' => 'N分钟',
              '6' => '每星期',
              '7' => '每月',
              '8' => '每天',
              '9' => 'N天',
              '10' => '每小时',
              '11' => 'N小时',
              '12' => '每分钟',
              '13' => 'N分钟',
              '14' => '每星期',
              '15' => '每月',
              '16' => '每天',
              '17' => 'N天',
              '18' => '每小时',
              '19' => 'N小时',
              '20' => '每分钟',
              '21' => 'N分钟',
              '22' => '每星期',
              '23' => '每月',
              '24' => '每天',
              '25' => 'N天',
              '26' => '每小时',
              '27' => 'N小时',
              '28' => '每分钟',
              '29' => 'N分钟',
              '30' => '每星期',
              '31' => '每月',
              '32' => '每天',
              '33' => 'N天',
              '34' => '每小时',
              '35' => 'N小时',
              '36' => '每分钟',
              '37' => 'N分钟',
              '38' => '每星期',
              '39' => '每月',
              '40' => '每天',
              '41' => 'N天',
              '42' => '每小时',
              '43' => 'N小时',
              '44' => '每分钟',
              '45' => 'N分钟',
              '46' => '每星期',
              '47' => '每月',
              '48' => '每天',
              '49' => 'N天',
              '50' => '每小时',
              '51' => 'N小时',
              '52' => '每分钟',
              '53' => 'N分钟',
              '54' => '每星期',
              '55' => '每月',
              '56' => '每天',
              '57' => 'N天',
              '58' => '每小时',
              '59' => 'N小时',
              '60' => '每分钟',
              '61' => 'N分钟',
              '62' => '每星期',
              '63' => '每月',
              '64' => '每天',
              '65' => 'N天',
              '66' => '每小时',
              '67' => 'N小时',
              '68' => '每分钟',
              '69' => 'N分钟',
              '70' => '每星期',
              '71' => '每月'
          ]
      ],
      'time_day' => [
          'title' => 'N号',
          'type' => 'num',
          'field' => 'tinyint(4) NULL',
          'value' => 0,
          'is_show' => 1
      ],
      'time_day2' => [
          'title' => 'N天',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'value' => 1,
          'is_show' => 1
      ],
      'time_hour' => [
          'title' => 'N点',
          'type' => 'num',
          'field' => 'tinyint(2) NULL',
          'value' => 0,
          'is_show' => 1,
          'show_set' => [
              'time_type' => [
                  '0' => 'day',
                  '1' => 'day-n',
                  '2' => 'hour-n',
                  '3' => 'week',
                  '4' => 'month'
              ]
          ]
      ],
      'time_minute' => [
          'title' => 'N分',
          'type' => 'num',
          'field' => 'tinyint(2) NULL',
          'value' => 0,
          'is_show' => 1,
          'show_set' => [
              'time_type' => [
                  '0' => 'day',
                  '1' => 'day-n',
                  '2' => 'hour',
                  '3' => 'hour-n',
                  '4' => 'minute-n',
                  '5' => 'week',
                  '6' => 'month'
              ]
          ]
      ],
      'time_week' => [
          'title' => '周N',
          'type' => 'select',
          'field' => 'char(50) NULL',
          'extra' => '周日
周一
周二
周三
周四
周五
周六',
          'is_show' => 1
      ],
      'site_id' => [
          'title' => '备份网站',
          'type' => 'select',
          'field' => 'char(50) NULL',
          'is_show' => 1
      ],
      'max_keep' => [
          'title' => '保留最新',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'value' => 3,
          'is_show' => 1,
          'show_set' => [
              'type' => [
                  '0' => 0,
                  '1' => 1,
                  '2' => 3,
                  '3' => 6
              ]
          ]
      ],
      'exclude' => [
          'title' => '排除规则',
          'type' => 'textarea',
          'field' => 'text NULL',
          'is_show' => 1,
          'show_set' => [
              'type' => [
                  '0' => 1,
                  '1' => 6
              ]
          ]
      ],
      'shell_content' => [
          'title' => '脚本内容',
          'type' => 'textarea',
          'field' => 'text NULL',
          'is_show' => 1,
          'show_set' => [
              'type' => [
                  '0' => 2
              ]
          ]
      ],
      'update_at' => [
          'title' => '最近执行',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'value' => 0,
          'auto_type' => 'function',
          'auto_rule' => 'time',
          'auto_time' => 2
      ],
      'database_id' => [
          'title' => '备份数据库',
          'type' => 'select',
          'field' => 'char(50) NULL'
      ],
      'jump_url' => [
          'title' => 'URL地址',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1,
          'show_set' => [
              'type' => [
                  '0' => 5
              ]
          ]
      ],
      'dir_path' => [
          'title' => '备份目录',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1,
          'show_set' => [
              'type' => [
                  '0' => 6
              ]
          ]
      ],
      'time_cron' => [
          'title' => '定时任务时间',
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