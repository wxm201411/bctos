<?php

namespace app\clock_in\model;

use app\common\model\Base;
use think\facade\Db;

// 同时为API和Wap提供数据的模型
class ApiData extends Base
{
    public function initialize(): void
    {
        parent::initialize();
        $fromAgent = input('follow_agent', 0, 'intval');
        if ($fromAgent > 0) {
            // 有分享加盟商，处理为待粉丝
            //D('agent/AgentFans')->deal_relation(get_mid(), $fromAgent, 0);
        }
    }

    function getConfig()
    {
        $config = getAppConfig('clock_in');
        $config['ratio'] = explode(':', $config['ratio'], 3);
        if (input('event_id') == -1) {
            $config['tcp'] = $config['recharge'];
        }
        return $config;
    }

    // 新增/编辑活动消息
    function eventEdit()
    {
        $id = id();
        $data = $this->getInfoWithDefault($id);
        $data['config'] = $this->getConfig();
        $data['info']['prize_money'] = wp_money_format($data['info']['prize_money'] / 100);
        $data['info']['prize_open'] = $data['info']['prize_open'] == 1 ? true : false;

        return $data;
    }

    //保存活动消息
    function eventEditSave()
    {
        $data = input('post.');
        $data['prize_money'] < 0 && $data['prize_money'] = 0;
        $data['prize_money'] = $prize_money = intval($data['prize_money']) * 100;

        $obj = D('common/Models')->getFileInfo('clock_in');
        $moneyArr = parse_field_attr($obj->fields['money_select']['extra']);
        $money_select = isset($moneyArr[$data['money_select']]) ? $moneyArr[$data['money_select']] : $data['money_select'];
        $data['money_int'] = $money_select == '免费' ? 0 : intval($money_select) * 100;

        $uid = get_mid();
        $pay_money = 0;
        if ($data['id'] > 0) {
            $id = $data['id'];
            $info = M('clock_in')->where('id', $id)->find();

            $data['update_at'] = NOW_TIME;
            if ($info['prize_money'] != $prize_money) {
                $pay_money = $prize_money - $info['prize_money'];
                $data['total_money'] = $info['total_money'] + $pay_money;
            }

            if ($pay_money > 0) {
                $title = '支付主播奖金差额';
            } else {
                $title = '退回主播奖金差额';
            }
            if ($pay_money != 0 && $GLOBALS['myinfo']['score'] >= $pay_money) {
                add_credit('auto_add', ['uid' => $uid, 'score' => 0 - $pay_money, 'title' => $title]);
                $data['is_pay'] = 1;
            } elseif ($info['prize_money'] == 0) {
                $data['is_pay'] = 1;
            }

            $data['status'] = 0;

            M('clock_in')->where('id', $id)->update($data);

//            if (!empty($data[''])) {
//                D('common/User')->updateInfo($uid, ['truename' => $data['truename']]);
//            }
        } else {
            $data['wpid'] = get_wpid();
            $data['create_at'] = NOW_TIME;
            $data['uid'] = $uid;
            $data['total_money'] = $pay_money = $prize_money;

            if ($pay_money != 0 && $GLOBALS['myinfo']['score'] >= $pay_money) {
                add_credit('auto_add', ['uid' => $uid, 'score' => 0 - $pay_money, 'title' => '支付主播奖金']);
                $data['is_pay'] = 1;
            } elseif ($pay_money == 0) {
                $data['is_pay'] = 1;
            }
            $id = M('clock_in')->insertGetId($data);
        }


        return $this->success($id);
    }

    //新增/编辑活动口令
    function keywordEdit()
    {
        $event_id = input('event_id');
        $data['info'] = $this->getEvent($event_id);
        $data['lists'] = find_data(M('clock_in_keyword')->where('event_id', $event_id)->select());
        if (!$data['lists']) {
            $data['lists'] = [];
        } else {
            foreach ($data['lists'] as &$vo) {
                $vo['start_time'] = date('H:i', $vo['start_time']);
                $vo['end_time'] = date('H:i', $vo['end_time']);

                $vo['is_show'] = true;
            }
        }

        $data['config'] = $this->getConfig();

        return $data;
    }

    //保存活动消息
    function keywordEditSave()
    {
        $lists = json_decode(input('post.lists'), true);

        $end_time_arr = $start_time_arr = $keyExit = [];
        $config = $this->getConfig();
        foreach ($lists as $k => &$data) {
            if (empty($data['keyword']) || empty($data['day']) || empty($data['start_time']) || empty($data['end_time'])) {
                unset($lists[$k]);
            } else {
                if (isset($keyExit[$data['keyword']])) {
                    return $this->error('关键词不能重复');
                } else {
                    $keyExit[$data['keyword']] = 1;
                }

                $start_time_arr[] = $data['start_time'] = strtotime($data['day'] . ' ' . $data['start_time']);
                $end_time_arr[] = $data['end_time'] = strtotime($data['day'] . ' ' . $data['end_time']);

                if ($data['start_time'] >= $data['end_time']) {
                    return $this->error('关键词:' . $data['keyword'] . ' 的开始时间不能早于结束时间');
                }
                if ($data['end_time'] - $data['start_time'] < $config['effective_time'] * 60) {
                    return $this->error('关键词:' . $data['keyword'] . ' 的打卡有效期不能少于 ' . $config['effective_time'] . ' 分钟');
                }
            }
        }
        if (count($lists) == 0) {
            return $this->error('关键词所有填空不能为空');
        }

        $event_id = input('post.event_id');
        $has = M('clock_in_keyword')->where('event_id', $event_id)->column('id', 'id');

        $keyword_count = 0;
        foreach ($lists as $data) {
            $keyword_count++;
            $data['event_id'] = $event_id;
            if ($data['id'] > 0) {
                $id = $data['id'];
                M('clock_in_keyword')->where('id', $id)->update($data);
                unset($has[$id]);
            } else {
                $id = M('clock_in_keyword')->insertGetId($data);
            }
        }
        if (!empty($has)) {
            M('clock_in_keyword')->whereIn('id', $has)->delete();
        }
        $end_time = max($end_time_arr);
        $start_time = min($start_time_arr);
        M('clock_in')->where('id', $event_id)->update(['keyword_count' => $keyword_count, 'end_time' => $end_time, 'start_time' => $start_time]);

        return $this->success('保存成功');
    }

    function index()
    {
        $type = input('type');
        if ($type == 'my') {
            $map['uid'] = get_mid();
        } elseif ($type == 'join') {
            $map['id'] = ['in', M('clock_in_join')->where('uid', get_mid())->column('event_id')];
        } else {
            $map['status'] = 1;
        }

        $lists = find_data(M('clock_in')->where(wp_where($map))->order('id desc')->select());
        if (empty($lists)) {
            return ['lists' => []];
        }
        $obj = D('common/Models')->getFileInfo('clock_in');
        $extra = $obj->fields['money_select']['extra'];
        $moneyArr = parse_field_attr($extra);
        //dump($moneyArr);
        foreach ($lists as &$vo) {
            $vo['user'] = getUserBaseInfo($vo);
            $vo['money_select'] = isset($moneyArr[$vo['money_select']]) ? $moneyArr[$vo['money_select']] : $vo['money_select'];
        }
        return ['lists' => $lists];
    }

    function center()
    {
        $data['score'] = wp_money_format($GLOBALS['myinfo']['score'] / 100);
        $data['my_event_count'] = M('clock_in')->where('uid', get_mid())->count();
        $data['my_join_count'] = M('clock_in_join')->where('uid', get_mid())->count();
        return $data;
    }

    function detail()
    {
        $uid = get_mid();
        $event_id = input('event_id/d', 0);
        $info = $this->getEvent($event_id);

        //口令信息
        $keywords = find_data(M('clock_in_keyword')->where('event_id', $event_id)->field('id,day,start_time,end_time,keyword')->select());
        $recodeArr = M('clock_in_recode')->where('uid', $uid)->where('event_id', $event_id)->column('id', 'keyword');
        if ($keywords) {
            foreach ($keywords as &$k) {
                $k['start_time_txt'] = date('H:i', $k['start_time']);
                $k['end_time_txt'] = date('H:i', $k['end_time']);
                $k['is_done'] = isset($recodeArr[$k['keyword']]) ? 1 : 0;
            }
        }

        //参加信息
        $info['has_join'] = 0;

        $obj = D('common/Models')->getFileInfo('clock_in_join');
        $statusArr = parse_field_attr($obj->fields['status']['extra']);

        $join_users = find_data(M('clock_in_join')->where('event_id', $event_id)->field(true)->select());
        $all_money = $info['total_money'];
        foreach ($join_users as &$u) {
            if ($u['uid'] == $uid) {
                $info['has_join'] = 1;
            }

            $u['create_at'] = friendlyDate($u['create_at']);
            $u['user'] = getUserBaseInfo($u);
            $u['status'] = isset($statusArr[$u['status']]) ? $statusArr[$u['status']] : $u['status'];
            $all_money += $u['pay_money'];
        }
        $imageUrl = $this->getActivityImg($info);
        $share = ['title' => $info['title'], 'imageUrl' => $imageUrl];

        $has_clockin_success = 0;
        //判断当前用户是否打卡成功
        if ($info['has_join'] && count($recodeArr) == count($keywords)) {
            $has_clockin_success = 1;
        }

        return ['info' => $info, 'keywords' => $keywords, 'join_users' => $join_users, 'all_money' => $all_money, 'share' => $share, 'has_clockin_success' => $has_clockin_success];
    }

    //参数 活动模板图片，二维码url，模板内二维码的位置
    private function getActivityImg($info)
    {
        $file_path = '/storage/wxacode/' . PBID . '/event_' . $info['id'] . '_share.png';

        if (file_exists(SITE_PATH . '/public' . $file_path)) {
            return SITE_URL . $file_path;
        }

        //海报模板
        $template = SITE_PATH . '/public/static/base/images/template.png';
        //海报的二维码位置
        $templateX = '350';
        $templateY = '200';
        //在海报中二维码的宽度，由于二维码是正方型，所以高也一样
        $qrShowWidth = '300';

        //获取小程序二维码
        $param = ['scene' => $info['id'] . ':' . get_mid(), 'width' => $qrShowWidth];
        $qrcode = D('weiapp/ApiData')->makeCode($param, 'B');
        if ($qrcode['status'] == 0) {
            throw new \think\Exception($qrcode['msg'], 10002);
        }

        //海报图象连接资源
        $bg = imagecreatefromstring(file_get_contents($template));
        $bgWidth = imagesx($bg);
        $bgHeigth = imagesy($bg);

        //二维码图象连接资源
        $qr = imagecreatefrompng($qrcode['path']);
        $qrWidth = imagesx($qr);
        $qrHeigth = imagesy($qr);


        //创建一个和海报一样大小的真彩色画布（ps：只有这样才能保证后面copy装备图片的时候不会失真）
        $canvas = imageCreatetruecolor($bgWidth, $bgHeigth);

        //为真彩色画布创建白色背景，再设置为透明
        $color = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $color);
        //imageColorTransparent($canvas, $color); //可以设为透明背景

        //首先将海报画布采样copy到真彩色画布中，不会失真
        imagecopyresampled($canvas, $bg, 0, 0, 0, 0, $bgWidth, $bgHeigth, $bgWidth, $bgHeigth);

        //再将二维码图片copy到已经具有人物图像的真彩色画布中，同样也不会失真
        $this->imagecopymerge_alpha($canvas, $qr, $templateX, $templateY, 0, 0, $qrWidth, $qrHeigth, 100);

        //将画布保存到指定的png文件
        imagepng($canvas, SITE_PATH . '/public' . $file_path);

        imagedestroy($bg);
        imagedestroy($qr);

        //返回生成的路径
        return SITE_URL . $file_path;
    }

    function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity)
    {
        // getting the watermark width
        $w = imagesx($src_im);
        // getting the watermark height
        $h = imagesy($src_im);

        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);
        // copying that section of the background to the cut
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        // inverting the opacity
        //$opacity = 100 - $opacity;

        // placing the watermark now
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
    }

    private function getEvent($event_id)
    {
        $info = find_data(M('clock_in')->where('id', $event_id)->find());
        if (empty($info)) {
            throw new \think\Exception('活动不存在', 10001);
        }

        $obj = D('common/Models')->getFileInfo('clock_in');

        $moneyArr = parse_field_attr($obj->fields['money_select']['extra']);
        $prizeTypeArr = parse_field_attr($obj->fields['prize_type']['extra']);
        $statusArr = parse_field_attr($obj->fields['status']['extra']);
        //dump($moneyArr);

        $info['user'] = getUserBaseInfo($info);
        $info['money_select'] = isset($moneyArr[$info['money_select']]) ? $moneyArr[$info['money_select']] : $info['money_select'];
        $info['money_int'] = $info['money_select'] == '免费' ? 0 : intval($info['money_select']);
        $info['prize_type'] = isset($prizeTypeArr[$info['prize_type']]) ? $prizeTypeArr[$info['prize_type']] : $info['prize_type'];
        $info['status_txt'] = isset($statusArr[$info['status']]) ? $statusArr[$info['status']] : $info['status'];
        return $info;
    }

    function join()
    {
        $event_id = input('event_id/d', 0);
        $info = $this->getEvent($event_id);
        return ['info' => $info];
    }

    function doJoin($type = '')
    {
        $data['event_id'] = $event_id = input('event_id/d', 0);

        $type = input('type');
        if ($type == 'main') {
            $event = find_data(M('clock_in')->where('id', $event_id)->find());
            $param = ['uid' => $event['uid'], 'score' => 0 - $event['prize_money'], 'title' => '支付主播奖金'];
            $res = add_credit('auto_add', $param);
            if (!$res) {
                return $this->success('支付失败');
            }

            M('clock_in')->where('id', $event_id)->update(['is_pay' => 1]);

            return $this->success("支付成功");
        } else {
            $data['uid'] = get_mid();


            $money = M('clock_in')->where('id', $event_id)->value('money_int');

            $check = M('clock_in_join')->where($data)->find();
            if ($check) {
                if ($check['is_pay'] == 1) {
                    return $this->error('请勿重复支付');
                }
                $id = $check['id'];
            } else {
                $data['create_at'] = NOW_TIME;
                $data['is_pay'] = 1;
                $data['pay_money'] = $money;
                $id = M('clock_in_join')->insertGetId($data);
                M('clock_in')->where('id', $event_id)->inc('join_count')->update();
            }

            //余额支付
            $param = ['uid' => $data['uid'], 'score' => 0 - $money, 'title' => '参与活动,ID：' . $event_id];
            $res = add_credit('auto_add', $param);
            if (!$res) {
                //执行上链处理
                D('clock_in/Service')->UserJoinEvent($data['uid'], $event_id);

                return $this->success('参加失败');
            }

            return $this->success($id);
        }
    }

    function clockIn()
    {
        $data['uid'] = $uid = get_mid();
        $data['event_id'] = $event_id = input('event_id/d', 0);

        $join = find_data(M('clock_in_join')->where($data)->find());
        if (!$join) {
            return $this->error('要先参与活动才能打卡');
        }

        $keyword = trim(input('keyword'));
        if (!$keyword) {
            return $this->error('请输入打卡口令');
        }

        $recodes = find_data(M('clock_in_recode')->where($data)->select());
        foreach ($recodes as $r) {
            if ($r['keyword'] == $keyword) {
                return $this->error('请勿重复打卡');
            }
        }

        //判断口令是否正确
        $keywords = find_data(M('clock_in_keyword')->where('event_id', $event_id)->select());
        $info = [];
        foreach ($keywords as $k) {
            if ($k['keyword'] == $keyword) {
                $info = $k;
            }
        }
        if (!$info) {
            return $this->error('口令不正确');
        }

        //判断打卡时间对不对
        //$info['end_time'] += 1800; //最多结束后30分钟内有效
        if (NOW_TIME < $info['start_time']) {
            return $this->error('该口令还没生效，请注意打卡的有效时间');
        }
        if (NOW_TIME > $info['end_time']) {
            return $this->error('该口令已过期，请注意打卡的有效时间');
        }

        //正式打卡
        $data['keyword'] = $keyword;
        $data['create_at'] = NOW_TIME;
        $id = M('clock_in_recode')->insertGetId($data);
        if (!($id > 0)) {
            return $this->error('网络拥堵，请一会重试');
        }

        //执行上链处理
        D('clock_in/Service')->UserClockIn($uid, $event_id, $keyword);

        //判断是否打卡成功
        $keyword_count = count($keywords);

        $has_finish = true;
        if ($keyword_count > 1) {
            //有多次打卡的情况下，要判断是否全部都打卡
            $recode_count = count($recodes) + 1;
            if ($recode_count < $keyword_count) {
                $has_finish = false; //还没完成全部的打卡
            }
        }

        if ($has_finish) {
            M('clock_in_join')->where('id', $join['id'])->update(['status' => 1, 'finish_at' => NOW_TIME]);
            $refund_desc = '打卡成功退款(' . $event_id . ')';
            if ($join['pay_type'] == 0) {
                add_credit('auto_add', ['uid' => $uid, 'score' => $join['pay_money'], 'title' => $refund_desc], 0);
            } else {
                $info = D('common/Publics')->getInfoById(PBID);
                //退还参与金额--微信支付退款
                $refund_money = $join['pay_money'];
                $refund_money = 1; //TODO 调试期间只退1分钱
                $pay = D('weixin/Payment')->refund($info['appid'], $join['out_trade_no'], $refund_money, $refund_desc);
                if ($pay['status'] == 0) {
                    return $this->error($pay['msg']);
                }
            }
            if ($join['pay_money'] > 0) {
                return $this->success('恭喜您，打卡挑战成功，已为您原路退回参与款，活动奖金将在活动结束后半小时内发放到余额中');
            } else {
                return $this->success('恭喜您，打卡挑战成功，活动奖金将在活动结束后半小时内发放到余额中');
            }
        } else {
            return $this->success('此次打卡任务完成，请留意下次打卡时间，再接再励！');
        }
    }

    function updateClockInFailUser()
    {
        $event_ids = M('clock_in_join')->where('status', 0)->where('is_pay', 1)->group('event_id')->column('event_id', 'event_id');
        if (!$event_ids) {
            return false;
        }

        //只需要获取已经开始或结束的活动就行,缩小范围
        $event_ids = M('clock_in')->whereIn('id', $event_ids)->where('start_time', '<', NOW_TIME)->column('id', 'id');
        if (!$event_ids) {
            return false;
        }

        /***
         * 打卡失败的情况是：
         * 只有一次打卡的，活动结束还没打卡的判为失败
         * 有多次打卡的，只要有一次在打卡结束后还没打卡的判为失败，可能不需要等活动结束
         * 总结：以口令有效结束时间此准，判断一个活动中，一个用户应该打卡的关键词数与用户实际的打卡数是否相同，不同则判为打卡失败
         **/

        //批量获取已经过期的口令的数量
        $keywords = find_data(M('clock_in_keyword')->whereIn('event_id', $event_ids)->where('end_time', '<', NOW_TIME)->field('event_id')->select());
        $countArr = [];
        foreach ($keywords as $k) {
            $countArr[$k['event_id']] += 1;
        }

        //获取要处理的用户ID，排除已经打卡成功或已经处理过的用户，,缩小查找范围
        $uids = M('clock_in_join')->whereIn('event_id', $event_ids)->where('status', 0)->where('is_pay', 1)->group('uid')->column('uid', 'uid');
        if (!$uids) {
            return false;
        }
        //批量获取用户打卡次数
        $recodes = find_data(M('clock_in_recode')->whereIn('event_id', $event_ids)->whereIn('uid', $uids)->field('event_id,uid')->select());
        $userArr = [];
        foreach ($recodes as $r) {
            $userArr[$r['event_id']][$r['uid']] += 1;
        }

        //开始比较
        $fault = $update_event_ids = [];
        foreach ($userArr as $event_id => $u) {
            $event_count = $countArr[$event_id]; //该活动应该打卡的次数
            foreach ($u as $uid => $user_count) { //该用户打卡的次数
                if ($user_count < $event_count) { //用户打卡的次数少于活动应该打的次数，判为打卡失败
                    $fault[] = [['event_id', '=', $event_id], ['uid', '=', $uid]];
                    $update_event_ids[$event_id] = $event_id;
                }
            }
        }
        if (empty($update_event_ids))
            return false;

        //批量更新数据表
        M('clock_in_join')->whereOr($fault)->update('status', 2);

        //计算奖金，并更新活动总奖金
        $inArr = M('clock_in')->whereIn('id', $update_event_ids)->column('money_int,prize_money,prize_open', 'id');
        foreach ($update_event_ids as $eid) {
            if ($inArr[$eid]['money_int'] == 0) {   //免费参与的活动不需要更新奖金
                continue;
            }

            $fault_count = M('clock_in_join')->where('event_id', $eid)->where('status', 2)->count();
            $total_money = $fault_count * $inArr[$eid]['money_int'];
            if ($inArr[$eid]['prize_open']) {
                $total_money += $inArr[$eid]['prize_money'];
            }

            M('clock_in')->where('id', $eid)->update(['total_money' => $total_money]);
        }
    }

    //活动结束自动结算奖金
    function eventEnd()
    {
        set_time_limit(0);

        $over_time = NOW_TIME - 180;//活动结束后3分钟再结算奖金
        $info = find_data(M('clock_in')->where('status', '<', 6)->where('is_pay', 1)->where('end_time', '>', 0)->where('end_time', '<', NOW_TIME)->find());
        if (!$info) {
            return false;
        }

        //判断用户的打卡信息是否全部处理完毕
        $check = M('clock_in_join')->where('event_id', $info['id'])->where('status', 0)->count();
        if ($check > 0) {
            return false;//还没处理完，待处理完再更新
        }


        M('clock_in')->where('id', $info['id'])->update(['status' => 6]);

        //获取打卡的用户ID
        $uids = M('clock_in_join')->where('event_id', $info['id'])->where('status', 1)->order('finish_at asc')->column('uid');
        $uid_count = count($uids);
        if ($uid_count == 0) {
            //没有打卡成功的
            return false;
        }
        /***
         * 资金分配方案
         * 没有主播奖金的活动，只有打卡失败的累计奖金，10%归平台，20%归主播，70%打卡成功者平分
         * 有主播奖金的活动，主播奖金部分按主播设置的颁奖方式90%分配给打卡成功者，10%归平台，打卡失败的累计奖金部分分配方式同上
         **/
        $config = $this->getConfig();
        $ratio = $config['ratio'];
        $moneyArr = [];
        //分配主播奖金
        $total_money = $info['total_money'] * 100;//由元转为分
        if ($info['prize_open'] && $info['prize_money'] > 0 && $info['prize_type'] > 0) {
            $info['prize_money'] *= 100;
            $total_money -= $info['prize_money'];

            $obj = D('common/Models')->getFileInfo('clock_in');

            $prizeTypeArr = parse_field_attr($obj->fields['prize_type']['extra']);
            $num = intval(str_replace('前', '', $prizeTypeArr[$info['prize_type']]));
            if ($uid_count < $num) {
                //用户数不足，以用户数为准
                $num = $uid_count;
            }
            //10%归平台
            $admin_money = floor($info['prize_money'] * $ratio[0] / 100);
            add_credit('auto_add', ['uid' => 1, 'score' => $admin_money, 'title' => "主播奖金{$ratio[0]}%归平台（{$info['id']})"], 0);
            //90%分配给打卡成功者
            $reward_money = $info['prize_money'] - $admin_money;
            $this->reward($reward_money, $num, $uids, $moneyArr);
        }

        //分配打卡失败的累计奖金
        //10%归平台
        $admin_money = floor($total_money * $ratio[0] / 100);
        add_credit('auto_add', ['uid' => 1, 'score' => $admin_money, 'title' => "累计奖金{$ratio[0]}%归平台（{$info['id']})"], 0);
        //20%归主播
        $user_money = floor($total_money * $ratio[1] / 100);
        add_credit('auto_add', ['uid' => 1, 'score' => $admin_money, 'title' => "累计奖金{$ratio[1]}%赠主播（{$info['id']})"], 0);
        //70%打卡成功者平分
        $total_money = $total_money - $user_money - $admin_money;
        $this->reward($total_money, $uid_count, $uids, $moneyArr);

        foreach ($moneyArr as $uid => $money) {
            add_credit('auto_add', ['uid' => $uid, 'score' => $money, 'title' => "打卡成功奖金（{$info['id']}"], 0);
        }
    }

    private function reward($total_money, $num, $uids, &$moneyArr)
    {
        if ($num > $total_money) {
            //当设置的金额不足每个人1分钱时，只分配前面的用户
            $num = $total_money;
        }
        $average = floor($total_money % $num);
        $yu = $total_money % $num;
        for ($i = 0; $i < $num; $i++) {
            $uid = $uids[$i];
            $moneyArr[$uid] = $average;
            if ($yu > 0 && $i < $yu) {
                $moneyArr[$uid] += 1;
            }
        }
    }

    /**
     * 积分记录
     *
     * @return boolean|unknown
     */
    function scoreList()
    {
        $map ['uid'] = $this->mid;
        $map ['wpid'] = get_wpid();

        $data ['score_data'] = find_data(M('credit_data')->where(wp_where($map))->field("cTime,credit_name,score,credit_title")->order('id desc')->select());
        foreach ($data['score_data'] as &$v) {
            $v['allday'] = friendlyDate($v['cTime']);
            $v['score'] = wp_money_format($v['score'] / 100);
        }
        return $data;
    }

    // 新增/编辑活动消息
    function cash()
    {
        //获取模型信息
        $model = get_model('clock_in_cash');
        $obj = D('common/Models')->getFileInfo($model);

        $default = $extra = [];
        foreach ($obj->fields as $key => $value) {
            $default[$key] = $value['value'];
            if ($value['extra']) {
                $extra[$key] = parse_field_attr($value['extra']);
            }
        }

        $info = find_data(M($model['name'])->where('uid', get_mid())->order('id desc')->find());
        if (!$info) {
            $info = $default;
        }
        $info['money'] = '';
        $img_srcs = $img_ids = [];
        if (!empty($info['qrcode'])) {
            $img_srcs[] = get_cover_url($info['qrcode']);
            $img_ids[] = $info['qrcode'];
        }

        return ['info' => $info, 'extra' => $extra, 'img_srcs' => $img_srcs, 'img_ids' => $img_ids];
    }

    function cashSave()
    {
        $data = input('post.');
        unset($data['id']);
        $uid = get_mid();

        $data['wpid'] = get_wpid();
        $data['create_at'] = NOW_TIME;
        $data['uid'] = $uid;
        $data['money'] *= 100;//转为分

        $id = M('clock_in_cash')->insertGetId($data);
        if ($id > 0) {
            add_credit('auto_add', ['uid' => $uid, 'score' => 0 - $data['money'], 'title' => '提现']);
            return $this->success('保存成功');
        } else {
            return $this->error('网络繁忙，请一会再试');
        }
    }

    function cash_recode()
    {
        $obj = D('common/Models')->getFileInfo('clock_in_cash');
        $statusArr = parse_field_attr($obj->fields['status']['extra']);

        $map ['uid'] = $this->mid;
        $map ['wpid'] = get_wpid();

        $data ['score_data'] = find_data(M('clock_in_cash')->where(wp_where($map))->field("id,create_at,status,money")->order('id desc')->select());
        foreach ($data['score_data'] as &$v) {
            $v['allday'] = friendlyDate($v['create_at']);
            $v['money'] = wp_money_format($v['money'] / 100);
            $v['status'] = $statusArr[$v['status']];
        }
        return $data;
    }

    function doPay()
    {
        $id = input('id');
        $type = input('type');
        if ($type == 'main') {
            $total_fee = find_data(M('clock_in')->where('id', $id)->value('prize_money'));
            $out_trade_no = 'main' . $id;
            $title = '支付主播奖金';

            $jump_url = '/pages/detail/detail?event_id=' . $id;
        } else {
            $data['event_id'] = $event_id = input('event_id/d', 0);
            $data['uid'] = get_mid();
            $check = M('clock_in_join')->where($data)->find();
            if ($check) {
                if ($check['is_pay'] == 1) {
                    return $this->error('请勿重复支付');
                }
                $id = $check['id'];
            } else {

                $event = find_data(M('clock_in')->where('id', $event_id)->find());
                if ($event['status'] == 6) {
                    return $this->error('活动已结束');
                }
                $total_fee = $event['money_int'];

                $data['create_at'] = NOW_TIME;
                $data['is_pay'] = 0;
                $data['pay_type'] = 1;
                $data['pay_money'] = $total_fee;
                $data['out_trade_no'] = $out_trade_no = date('YmdHis') . rand(1000, 9999);
                $id = M('clock_in_join')->insertGetId($data);
                M('clock_in')->where('id', $event_id)->inc('join_count')->update();
            }
            $title = '支付参与费用';

            $jump_url = '/pages/detail/detail?event_id=' . $event_id;
        }

        $info = D('common/Publics')->getInfoById(PBID);
        if (empty($info['mch_id'])) {
            return $this->error('还没配置支付信息');
        }
        $total_fee = 1;//TODO 测试期间先固定1分钱
        $product = [
            'openid' => input('openid'),
            'body' => $title,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee,
            'spbill_create_ip' => '47.106.212.16'
        ];
        $callback = 'clock_in/ApiData/payOk';

        $pay = D('weixin/Payment')->weiapp_pay($info['appid'], $product, $callback);
        if ($pay['status'] == 0) {
            return $this->error($pay['msg']);
        }
        $data['pay'] = $pay;
        $data['jump_url'] = $jump_url;
        $data['code'] = 1;

        return $data;
    }

    function payOk($data)
    {
        if (strpos($data['out_trade_no'], 'main') === false) {
            $join = M('clock_in_join')->where('out_trade_no', $data['out_trade_no'])->find();
            M('clock_in_join')->where('out_trade_no', $data['out_trade_no'])->update(['is_pay' => 1]);

            //执行上链处理
            D('clock_in/Service')->UserJoinEvent($join['uid'], $join['event_id']);
        } else {
            $arr = explode("main", $data['out_trade_no']);
            $id = $arr[1];
            M('clock_in')->where('id', $id)->update(['is_pay' => 1]);
        }

        return [
            'status' => 1
        ];
    }

    function upChain()
    {
        $event_id = input('event_id/d', 0);
        M('clock_in')->where('id', $event_id)->update(['audit_status' => 1, 'status' => 1]);

        //执行上链处理
        D('clock_in/Service')->EventReg($event_id);
        return $this->success('你的活动已经上传到区块链，请邀请给朋友们一起来打卡吧');
    }

    function editUser()
    {
        $data = input('post.');
        if (isset($data['headimgurl']) && $data['headimgurl'] > 0) {
            $data['headimgurl'] = get_cover_url($data['headimgurl']);
        }
        D('common/User')->updateInfo($this->mid, $data);
        return $this->success('更新成功');
    }
}
