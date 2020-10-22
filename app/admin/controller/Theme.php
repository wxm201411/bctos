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
 * 模型数据管理控制器
 *
 * @author 凡星
 */
class Theme extends Admin
{

    /**
     * 显示指定模型列表数据
     *
     * @param String $model
     *          模型标识
     * @author 凡星
     */
    public function lists()
    {
        $app = input('app');
        $this->assign('app', $app);

        $map['status'] = 1;
        $apps = M('apps')->where($map)->column('name,title,theme', 'name');
        $this->assign('apps', $apps);

        if ($app) {
            //只取某个应用的主题
            $theme_apps[$app] = $apps[$app];
        } else {
            //取全部主题
            $theme_apps = $apps;
        }

        $theme_lists = $this->getLocalTheme($theme_apps);
        $this->assign('theme_lists', $theme_lists);


        return $this->fetch();
    }

    //引用wordpress的方法，并兼容wordpress的变量输出
    private function getThemeInfo($file)
    {
        // We don't need to write to the file, so just open for reading.
        $fp = fopen($file, 'r');

        // Pull only the first 8kiB of the file in.
        $file_data = fread($fp, 8192);

        // PHP will close file handle, but we are good citizens.
        fclose($fp);

        // Make sure we catch CR-only line endings.
        $file_data = str_replace("\r", "\n", $file_data);


        $all_headers = [
            'Name' => 'Theme Name',
            'ThemeURI' => 'Theme URI',
            'Description' => 'Description',
            'Author' => 'Author',
            'AuthorURI' => 'Author URI',
            'Version' => 'Version',
            'Template' => 'Template',
            'Status' => 'Status',
            'Tags' => 'Tags',
            'TextDomain' => 'Text Domain',
            'DomainPath' => 'Domain Path'
        ];


        foreach ($all_headers as $field => $regex) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $file_data, $match) && $match[1]) {
                $all_headers[$field] = trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', ($match[1])));
            } else {
                $all_headers[$field] = '';
            }
        }

        return $all_headers;
    }

    private function getLocalTheme($apps)
    {
        $theme_lists = $top_lists = [];

        //搜索某个主题
        $key = input('key');

        $base_path = SITE_PATH . '/public/';
        foreach ($apps as $app) {
            $app_path = $base_path . $app['name'];
            if (!is_dir($app_path)) continue;

            $dir = dir($app_path);
            while (false !== $entry = $dir->read()) {
                //排除非目录
                if ($entry == '.' || $entry == '..' || $entry == '.svn' || !is_dir($app_path . '/' . $entry)) {
                    continue;
                }
                //判断目录下是否存在主题特有的info.php文件
                if (is_file($app_path . '/' . $entry . '/style.css')) {
                    $headers = $this->getThemeInfo($app_path . '/' . $entry . '/style.css');
                    //dump($headers);
                    if (isset($headers['Name']) && !empty($headers['Name'])) {
                        //搜索符合搜索条件的主题
                        if ($key && stripos($headers['Name'], $key) === false) {
                            continue;
                        }

                        $headers['AppName'] = $app['name'];
                        $headers['AppTitle'] = $app['title'];
                        $headers['Theme'] = $entry;
                        if (file_exists($app_path . '/' . $entry . '/screenshot.png')) {
                            $headers['ScreenShot'] = SITE_URL . '/' . $app['name'] . '/' . $entry . '/screenshot.png';
                        } else {
                            $headers['ScreenShot'] = SITE_URL . '/static/base/images/screenshot.png';
                        }
                        if ($entry == $app['theme']) {
                            $top_lists[] = $headers;
                        } else {
                            $theme_lists[] = $headers;
                        }
                    }
                }
            }

            $dir->close();
        }

        return array_merge($top_lists, $theme_lists);
    }

    //使用主题
    function doUser()
    {
        $theme = input('theme');
        $app = input('app');

        $res = M('apps')->where('name', $app)->update(['theme' => $theme]);

        if ($res !== false) {
            return $this->success('设置成功');
        } else {
            return $this->error('设置失败');
        }
    }

    //删除主题
    function del()
    {
        $theme = input('theme');
        $app = input('app');

        //如果当前正在使用该主题，先切换回默认的主题
        M('apps')->where('name', $app)->where('theme', $theme)->update(['theme' => '']);

        //删除主题目录
        rmdirr(SITE_PATH . '/public/' . $app . '/' . $theme);

        return $this->success('删除完成');
    }
}
