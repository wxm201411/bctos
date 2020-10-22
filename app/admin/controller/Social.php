<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: aMan
// +----------------------------------------------------------------------
namespace app\admin\controller;

/**
 * 后台社区管理控制器
 */
class Social extends Admin
{

    /**
     * 后台社区管理首页
     */
    public function index()
    {
        $now = time();
        $title = I('title');
        $group_id = I('group_id', 0, 'intval');
        if ($title) {
            $map['title'] = array(
                'like',
                '%' . (string) $title . '%'
            );
        }
        if ($group_id == 1) {
            // 免费版
            $map['power_end_time'] = array(
                '<',
                $now
            );
        } else 
            if ($group_id == 2) {
                // 收费版
                $map['power_end_time'] = array(
                    '>=',
                    $now
                );
            } else 
                if ($group_id == 3) {
                    // //超级管理版
                    $map['id'] = 1;
                }
        $list = $this->lists('social', $map, 'id desc');
        foreach ($list as &$info) {
            $user = get_userinfo($info['uid']);
            $info['nickname'] = $user['nickname'];
            $info['cTime'] = time_format($user['reg_time']);
            $info['domainUrl'] = U('social/Index/index');
            $info['domainUrl'] = domain_url($info['domainUrl'], $info['domain']);
            if ($now > $info['power_end_time']) {
                $info['has_power'] = 0; // 免费、或过期
            } else {
                $info['has_power'] = 1;
            }
        }
        // 获取收费配置标准
        $chargeArr = get_social_charge();
        unset($chargeArr[0]);
        $this->assign('set_year', $chargeArr);
        $this->assign('_list', $list);
        $this->assign('group_id', $group_id);
        // dump($list);
        $this->meta_title = '社区管理';
        return $this->fetch();
    }
    function setEndTime(){
        $social_id = I('id',0,'intval');
        $group=I('group_id',0,'intval');
        // 获取收费配置标准
        $chargeArr = get_social_charge();
        if ($social_id && $group){
            $socialInfo = D ( 'common/Social')->getInfo($social_id);
            $now = time();
            if ($socialInfo['has_power']==1){
                //当前为收费版
                $year = $chargeArr[$group]['year_count'];
                $et = intval($socialInfo['power_end_time']);
                $save['power_end_time']=strtotime('+'.$year.' year',$et);
            }else{
                //当前免费版
                $save['power_start_time']=$now;
                $year = $chargeArr[$group]['year_count'];
                $save['power_end_time']=strtotime('+'.$year.' year',$now);
            }
           $res = D ( 'common/Social')->where(wp_where(array('id'=>$social_id)))->update($save);
           if ($res){
               D ( 'common/Social')->clear($social_id);
               echo 1;
           }else{
               echo 0;
           }
        }else{
            echo 0;
        }
    }
}
