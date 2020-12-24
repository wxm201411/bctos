<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class SampleInstall extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('sample_install')
            ->addArgument('id', Argument::OPTIONAL, "install form id")
            ->addArgument('domain', Argument::OPTIONAL, "domain name")
            ->addArgument('db_pwd', Argument::OPTIONAL, "database password")
            ->setDescription('sample install soft');
    }

    protected function execute(Input $input, Output $output)
    {
        $id = trim($input->getArgument('id'));
        $domain = trim($input->getArgument('domain'));
        $db_pwd = trim($input->getArgument('db_pwd'));

        $content = wp_file_get_contents("https://www.bctos.cn/index.php?s=/home/index/installData/id/$id");
        $data = json_decode($content, true);

        //增加数据库记录
        $path = "/bctos/wwwroot/{$domain}";
        $db_name = $domain == 'default' ? 'bctos_default' : str_replace('.', '_', $domain);
        $db = ['database' => $data['database'], 'db_name' => $db_name, 'db_user' => $db_name, 'db_passwd' => $db_pwd, 'db_set' => $data['db_set']];
        $site = ['domain' => $domain, 'path' => $path, 'php_version' => $data['php_version'], 'title' => $domain, 'public_path' => $data['public_path'], 'rewrite_mod' => $data['rewrite_mod'], 'create_at' => time()];
        $site_id = M('site')->insertGetId(array_merge($db, $site));
        if ($data['database'] != 'not') {
            $db['power'] = '%';
            $database_id = M('database')->insertGetId($db);
            //自动增加定时任务
            D('admin/Cron')->addCronAuto(0, $database_id, $db_name, $data['database']);
        }
        //自动增加定时任务
        D('admin/Cron')->addCronAuto(1, $site_id, $site['title'], $site['path']);

        // dump($data);exit;
        $install_id = M('install')->where('bctos_id', $id)->value('id');
        unset($data['id']);
        $data['status'] = 1;
        if ($install_id > 0) {
            M('install')->where('id', $install_id)->update($data);
        } else {
            $data['bctos_id'] = $id;
            M('install')->insert($data);
        }

        // 指令输出
        $output->writeln('数据库增加记录完成');
    }
}
