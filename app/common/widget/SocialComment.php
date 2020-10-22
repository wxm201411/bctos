<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------


namespace app\common\widget;
use app\common\controller\base;

/**
 * 通用社交化评论插件
 * @author thinkphp
 */

    class SocialComment extends base{

        public $info = array(
            'name'=>'SocialComment',
            'title'=>'通用社交化评论',
            'description'=>'集成了各种社交化评论插件，轻松集成到系统中。',
            'status'=>1,
            'author'=>'thinkphp',
            'version'=>'0.1'
        );

        public function install(){
            return true;
        }

        public function uninstall(){
            return true;
        }

        //实现的pageFooter钩子方法
        public function documentDetailAfter($param){
            $this->assign('addons_config', $this->getConfig());
            return $this->fetch('common@widget/social_comment/comment');
        }
    }