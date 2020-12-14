<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class CronTime extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('cron_time')
            ->addArgument('id', Argument::OPTIONAL, "the id from wp_cron table")
            ->addArgument('size', Argument::OPTIONAL, "the size with the zip")
            ->addArgument('file', Argument::OPTIONAL, "the fileName with the zip")
            ->addArgument('delFiels', Argument::OPTIONAL, "the files need delete")
            ->setDescription('the cron_time command');
    }

    protected function execute(Input $input, Output $output)
    {
        $id = intval($input->getArgument('id'));
//        $output->writeln('$id:' . $id);
        if ($id < 1) {
            return false;
        }
        $size = $input->getArgument('size');
//        $output->writeln('$size:' . $size);
        $file = $input->getArgument('file');
//        $output->writeln('$file:' . $file);
        $delFiels = $input->getArgument('delFiels');
//        $output->writeln('$delFiels:' . $delFiels);
        $config = require("config/database.php");
        $config = $config['connections']['mysql'];

        $dao = \think\facade\Db::connect('mysql');
        $info = $dao->table($config['prefix'] . 'cron')->where('id', $id)->find();
//        $output->writeln('$info:' . json_encode($info));
        $save['backup_at'] = $time = time();
        $dao->table($config['prefix'] . 'cron')->where('id', $id)->update(['update_at' => $time]);
        if ($info['type'] == 1) {
            $dao->table($config['prefix'] . 'site')->where('id', $info['site_id'])->update($save);
        } elseif ($info['type'] == 0) {
            $dao->table($config['prefix'] . 'database')->where('id', $info['site_id'])->update($save);
//            $output->writeln('sql:' . $dao->table($config['prefix'] . 'database')->getLastSql());
        }
        if ($info['type'] < 2) {
            $recode['title'] = $file;
            $recode['type'] = $info['type'];
            $recode['file_size'] = $size;
            $recode['backup_at'] = $time;
            $recode['site_id'] = $info['site_id'];
            $dao->table($config['prefix'] . 'site_backup')->insert($recode);
//            $output->writeln('sql:' . $dao->table($config['prefix'] . 'site_backup')->getLastSql());
            if ($delFiels != null) {
                $files = wp_explode($delFiels);
                $dao->table($config['prefix'] . 'site_backup')->whereIn('type', [0, 1])->whereIn('title', $files)->delete();
            }
        }

        // 指令输出
        //$output->writeln('cron_time:' . $time);
    }
}
