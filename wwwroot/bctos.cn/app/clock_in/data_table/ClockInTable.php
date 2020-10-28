<?php
/**
 * ClockIn数据模型
 */
class ClockInTable {
    // 数据表模型配置
    public $config = [
      'name' => 'clock_in',
      'title' => '打卡挑战',
      'search_key' => '',
      'add_button' => 0,
      'del_button' => 0,
      'search_button' => 1,
      'check_all' => 0,
      'list_row' => 20,
      'addon' => 'clock_in'
  ];

    // 列表定义
    public $list_grid = [
      'title' => [
          'title' => '标题'
      ],
      'uid' => [
          'title' => '用户'
      ],
      'audit_status' => [
          'title' => '状态'
      ],
      'money_select' => [
          'title' => '参与金额'
      ],
      'prize_money' => [
          'title' => '奖金总额'
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'href' => [
              '0' => [
                  'title' => '编辑',
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
      'wpid' => [
          'title' => '平台账号ID',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'remark' => '区分平台账号之间的数据',
          'auto_rule' => 'get_wpid',
          'auto_time' => 1,
          'auto_type' => 'function',
          'value' => 0
      ],
      'create_at' => [
          'title' => '创建时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'auto_rule' => 'time',
          'auto_time' => 1,
          'auto_type' => 'function',
          'value' => 0
      ],
      'update_at' => [
          'title' => '更新时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'auto_rule' => 'time',
          'auto_time' => 2,
          'auto_type' => 'function',
          'value' => 0
      ],
      'uid' => [
          'title' => '用户',
          'type' => 'user',
          'field' => 'int(10) NULL',
          'auto_rule' => 'get_mid',
          'auto_time' => 1,
          'auto_type' => 'function',
          'value' => 0
      ],
      'audit_status' => [
          'title' => '状态',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:未审核
1:审核通过
2:审核不通过',
          'is_show' => 1,
          'value' => 0
      ],
      'title' => [
          'title' => '标题',
          'type' => 'string',
          'field' => 'varchar(255) NOT NULL',
          'is_show' => 1,
          'is_must' => 1
      ],
      'room' => [
          'title' => '直播间入口',
          'type' => 'string',
          'field' => 'varchar(255) NULL',
          'is_show' => 1
      ],
      'money_select' => [
          'title' => '参与金额',
          'type' => 'radio',
          'field' => 'tinyint(4) NULL',
          'extra' => '0:免费
1:1.00元
2:5.00元
3:10.00元',
          'is_show' => 1,
          'value' => 0
      ],
      'money_int' => [
          'title' => 'money_int',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'prize_open' => [
          'title' => '追加主播奖金',
          'type' => 'radio',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:否
1:是',
          'value' => 1,
          'is_show' => 1
      ],
      'prize_type' => [
          'title' => '颁奖方式',
          'type' => 'radio',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:打卡成功者平分
1:前10名打卡成功者分
2:前20名打卡成功者分
3:前50名打卡成功者分
4:前100名打卡成功者分
5:前1000名打卡成功者分',
          'is_show' => 1,
          'value' => 0
      ],
      'prize_money' => [
          'title' => '奖金总额',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'remark' => [
          'title' => '审核意见',
          'type' => 'textarea',
          'field' => 'text NULL',
          'is_show' => 1
      ],
      'join_count' => [
          'title' => '参加人数',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'total_money' => [
          'title' => '总奖金',
          'type' => 'num',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'keyword_count' => [
          'title' => '打卡次数',
          'type' => 'num',
          'field' => 'tinyint(2) NULL',
          'value' => 1,
          'is_show' => 1
      ],
      'wechat' => [
          'title' => '微信号',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'truename' => [
          'title' => '主播名',
          'type' => 'string',
          'field' => 'varchar(100) NULL',
          'is_show' => 1
      ],
      'end_time' => [
          'title' => '结束时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ],
      'status' => [
          'title' => '是否已结束',
          'type' => 'radio',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:未上链
1:进行中
6:已结束',
          'value' => 0,
          'is_show' => 1
      ],
      'is_pay' => [
          'title' => '是否支付',
          'type' => 'bool',
          'field' => 'tinyint(2) NULL',
          'extra' => '0:否
1:是',
          'is_show' => 1,
          'value' => 0
      ],
      'start_time' => [
          'title' => '开始时间',
          'type' => 'datetime',
          'field' => 'int(10) NULL',
          'is_show' => 1,
          'value' => 0
      ]
  ];   
}