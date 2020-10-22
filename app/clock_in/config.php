<?php
return array(
    'ratio' => array(//配置在表单中的键名 ,这个会是config[random]
        'title' => '分账比例:',//表单的文字
        'type' => 'text',         //表单的类型：text、textarea、checkbox、radio、select、editor等
        'value' => '10:20:70',             //表单的默认值
    ),
    'max_limit' => array(//配置在表单中的键名 ,这个会是config[random]
        'title' => '最多打卡次数:',//表单的文字
        'type' => 'text',         //表单的类型：text、textarea、checkbox、radio、select、editor等
        'value' => '3',             //表单的默认值
    ),
    'effective_time' => array(//配置在表单中的键名 ,这个会是config[random]
        'title' => '有效时间间隔（分钟）:',//表单的文字
        'type' => 'text',         //表单的类型：text、textarea、checkbox、radio、select、editor等
        'value' => '30',             //表单的默认值
    ),
    'tcp' => array(//配置在表单中的键名 ,这个会是config[random]
        'title' => '活动说明:',//表单的文字
        'type' => 'editor',         //表单的类型：text、textarea、checkbox、radio、select、editor等
        'value' => '',             //表单的默认值
    ),
    'recharge' => array(//配置在表单中的键名 ,这个会是config[random]
        'title' => '充值说明:',//表单的文字
        'type' => 'editor',         //表单的类型：text、textarea、checkbox、radio、select、editor等
        'value' => '',             //表单的默认值
    )
);