<?php
//blockchain配置文件
return [
      'auth' => [
          'title' => '认证方式',
          'type' => 'radio',
          'field' => 'char(10) NULL',
          'extra' => 'crypto:cryptogen
ca:CA证书',
          'value' => 'crypto',
          'remark' => '需要重启网后生效',
          'is_show' => 1,
          'extra_array' => [
              'crypto' => 'cryptogen',
              'ca' => 'CA证书'
          ],
          'extra_array_id' => [
              '0' => 'crypto',
              '1' => 'ca'
          ],
          'extra_array_title' => [
              '0' => 'cryptogen',
              '1' => 'CA证书'
          ]
      ],
      'channel' => [
          'title' => '通道名称',
          'type' => 'string',
          'field' => 'varchar(50) NULL',
          'value' => 'mychannel',
          'is_show' => 1
      ]
  ];