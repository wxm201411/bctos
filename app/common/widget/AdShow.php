<?php

namespace app\common\widget;

use app\common\controller\base;

/**
 * 广告栏插件
 *
 * @author 凡星
 */
class AdShow extends base
{
    public $info = array(
        'name' => 'AdShow',
        'title' => '广告栏',
        'description' => '可以在后台定制前台显示的广告',
        'status' => 1,
        'author' => '凡星',
        'version' => '0.1',
        'has_adminlist' => 1
    );

    public function install()
    {
        $install_sql = './Plugins/AdShow/install.sql';
        if (file_exists($install_sql)) {
            execute_sql_file($install_sql);
        }
        return true;
    }

    public function uninstall()
    {
        $uninstall_sql = './Plugins/AdShow/uninstall.sql';
        if (file_exists($uninstall_sql)) {
            execute_sql_file($uninstall_sql);
        }
        return true;
    }

    // 实现的show_ad_space钩子方法
    public function show_ad_space($param)
    {
        // dump($param);
        // 获取位置广告信息
        $place = safe($param ['place']);
        $placeInfo = $this->_config($place);
        isset($placeInfo ['height']) || $placeInfo ['height'] = '';
        isset($placeInfo ['bottom']) || $placeInfo ['bottom'] = '';
        isset($placeInfo ['height']) || $placeInfo ['height'] = '';
        $data = $this->getAdSpaceByPlace($placeInfo ['id']);
        foreach ($data as &$value) {
            if ($value ['type'] == 3) {
                $value ['content'] = empty($value ['content']) ? [] : unserialize($value ['content']);
                // 获取附件图片地址
                foreach ($value ['content'] as &$val) {
                    if ($placeInfo ['width'] && $placeInfo ['height']) {
                        $val ['bannerpic'] = get_cover_url($val ['image'], $placeInfo ['width'], $placeInfo ['height']);
                    } else {
                        $val ['bannerpic'] = get_cover_url($val ['image']);
                    }
                }
            }
        }
        // dump($data);
        $this->assign('data', $data);
        // 设置宽度
        $width = intval($placeInfo ['width']);
        $this->assign('width', $width);
        // 设置高度
        $height = intval($placeInfo ['height']);
        $this->assign('height', $height);
        // 设置距离顶端距离
        $top = intval($placeInfo ['top']);
        $bottom = intval($placeInfo ['bottom']);
        $this->assign('top', $top);
        $this->assign('bottom', $bottom);
        // 增加自定义广告显示模板功能
        $tpl = isset($placeInfo ['tpl']) ? $placeInfo ['tpl'] : 'show_ad_space';
        $tpl = isset($param ['tpl']) ? $param ['tpl'] : $tpl;
        // dump ( $tpl );exit;
        return $this->fetch('common@widget/ad_show/' . $tpl);
    }

    function _config($key)
    {
        $place = array(

            // 'home_middle' => array('id'=>'1','name'=>'朋友圈首页中部','key'=>'home_middle','width'=>670, 'bottom'=>20),
            // 'home_right_bottom' => array('id'=>'3','name'=>'朋友圈首页右下','key'=>'home_right_bottom','width'=>282),
            // 'topic_right' => array('id'=>'4','name'=>'话题右下','key'=>'topic_right','width'=>282),
            'weiba_right' => array(
                'id' => '12',
                'name' => '圈子右下',
                'key' => 'weiba_right',
                'width' => 280,
                'top' => 0
            ),

            /*'weiba_post_top' => array('id'=>'13','name'=>'圈子帖子中部','key'=>'weiba_post_top','width'=>670),*/
            /*'weiba_post_right' => array('id'=>'14','name'=>'圈子帖子右下','key'=>'weiba_post_right','width'=>240, 'top'=>10),*/
            /*'w3g_home_top' => array('id'=>'101','name'=>'首页顶部广告','key'=>'w3g_home_top','width'=>448,'preview'=>'w3g_preview2','tpl'=>'w3g_showAdSpace2'),*/
            //'home_top' => array('id'=>'15','name'=>'广场首页顶部','key'=>'home_top','width'=>700, 'height'=>380),
            'weiba_banner' => array(
                'id' => '16',
                'name' => '论坛首页幻灯片',
                'key' => 'weiba_banner',
                'width' => 730,
                'top' => 0,
                'height' => 374
            ),
            'weiba_index_right' => array(
                'id' => '17',
                'name' => '论坛首页右下',
                'key' => 'weiba_index_right',
                'width' => 280,
                'top' => 0
            ),
            'weiba_detail_right' => array(
                'id' => '18',
                'name' => '主题详情右下',
                'key' => 'weiba_detail_right',
                'width' => 280,
                'top' => 0
            ),
            'posts_right' => array(
                'id' => '12',
                'name' => '帖子右下',
                'key' => 'posts_right',
                'width' => 280,
                'top' => 0
            ),

            /*'posts_post_top' => array('id'=>'13','name'=>'圈子帖子中部','key'=>'posts_post_top','width'=>670),*/
            /*'posts_post_right' => array('id'=>'14','name'=>'圈子帖子右下','key'=>'posts_post_right','width'=>240, 'top'=>10),*/
            /*'w3g_home_top' => array('id'=>'101','name'=>'首页顶部广告','key'=>'w3g_home_top','width'=>448,'preview'=>'w3g_preview2','tpl'=>'w3g_showAdSpace2'),*/
            //'home_top' => array('id'=>'15','name'=>'广场首页顶部','key'=>'home_top','width'=>700, 'height'=>380),
            'posts_banner' => array(
                'id' => '16',
                'name' => '帖子首页幻灯片',
                'key' => 'posts_banner',
                'width' => 730,
                'top' => 0,
                'height' => 374
            ),
            'posts_index_right' => array(
                'id' => '17',
                'name' => '帖子首页右下',
                'key' => 'posts_index_right',
                'width' => 280,
                'top' => 0
            ),
            'posts_detail_right' => array(
                'id' => '18',
                'name' => '帖子详情右下',
                'key' => 'posts_detail_right',
                'width' => 280,
                'top' => 0
            )
        );
        return $place [$key];
    }

    /**
     * 通过位置ID获取相应的广告信息
     *
     * @param integer $place
     *            位置ID
     * @return array 位置ID获取相应的广告信息
     */
    public function getAdSpaceByPlace($place)
    {
        if (empty ($place)) {
            return [];
        }
        // 获取信息
        $map ['place'] = $place;
        $map ['status'] = 1;
		$map ['social_id'] = get_social_id ();        

        $data = M('ad')->where(wp_where($map))->order('id DESC')->select();

        return $data;
    }
}