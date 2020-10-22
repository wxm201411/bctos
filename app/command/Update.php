<?php
declare (strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Update extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('update')
            ->addArgument('tag', Argument::OPTIONAL, "new tag name")
            ->setDescription('update system tag number');
    }

    protected function execute(Input $input, Output $output)
    {
        $tag = trim($input->getArgument('tag'));
        db_config('local_tag', $tag);

        // 指令输出
        $output->writeln('已更新到版本： ' . $tag);
    }
}
