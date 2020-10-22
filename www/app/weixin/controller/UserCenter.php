<?php

namespace app\weixin\controller;

use app\common\controller\WebBase;

class UserCenter extends WebBase
{

    var $syc_wechat = true;

    // 是否需要与微信端同步，目前只有认证的订阅号和认证的服务号可以同步
    function initialize()
    {
        parent::initialize();
        $this->syc_wechat = public_interface('user_group');

        $type = input('type/d', 0);
        $this->assign('type', $type);

        $this->assign('nav', []);
    }

    function lists_choose()
    {
        return $this->lists(1);
    }

    function user($only_body = 0)
    {
        $model = $this->getModel('user');
        $only_body == 0 && $only_body = I('only_body');
        $this->assign('only_body', $only_body);

        $isRadio = I('isRadio');
        $this->assign('isRadio', $isRadio);

        // 解析列表规则
        $list_data = $this->listGrid($model);

        $map['status'] = array(
            '>',
            0
        );

        // 搜索类型
        $search_type = input('search_type', 0);
        $this->assign('search_type', $search_type);

        $key = input('key');
        $this->assign('search_key', $key);
        if (!empty($key)) {
            $is_opneid = $search_type == 2 ? true : false;
            $uidstr = D('common/User')->searchUser($key, $is_opneid);
            if ($uidstr) {
                $map['uid'] = array(
                    'in',
                    $uidstr
                );
            } else {
                $map['uid'] = 0;
            }
        }

        $group_id = input('group_id/d', 0);
        $this->assign('group_id', $group_id);
        if ($group_id > 0) {
            $uids = M('auth_group_access')->where('group_id', $group_id)->column('uid');
            if (empty($uids)) {
                $map['uid'] = 0;
            } else {
                $map['uid'] = array(
                    'in',
                    $uids
                );
            }
        }

        $param = [];

        $row = empty($model['list_row']) ? 20 : $model['list_row'];
        $order = 'uid desc';
        // 读取模型数据列表

        $px = DB_PREFIX;
        $map['mobile'] = ['<>', ''];
        $data = M('user')
            ->field('uid')
            ->where(wp_where($map))
            ->order($order)
            ->paginate($row);

        $list_data = $this->parsePageData($data, $model, $list_data, false);

        foreach ($list_data['list_data'] as $k => $d) {
            $user = getUserInfo($d['uid']);
            if (!empty($user['in_blacklist'])) {
                $user['group'] = '黑名单';
            } else {
                $user['group'] = !empty($user['groups']) ? implode(', ', getSubByKey($user['groups'], 'title')) : '未分组';
            }
            $list_data['list_data'][$k] = array_merge($d, $user);
        }

        // 用户组
        $gmap['pbid'] = get_pbid();
        $auth_group = find_data(M('auth_group')->where(wp_where($gmap))->select());
        $this->assign('auth_group', $auth_group);

        $tagmap['pbid'] = get_pbid();
        $tags = find_data(M('auth_group')->where(wp_where($tagmap))->select());
        $this->assign('tags', $tags);

        $this->assign('syc_wechat', $this->syc_wechat);
        $this->assign($list_data);

        return $this->fetch('user');

    }

    /**
     * 微信用户列表数据
     */
    public function lists($only_body = 0)
    {
        $model = $this->getModel('user');
        $only_body == 0 && $only_body = I('only_body');
        $this->assign('only_body', $only_body);

        $isRadio = I('isRadio');
        $this->assign('isRadio', $isRadio);

        // 解析列表规则
        $list_data = $this->listGrid($model);

        $map['status'] = array(
            '>',
            0
        );

        // 搜索类型
        $search_type = input('search_type', 0);
        $this->assign('search_type', $search_type);

        $key = input('key');
        $this->assign('search_key', $key);
        if (!empty($key)) {
            $is_opneid = $search_type == 2 ? true : false;
            $uidstr = D('common/User')->searchUser($key, $is_opneid);
            if ($uidstr) {
                $map['uid'] = array(
                    'in',
                    $uidstr
                );
            } else {
                $map['uid'] = 0;
            }
        }

        $group_id = input('group_id/d', 0);
        $this->assign('group_id', $group_id);
        if ($group_id > 0) {
            $uids = M('auth_group_access')->where('group_id', $group_id)->column('uid');
            if (empty($uids)) {
                $map['uid'] = 0;
            } else {
                $map['uid'] = array(
                    'in',
                    $uids
                );
            }
        }

        $param = [];
        // 时间
        $s_time = input('s_time');
        $this->assign('s_time', $s_time);
        $s_time = strtotime($s_time);
        empty($s_time) && $s_time = 0;

        $e_time = input('e_time', '');
        $this->assign('e_time', $e_time);
        if ($e_time) {
            $e_time = strtotime($e_time);
        } else {
            $e_time = NOW_TIME;
        }
        if ($s_time || $e_time) {
            $map['reg_time'] = [
                'between',
                [
                    $s_time,
                    $e_time
                ]
            ];
        }

        // 标签
        $tag_id = input('tag_id', '');
        if ($tag_id) {
            $param['tag_id'] = $tag_id;
            $uidstr = D('common/User')->searchUserS($param);
            if ($uidstr) {
                $map['uid'] = array(
                    'in',
                    $uidstr
                );
            }
        }
        $this->assign('tag_id', $tag_id);

        // 性别
        $sex = input('sex', '');
        if ($sex) {
            $map['sex'] = $sex;
        }
        $this->assign('sex', $sex);

        $row = empty($model['list_row']) ? 20 : $model['list_row'];
        $order = 'uid desc';
        // 读取模型数据列表

        $px = DB_PREFIX;
        $data = M('user')->where(wp_where($map))
            ->order($order)
            ->paginate($row);
        $list_data = $this->parsePageData($data, $model, $list_data, false);

        foreach ($list_data['list_data'] as $k => $d) {
            $d['group'] = !empty($d['groups']) ? implode(', ', getSubByKey($d['groups'], 'title')) : '未分组';
            $d['headimgurl'] = empty($d['headimgurl']) ? url_img_html(__ROOT__ . '/static/face/default_head_50.png') : $d['headimgurl'];
            $list_data['list_data'][$k] = $d;
        }

        // 用户组

        $auth_group = find_data(M('auth_group')->select());
        $this->assign('auth_group', $auth_group);
        $this->assign('tags', $auth_group);

        $this->assign('syc_wechat', $this->syc_wechat);
        if ($this->syc_wechat && $only_body == 0) {
            $this->assign('page_tips', '请定期手动点击“一键同步微信公众号粉丝”按钮同步微信数据');
        }
        $this->assign($list_data);
        if ($only_body == 1) {
            $this->assign('isRadio', $isRadio);
            return $this->fetch('lists_data');
        } elseif ($only_body == 2) {
            return $this->fetch('user');
        } else {
            return $this->fetch();
        }
    }

    function getUserRemark()
    {
        $uid = I('uid');
        $remark = '';
        $user = get_userinfo($uid);
        $pbid = get_pbid();
        if ($user['remarks'][$pbid]) {
            $remark = $user['remarks'][$pbid];
        }
        echo $remark;
    }

    function detail()
    {
        $uid = I('uid');
        $userInfo = getUserInfo($uid);
        if (!empty($userInfo['in_blacklist'])) {
            $userInfo['groupstr'] = '黑名单';
        } else {
            // dump($userInfo);
            $strgroup = '';
            foreach ($userInfo['groups'] as $v) {
                $strgroup .= $v['title'] . ',';
            }
            $len = strlen($strgroup) - 1;
            $str = substr($strgroup, 0, $len);
            $userInfo['groupstr'] = $str;
        }


        $userInfo['openid'] = isset($userInfo['pbids'][PBID]) ? $userInfo['pbids'][PBID] : '';

        if ($userInfo['reg_time'] > 0) {
            $day = ceil((NOW_TIME - $userInfo['reg_time']) / 86400);
            $year = floor($day / 365);
            $day = $day % 365;

            $userInfo['reg_time'] = '';
            if ($year > 0) {
                $userInfo['reg_time'] = $year . '年 ';
            }
            $userInfo['reg_time'] .= $day . '天';
        } else {
            $userInfo['reg_time'] = '';
        }
        $userInfo['last_login_ip'] = intval($userInfo['last_login_ip']);

        $this->assign('info', $userInfo);

        return $this->fetch();
    }

    function set_login()
    {
        $model = $this->getModel('user');
        $map['uid'] = $id = I('uid');

        // 获取数据
        $data = M($model['name'])->where('id', $id)->find();
        if (empty($data)) return $this->error('数据不存在！');

        if (IS_POST) {
            if (empty(input('post.login_name')) || empty(input('post.login_password'))) {
                return $this->error('账号信息不能为空');
            }

            $save['login_name'] = I('login_name');
            $old_uid = M('user')->where(wp_where($save))->value('uid');
            if ($old_uid > 0 && $old_uid != $id) {
                return $this->error('该账号已经存在，请更换后再试');
            }
            // 手工升级会员时，用户经历值也增加到该会员级别的条件经历值
            if (is_install("Shop")) {
                $membership_condition = M('shop_membership')->where('id', input('post.membership'))->value('condition');
            }

            $save['leven'] = 1;
            $save['manager_id'] = $this->mid;
            $save['is_audit'] = 1;
            $save['is_init'] = 1;
            $save['status'] = 1;
            $save['login_password'] = I('login_password');
            $save['password'] = think_weiphp_md5($save['login_password']);
            $save['membership'] = input('post.membership');
            // 获取模型的字段信息
            if (M('user')->where(wp_where($map))->update($save) !== false) {
                D('common/User')->getUserInfo($id, true);

                return $this->success('保存' . $model['title'] . '成功！', U('lists'));
            } else {
                return $this->error('保存失败');
            }
        } else {
            $fields = get_model_attribute($model);

            $extra = $this->getMembershipData();
            if (!empty($extra)) {
                foreach ($fields as &$vo) {
                    if ($vo['name'] == 'membership') {
                        $vo['extra'] .= "\r\n" . $extra;
                    }
                }
            }

            $this->assign('fields', $fields);
            $this->assign('data', $data);

            $this->assign('post_url', U('set_login', $map));

            return $this->fetch('edit');
        }
    }

    // 获取会员等级
    function getMembershipData()
    {
        $map['uid'] = $this->mid;
        $map['wpid'] = get_wpid();
        $uid = I('uid');
        $extra = '';
        if (is_install("Shop")) {
            $list = find_data(M('shop_membership')->where(wp_where($map))->select());
        }
        return $extra;
    }


    public function userCenter()
    {
        return $this->fetch();
    }

    function config()
    {
        // 使用提示
        $page_tips = '如需用户关注时提示先绑定，请进入‘欢迎语’插件按提示进行配置提示语';
        $this->assign('page_tips', $page_tips);
        if (IS_POST) {
            $config = I('config');
            $credit['score'] = intval($config['score']);
            $pbid = get_pbid();
            D('common/Credit')->updateSubscribeCredit($pbid, $credit, 1);
        }

        return parent::config();
    }

    // 设置用户组
    public function changeGroup()
    {
        $uids = array_unique((array)I('ids', 0));

        if (empty($uids)) {
            return $this->error('请选择用户!');
        }
        $group_id = I('group_id', 0);
        if (empty($group_id)) {
            return $this->error('请选择用户组!');
        }
        D('home/AuthGroup')->move_group($uids, $group_id);
        foreach ($uids as $uid) {
            D('common/User')->getUserInfo($uid, true);
        }
        echo 1;
    }

    // 设置用户标签
    public function changeTag()
    {
        $uids = array_unique((array)I('ids', 0));

        if (empty($uids)) {
            return $this->error('请选择用户!');
        }
        $tags = array_filter(explode(',', I('tags')));
        if (empty($tags)) {
            return $this->error('请选择用户标签!');
        }

        M('auth_group_access')->whereIn('uid', $uids)->delete();
        foreach ($uids as $uid) {
            foreach ($tags as $tid) {
                $data['uid'] = $uid;
                $data['tag_id'] = $tid;
                $data['cTime'] = NOW_TIME;
                M('auth_group_access')->insert($data);
            }
            D('common/User')->getUserInfo($uid, true);
        }
        echo 1;
    }

    // 预先同步好用户组数据
    function syc_auth_group()
    {
        return redirect(U('home/AuthGroup/updateWechatGroup', array(
            'need_return' => 1
        )));
    }

    // 第一步：获取全部用户的ID，并先保存到public_follow表中，新的用户UID暂时为0，后面的步骤补充
    function syc_openid()
    {
        $map['pbid'] = $save['pbid'] = get_pbid();

        $next_openid = I('next_openid');
        if (!$next_openid) {
            $res = M('public_follow')->where(wp_where($map))->update(['has_subscribe' => 0]);
        }
        // 获取openid列表
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . get_access_token() . '&next_openid=' . $next_openid;
        $data = wp_file_get_contents($url);
        $data = json_decode($data, true);

        if (!isset($data['count']) || $data['count'] == 0) {
            // 拉取完毕
            return $this->jump(U('syc_user'), '同步用户数据中，请勿关闭');
        }

        $map['openid'] = array(
            'in',
            $data['data']['openid']
        );
        $map['pbid'] = $save['pbid'] = get_pbid();
        $pdata['syc_status'] = 0;
        $pdata['has_subscribe'] = 1;
        $res = M('public_follow')->where(wp_where($map))->update($pdata);
        if ($res != $data['count']) {
            // 更新的数量不一致，可能有增加的用户openid
            $openids = (array)M('public_follow')->where(wp_where($map))->column('openid');
            $diff = array_diff($data['data']['openid'], $openids);
            if (!empty($diff)) {
                foreach ($diff as $id) {
                    $save['openid'] = $id;
                    $save['uid'] = 0;
                    $save['syc_status'] = 0;
                    $save['has_subscribe'] = 1;
                    $res = M('public_follow')->insertGetId($save);
                }
            }
        }

        $param2['next_openid'] = $data['next_openid'];
        $url = U('syc_openid', $param2);
        return $this->jump($url, '同步用户OpenID中，请勿关闭');
    }

    // 第二步：同步用户信息
    function syc_user()
    {
        $map['pbid'] = $map2['pbid'] = $map5['pbid'] = get_pbid();
        $map['syc_status'] = 0;
        $map['has_subscribe'] = 1;
        $list = find_data(M('public_follow')->where(wp_where($map))
            ->field('uid,openid')
            ->limit(100)
            ->select());

        if (empty($list)) {
            return $this->jump(U('syc_user_group'), '用户分组信息同步中');
        }
        $openids = [];
        foreach ($list as $vo) {
            $param['user_list'][] = array(
                'openid' => $vo['openid']
            );
            $openids[] = $vo['openid'];
            $uids[$vo['openid']] = $vo['uid'];
        }

        // 先把关注状态设置未关注
        $map2['openid'] = array(
            'in',
            $openids
        );
        // M( 'public_follow' )->where ( wp_where( $map2 ) )->update([ 'has_subscribe'=> 0 ]);

        $url = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=' . get_access_token();
        $data = post_data($url, $param);

        $userDao = D('common/User');
        $config = getAppConfig('UserCenter');
        isset($config['score']) || $config['score'] = 0;

        $countdata['list_count'] = count($list);
        $countdata['wp_data_count'] = count($data['user_info_list']);
        if ($countdata['list_count'] != $countdata['wp_data_count']) {
            $countdata['listopenid'] = $param;
            $countdata['wp_op'] = $data;
            $countdata['access_token'] = get_access_token();
            foreach ($param['user_list'] as $p) {
                $single_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . get_access_token() . '&openid=' . $p['openid'] . '&lang=zh_CN';
                $tempArr = json_decode(wp_file_get_contents($single_url), true);
                $tempArr['tagid_list'] = isset($tempArr['tagid_list']) ? json_encode($tempArr['tagid_list']) : '';
                if (empty($tempArr)) {
                    $tempArr = outputCurl($single_url);
                    $tempArr = json_decode($tempArr, true);
                    // addWeixinLog($tempArr,'syc_userlistscount5');
                }
                if (empty($tempArr)) {
                    continue;
                }
                if (isset($tempArr))
                    $uid = isset($uids[$tempArr['openid']]) ? intval($uids[$tempArr['openid']]) : 0;
                if ($uid == 0) { // 新增加的用户
                    $tempArr['score'] = intval($config['score']);
                    $tempArr['reg_time'] = $tempArr['subscribe_time'];
                    $tempArr['status'] = 1;
                    $tempArr['is_init'] = 1;
                    $tempArr['is_audit'] = 1;

                    $uid = D('common/User')->addUser($tempArr);

                    $map5['openid'] = $tempArr['openid'];
                    $uid > 0 && M('public_follow')->where(wp_where($map5))->update(['uid' => $uid]);
                } else { // 更新的用户
                    $userDao->updateInfo($uid, $tempArr);
                }
            }

            M('public_follow')->where(wp_where($map2))->update(['syc_status' => 1]);
            return $this->jump(U('syc_user'), '同步用户数据中，请勿关闭');
        }
        // addWeixinLog($countdata,'syc_userlistscount2');

        foreach ($data['user_info_list'] as $u) {
            if ($u['subscribe'] == 0) {
                continue;
            }
            $u['tagid_list'] = isset($u['tagid_list']) ? json_encode($u['tagid_list']) : '';
            $uid = intval($uids[$u['openid']]);
            if ($uid == 0) { // 新增加的用户
                $u['score'] = intval($config['score']);
                $u['reg_time'] = $u['subscribe_time'];
                $u['status'] = 1;
                $u['is_init'] = 1;
                $u['is_audit'] = 1;
                unset($u['id']);

                $uid = D('common/User')->addUser($u);

                $map5['openid'] = $u['openid'];
                $uid > 0 && M('public_follow')->where(wp_where($map5))->update(['uid' => $uid]);
            } else { // 更新的用户
                $userDao->updateInfo($uid, $u);
            }

            $openidArr[] = $u['openid'];
        }
        M('public_follow')->where(wp_where($map2))->update(['syc_status' => 1]);

        return $this->jump(U('syc_user?uid=' . $uid), '同步用户数据中，请勿关闭');
    }

    // 第三步：同步用户组信息
    function syc_user_group()
    {
        $map['pbid'] = $map2['pbid'] = get_pbid();
        $map['syc_status'] = 1;
        $map['uid'] = array(
            '>',
            0
        );


        $uids = M('public_follow')->where(wp_where($map))
            ->limit(100)
            ->column('uid');

        if (empty($uids)) {
            $model = $this->getModel('user');
            $obj = D('common/Models')->getFileInfo($model);
            if (isset($obj->fields['in_blacklist'])) {
                return $this->jump(U('syc_blacklist'), '用户分组信息同步完毕');
            } else {
                return $this->jump(U('lists'), '用户分组信息同步完毕');
            }

        }

        /*  $list = M('user')->whereIn('uid', $uids)
             ->field('uid,groupid')
             ->select();
         foreach ($list as $vo) {
             $userArr[$vo['uid']] = $vo['groupid'];
         } */
        $list = find_data(M('user')->whereIn('uid', $uids)->select());
        foreach ($list as $vo) {
            if (isset($vo['tagid_list'])) {
                $userArr[$vo['uid']] = json_decode($vo['tagid_list'], true);
                if (empty($userArr[$vo['uid']])) {
                    $userArr[$vo['uid']][] = 0;
                }
            } else {
                $userArr[$vo['uid']] = [$vo['groupid']];
            }
        }

//         $auth_map['manager_id'] = $this->mid;
        $auth_map['pbid'] = get_pbid();
        $groups = M('auth_group')->where(wp_where($auth_map))
            ->field('id,wechat_group_id')
            ->select();
        foreach ($groups as $g) {
            $groupArr[$g['id']] = $g['wechat_group_id'];
            $wechatArr[$g['wechat_group_id']] = $g['id'];
        }

        M('auth_group_access')->whereIn('uid', $uids)->delete();

        $access = [];
        /*  $list = find_data( M('auth_group_access')->whereIn('uid', $uids)->select() );

         foreach ($list as $vo) {
             $access[$vo['uid']] = $vo['group_id'];
         } */

        foreach ($uids as $uid) {
            //微信标签id
            $new_groupid = isset($userArr[$uid]) ? $userArr[$uid] : [];
            foreach ($new_groupid as $gid) {
                if (isset($access[$uid]) && in_array($gid, $access[$uid])) {
                    //保存过了不再保存
                    continue;
                }
                $save = [];
                $save['uid'] = $uid;
                $save['group_id'] = isset($wechatArr[$gid]) ? $wechatArr[$gid] : 0;
                $res = M('auth_group_access')->insert($save);
                if ($res) {
                    $access[$uid][] = $gid;
                }
            }

            /*  $access_id = isset($access[$uid]) ? $access[$uid] : 0;
             $old_groupid = isset($groupArr[$access_id]) ? $groupArr[$access_id] : 0;

             if (isset($access[$uid]) && $new_groupid == $old_groupid) {
                 continue;
             }
             $save['group_id'] = isset($wechatArr[$new_groupid]) ? $wechatArr[$new_groupid] : 0;
             if (isset($access[$uid])) {
                 $amap['uid'] = $uid;
                 $amap['group_id'] = $access_id;
                 $res = M('auth_group_access')->where(wp_where($amap))->update($save);
             } else {
                 $save['uid'] = $uid;
                 $access[$uid] = M('auth_group_access')->insertGetId($save);
             } */
            D('common/User')->getUserInfo($uid, true);
        }
        M('public_follow')->where(wp_where($map2))
            ->whereIn('uid', $uids)
            ->update(['syc_status' => 2]);

        return $this->jump(U('syc_user_group?uid=' . $uid), '用户分组信息同步中，请勿关闭');
    }

    //第四步：同步黑名单用户
    function syc_blacklist()
    {
        $map['pbid'] = get_pbid();

        $next_openid = I('next_openid');
        $param = [];
        if ($next_openid) {
            $param['begin_openid'] = $next_openid;
        }
        // 获取openid列表
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/members/getblacklist?access_token=' . get_access_token();
        $data = post_data($url, $param);

        if (!isset($data['count']) || $data['count'] == 0) {
            // 拉取完毕
            return $this->jump(U('lists'), '用户信息同步完毕');
        }

        $map['openid'] = array(
            'in',
            $data['data']['openid']
        );

        $uids = M('public_follow')->where(wp_where($map))->column('uid');
        if (!empty($uids)) {
            $uDao = D('common/User');
            foreach ($uids as $uid) {
                $save['in_blacklist'] = 1;
                $uDao->updateInfo($uid, $save);
            }
        }
        if ($data['count'] == 1 && $data['data']['openid'][0] == $data['next_openid']) {
            return $this->jump(U('lists'), '用户信息同步完毕');
        } else {
            $param2['next_openid'] = $data['next_openid'];
            $url = U('syc_blacklist', $param2);
            return $this->jump($url, '同步黑名单用户，请勿关闭');
        }

    }

    //移出黑名单
    function outbacklist()
    {
        $uid = I('uid', 0);
        $openid = I('bopenid', '');
        if (empty($openid) || empty($uid)) {
            return $this->error('用户信息出错！');
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchunblacklist?access_token=' . get_access_token();
        $param['openid_list'] = [$openid];
        $res = post_data($url, $param);
        if (isset($res['errcode']) && $res['errcode'] === 0) {
            $save['in_blacklist'] = 0;
            D('common/User')->updateInfo($uid, $save);
            return $this->success('设置成功');
        } else {
            return $this->error('设置失败');
        }
    }

    //加入黑名单
    function addblacklist()
    {
        $uids = array_unique((array)I('ids', 0));
        if (empty($uids)) {
            return $this->error('请选择用户!');
        }
        $map['pbid'] = get_pbid();
        $map['uid'] = array('in', $uids);
        $openids = M('public_follow')->where(wp_where($map))->column('openid');
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchblacklist?access_token=' . get_access_token();
        if (empty($openids)) {
            return $this->error('用户信息出错');
        }
        $param['openid_list'] = $openids;
        $res = post_data($url, $param);
        if (isset($res['errcode']) && $res['errcode'] === 0) {
            foreach ($uids as $uid) {
                $save['in_blacklist'] = 1;
                D('common/User')->updateInfo($uid, $save);
            }
            return $this->success('设置成功');
        } else {
            return $this->error('设置失败');
        }
    }

    function set_remark()
    {
        $map['uid'] = I('uid/d', 0);
        if (empty($map['uid'])) {
            return $this->error('用户信息出错');
        }

        $param['remark'] = I('remark');
        if (empty($param['remark'])) {
            return $this->error('备注不能为空');
        }

        $map['pbid'] = get_pbid();

        $info = M('public_follow')->where(wp_where($map))->find();
        if (!$info) {
            return $this->error('用户信息出错啦');
        }

        $res = M('public_follow')->where(wp_where($map))->update($param);
        if ($res !== false) { // 同步到微信端
            D('common/User')->getUserInfo($map['uid'], true);
            if (config('app.USER_REMARK')) {
                $url = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=' . get_access_token();
                $param['openid'] = $info['openid'];
                $result = $this->postData($url, $param);
            }
        } else {
            return $this->error('保存数据库失败');
        }

        return $this->success('设置成功');
    }

    function clear_score()
    {
        $pbid = get_pbid();
        $map['pbid'] = $pbid;
        $users = M('public_follow')->where(wp_where($map))->column('uid');
        $scoresave['score'] = 0;
        $userdao = D('common/User');
        // 每个用户积分清0
        foreach ($users as $uid) {
            if (empty($uid)) {
                continue;
            }
            $uidArr[$uid] = $uid;
            // $userdao->updateInfo ( $uid, $scoresave );
        }
        $userMap['score'] = array(
            '<>',
            0
        );
        $userdao->where(wp_where($userMap))
            ->whereIn('uid', $uidArr)
            ->update($scoresave);
        foreach ($uidArr as $u) {
            $key = 'getUserInfo_' . $u;
            S($key, null);
        }
        // 积分记录
        $creditMap['wpid'] = get_wpid();
        M('credit_data')->where(wp_where($creditMap))->delete();
        // 会员卡设置等级
        if (is_install('card')) {
            $firstlevel = M('card_level')->where(wp_where($map))
                ->whereIn('uid', $uidArr)
                ->order('score asc')
                ->value('id');
            // 会员卡等级都设为体验卡
            $cardMap1[] = array(
                'uid',
                'in',
                $uidArr
            );
            $cardMap1[] = array(
                'level',
                '>',
                0
            );
            $savecardlev['level'] = intval($firstlevel);
            M('card_member')->where(wp_where($cardMap1))->update($savecardlev);
        }

        echo 1;
    }

    function jump($url, $msg)
    {
        $this->assign('url', $url);
        $this->assign('msg', $msg);
        return $this->fetch('loading');
    }

    public function export()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        $map['status'] = ['>', 0];
        $map['mobile'] = ['<>', ''];
        $field = 'uid,mobile,province,city,score,reg_time';
        $data = find_data(M('user')
            ->field($field)
            ->where(wp_where($map))
            ->order('uid asc')
            ->select());

        foreach ($data as $k => &$vo) {
            $vo['reg_time'] = time_format($vo['reg_time']);
        }

        $ht = array(
            '用户编号',
            '手机号',
            '省份',
            '城市',
            '积分',
            '注册时间'
        );
        $dataArr[0] = $ht;
        $dataArr = array_merge($dataArr, (array)$data);

        outExcel($dataArr, 'user_' . date('YmdHis'));
    }

    function company()
    {
        if (IS_POST) {
            D('common/User')->updateInfo($this->mid, ['truename' => input('truename')]);
            return $this->success('保存成功');
        } else {
            $this->assign('info', $GLOBALS['myinfo']);

            $config = getAppConfig('CodeAdmin');
            $this->assign('recharge_tips', $config['recharge_tips']);

            return $this->fetch();
        }
    }

    /**
     * 微信用户列表数据
     */
    public function admin($only_body = 0)
    {
        $model = $this->getModel('user');

        // 解析列表规则
        $list_data = $this->listGrid($model);

        $map = [];
        //$map['level'] = 1;

        // 搜索类型
        $key = input('key');
        $this->assign('search_key', $key);
        if (!empty($key)) {
            $map['nickname|mobile|email'] = ['like', "%{$key}%"];
        }

        $row = empty($model['list_row']) ? 20 : $model['list_row'];
        $order = 'uid desc';
        // 读取模型数据列表
        $data = M('user')->where(wp_where($map))->order($order)->paginate($row);
        $list_data = $this->parsePageData($data, $model, $list_data);

        return $this->fetch();
    }

    function set_score()
    {
        $uid = I('uid/d', 0);
        if (empty($uid)) {
            return $this->error('用户信息出错');
        }

        $need_money = input('score', 0);
        if ($need_money <= 0) {
            return $this->error('充值金额不能为空或负数');
        }

        $need_money = $need_money * 100;//元转分
        $total_memory = M('user')->where('uid', $uid)->value('score');
        //增加消费记录
        $res = M('code_money')->insert([
            'money' => $need_money, 'create_at' => NOW_TIME, 'uid' => $uid, 'model' => '', 'number' => 0, 'price' => 0, 'type' => 1, 'wpid' => $uid
        ]);

        D('common/User')->updateInfo($uid, ['score' => $total_memory + $need_money]);
        if ($res !== false) {
            return $this->success('充值成功');
        } else {
            return $this->error('充值失败');
        }
    }

    function set_passwd()
    {
        $uid = I('uid/d', 0);
        if (empty($uid)) {
            return $this->error('用户信息出错');
        }

        $password = input('passwd');
        if (empty($password)) {
            return $this->error('密码不能为空');
        }

        $res = D('common/User')->updateInfo($uid, ['password' => think_weiphp_md5($password)]);
        if ($res !== false) {
            return $this->success('重置成功');
        } else {
            return $this->error('重置失败');
        }
    }

    function add($username = '', $password = '', $repassword = '', $mobile = '', $email = '', $verify = '', $truename = '', $price = 0.2)
    {
        if (request()->isPost()) {
            // 注册用户
            $username = trim($username);
            empty($username) && $username = $mobile = trim($mobile);
            $hasusername = D('common/User')->where('nickname', $username)->value('uid');

            /* 测试用户名 */
            if (empty($username)) {
                return $this->error('用户名不能为空！');
            } elseif (!preg_match('/[a-zA-Z0-9_]$/', $username)) {
                return $this->error('用户名必须由‘字母’、‘数字’、‘_’组成！');
            } elseif (strlen($username) > 16) {
                return $this->error('用户名长度必须在16个字符以内！');
            } elseif ($hasusername) {
                return $this->error('该用户名已经存在，请重新填写用户名！');
            }
            /* 检测密码 */
            if (strlen($password) < 6 || strlen($password) > 30) {
                return $this->error('密码长度必须在6-30个字符之间！');
            }
            if ($password != $repassword) {
                return $this->error('密码和重复密码不一致！');
            }

            if (empty($mobile)) {
                return $this->error('手机号码不能为空');
            }
            /* 测试手机号 */
            if (!preg_match('/^[1][3578][0-9]{9}$/', $mobile)) {
                return $this->error('手机格式不正确！');
            }
            if ($price <= 0) {
                return $this->error('单价不能为空或负数');
            }
            $price = $price * 100;//元转分
            /* 测试邮箱 */
//            if (empty($email)) {
//                return $this->error('邮箱不能为空');
//            }
//            if (!preg_match('/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/', $email)) {
//                return $this->error('邮箱格式不正确！');
//            }
            /* 调用注册接口注册用户 */
            $uid = D('common/User')->register($username, $password, $email, $mobile, $truename, $price);

            if (0 < $uid) {
                // 自动加入平台管理组
                $access['uid'] = $uid;
                $access['group_id'] = 1; // TODO 后续可优化为自动获取
                M('auth_group_access')->insert($access);

                $url = U('admin');
                return $this->success('注册成功！', $url);
            } else {
                // 注册失败，显示错误信息
                return $this->error('新增失败');
            }
        } else {
            // 显示注册表单
            return $this->fetch();
        }
    }

    // 管理员编辑
    public function edit()
    {
        $model = $this->getModel('user');


        if (IS_POST) {
            $save = input('post.');
            $uid = $save['uid'];

            $username = trim($save['nickname']);
            $hasusername = D('common/User')->where('nickname', $username)->where('uid', '<>', $uid)->value('uid');
            file_log(D('common/User')->getLastSql(), 'sql');
            /* 测试用户名 */
            if (empty($username)) {
                return $this->error('用户名不能为空！');
            } elseif (!preg_match('/[a-zA-Z0-9_]$/', $username)) {
                return $this->error('用户名必须由‘字母’、‘数字’、‘_’组成！');
            } elseif (strlen($username) > 16) {
                return $this->error('用户名长度必须在16个字符以内！');
            } elseif ($hasusername) {
                return $this->error('该用户名已经存在，请重新填写用户名！');
            }

            if (empty($save['mobile'])) {
                return $this->error('手机号码不能为空');
            }
            /* 测试手机号 */
            if (!preg_match('/^[1][3578][0-9]{9}$/', $save['mobile'])) {
                return $this->error('手机格式不正确！');
            }
            if ($save['price'] <= 0) {
                return $this->error('单价不能为空或负数');
            }
            $save['price'] = $save['price'] * 100;//元转分

            $res = D('common/User')->updateInfo($uid, $save);
            if ($res !== false) {
                return $this->success('操作成功！', U('admin'));
            } else {
                return $this->error('保存失败');
            }
        } else {
            $uid = input('uid/d', 0);
            $this->assign('user', M('user')->where('uid', $uid)->find());
            return $this->fetch();
        }
    }

    function set_status()
    {
        $uid = input('uid/d', 0);
        $status = D('common/User')->where('uid', $uid)->value('status');

        $res = D('common/User')->updateInfo($uid, ['status' => 1 - $status]);
        if ($res !== false) {
            return $this->success('操作成功！');
        } else {
            return $this->error('保存失败');
        }
    }

    function set_audit()
    {
        $uid = input('uid/d', 0);

        $res = D('common/User')->updateInfo($uid, ['is_audit' => 1]);
        if ($res !== false) {
            return $this->success('操作成功！');
        } else {
            return $this->error('保存失败');
        }
    }

    function set_reason()
    {
        $uid = input('uid/d', 0);

        $res = D('common/User')->updateInfo($uid, ['audit_reason' => input('reason'), 'is_audit' => 2]);
        if ($res !== false) {
            return $this->success('操作成功！');
        } else {
            return $this->error('保存失败');
        }
    }

    //进入平台管理员界面，需要切换账号
    function enter()
    {
        session('admin_enter', true);
        if ($this->mid != 1) {
            return $this->error('你无权限操作');
        }

        $uid = input('uid/d', 0);
        $myinfo = getUserInfo($uid);
        if (isset($myinfo['is_audit']) && $myinfo['is_audit'] != 1) {
            $msg = $myinfo['is_audit'] == 0 ? '待审核中,请等审核通过后再登录' : '审核不通过，原因：<br/> ' . $myinfo['audit_reason'];

            return $this->error($msg);
        }

        if (isset($myinfo['status']) && $myinfo['status'] != 1) {
            return $this->error('该账号已停用');
        }

        D('common/User')->autoLogin($myinfo, false);
        session('wpid', $uid);

        return redirect(U('code/Count/index'));
    }

    //由平台管理员界面返回超级管理界面，需要切换账号
    function back()
    {
        if (!session('admin_enter')) {
            return $this->error('你无权限操作');
        }
        session('admin_enter', false);


        $myinfo = getUserInfo(1);
        D('common/User')->autoLogin($myinfo, false);
        session('wpid', 1);

        return redirect(U('weixin/user_center/admin?mdm=851'));
    }
}
