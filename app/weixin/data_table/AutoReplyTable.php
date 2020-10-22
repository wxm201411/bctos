<?php
/**
 * AutoReply数据模型
 */
class AutoReplyTable {
    // 数据表模型配置
    public $config = [
      'name' => 'auto_reply',
      'title' => '自动回复',
      'search_key' => '',
      'add_button' => 1,
      'del_button' => 1,
      'search_button' => 1,
      'check_all' => 1,
      'list_row' => 10,
      'addon' => 'weixin'
  ];

    // 列表定义
    public $list_grid = [
      'keyword' => [
          'title' => '关键词'
      ],
      'content' => [
          'title' => '回复内容',
          'raw' => 1
      ],
      'urls' => [
          'title' => '操作',
          'come_from' => 1,
          'href' => [
              '0' => [
                  'title' => '编辑',
                  'url' => '[EDIT]&type=[msg_type]'
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
      'img_id' => [
          'title' => '上传图片',
          'field' => 'varchar(100) NULL',
          'type' => 'picture',
          'is_show' => 1,
          'value' => 0
      ],
      'news_id' => [
          'title' => '图文',
          'field' => 'int(10) NULL',
          'type' => 'news',
          'is_show' => 1,
          'value' => 0
      ],
      'keyword' => [
          'title' => '关键词',
          'field' => 'varchar(255) NULL',
          'type' => 'string',
          'remark' => '多个关键词可以用空格分开，如“高富帅 白富美”',
          'is_show' => 1
      ],
      'video_id' => [
          'title' => '视频素材id',
          'field' => 'int(10) NULL',
          'type' => 'num',
          'is_show' => 4,
          'value' => 0
      ],
      'voice_id' => [
          'title' => '语音素材id',
          'field' => 'int(10) NULL',
          'type' => 'num',
          'is_show' => 4,
          'value' => 0
      ],
      'image_material' => [
          'title' => '素材图片id',
          'field' => 'int(10) NULL',
          'type' => 'num',
          'value' => 0
      ],
      'pbid' => [
          'title' => '公众号id',
          'type' => 'string',
          'field' => 'varchar(50) NULL'
      ],
      'manager_id' => [
          'title' => '管理员ID',
          'field' => 'int(10) NULL',
          'type' => 'num',
          'value' => 0
      ],
      'msg_type' => [
          'title' => '消息类型',
          'field' => 'char(50) NULL',
          'type' => 'select',
          'extra' => 'text:文本|content@show,group_id@hide,image_id@hide,voice_id@hide,video_id@hide
news:图文|content@hide,group_id@show,image_id@hide,voice_id@hide,video_id@hide
image:图片|content@hide,group_id@hide,image_id@show,voice_id@hide,video_id@hide
voice:语音|content@hide,group_id@hide,image_id@hide,voice_id@show,video_id@hide
video:视频|content@hide,group_id@hide,image_id@hide,voice_id@hide,video_id@show
'
      ],
      'text_id' => [
          'title' => '文本素材id',
          'field' => 'int(10) NULL',
          'type' => 'num',
          'value' => 0
      ]
  ];   
}