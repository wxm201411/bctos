<?php

namespace app\common\model;

use app\common\model\Base;

/**
 * 插件模型 - 数据对象模型
 * @author jason <yangjs17@yeah.net>
 * @version TS3.0
 */
class Phinx extends Base
{
    protected $table = DB_PREFIX . 'phinxlog';

    private function mysqlDiffGetDb($num = 1)
    {
        $name = 'phinx_db' . $num;
        $dsn = input('db' . $num);
        db_config($name, $dsn);

        $config = config('database');
        $db = ['type' => 'mysql', 'charset' => 'utf8mb4', 'prefix' => DB_PREFIX, 'fields_strict' => false];

        list($db['hostname'], $db['hostport'], $db['database'], $db['username'], $db['password']) = wp_explode($dsn, ';');
        $config['connections'][$name] = $db;
        config($config, 'database');

        $sql = "SELECT TABLE_NAME,COLUMN_NAME,COLUMN_DEFAULT,IS_NULLABLE,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH,NUMERIC_PRECISION,NUMERIC_SCALE,CHARACTER_SET_NAME,COLLATION_NAME,COLUMN_TYPE,COLUMN_COMMENT
FROM information_schema.`COLUMNS` where TABLE_SCHEMA='{$db['database']}'";

        $lists = find_data(\think\facade\Db::connect($name)->query($sql));
        //dump($lists);
        $md5 = $table = $newTables = [];
        foreach ($lists as $k => $vo) {
            //$t = str_replace(DB_PREFIX, '', $vo['TABLE_NAME']);
            $table[$vo['TABLE_NAME']][$vo['COLUMN_NAME']] = $vo;
        }

        $sql = "SELECT TABLE_NAME,TABLE_COMMENT,ROW_FORMAT,`ENGINE`,TABLE_COLLATION FROM information_schema.`TABLES` where TABLE_SCHEMA='{$db['database']}'";
        $tables = find_data(\think\facade\Db::connect($name)->query($sql));
        foreach ($tables as $vo) {
            //$t = str_replace(DB_PREFIX, '', $vo['TABLE_NAME']);
            $newTables[$vo['TABLE_NAME']] = $vo;
        }

        $sql = "SELECT * FROM `" . DB_PREFIX . "model`";
        $lists = find_data(\think\facade\Db::connect($name)->query($sql));
        $model_data = [];
        foreach ($lists as $vo) {
            unset($vo['id']);
            $model_data[$vo['name']] = $vo;
        }

        return [$table, $newTables, $model_data];
    }

    function mysqlType($type)
    {
        $typeArr = [
            'biginteger' => 'biginteger',
            'binary' => 'binary',
            'boolean' => 'boolean',
            'char' => 'char',
            'date' => 'date',
            'datetime' => 'datetime',
            'decimal' => 'decimal',
            'float' => 'float',
            'int' => 'integer',
            'tinyint' => 'integer',
            'smallinteger' => 'smallinteger',
            'varchar' => 'string',
            'text' => 'text',
            'time' => 'time',
            'timestamp' => 'timestamp',
            'uuid' => 'uuid'
        ];
        $type = strtolower($type);
        return isset($typeArr[$type]) ? $typeArr[$type] : 'string';
    }

    function mysqlParam($field)
    {
        $param = [];
        // 获取默认值
        is_null($field['COLUMN_DEFAULT']) || $param['default'] = $field['COLUMN_DEFAULT'];
        $field['IS_NULLABLE'] == 'YES' && $param['null'] = true;

        if (strpos($field['COLUMN_TYPE'], '(') && strpos($field['COLUMN_TYPE'], ',') === false) {
            $param['limit'] = intval(explode('(', $field['COLUMN_TYPE'])[1]);
        }
        is_null($field['CHARACTER_MAXIMUM_LENGTH']) || $param['limit'] = $field['CHARACTER_MAXIMUM_LENGTH'];
        if ($field['NUMERIC_SCALE'] > 0) {
            unset($param['limit']);
            $param['precision'] = $field['NUMERIC_PRECISION'];
            $param['scale'] = $field['NUMERIC_SCALE'];
        }
        strpos($field['COLUMN_TYPE'], 'unsigned') !== false && $param['signed'] = true;
        is_null($field['CHARACTER_SET_NAME']) || $param['encoding'] = $field['CHARACTER_SET_NAME'];
        is_null($field['COLLATION_NAME']) || $param['collation'] = $field['COLLATION_NAME'];
        is_null($field['COLUMN_COMMENT']) || $param['comment'] = $field['COLUMN_COMMENT'];

        return str_replace(PHP_EOL, '', var_export($param, true));
    }

    function tableParam($id, $engine, $comment, $row_format = '', $collation = '')
    {
        $param['id'] = $id;
        $param['engine'] = $engine;
        $param['comment'] = $comment;
        $row_format && $param['row_format'] = $row_format;
        $collation && $param['collation'] = $collation;
        return str_replace(PHP_EOL, '', var_export($param, true));
    }

    function mysqlDiff()
    {
        list($table_1, $newTables_1, $model_data_1) = $this->mysqlDiffGetDb(1);
        list($table_2, $newTables_2, $model_data_2) = $this->mysqlDiffGetDb(2);

        $phinx = '';
        //$table_1不存在，$table_2存在，则表示要删除表
        $del_table = array_keys(array_diff_key($table_2, $table_1));
        foreach ($del_table as $d) {
            $phinx .= "\$this->table('{$d}')->drop()->save();" . PHP_EOL;
            unset($table_2[$d]);
        }

        //$table_1存在，$table_2不存在，则表示要新增表
        $add_table = array_diff_key($table_1, $table_2);
        foreach ($add_table as $d => $cols) {
            $id = isset($cols['id']) ? 'true' : 'false';
            $n = $newTables_1[$d];

            $phinx .= "\$this->table('{$d}', " . $this->tableParam($id, $n['ENGINE'], $n['TABLE_COMMENT'], $n['ROW_FORMAT'], $n['TABLE_COLLATION']) . ")" . PHP_EOL;
            foreach ($cols as $f => $vo) {
                if ($f == 'id') continue;
                $type = $this->mysqlType($vo['DATA_TYPE']);
                $param = $this->mysqlParam($vo);
                $phinx .= "->addColumn('{$f}', '{$type}', $param)" . PHP_EOL;
            }
            $phinx .= '->create();' . PHP_EOL;
            unset($table_1[$d]);
        }

        //两边的表都在，开始比较字段
        foreach ($table_1 as $t => $cols_1) {
            $cols_2 = $table_2[$t];
            $content = '';
            //$cols_1不存在，$cols_2存在，则表示要删除字段
            $del_cols = array_keys(array_diff_key($cols_2, $cols_1));
            foreach ($del_cols as $c) {
                $content .= "->removeColumn('{$c}')" . PHP_EOL;;
            }

            //$cols_1存在，$cols_2不存在，则表示要新增字段
            $add_cols = array_diff_key($cols_1, $cols_2);
            foreach ($add_cols as $f => $vo) {
                if ($f == 'id') continue;
                $type = $this->mysqlType($vo['DATA_TYPE']);
                $param = $this->mysqlParam($vo);
                $content .= "->addColumn('{$f}', '{$type}', $param)" . PHP_EOL;
                unset($cols_1[$f]);
            }
            //相同的字段对比属性是否相同，不同则表示要更新字段
            if (!empty($cols_1)) {
                foreach ($cols_1 as $f => $vo) {
                    if ($f == 'id') continue;
                    $md5_1 = md5(json_encode($vo));
                    $md5_2 = md5(json_encode($cols_2[$f]));
                    if ($md5_1 == $md5_2) continue; //属性相同，不需要更新

                    $type = $this->mysqlType($vo['DATA_TYPE']);
                    $param = $this->mysqlParam($vo);
                    $content .= "->changeColumn('{$f}', '{$type}', $param)" . PHP_EOL;
                    unset($cols_1[$f]);
                }
            }

            //表属性比较
            $t_1 = $newTables_1[$t];
            $t_2 = $newTables_2[$t];
            if ($t_1['TABLE_COMMENT'] != $t_2['TABLE_COMMENT']) {
                $content .= "->changeComment('{$t_1['TABLE_COMMENT']}')" . PHP_EOL;
            } elseif ($t_1['ROW_FORMAT'] != $t_2['ROW_FORMAT'] || $t_1['ENGINE'] != $t_2['ENGINE'] || $t_1['TABLE_COLLATION'] != $t_2['TABLE_COLLATION']) {
                //这部分目前只能手工处理TODO
            }

            $content && $phinx .= "\$this->table('{$t}')" . PHP_EOL . $content . '->update();' . PHP_EOL;
        }

        //比较wp_model表的数据
        $phinx .= $this->diffModelData($model_data_1, $model_data_2);


        $build = input('build/d', 0);
        if ($build == 0) {
            return nl2br($phinx);
        } else {
            return $this->buildMigrator($phinx);
        }
    }

    function diffModelData($table_1, $table_2)
    {
        $table_name = "model";
        $phinx = '';
        //$table_1不存在，$table_2存在，则表示要删除记录
        $del_table = array_keys(array_diff_key($table_2, $table_1));
        if (!empty($del_table)) {
            $sql = M($table_name)->fetchSql(true)->whereIn('name', $del_table)->delete();
            $phinx .= '$this->execute("' . $sql . '");' . PHP_EOL;
        }

        //$table_1存在，$table_2不存在，则表示要新增记录
        $add_table = array_diff_key($table_1, $table_2);
        foreach ($add_table as $d => $cols) {
            $sql = M($table_name)->fetchSql(true)->insert($cols);
            $phinx .= '$this->execute("' . $sql . '");' . PHP_EOL;
            unset($table_1[$d]);
        }

        //两边的记录都在，开始比较字段的内容
        if (!empty($table_1)) {
            foreach ($table_1 as $f => $vo) {
                if ($f == 'id') continue;
                $md5_1 = md5(json_encode($vo));
                $md5_2 = md5(json_encode($table_2[$f]));
                if ($md5_1 == $md5_2) continue; //属性相同，不需要更新

                $sql = M($table_name)->fetchSql(true)->where('name', $f)->update($vo);
                $phinx .= '$this->execute("' . $sql . '");' . PHP_EOL;
            }
        }

        return $phinx;
    }

    function buildMigratorBySql($sql)
    {
        $fileName = 'model_sql' . date("YmdHis") . rand(10, 99);
        $phinx = '$this->execute("' . $sql . '");' . PHP_EOL;
        return $this->buildMigrator($phinx, $fileName);
    }

    function buildMigrator($phinx, $fileName = '')
    {
        //查找有没有重复的文件
        $path = SITE_PATH . '/db/migrations/';
        $dirfat = dir($path);
        while (false !== $entry = $dirfat->read()) {
            if (!is_file($path . DIRECTORY_SEPARATOR . $entry)) continue;
            $content = file_get_contents($path . DIRECTORY_SEPARATOR . $entry);
            if (strpos($content, $phinx) !== false) {
                return "请勿重复生成升级文件";
            }
        }
        $dirfat->close();

        $time = date("YmdHis");
        $fileName || $fileName = 'update_by_diff_mysql' . $time . rand(10, 99);
        $className = parse_name_upper($fileName);
        $content = <<<str
<?php
declare(strict_types=1);
use Phinx\Migration\AbstractMigration;
final class {$className} extends AbstractMigration
{
    public function change()
    {
{$phinx}
    }
}
str;
        $content = str_replace('&nbsp;', '  ', $content);
        file_put_contents($path . $time . '_' . $fileName . '.php', $content);

        $config = config('database.connections.mysql');
        $db1 = $config['hostname'] . ';' . $config['hostport'] . ';' . $config['database'] . ';' . $config['username'] . ';' . $config['password'];
        if ($db1 == input('db1')) {
            //本人的数据库不需要升级
            $date = date("Y-m-d H:i:s");
            M('phinxlog')->insert(['version' => $time, 'migration_name' => $className, 'start_time' => $date, 'end_time' => $date, 'breakpoint' => 0]);
        }

        return "升级文件生成成功，位置：" . $path . $time . '_' . $fileName . '.php';
    }
}