<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星
// +----------------------------------------------------------------------
namespace app\admin\controller;

/**
 * 升级图片上传功能
 */
class PitureUpdate extends Admin
{
    public function index()
    {
        set_time_limit(0);
        //$this->step_1();
        $this->step_2();
//        $this->step_3();
//        $this->step_4();
    }

    function step_1()
    {
        //第一步
        $fileArr = $this->file_scan();
        file_put_contents(SITE_PATH . '/fileArr.txt', json_encode($fileArr));
        exit;
    }

    function step_2()
    {
        //第二步
        $fileArr = json_decode(file_get_contents(SITE_PATH . '/fileArr.txt'), true);

        $dao = D('common/Models');
        $updateFields1 = $updateFields2 = [];
        foreach ($fileArr as $file) {
            require_once $file;
            $pathinfo = pathinfo($file);
            $class = $pathinfo['filename'];

            $obj = new $class();
            $fields = $obj->fields;
            $need = false;
            $name = $obj->config['name'];
            $model = $dao->field(true)->where('name', $name)->find();
            foreach ($fields as $n => &$f) {
                $f = $dao->fillField($n, $f);
                $old = $f;
                $f['model_id'] = $model['id'];
                if ($f['type'] == 'picture') {
                    $need = true;
                    $f['field'] = $f['is_must'] == 1 ? 'varchar(100) NOT NULL DEFAULT ""' : 'varchar(100) NULL DEFAULT ""';
                    $dao->updateField($f, $old);
                    $f['field'] = $f['is_must'] == 1 ? 'varchar(100) NOT NULL' : 'varchar(100) NULL';
                    $updateFields1[$name][] = $n;
                } elseif ($f['type'] == 'mult_picture') {
                    $need = true;
                    $f['field'] = $f['is_must'] == 1 ? 'text NOT NULL' : 'text NULL';
                    $dao->updateField($f, $old);
                    $updateFields2[$name][] = $n;
                }
            }
            if ($need) {
                // 删除字段缓存文件

                $dao->buildFile($model, $fields);
            }
        }
        file_put_contents(SITE_PATH . '/updateFields1.txt', json_encode($updateFields1));
        file_put_contents(SITE_PATH . '/updateFields2.txt', json_encode($updateFields2));
        exit;
    }

    function step_3()
    {
        //第三步
        $lists = json_decode(file_get_contents(SITE_PATH . '/updateFields1.txt'), true);
        $imgs = M('picture')->column('path', 'id');
        //dump($imgs);
        foreach ($lists as $table => $fields) {
            $res = M()::query("SHOW TABLES LIKE 'wp_{$table}'");
            if (count($res) == 0) continue;

            $where = '1=1';
            foreach ($fields as $f) {
                $where .= " and {$f}>0";
            }

            $data = find_data(M($table)->where($where)->field(implode(',', $fields))->select());
            if (empty($data)) continue;

            $fieldData = [];
            foreach ($data as $d) {
                foreach ($fields as $f) {
                    empty($d[$f]) || $fieldData[$f][$d[$f]] = $d[$f];
                }
            }
            foreach ($fields as $f) {
                $sql = "UPDATE wp_{$table} SET {$f} = CASE $f ";
                foreach ($fieldData[$f] as $id) {
                    if (isset($imgs[$id])) {
                        $url = $imgs[$id];
                        $sql .= " WHEN {$id} THEN '{$url}'";
                    } else {
                        $sql .= " WHEN {$id} THEN ''";
                    }
                }

                $sql .= " END WHERE  {$f} > 0";
                //dump($sql);
                M()::execute($sql);
            }
        }
        exit;
    }

    function step_4()
    {
        $lists = json_decode(file_get_contents(SITE_PATH . '/updateFields2.txt'), true);
        $imgs = M('picture')->column('path', 'id');
        //dump($imgs);
        foreach ($lists as $table => $fields) {
            $res = M()::query("SHOW TABLES LIKE 'wp_{$table}'");
            if (count($res) == 0) continue;


            $data = find_data(M($table)->field(implode(',', $fields))->select());
            if (empty($data)) continue;

            $fieldData = [];
            foreach ($data as $d) {
                foreach ($fields as $f) {
                    if (empty($d[$f])) continue;
                    $ids = explode(',', $d[$f]);
                    $url = [];
                    foreach ($ids as $id) {
                        if (isset($imgs[$id])) {
                            $url[] = $imgs[$id];
                        }
                    }
                    $fieldData[$f][$d[$f]] = implode(',', $url);
                }
            }
            foreach ($fields as $f) {
                if(!isset($fieldData[$f])) continue;
                
                $sql = "UPDATE wp_{$table} SET {$f} = CASE $f ";
                foreach ($fieldData[$f] as $id => $url) {
                    $sql .= " WHEN '{$id}' THEN '{$url}'";
                }

                $sql .= " END WHERE  {$f} is not null";
                dump($sql);
                M()::execute($sql);
            }
        }
        exit;

    }

    // 遍历所有的模型文件
    public function file_scan()
    {
        $fileArr = [];

        $path = SITE_PATH . DIRECTORY_SEPARATOR . 'app';
        $dirfat = dir($path);
        while (false !== $entry = $dirfat->read()) {
            if ($entry == '.' || $entry == '..' || $entry == '.svn' || is_file($path . DIRECTORY_SEPARATOR . $entry)) {
                continue;
            }
            if (!is_dir($path . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'data_table')) {
                continue;
            }
            $fileDir = dir($path . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'data_table');
            while (false !== $file = $fileDir->read()) {
                if ($file == '.' || $file == '..' || $file == '.svn') {
                    continue;
                }
                $fileArr[] = $file_name = $path . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'data_table' . DIRECTORY_SEPARATOR . $file;
            }
            $fileDir->close();
        }

        $dirfat->close();

        return $fileArr;
    }
}
