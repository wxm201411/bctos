<?php
declare (strict_types=1);

namespace app\command;

use Com\Alibaba\Otter\Canal\Protocol\Column;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\RowData;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;

class Canal extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('canal')->setDescription('canal client');
    }

    protected function execute(Input $input, Output $output)
    {
        if (memory_get_usage() > 100 * 1024 * 1024) {
            exit(0);//大于100M内存退出程序，防止内存泄漏被系统杀死导致任务终端
        }

        try {
            $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);

            $client->connect("127.0.0.1", 11111);
            $client->checkValid();
            $client->subscribe("1001", "example", config('database.connections.mysql.database') . "\\..*");
            //$client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤

            while (true) {
                $message = $client->get(100);
                if ($entries = $message->getEntries()) {
                    foreach ($entries as $entry) {
                        $entryType = $entry->getEntryType();
                        if ($entryType == EntryType::TRANSACTIONBEGIN || $entryType == EntryType::TRANSACTIONEND) continue;

                        $rowChange = new RowChange();
                        $rowChange->mergeFromString($entry->getStoreValue());

                        $evenType = $rowChange->getEventType();
                        if (!in_array($evenType, [EventType::INSERT, EventType::UPDATE, EventType::DELETE])) continue;

                        $header = $entry->getHeader();
                        $data = [
                            'tableName' => $header->getTableName(),
                            'eventType' => $evenType,
                            'befdata' => []
                        ];
                        if (in_array($data['tableName'], ['wp_debug_log', 'wp_request_log', 'wp_visit_log', 'wp_cache_keys'])) continue;

                        $key_config = keys_list($data['tableName']);
                        if (empty($key_config)) continue;

                        /** @var RowData $rowData */
                        foreach ($rowChange->getRowDatas() as $rowData) {
                            switch ($evenType) {
                                case EventType::DELETE:
                                    $data['data'] = $this->ptColumn($rowData->getBeforeColumns());
                                    break;
                                case EventType::INSERT:
                                    $data['data'] = $this->ptColumn($rowData->getAfterColumns());
                                    break;
                                default:
                                    $data['befdata'] = $this->ptColumn($rowData->getBeforeColumns());
                                    $data['data'] = $this->ptColumn($rowData->getAfterColumns());
                                    // 老数据对应的缓存也要清空
                                    $this->ClearCache($data, $data['befdata'], $key_config);
                                    break;
                            }

                            $this->ClearCache($data, $data['data'], $key_config);
                        }
                    }
                }
                sleep(1);
            }

            $client->disConnect();
        } catch (\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
        }
    }

    private function ptColumn($columns)
    {
        $data = [];
        foreach ($columns as $column) {
            $data[$column->getName()] = $column->getValue();
        }
        return $data;
    }

    private function ClearCache($log, $data, $key_config)
    {
        // 先取出新增数据的数据表下的所有key规则，然后根据key规则和增加的数据，逐个生成各个key，然后把这些key的值全部设置为null，相当于清它的缓存。
        $search = $replace = [];
        foreach ($data as $k => $v) {
            $search[] = '[' . $k . ']';
            $replace[] = $v;
        }

        foreach ($key_config as $info) {
            $key = str_replace($search, $replace, $info['key_rule']);

            // 还可以额外判断缓存的数据是否被更新过，如果没更新，则可以不清空缓存。
            if ($log['eventType'] == EventType::UPDATE && !empty($info['data_field'])) {
                $data_field = wp_explode($info['data_field'], ',');

                // 判断这些字段是否被更新过
                $update = false;
                foreach ($data_field as $field) {
                    if ($log['befdata'][$field] != $log['data'][$field]) {
                        $update = true;
                        break;
                    }
                }

                if ($update === true) {
                    S($key, NULL);
                }
            } else {
                S($key, NULL);
            }
        }
    }
}
