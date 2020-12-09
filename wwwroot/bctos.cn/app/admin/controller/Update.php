<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星
// +----------------------------------------------------------------------
namespace app\admin\controller;

use QL\QueryList;

/**
 * 在线升级控制器
 */
define('DATA_PATH', SITE_PATH . '/runtime/');

class Update extends Admin
{
    public function index()
    {
        return $this->fetch();
    }

    // 清空缓存
    function delcache()
    {
        $cahce_dirs = SITE_PATH . '/runtime';
        $this->rmdirr($cahce_dirs);

        @mkdir($cahce_dirs, 0777, true);
        return $this->fetch();
    }

    function getRemoteVersion()
    {
        //远程最新版本号
        $remote_tag = QueryList::get(GITEE_URL)->find('.tag-name a')->title;
        if (!$remote_tag) {
            $remote_tag = QueryList::get(GITHUB_URL)->find('.commit-title a')->eq(0)->text();
        }
        $db_remote = db_config('remote_tag');
        if (!$remote_tag) {
            $local_tag = $db_remote;
        } elseif ($remote_tag != $db_remote) {
            db_config('remote_tag', $remote_tag);
        }

        $local_tag = ssh2('cd ' . SITE_PATH . "; git tag | awk 'END {print}'");
        $db_local = db_config('local_tag');
        if (!$local_tag) {
            $local_tag = $db_local;
        } elseif ($local_tag != $db_local) {
            db_config('local_tag', $local_tag);
        }

        if ($remote_tag != $local_tag && cookie('cookie_close_version') != $remote_tag) {
            //需要提示更新
            echo $remote_tag;
        } else {
            echo 0;
        }
    }

    // 获取关闭升级提醒
    public function set_cookie_close_version()
    {
        $cookie_close_version = intval($_GET ['cookie_close_version']);
        cookie('cookie_close_version', $cookie_close_version);
    }

    // =========================一键升级功能==========================================
    function initialize()
    {
        parent::initialize();
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
    }

    // 查询是否有更新版本
    function step01_checkVersionByAjax()
    {
        //优先从gitee上获取
        $rules = [
            'version' => ['.tag-name>a', 'title'],
            'desc' => ['.tag-message', 'text'],
            'time' => ['.tag-item-action-row:eq(1)', 'text']
        ];
        $rt = QueryList::get(GITEE_URL)->rules($rules)->range('.tag-list .tag-item')->query()->getData();
        $list = $rt->all();
        if (empty($list)) {
            $rules = [
                'version' => ['.commit-title>a', 'text'],
                'desc' => ['.commit-desc>.text-gray', 'text'],
                'time' => ['.no-wrap', 'text']
            ];
            $rt = QueryList::get(GITHUB_URL)->rules($rules)->range('.Box .Box-row')->query()->getData();
            $list = $rt->all();
        }
        $local_tag = db_config('local_tag');

        $count = 0;
        foreach ($list as &$v) {
            if (version_compare($local_tag, $v['version'], 'ge')) {
                $v['status'] = '<span style="color: #999">已更新</span>';
            } else {
                $count += 1;
                $v['status'] = '<span style="color: #F00">待更新</span>';
            }
        }
        $this->assign('count', $count);
        $this->assign('_list', $list);

        return $this->fetch();
    }

    // 下载更新包
    function step02_download()
    {
        header("content-Type: text/html; charset=utf-8");

        $version = I('post.version');

        require_once(SITE_PATH . '/vendor/Update.php');
        $updateClass = new \Update ();

        $packageURL = $this->remoteBaseURL . '/index.php?s=/home/index/download_update_package.html&version=' . $version;

        echo $updateClass->downloadFile($packageURL);
    }

    // 解压更新包
    function step03_unzipPackage()
    {
        require_once(SITE_PATH . '/vendor/Update.php');
        $updateClass = new \Update ();

        $version = I('version');
        echo $updateClass->unzipPackage($version);
    }

    // 检查要覆盖的文件的可写权限和md5码
    function step04_checkFileIsWritable()
    {
        $list = $this->_checkFileIsWritable();
        if (empty ($list)) {
            echo 1;
            exit ();
        }

        // 删除更新锁
        $version = I('version');
        $lockName = DATA_PATH . '/update/' . str_replace('.zip', '.lock', $version);
        @unlink($lockName);

        $this->assign('list', $list);
        return $this->fetch();
    }

    // 关闭站点，并设置关闭原因
    function closeSite()
    {
        $data = model('Xdata')->get('admin_Config:site');

        $config ['site_closed'] = $data ['site_closed'];
        $config ['site_closed_reason'] = $data ['site_closed_reason'];

        // 保存当前站点的配置关闭原因
        S('site_config', $config);

        $data ['site_closed'] = 0;
        $data ['site_closed_reason'] = '站点升级中...请稍后再访问。';

        model('Xdata')->put('admin_Config:site', $data);
    }

    // 恢复升级前的站点配置
    function openSite()
    {
        $config = S('site_config', null);
        if (empty ($config)) {
            return false;
        }

        $data = model('Xdata')->get('admin_Config:site');
        $data ['site_closed'] = $config ['site_closed'];
        $data ['site_closed_reason'] = $config ['site_closed_reason'];

        model('Xdata')->put('admin_Config:site', $data);
    }

    // 清除文件缓存
    function cleanCache()
    {
        $this->_rmdirr(CORE_RUN_PATH . '/');
    }

    // 自动更新数据库
    function step07_dealSQL()
    {
        // $this->closeSite();
        $filePath = $targetDir = DATA_PATH . '/update/download/unzip/updateDB.php';
        if (!file_exists($filePath)) { // 如果本次升级没有数据库的更新，直接返回
            echo 1;
            exit ();
        }

        require_once($filePath);
        updateDB();
        @unlink($filePath);

        // 数据库验证
        $filePath = $targetDir = DATA_PATH . '/update/download/unzip/checkDB.php';
        if (!file_exists($filePath)) { // 如果本次升级没有数据库的更新后的验证代码，直接返回
            echo 1;
            exit ();
        }

        require_once($filePath);
        // checkDB方法正常返回1 否则返回异常的说明信息，如：ts_xxx数据表创建不成功
        checkDB();

        @unlink($filePath);
        echo 1;
    }

    // 递归检查文件的可写权限和md5
    private function _checkFileIsWritable($source = '', $res = [])
    {
        if (empty ($source))
            $source = DATA_PATH . '/update/download/unzip';

        $handle = dir($source);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                $file = $source . "/" . $entry;
                if (is_dir($file)) {
                    $res = $this->_checkFileIsWritable($file, $res);
                } else {
                    // 检查可写权限
                    if (!is_writable($file)) {
                        $res [] = $file;
                    }
                    // 检查文件md5
                }
            }
        }

        return $res;
    }

    // 自动覆盖文件
    function step06_overWritten()
    {
        // 提示需要删除的文件
        $filePath = $targetDir = DATA_PATH . '/update/download/unzip/fileForDeleteList.php';
        if (file_exists($filePath)) {
            $deleteList = require_once($filePath);
            foreach ($deleteList as $d) {
                @unlink(SITE_PATH . '/' . $d);
            }
            @unlink($filePath);
        }

        // 执行文件替换
        tsload(ADDON_PATH . '/library/Update.class.php');
        $updateClass = new Update ();
        $res = $updateClass->overWrittenFile();
        if (!empty ($res ['error'])) {
            $this->assign('error', $res ['error']);
            return $this->fetch();
        } else {
            echo 1;
        }
    }

    function step08_finishUpate()
    {
        // 清除缓存
        $this->cleanCache();

        // 开启站点
        $this->openSite();

        // 更新本地版本号信息
        $this->_updateFinishVersionStatus();

        // 如果是一键升级的话
        if ($_SESSION ['admin_update_upateAll'] == true) {
            echo 1;
        } else {
            echo 0;
        }
    }

    // 写入当前版本信息
    private function _writeVersion($key, $arr)
    {
        $path = DATA_PATH . '/update';
        $arr ['status'] = 0; // 未升级状态

        $versionArr = $this->_getVersionInfo($path);
        $versionArr [$key] = $arr;

        S('versions', $versionArr);

        return $versionArr;
    }

    private function _updateVersionStatus($key)
    {
        $path = DATA_PATH . '/update';
        $versionArr = $this->_getVersionInfo($path);

        foreach ($versionArr as $k => &$vo) {
            if ($k != $key)
                continue;

            $vo ['status'] = 1; // 升级中的状态
        }

        S('versions', $versionArr);
    }

    private function _updateFinishVersionStatus()
    {
        $path = DATA_PATH . '/update';
        $versionArr = $this->_getVersionInfo($path);

        foreach ($versionArr as $k => &$vo) {
            if ($vo ['status'] != 1)
                continue;

            $vo ['status'] = 2; // 升级完成的状态
        }

        S('versions', $versionArr);
    }

    private function _getVersionInfo($path)
    {
        $file = $path . '/versions.php';

        $versionArr = [];
        if (file_exists($file)) {
            $versionArr = S('versions', null);
        }

        return $versionArr;
    }

    private function _rmdirr($dirname)
    {
        if (!file_exists($dirname)) {
            return false;
        }
        if (is_file($dirname) || is_link($dirname)) {
            return @unlink($dirname);
        }
        $dir = dir($dirname);
        if ($dir) {
            while (false !== $entry = $dir->read()) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $this->_rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
            }
        }
        $dir->close();
        return rmdir($dirname);
    }

    private function _createtable($sql, $db_charset)
    {
        $db_charset = (strpos($db_charset, '-') === FALSE) ? $db_charset : str_replace('-', '', $db_charset);
        $type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
        $type = in_array($type, array(
            "MYISAM",
            "HEAP"
        )) ? $type : "MYISAM";
        return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql) . (mysql_get_server_info() > "4.1" ? " ENGINE=$type DEFAULT CHARSET=$db_charset" : " TYPE=$type");
    }

    private function _updataStorey()
    {
        $map ['data'] = array(
            '<>',
            'N;'
        );
        $commentlist = find_data(D('comment')->where(wp_where($map))->select());
        foreach ($commentlist as $v) {
            $data = unserialize($v ['data']);
            if ($data ['storey']) {
                D('comment')->where('comment_id=' . $v ['comment_id'])->update(['storey' => $data ['storey']]);
            }
        }
    }

    function md5File()
    {
        ini_set();
        $res = $this->_md5File();
    }

    private function _md5File($source = '.', $res = [])
    {
        $handle = dir($source);

        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                $file = $source . "/" . $entry;
                if (is_dir($file)) {
                    $this->_md5File($file, $res);
                } else {
                    $data ['version'] = 221301;
                    $data ['file'] = str_replace('./', '', $file);
                    $data ['md5'] = md5_file($file);
                    M('file_version')->insert($data);
                }
            }
        }

        return $res;
    }

    /**
     * 本地一键安装接口
     *
     * @return void
     */
    public function downloadAndInstall()
    {
        header("content-Type: text/html; charset=utf-8");
        // 获取下载地址
        $id = id();
        $uid = input('uid');
        $url = $this->remoteBaseURL . '/index.php?s=home/index/downloadApp/id/' . $id . '/uid/' . $uid;
        //dump($url);
//        exit;
        $info = wp_file_get_contents($url);
        //dump($info);
        $info = json_decode($info, true);
        //dump($info);
        //exit;
        if (!$info ['status']) {
            return $this->error($info ['error']);
        }

        // 载入下载类
        require_once(SITE_PATH . '/vendor/Update.php');
        $updateClass = new \Update ();
        // 从服务器端下载应用到本地
        $res = $updateClass->downloadFile($info ['data'] ['packageURL']);
        if ($res != 1) {
            return $this->error('下载应用失败，请确认网络是否正常');
        }

        // 压缩
        $package = explode('/', $info ['data'] ['packageURL']);
        $packageName = array_pop($package);
        $targetDir = $updateClass->downloadPath . 'unzip';

        // 创建目录unzip
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777);
        }
        $res = $updateClass->unzipPackage($packageName, $targetDir);
        if ($res != 1) {
            return $this->error('下载应用解压失败');
        }

        // 覆盖代码
        switch ($info ['data'] ['type']) {
            case 3 :
                // 万能页面功能块
                $res = $updateClass->overWrittenFile(SITE_PATH . '/app/diy/Widget');
                break;
            case 2 :
                // 在线素材
                $res = $updateClass->overWrittenFile(SITE_PATH . '/app/material');
                break;
            case 2 :
                // 微官网模板
                $res = $updateClass->overWrittenFile(SITE_PATH . '/app/wei_site/view');
                break;
            default :
                // 应用应用
                $res = $updateClass->overWrittenFile(SITE_PATH . '/app');
                if (empty ($res)) {
                    $this->install($updateClass->addon_name);
                    exit ();
                }
        }
        return $this->success('安装完成');
    }

    /**
     * 自动安装应用
     */
    public function install($addon_name)
    {
        $class = get_addon_class($addon_name);
        if (!class_exists($class))
            return $this->error('应用不存在');
        $addons = new $class ();
        $info = $addons->info;
        if (!$info || !$addons->checkInfo()) // 检测信息的正确性
            return $this->error('应用信息缺失');
        session('addons_install_error', null);
        $install_flag = $addons->install();
        if (!$install_flag) {
            return $this->error('执行应用预安装操作失败' . session('addons_install_error'));
        }
        $addonsModel = D('Apps');
        $data = $info;
        if (!$data)
            return $this->error($addonsModel->getError());

        // isset($data['has_adminlist']) || $data['has_adminlist'] = intval(is_array($addons->admin_list) && $addons->admin_list !== []);
        isset ($data ['type']) || $data ['type'] = intval(file_exists(SITE_PATH . '/app/' . $data ['name'] . '/model/Service.php'));

        if ($addonsModel->insertGetId($data)) {
            $config = array(
                'config' => json_encode($addons->getConfig())
            );
            $addonsModel->where("name='{$addon_name}'")->update($config);
            $hooks_update = D('Hooks')->updateHooks($addon_name);
            if ($hooks_update) {
                return $this->success('安装成功');
            } else {
                $addonsModel->where("name='{$addon_name}'")->delete();
                return $this->error('更新钩子处应用失败,请卸载后尝试重新安装');
            }
        } else {
            return $this->error('写入应用数据失败');
        }
    }

    // 更新本地的授权信息
    function save_store_license()
    {
        $config_map ['name'] = 'WEIPHP_STORE_LICENSE';
        $res = M('config')->where(wp_where($config_map))->update(['value' => I('license')]);
    }

    function sshUpdate()
    {
        $commond = ['data' => 'sys_update'];
        $this->assign('commond', json_encode($commond));
        return $this->fetch('common@base/terminal');
    }
}
