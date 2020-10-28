<?php

namespace app\notice;
use app\common\controller\InfoBase;

/**
 * Notice应用
 */
class Info extends InfoBase{
    public $info = array(
            'name'=>'notice',
            'title'=>'微信支付回调',
            'description'=>'',
            'author'=>'凡星',
            'version'=>'0.1',
            'has_adminlist'=>1
        );

    //自定义权限规则
    public $auth_rule = [];

    //自定义积分规则
    public $credit_config = [];

    //自定义入口地址,默认是lists或者config
    public $init_url = [];

    function reply($dataArr, $keywordArr = []) {
        $config = getAppConfig ( 'notice' ); // 获取后台应用的配置参数
        //dump($config);
    }

    public function install() {
        $install_sql = SITE_PATH.'/app/notice/install.sql';
        if (file_exists ( $install_sql )) {
            execute_sql_file ( $install_sql );
        }
        return true;
    }
    public function uninstall() {
        $uninstall_sql = SITE_PATH.'/app/notice/uninstall.sql';
        if (file_exists ( $uninstall_sql )) {
            execute_sql_file ( $uninstall_sql );
        }
        return true;
    }
}
