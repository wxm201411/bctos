<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'canal' => 'app\command\Canal',
        'update' => 'app\command\Update',
        'cron_time' => 'app\command\CronTime',
        'sample_install' => 'app\command\SampleInstall'
    ],
];
