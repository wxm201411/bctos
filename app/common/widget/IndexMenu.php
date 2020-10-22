<?php
namespace app\common\widget;
use app\common\controller\WapBase;
/**
 * 加载首页设置菜单
 * @example W('IndexMenu', [])
 * @author wuming
 * @version bl1.0
 */
class IndexMenu extends WapBase {

    public $info = array(
        'name' => 'IndexMenu',
        'title' => 'PC菜单',
        'description' => '',
        'status' => 1,
        'author' => '凡星',
        'version' => '0.1',
        'has_adminlist' => 0,
        'type' => 0
    );
	/**
     * 渲染菜单模板
     * @example
	 */
	public function render($data=[]) {
		$social_id = get_social_id();
		$map = array(
				'social_id'=>$social_id
		);
		$menu = M('social_index_menu')->where(wp_where($map))->order('m_sort asc,id asc')->select();//dump($var);
		foreach ($menu as &$v){
			if($v['type'] == 1){//外部链接
				$true_link = trim($v['link']);
			}elseif($v['type'] == 0){//内部链接
				$true_link = U(ucfirst($v['m_model']).'/'.ucfirst($v['m_controller']).'/'.$v['m_func']);
			}else{//默认
				$default_link = U('social/Index/index');
			}
			$v['true_link'] = $true_link ? $true_link : $default_link;
		}

		$menu_list['menu'] = $menu;
		//dump($menu_list);exit;
		// 渲染模版
		$this->assign($menu_list);
		$content = $this->fetch('common@widget/menu_list');

		// 输出数据
		return $content;
    }
}