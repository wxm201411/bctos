<?php

namespace app\weiapp\controller;

use app\common\controller\ApiBase;
use decrypdata;

class Api extends ApiBase
{
    protected $apiModel;

    function initialize()
    {
        parent::initialize();
        $this->apiModel = D('ApiData');
    }

    function index()
    {
        // 接口请求日志记录 TODO
        $param = input();
        if (!isset($param['act'])) {
            return $this->error('参数出错');
        }
        $mod = isset($param['mod']) ? $param['mod'] : 'weiapp';
        $act = $param['act'];

        $res = D($mod . '/Service')->$act($param);
        // 接口返回日志记录 TODO
        return $res;
    }

    function sendSessionCode()
    {
        $code = input('code');
        $config = D('common/Publics')->getInfoById(PBID);
        $param = [
            'appid' => $config['appid'],
            'secret' => $config['secret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        //dump($config);
        if ($config['is_bind']) {
            $component_appid = config('COMPONENT_APPID');
            $component_appsecret = D('PublicBind/PublicBind')->_get_component_access_token();
            //dump($component_appsecret);exit;
            $url = "https://api.weixin.qq.com/sns/component/jscode2session?appid={$param['appid']}&js_code={$param['js_code']}&grant_type=authorization_code&component_appid={$component_appid}&component_access_token={$component_appsecret}";
        } else {
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$param['appid']}&secret={$param['secret']}&js_code={$param['js_code']}&grant_type=authorization_code";
        }
        $data = post_data($url, []);
        if ((isset($data['errcode']) && $data['errcode'] == '40029') || !isset($data['session_key']) || !isset($data['openid'])) {
            return $this->apiError('获取微信信息失败！', [], 410001);
        } else {
            $data['openid'] = isset($data['openid']) ? $data['openid'] : '';
            $uid = M('public_follow')->where('openid', $data['openid'])->value('uid');
            $data['session_key'] = isset($data['session_key']) ? $data['session_key'] : '';
            session('session_key', $data['session_key']);
            session('openid', $data['openid']);

            if (!$uid) {
                $data['unionid'] = isset($data['unionid']) ? $data['unionid'] : '';
                empty($data['unionid']) || $uid = M('public_follow')->where('unionid', $data['unionid'])->value('uid');
                $uid = intval($uid);
                if (!($uid > 0)) {
                    $uid = M('user')->insertGetId([
                        'reg_time' => NOW_TIME,
                        'unionid' => $data['unionid']
                    ]);
                }
                $addData['openid'] = $data['openid'];
                $addData['unionid'] = $data['unionid'];
                $addData['uid'] = $uid;
                $addData['pbid'] = get_pbid();
                M('public_follow')->insert($addData);
            } elseif (!empty($data['unionid'])) {
                $re = M('public_follow')->alias('a')
                    ->join(DB_PREFIX . 'user b', 'a.uid = b.uid')
                    ->where('a.unionid', $data['unionid'])
                    ->find();
                session('user_info', $re);
                if (empty($re['unionid']) && !empty($data['unionid'])) {
                    // 处理历史数据
                    M('public_follow')->where('openid', $data['openid'])->update([
                        'unionid' => $data['unionid']
                    ]);
                }

                $uid = $re['uid'];
            }
            $this->saveSession();
            session('mid', $uid);
            session('uid', $uid);
            session('openid_' . get_pbid(), $data['openid']);

            $return['token'] = $this->token;
            $return['uid'] = $uid;
            $return['openid'] = $data['openid'];
            $return['unionid'] = isset($data['unionid']) ? $data['unionid'] : '';
            return $this->apiSuccess($return);
        }
    }

    public function checkLogin()
    {
        if ($this->mid > 0) {
            return $this->apiSuccess([
                $this->mid
            ]);
        } else {
            return $this->apiError('用户登录消息不存在，请重新登录');
        }
    }

    function saveUserInfo()
    {
        $encryptedData = input('encryptedData');
        $iv = input('iv');
        $config = D('common/Publics')->getInfoById(PBID);
        if ($encryptedData != '' && $iv != '') {
            include_once SITE_PATH . '/vendor/decrypdata/wxBizDataCrypt.php';

            $appid = $config['appid'];
            $sessionKey = session('session_key');

            $pc = new \WXBizDataCrypt($appid, $sessionKey);
            $data = [];
            $errCode = $pc->decryptData($encryptedData, $iv, $data);

            if ($errCode == 0) {
                $data = json_decode($data, true);
                session('user_info', $data);
                $save = [
                    'nickname' => $data['nickName'],
                    'sex' => $data['gender'],
                    'language' => $data['language'],
                    'city' => $data['city'],
                    'province' => $data['province'],
                    'country' => $data['country'],
                    'headimgurl' => $data['avatarUrl']
                ];
                isset($data['unionId']) && $save['unionid'] = $data['unionId'];
                $mid = session('mid');
                $user = getUserInfo($mid);
                if (empty($user['unionid']) && isset($data['unionId'])) {
                    M('public_follow')->where('openid', $data['openId'])->update([
                        'unionid' => $data['unionId']
                    ]);
                }
                if ($mid > 0) {
                    empty($user['truename']) && D('common/User')->updateInfo($mid, $save);
                    $save['uid'] = $mid;
                    $save['wpid'] = get_pbid();
                    D('common/User')->autoLogin($save);
                    return $this->apiSuccess(true);
                } else {
                    return $this->apiError('登录错误', 4100025);
                }
            } else {
                // print($errCode . "\n");
                return $this->apiError($errCode, []);
            }
        } else {
            return $this->apiError('缺少用户加密信息', 140002);
        }
    }

    // 保存小程序的图片
    public function upload()
    {
        /* 返回标准数据 */
        $return = array(
            'status' => 1,
            'info' => '上传成功',
            'data' => ''
        );

        /* 调用文件上传组件上传文件 */
        $Picture = D('home/Picture');
        $pic_driver = strtolower(config('app.picture_upload_driver'));

        $info = $Picture->upload(config('app.picture_upload'), config('app.picture_upload_driver'), config("app.upload_{$pic_driver}_config")); // TODO:上传到远程服务器

        /* 记录图片信息 */
        if ($info) {
            $return['status'] = 1;
            $return = array_merge($info['download'], $return);
        } else {
            $return['status'] = 0;
            $return['info'] = $Picture->getError();
        }
        add_debug_log($return, 'upload_return');
        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }

    function saveSession()
    {
        $list = D('shop/Cart')->saveSession();

        return $list;
    }

    // 获取二维码
    public function getwxacode($array = false)
    {
        $type = I('type', 'A');
        $param = I('param');
        $result = D('ApiData')->makeCode($param, $type);

        return $this->returncode($result, $array);
    }

    function post_data($url, $param = [], $type = 'json', $return_array = true, $useCert = [])
    {
        $has_json = false;
        if ($type == 'json' && is_array($param)) {
            $has_json = true;
            $param = json_encode($param, JSON_UNESCAPED_UNICODE);
        } elseif ($type == 'xml' && is_array($param)) {
            $param = ToXml($param);
        }
        add_debug_log($url, 'post_data');

        // 初始化curl
        $ch = curl_init();
        if ($type != 'file') {
            add_debug_log($param, 'post_data');
            // 设置超时
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        } else {
            // 设置超时
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // 设置header
        if ($type == 'file') {
            $header[] = "content-type: multipart/form-data; charset=UTF-8";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        } elseif ($type == 'xml') {
            curl_setopt($ch, CURLOPT_HEADER, false);
        } elseif ($has_json) {
            $header[] = "content-type: application/json; charset=UTF-8";
            //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        // curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        // dump($param);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 使用证书：cert 与 key 分别属于两个.pem文件
        if (isset($useCert['certPath']) && isset($useCert['keyPath'])) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $useCert['certPath']);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $useCert['keyPath']);
        }

        $res = curl_exec($ch);
        //dump($res);
        if ($type != 'file') {
            add_debug_log($res, 'post_data');
        }
        // echo $res;die;
        $flat = curl_errno($ch);

        $msg = '';
        if ($flat) {
            $msg = curl_error($ch);
        }
        if ($flat) {
            return [
                'code' => $flat,
                'msg' => $msg
            ];
        } else {
            if ($return_array && !empty($res)) {
                $res = $type == 'json' ? json_decode($res, true) : FromXml($res);
            }

            return $res;
        }
    }

    function payment()
    {
        $info = get_pbid_appinfo();

        $money = I('money');
        $body = I('body');
        if (empty($body)) {
            // 商家名称-销售商品类目
            $body = $info ['public_name'] . '-服务购买';
        }
        $out_trade_no = I('out_trade_no');
        if (empty($out_trade_no)) {
            $out_trade_no = date('ymd') . NOW_TIME . rand(100, 999);
        }
        $openid = I('openid');
        if (empty($openid)) {
            $pbid = get_pbid();
            $openid = $GLOBALS ['myinfo'] [$pbid] ['openid'];
        }

        $appid = $info ['appid'];
        $param ['body'] = $body;
        $param ['out_trade_no'] = $out_trade_no;
        $param ['total_fee'] = $money * 100;
        $param ['openid'] = $openid;
        $param ['mch_id'] = $info ['mch_id'];
        $param ['partner_key'] = $info ['partner_key'];
        $param ['attach'] = I('username');

        $order = D('weixin/Payment')->weiapp_pay($appid, $param, 'Home/Service/payok');

        echo json_url($order);
    }

    function send_message()
    {
        // 发送模板消息给用户
        $openid = I('openid');
        $formId = I('formId');
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . get_access_token();
        $param ['touser'] = $openid;
        $param ['template_id'] = 'La8USWZSU0E4VK_NERYtDrwmLOBWif5ch57qZM7w4V4';
        $param ['page'] = 'pages/index/index';
        $param ['form_id'] = $formId;

        $param ['data'] = [
            'keyword1' => [
                'value' => '100001',
                'color' => '#173177'
            ],
            'keyword2' => [
                'value' => '小韦云',
                'color' => '#173177'
            ],

            'keyword3' => [
                'value' => date('Y-m-d H:i:s'),
                'color' => '#173177'
            ],
            'keyword4' => [
                'value' => '0.00元',
                'color' => '#173177'
            ],
            'keyword5' => [
                'value' => '1.00元',
                'color' => '#173177'
            ],
            'keyword6' => [
                'value' => '100分',
                'color' => '#173177'
            ],
            'keyword7' => [
                'value' => '100分',
                'color' => '#173177'
            ],
            'keyword8' => [
                'value' => '1706261498468955374',
                'color' => '#173177'
            ]
        ];

        $res = post_data($url, $param);

        echo json_url($res);
    }

    private function returncode($result, $array = false)
    {
        if ($array) {
            return $result;
        } else {
            $this->ajaxReturn($result);
        }
    }

    function sendCode()
    {
        $mobile = I('mobile');
        if (empty($mobile)) {
            echo $this->apiError('手机号码不能为空');
            exit();
        }
        /* 测试手机号 */
        if (!preg_match('/^[1][3578][0-9]{9}$/', $mobile)) {
            echo api_error('手机格式不正确！');
            exit();
        }

        $res = D('Sms/Sms')->sendSms($mobile);
        $res ['uid'] = $this->mid;
        echo json_url($res);
    }

    function register()
    {
        $map ['mobile'] = $save ['mobile'] = $mobile = I('mobile');
        $code = I('code');

        // 验证码判断
        $check = D('sms/Sms')->checkSms($mobile, $code);

        if ($check ['result'] == 0) {
            echo $this->apiError($check ['msg']);
            exit();
        }
        add_debug_log($this->mid, 'reg_uid');
        $res1 = M('user')->where($map)->find();
        if ($res1) {
            echo $this->apiError('号码已被注册过！');
        } else {
            // dump($_SESSION);
            $res = D('common/User')->updateInfo($this->mid, $save);
            if ($res) {
                echo $this->apiSuccess();
            } else {
                echo $this->apiError('注册失败');
            }
        }
    }

    // 卡券URL升级为小程序
    function wxAppCard()
    {
        $card_id = 'prgF0txhQxLw5fRWIy068GyPJcQk';
        $token = 'gh_6d3bf5d72981';
        $app_page = 'pages/index/index';

        $param = [
            'card_id' => $card_id,
            'general_coupon' => [
                'base_info' => [
                    'custom_url_name' => '小程序',
                    'custom_url' => 'https://leyao.tv/weishop/index.php?s=/Api/Api/wxAppCard',
                    'custom_app_brand_user_name' => $token . '@app',
                    'custom_app_brand_pass' => $app_page,
                    'center_app_brand_user_name' => $token . '@app',
                    'center_app_brand_pass' => $app_page,
                    'custom_url_sub_title' => '点击进入',
                    'promotion_url_name' => '小程序',
                    'promotion_url' => 'https://leyao.tv/weishop',
                    'promotion_app_brand_user_name' => $token . '@app',
                    'promotion_app_brand_pass' => $app_page
                ]
            ]
        ];
        // dump($param);exit;
        $access_token = 'hbeiWYjv7UpvoXQ_aGBpf3o33uA_gRQCCCQsbERKjWqM8gn-pepjJayDTsO2Ts-xkvdteo7SCl41Fo4tPAZeGQZkvDxZdGMCaAxSbMFQLAdghp4l84MuO5J59rv0v-0rZOWeAIAODL';
        $url = "https://api.weixin.qq.com/card/update?access_token=" . $access_token; // get_access_token ($token);

        $res = post_data($url, $param);
        echo json($res);
    }

    // 用户领取卡券
    function addCard()
    {
        $card_id = 'prgF0txhQxLw5fRWIy068GyPJcQk';
        $token = 'gh_6d3bf5d72981';

        $access_token = 'hbeiWYjv7UpvoXQ_aGBpf3o33uA_gRQCCCQsbERKjWqM8gn-pepjJayDTsO2Ts-xkvdteo7SCl41Fo4tPAZeGQZkvDxZdGMCaAxSbMFQLAdghp4l84MuO5J59rv0v-0rZOWeAIAODL';
        $ticket = $this->api_ticket($access_token);

        // $param ['code'] = '';
        $param ['api_ticket'] = $ticket;
        $param ['card_id'] = $card_id;
        $param ['timestamp'] = NOW_TIME; // 也可以用time（）获取时间戳
        $param ['nonce_str'] = uniqid();

        // 将 api_ticket、timestamp、card_id、code、openid、nonce_str的value值进行字符串的字典序排序

        asort($param, SORT_STRING);
        $sortString = "";
        foreach ($param as $temp) {
            $sortString = $sortString . $temp;
        }
        $param ['signature'] = sha1($sortString);
        $param ['code'] = '';
        $param ['openid'] = '';

        echo $this->apiSuccess($param);
    }

// api_ticket 是用于调用微信卡券JS API的临时票据，有效期为7200 秒，通过access_token 来获取
    function api_ticket($access_token = '', $update = false)
    {
        $pbid = get_pbid();
        if (empty ($access_token)) {
            $access_token = get_access_token($pbid);
        }

        $key = 'api_ticket_token_' . $pbid;
        $res = S($key);
        if ($res !== false && !$update)
            return $res;

        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $access_token . '&type=wx_card';
        $tempArr = json_decode(get_data($url), true);
        if (@array_key_exists('ticket', $tempArr)) {
            S($key, $tempArr ['ticket'], $tempArr ['expires_in']);
            return $tempArr ['ticket'];
        } else {
            return 0;
        }
    }

    function decrypt()
    {
        $code = I('code');

        $access_token = 'hbeiWYjv7UpvoXQ_aGBpf3o33uA_gRQCCCQsbERKjWqM8gn-pepjJayDTsO2Ts-xkvdteo7SCl41Fo4tPAZeGQZkvDxZdGMCaAxSbMFQLAdghp4l84MuO5J59rv0v-0rZOWeAIAODL';
        $url = 'https://api.weixin.qq.com/card/code/decrypt?access_token=' . $access_token;

        $param ['encrypt_code'] = $code;

        $res = post_data($url, $param);
        echo json_encode($res);
    }

    function login()
    {
        if (IS_POST) {
            $mobile = input('mobile');
            $passwd = input('passwd');
            $uid = D('common/User')->login($mobile, $passwd, 'api');
            if ($uid > 0) {
                $user = getUserInfo($uid);
                $return['token'] = $this->token;
                $return['uid'] = $user['uid'];
                $return['openid'] = isset($user['openid']) ? $user['openid'] : '';
                $return['unionid'] = isset($user['unionid']) ? $user['unionid'] : '';
                $return['userInfo'] = $user;
                S('check_' . $this->token, $user['uid']);
                return $this->apiSuccess($return, "登录成功");
            } else {
                return $this->apiError('手机号或密码不正确！', 410001);
            }
        } else {
            return $this->apiSuccess();
        }
    }

    function reg()
    {
        $mobile = input('mobile');
        $uid = M('user')->where('mobile', $mobile)->value('uid');

        if ($uid > 0) {
            return $this->apiError('该手机号已存在！', 410001);

        } else {
            $passwd = input('passwd');
            $uid = D('common/User')->register($mobile, $passwd, "", $mobile);
            if ($uid > 0) {
                D('clock_in/Service')->UserReg($uid);

                $user = getUserInfo($uid);
                $return['token'] = $this->token;
                $return['uid'] = $user['uid'];
                $return['openid'] = isset($user['openid']) ? $user['openid'] : '';
                $return['unionid'] = isset($user['unionid']) ? $user['unionid'] : '';
                $return['userInfo'] = $user;
                S('check_' . $this->token, $user['uid']);
                return $this->apiSuccess($return, "注册成功");
            } else {
                return $this->apiError('注册失败！', 410001);
            }
        }

    }
}

