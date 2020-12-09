<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace app\common\model;

use app\common\model\Base;

/**
 * 文档基础模型
 */
class Models extends Base
{

    protected $table = DB_PREFIX . 'model';

    /**
     * 检查列表定义
     *
     * @param type $data
     */
    protected function checkListGrid($data)
    {
        return !empty($data);
    }

    /**
     * 新增或更新一个文档
     *
     * @return boolean fasle 失败 ， int 成功 返回完整的数据
     * @author huajie <banhuajie@163.com>
     */
    public function updateModels()
    {
        /* 获取数据对象 */
        $data = input('post.');

        if (empty($data)) {
            return false;
        }
        /* 添加或新增基础内容 */
        if (empty($data['id'])) {
            // 新增数据
            $data['create_time'] = time();
            $data['id'] = $this->insertGetId($data); // 添加基础内容
            if (!$data['id']) {
                $this->error = '新增模型出错！';
                return false;
            }
            $phinx_sql = $this->getLastSql();
            D('common/Phinx')->buildMigratorBySql($phinx_sql);
        } else {
            // 更新数据
            $model = $this->where('id', $data['id'])->find(); // 先取旧的模型名
            $data['update_time'] = time();
            $status = $this->save($data); // 更新基
            $phinx_sql = $this->getLastSql();
            D('common/Phinx')->buildMigratorBySql($phinx_sql);
            if (false === $status) {
                $this->error = '更新模型出错！';
                return false;
            } elseif ($model['name'] != $data['name']) {
                // 同时更新模型对应的数据表，先判断数据表是否存在
                $table_name = DB_PREFIX . strtolower($model['name']);
                $new_table_name = DB_PREFIX . strtolower($data['name']);

                $sql = "SHOW TABLES LIKE '{$table_name}'";
                $res = M()::query($sql);
                if (count($res)) {
                    $sql = "ALTER TABLE `{$table_name}` RENAME TO `{$new_table_name}`";
                    M()::execute($sql);
                }
            }
        }
        // 清除模型缓存数据
        S('DOCUMENT_MODEL_LIST', null);

        // 记录行为
        action_log('update_model', 'model', $data['id'], UID);

        $this->buildFile($data['id']);
        // 内容添加或更新完成
        return $data;
    }

    /**
     * 处理字段排序数据
     */
    protected function getSortFields($fields)
    {
        return empty($fields) ? '' : json_encode($fields);
    }

    protected function getAttribute($fields)
    {
        return empty($fields) ? '' : implode(',', $fields);
    }

    /**
     * 获取指定数据库的所有表名
     */
    public function getTables()
    {
        return $this->db->getTables();
    }

    /**
     * 根据数据表生成模型及其属性数据
     *
     * @author huajie <banhuajie@163.com>
     */
    public function generate($table, $name = '', $title = '')
    {
        // 新增模型数据
        if (empty($name)) {
            $name = $title = substr($table, strlen(DB_PREFIX));
        }
        $data = array(
            'name' => $name,
            'title' => $title
        );

        if ($data) {
            $res = $this->insertGetId($data);
            $phinx_sql = $this->getLastSql();
            D('common/Phinx')->buildMigratorBySql($phinx_sql);
            if (!$res) {
                return false;
            }
        } else {
            $this->error = $this->getError();
            return false;
        }
        return true;
    }

    /**
     * 删除一个模型
     *
     * @param integer $id
     *            模型id
     * @author huajie <banhuajie@163.com>
     */
    public function del($id)
    {
        // 获取表名
        $model = $this->field(true)
            ->where('id', $id)
            ->find();
        $table_name = DB_PREFIX . strtolower($model['name']);

        // 删除模型数据
        $res = $this->where('id', $id)->delete();
        if ($res) {
            $phinx_sql = $this->getLastSql();

            // 删除该表
            $sql = <<<sql
                DROP TABLE  IF EXISTS {$table_name};
sql;
            $res = M()::execute($sql);

            // 删除数据模型文件
            $this->delFile($model);
            D('common/Phinx')->buildMigratorBySql($phinx_sql);
        }
        return $res !== false;
    }

    public function requireFile($model = [], $return_dir = false)
    {
        $name = parse_name($model['name'], 1);
        $model['addon'] = parse_name($model['addon']);
        $app_path = SITE_PATH . '/app/';
        if (!empty($model['addon']) && $model['addon'] != 'core') {
            if (is_dir($app_path . $model['addon'])) {
                $dir = $app_path . $model['addon'] . '/data_table/';
            } else {
                $dir = $app_path . $model['addon'] . '/data_table/';
            }
        } else {
            $dir = $app_path . 'common/data_table/';
        }

        if ($return_dir) {
            return $dir;
        }

        $file = $dir . $name . 'Table.php';

        if (file_exists($file)) {
            return $file;
        } else {
            return false;
        }
    }

    public function delFile($model = [])
    {
        $file = $this->requireFile($model);
        $file === false || @unlink($file);
    }

    public function getFileInfo($model)
    {
        $type = gettype($model);
        if ($type != 'array' && $type != 'object') {
            if (is_numeric($model)) {
                $model = $this->where('id', $model)->find();
            } else {
                $map['name'] = $model;
                $model = $this->where($map)->find();
            }
        }

        $file = $this->requireFile($model);
        if ($file === false) {
            return false;
        }
        if (strpos($file, 'TitleTable')) {
            file_log($file, 'files');
        }
        require_once $file;

        $name = parse_name($model['name'], 1);
        $class = $name . 'Table';

        $obj = new $class();
        // 补充默认字段后再输出
        foreach ($obj->list_grid as $n => &$g) {
            $g['name'] = $n;
            isset($g['function']) || $g['function'] = '';
            isset($g['width']) || $g['width'] = '';
            isset($g['is_sort']) || $g['is_sort'] = 0;
            isset($g['raw']) || $g['raw'] = 0;
            isset($g['come_from']) || $g['come_from'] = 0;
            isset($g['href']) || $g['href'] = [];
            if (!empty($g['href'])) {
                foreach ($g['href'] as &$h) {
                    isset($h['show_set']) || $h['show_set'] = [];
                }
            }
            isset($g['can_edit']) || $g['can_edit'] = 0;
        }
        foreach ($obj->fields as $n => &$f) {
            $f = $this->fillField($n, $f);
        }
        $obj->datatable_path = $file;
        return $obj;
    }

    public function filterFields($fields)
    {
        foreach ($fields as $n => &$f) {
            $f = $this->fillField($n, $f);
        }
        return $fields;
    }

    public function fillField($n, $f)
    {
        $f['name'] = $n;
        isset($f['title']) || $f['title'] = $n;
        isset($f['field']) || $f['field'] = '';
        isset($f['value']) || $f['value'] = '';
        isset($f['placeholder']) || $f['placeholder'] = '请输入内容';
        isset($f['remark']) || $f['remark'] = '';
        isset($f['is_show']) || $f['is_show'] = 0;
        isset($f['is_must']) || $f['is_must'] = 0;
        if (isset($f['extra'])) {
            $f['extra'] = str_replace("：", ":", $f['extra']);
        } else {
            $f['extra'] = '';
        }

        if (empty($f['value']) && strpos($f['field'], 'int') !== false) {
            $f['value'] = 0;
        }

        if (!empty($f['extra']) && isset($GLOBALS['extra_data']) && !empty($GLOBALS['extra_data'])) {
            $original_data = $GLOBALS['extra_data'];
            $f['extra'] = preg_replace_callback('/\[([a-z_]+)\]/', function ($match) use ($original_data) {
                return isset($original_data[$match[1]]) ? $original_data[$match[1]] : '';
            }, $f['extra']);
        }

        isset($f['validate_type']) || $f['validate_type'] = 'regex';
        isset($f['validate_rule']) || $f['validate_rule'] = '';
        isset($f['validate_time']) || $f['validate_time'] = 3;
        isset($f['error_info']) || $f['error_info'] = '';

        isset($f['auto_rule']) || $f['auto_rule'] = '';
        isset($f['auto_time']) || $f['auto_time'] = 3;
        isset($f['auto_type']) || $f['auto_type'] = 'function';

        if (isset($f['type']) && $f['type'] == 'file') {
            isset($f['validate_file_exts']) || $f['validate_file_exts'] = '';
            isset($f['validate_file_size']) || $f['validate_file_size'] = 10485760;
        }
        isset($f['show_set']) || $f['show_set'] = [];

        return $f;
    }

    private function dealFieldStr($fields)
    {
        $fieldsArr = [];

        if (is_array($fields)) {
            foreach ($fields as $fname => $f) {
                unset($f['id'], $f['name'], $f['update_time'], $f['create_time'], $f['model_name'], $f['model_id'], $f['status']);
                if (empty($f['auto_rule'])) {
                    unset($f['auto_rule'], $f['auto_time'], $f['auto_type']);
                }
                if (empty($f['validate_rule'])) {
                    unset($f['validate_rule'], $f['validate_time'], $f['error_info'], $f['validate_type']);
                }
                foreach ($f as $k => $i) {
                    if (($k != 'value' && empty($i)) || ($k == 'value' && $i === '') || $i === '请输入内容') {
                        unset($f[$k]);
                    }
                }
                $fieldsArr[$fname] = $f;
            }
        }

        return $this->wp_var_export($fieldsArr, 1);
    }

    public function buildFile($model, $fields = null, $list_grid = null, $config = null)
    {
        // dump ( $model );dump ( $fields );dump ( $list_grid );dump ( $config );
        if (empty($model) || ($fields === null && $list_grid === null && $config === null)) {
            return false;
        }
        $type = gettype($model);
        if ($type != 'array' && $type != 'object') {
            $model = $this->where('id', $model)->find();
        }

        $old = $this->getFileInfo($model);
        // dump($old);
        if ($old !== false) {
            $fields === null && $fields = $old->fields;
            $list_grid === null && $list_grid = $old->list_grid;
            $config === null && $config = $old->config;
        }
        if (empty($config)) {
            $config = $model;
        }
        $dir = $this->requireFile($config, true);
        $this->checkDataTablesDir($dir);

        $name = parse_name($config['name'], 1);
        // dump($config);exit;
        $configStr = $this->wp_var_export($config, 1);
        // dump ( $list_grid );
        // exit ();
        foreach ($list_grid as $k => $a) {
            if (empty($a['function']))
                unset($list_grid[$k]['function']);
            if (empty($a['width']))
                unset($list_grid[$k]['width']);
            if (empty($a['is_sort']))
                unset($list_grid[$k]['is_sort']);
            if (empty($a['raw']))
                unset($list_grid[$k]['raw']);
            if (empty($a['come_from']))
                unset($list_grid[$k]['come_from']);
            if (empty($a['href']) || $a['href'] == '[ ]')
                unset($list_grid[$k]['href']);
            if (isset($a['name']))
                unset($list_grid[$k]['name']);
        }

        $list_grid_str = $this->wp_var_export($list_grid, 1);
//dump($list_grid_str);exit;
        $fieldsStr = $this->dealFieldStr($fields);
        $content = <<<str
<?php
/**
 * {$name}数据模型
 */
class {$name}Table {
    // 数据表模型配置
    public \$config = {$configStr};

    // 列表定义
    public \$list_grid = {$list_grid_str};

    // 字段定义
    public \$fields = {$fieldsStr};   
}
str;
        $content = str_replace('&nbsp;', '  ', $content);
        // dump ( $content );
        // exit ();
        file_put_contents($dir . $name . 'Table.php', $content);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($dir . $name . 'Table.php');
        }
        if (parse_name_lower($model['addon']) != parse_name_lower($config['addon'])) {
            // 删除旧文件
            $file = $this->requireFile($model);
            @unlink($file);
        }

        // 更新model数据库
        if (isset($model['id']) && !empty($model['id'])) {
            $this->where('id', $model['id'])->save($config);
        } else {
            $this->insertGetId($config);
        }
        $sql = $this->getLastSql();
        D('common/Phinx')->buildMigratorBySql($sql);

        return true;
    }

    public function buildConfigFile($app, $fields = null, $old = [])
    {
        // dump ( $model );dump ( $fields );dump ( $list_grid );dump ( $config );
        if (empty($app) || $fields === null) {
            return false;
        }

        $fieldsStr = $this->dealFieldStr($fields);
        $content = <<<str
<?php
//{$app}配置文件
return {$fieldsStr};
str;
        $content = str_replace('&nbsp;', '  ', $content);
        // dump ( $content );
        // exit ();
        return file_put_contents(SITE_PATH . '/app/' . $app . '/config.php', $content);
    }

    public function wp_var_export($arr, $count)
    {
        if (empty($arr))
            return '[ ]';

        $html = $space = '';
        for ($i = 0; $i < $count; $i++) {
            $space .= '&nbsp;';
        }
        foreach ($arr as $k => $v) {
            $v_html = '';
            if (is_array($v)) {
                $v = $this->wp_var_export($v, $count + 2);
                if (empty($v)) {
                    $v_html = "'{$k}' => [ ]," . PHP_EOL;
                } else {
                    $v_html = "'{$k}' => {$v}," . PHP_EOL;
                }
            } elseif (is_numeric($v)) {
                $v_html = "'{$k}' => {$v}," . PHP_EOL;
            } else {
                $v = addslashes($v);
                $v_html = "'{$k}' => '{$v}'," . PHP_EOL;
            }

            $html .= $space . '&nbsp;&nbsp;' . $v_html;
        }

        if (empty($html)) {
            return '[ ]';
        } else {
            $html = trim($html);
            $html = rtrim($html, ",");
            $html = '[' . PHP_EOL . $html . PHP_EOL . $space . ']';
        }
        return $html;
    }

    public function checkDataTablesDir($dir)
    {
        if (is_dir($dir)) {
            return true;
        }

        return mkdirs($dir);
    }

    /**
     * 检查同一张表是否有相同的字段
     *
     * @author huajie <banhuajie@163.com>
     */
    public function checkName()
    {
        $name = I('post.name');
        $model_id = I('post.model_id');
        $id = I('post.id');
        $map = array(
            'name' => $name,
            'model_id' => $model_id
        );
        if (!empty($id)) {
            $map['id'] = array(
                '<>',
                $id
            );
        }
        $res = $this->where($map)->find();
        return empty($res);
    }

    /**
     * 检查当前表是否存在
     *
     * @param intger $model_id
     *            模型id
     * @return intger 是否存在
     * @author huajie <banhuajie@163.com>
     */
    public function checkTableExist($model)
    {
        // 当前操作的表
        if (is_numeric($model)) {
            $name = $this->where([
                'id' => $model
            ])->value('name');
        } elseif (isset($model['name'])) {
            $name = $model['name'];
        } else {
            $name = $model;
        }

        $table_name = $this->table_name = DB_PREFIX . strtolower($name);
        $sql = "SHOW TABLES LIKE '{$table_name}'";
        $res = M()::query($sql);
        return count($res);
    }

    /**
     * 新建表字段
     *
     * @param array $field
     *            需要新建的字段属性
     * @return boolean true 成功 ， false 失败
     * @author huajie <banhuajie@163.com>
     */
    public function addField($field)
    {
        $model_info = M('model')->field('engine_type,need_pk,name,title')->getById($field['model_id']);
        // 检查表是否存在
        $table_exist = $this->checkTableExist($model_info);

        list($default, $type, $param) = $this->dealMigrator($field);

        $change = "->addColumn('{$field['name']}', '{$type}', {$param})";
        $table_param = '';
        if ($table_exist) {
            $sql = "ALTER TABLE `{$this->table_name}` ADD COLUMN `{$field['name']}`  {$field['field']} {$default} COMMENT '{$field['title']}'";
            $change .= "->update();";
        } else {
            $change .= "->create();";
            $sql = "CREATE TABLE IF NOT EXISTS `{$this->table_name}` (" . PHP_EOL;
            if ($model_info['need_pk']) {
                $sql .= "`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
                `{$field['name']}`  {$field['field']} {$default} COMMENT '{$field['title']}' ,
                PRIMARY KEY (`id`)";
            } else {
                $sql .= "`{$field['name']}`  {$field['field']} {$default} COMMENT '{$field['title']}'";
            }
            $sql .= PHP_EOL . ")ENGINE={$model_info['engine_type']} DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci COMMENT = '{$model_info['title']}' CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;";
            $table_param = D('common/Phinx')->tableParam($model_info['need_pk'] == 1, $model_info['engine_type'], $model_info['title']);
        }
        $table = $this->phinxTable($model_info['name'], $table_param);
        $res = M()::execute($sql);
        if ($res !== false) {
            $this->buildMigrator($model_info, $field, $table . $change);
        }
        return $res !== false;
    }

    function dealMigrator($field)
    {
        // 获取默认值
        $default = '';
        if ($field['value'] === '') {
        } elseif (is_numeric($field['value']) || $field['value'] == 'CURRENT_TIMESTAMP') {
            $default = ' DEFAULT ' . $field['value'];
        } elseif (is_string($field['value'])) {
            $default = " DEFAULT '{$field['value'] }'";
        }
        $param = [];
        // 获取默认值
        if ($default) {
            $param['default'] = $field['value'];
        }
        $null = '';
        if (stripos($field['field'], 'not') === false) {
            $param['null'] = true;
        }
        $limit = '';
        if (stripos($field['field'], '(') != false) {
            $arr = explode('(', $field['field']);
            if (stripos($arr[1], ',') != false) {
                $arr2 = explode(',', $arr[1]);
                $param['precision'] = intval($arr2[0]);
                $param['scale'] = intval($arr2[1]);
            } else {
                $param['limit'] = intval($arr[1]);
            }
        } else {
            $arr = wp_explode($field['field']);
        }
        $param['comment'] = $field['title'];
        $type = D('common/Phinx')->mysqlType($arr[0]);

        return [$default, $type, str_replace(PHP_EOL, '', var_export($param, true))];
    }

    function buildMigrator($model_info, $field, $phinx)
    {
        $time = date("YmdHis");
        $fileName = $model_info['name'] . '_' . $field['name'] . $time . rand(10, 99);
        return D('common/Phinx')->buildMigrator($phinx, $fileName);
    }

    /**
     * 更新表字段
     *
     * @param array $field
     *            需要更新的字段属性
     * @return boolean true 成功 ， false 失败
     * @author huajie <banhuajie@163.com>
     */
    public function updateField($field, $old)
    {
        $model_info = M('model')->field('engine_type,need_pk,name')->getById($field['model_id']);
        // 检查表是否存在
        $table_exist = $this->checkTableExist($model_info);
        if (!$table_exist) return false;

        // 获取原字段名
        $last_field = $old['name'];

        list($default, $type, $param) = $this->dealMigrator($field);
        $sql = <<<sql
            ALTER TABLE `{$this->table_name}` CHANGE COLUMN `{$last_field}` `{$field['name']}`  {$field['field']} {$default} COMMENT '{$field['title']}' ;
sql;
        $res = M()::execute($sql);
        if ($res !== false) {
            $phinx = $this->phinxTable($model_info['name']);
            if ($last_field != $field['name']) {
                $phinx .= "->renameColumn('{$last_field}', '{$field['name']}')" . PHP_EOL;
            }
            $phinx .= "->changeColumn('{$field['name']}', '{$type}', {$param})->update();";
            $this->buildMigrator($model_info, $field, $phinx);
        }
        return $res !== false;
    }

    /**
     * 删除一个字段
     *
     * @param array $field
     *            需要删除的字段属性
     * @return boolean true 成功 ， false 失败
     * @author huajie <banhuajie@163.com>
     */
    public function deleteField($field, $model_id)
    {
        $model_info = M('model')->field('engine_type,need_pk,name')->getById($model_id);
        // 检查表是否存在
        $table_exist = $this->checkTableExist($model_info);

        $sql = <<<sql
            ALTER TABLE `{$this->table_name}` DROP COLUMN `{$field['name']}`;
sql;
        $res = M()::execute($sql);
        if ($res !== false) {
            $phinx = $this->phinxTable($model_info['name']) . "->removeColumn('{$field['name']}')->update()";
            $this->buildMigrator($model_info, $field, $phinx);
        }
        return $res !== false;
    }

    private function phinxTable($table, $param = '')
    {
        $table = DB_PREFIX . $table;
        return $param ? "\$this->table('{$table}', {$param})" : "\$this->table('{$table}')";
    }

    public function parseExtra($extra, $val = null)
    {
        $arr = parse_field_attr($extra);

        if ($val !== null) {
            return isset($arr[$val]) ? $arr[$val] : '';
        } else {
            return $arr;
        }
    }

    function buildFileByData($data)
    {
        $config = [
            'name' => $data['name'],
            'title' => $data['title'],
            'search_key' => isset($data['search_key']) ? $data['search_key'] : '',
            'add_button' => isset($data['add_button']) ? $data['add_button'] : 1,
            'del_button' => isset($data['del_button']) ? $data['del_button'] : 1,
            'search_button' => isset($data['search_button']) ? $data['search_button'] : 1,
            'check_all' => isset($data['check_all']) ? $data['check_all'] : 1,
            'list_row' => isset($data['list_row']) ? $data['list_row'] : 20,
            'addon' => parse_name_lower($data['addon']),
            'inherit' => isset($data['inherit']) ? $data['inherit'] : ''
        ];

        // dump ( $config );
        $list_grid = [];
        $fields = null;
        if (isset($data['attr_title'])) {
            $j = 0;
            foreach ($data['attr_title'] as $k => $vo) {
                if (!empty($vo)) {
                    $res = [];
                    $res['title'] = $vo;
                    $res['come_from'] = $from = $data['come_from'][$k];
                    $res['width'] = $data['width'][$k];
                    $res['can_edit'] = $data['can_edit'][$k];
                    if ($from == 1) {
                        $name = $j == 0 ? 'urls' : 'urls' . $j;
                        $j++;

                        $res['is_sort'] = 0;
                        $res['href'] = [];
                        if (isset($data['url_title'][$k])) {
                            foreach ($data['url_title'][$k] as $kk => $vv) {
                                $title = $vv;
                                $url = $data['url_url'][$k][$kk];
                                if (!empty($title) && !empty($url)) {
                                    $arr = ['title' => $title, 'url' => $url];

                                    $show = $data['show_set'][$k][$kk];
                                    if (!empty($show)) {
                                        $arr['show_set'] = json_decode(urldecode($show), true);
                                    }
                                    $class = $data['class'][$k][$kk];
                                    if (!empty($class)) {
                                        $arr['class'] = $class;
                                    }

                                    $res['href'][] = $arr;
                                }
                            }
                        }
                    } else {
                        $name = $data['field'][$k];
                        $res['is_sort'] = $data['is_sort'][$k];
                    }

                    $list_grid[$name] = $res;
                }
            }
        } elseif (!empty($config['inherit'])) {
            $meta_table = $this->getFileInfo($config['inherit']);
            $list_grid = $meta_table->list_grid;
            $fields = $meta_table->fields;
        }
        //dump($list_grid);exit;
        $res = $this->buildFile($data, $fields, $list_grid, $config);
        return $res;
    }

    function fieldData($field)
    {
        $data = [
            'wpid' => [
                "title" => "平台账号ID",
                "type" => "num",
                "field" => "int(10) NULL",
                "value" => "0",
                "remark" => "区分平台账号之间的数据",
                "auto_rule" => "get_wpid",
                "auto_time" => "1"
            ],
            'create_at' => [
                "title" => "创建时间",
                "type" => "datetime",
                "field" => "int(10) NULL",
                "auto_rule" => "time",
                "auto_time" => "1"
            ],
            'update_at' => [
                "title" => "更新时间",
                "type" => "datetime",
                "field" => "int(10) NULL",
                "auto_rule" => "time",
                "auto_time" => "2"
            ],
            'uid' => [
                "title" => "用户",
                "type" => "user",
                "field" => "int(10) NULL",
                "auto_rule" => "get_mid",
                "auto_time" => "1"
            ],
            'status' => [
                "title" => "状态",
                "type" => "bool",
                "field" => "tinyint(2) NULL",
                "extra" => "0:禁用\r\n1:启用",
                "value" => "0",
                "is_show" => "1",
            ],
            'type' => [
                "title" => "类型",
                "type" => "radio",
                "field" => "tinyint(2) NULL",
                "extra" => "0:分类A\r\n1:分类B",
                "value" => "0",
                "is_show" => "1",
            ],
            'title' => [
                "title" => "标题",
                "type" => "string",
                "field" => "varchar(255) NULL",
                "is_show" => "1",
                "is_must" => "1"
            ],
            'content' => [
                "title" => "内容",
                "type" => "editor",
                "field" => "text  NULL",
                "is_show" => "1",
                "is_must" => "1",
            ],
            'cover' => [
                "title" => "封面",
                "type" => "picture",
                "field" => "varchar(100) NULL",
                "is_show" => "1",
                "is_must" => "1",
            ],
            'sort' => [
                "title" => "排序号",
                "type" => "num",
                "field" => "int(10) NULL",
                "remark" => "数值越小越靠前",
                "is_show" => "1",
            ],
            'remark' => [
                "title" => "备注",
                "type" => "textarea",
                "field" => "text NULL",
                "is_show" => "1",
            ],
            'delelte_at' => [
                "title" => "删除时间",
                "type" => "datetime",
                "field" => "int(10) NULL"
            ]
        ];
        if ($field == null) return $data;

        if (!isset($data[$field])) return [];

        return $this->fillField($field, $data[$field]);
    }
}
