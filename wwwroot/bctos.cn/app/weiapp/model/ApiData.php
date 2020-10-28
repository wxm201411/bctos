<?php

namespace app\weiapp\model;

use app\common\model\Base;

/**
 * 模型
 */
class ApiData extends Base
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function makeCode($param = [], $type = 'A')
    {
        $result['status'] = 0;
        if (empty($param)) {
            $result['msg'] = 'param参数不能为空';
            return $result;
        }
        is_array($param) || $param = json_decode($param, true);

        $file = '/storage/wxacode/' . PBID . '/' . md5($type . json_encode($param)) . '.png';
        $fielPath = SITE_PATH . '/public' . $file;
        $fileUrl = SITE_URL . $file;

        mkdirs(SITE_PATH . '/public/storage/wxacode/' . PBID);

        if (file_exists($fielPath)) {
            $result = [
                'status' => 1,
                'url' => $fileUrl,
                'path' => $fielPath
            ];

            return $result;
        }

        $access_token = get_access_token();
        if (!$access_token) {
            $result['access_token'] = $access_token;
            $result['msg'] = '获取access_token失败';
            return $result;
        }

        if ($type == 'A') {
            $url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=' . $access_token;

            if (!isset($param ['path']) || empty($param ['path'])) {
                $result['msg'] = 'path参数不能为空';
                return $result;
            }
            $p ['path'] = $param ['path'];
            $p ['width'] = isset($param ['width']) ? $param ['width'] : 430;
            $p ['auto_color'] = isset($param ['auto_color']) ? $param ['auto_color'] : false;
            $p ['line_color'] = isset($param ['line_color']) ? $param ['line_color'] : ( object )array(
                'r' => '0',
                'g' => '0',
                'b' => '0'
            );
        } elseif ($type == 'B') {
            $url = 'http://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token;

            if (!isset($param ['scene']) || empty($param ['scene'])) {
                $result['msg'] = 'scene参数不能为空';
                return $result;
            }
            $p ['scene'] = $param ['scene'];
            $p ['width'] = isset($param ['width']) ? $param ['width'] : 430;
            isset($param['page']) && $p['page'] = $param['page'];
            $p['is_hyaline'] = true;
            $p ['auto_color'] = isset($param ['auto_color']) ? $param ['auto_color'] : false;
            $p ['line_color'] = isset($param ['line_color']) ? $param ['line_color'] : ( object )array(
                'r' => '0',
                'g' => '0',
                'b' => '0'
            );
        } else {
            $url = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=' . $access_token;

            if (!isset($param ['path']) || empty($param ['path'])) {
                $result['msg'] = 'path参数不能为空';
                return $result;
            }
            $p ['path'] = $param ['path'];
            $p ['width'] = isset($param ['width']) ? $param ['width'] : 430;
        }

        $content = post_data($url, $p, 'json', false);
        if (isset($content ['msg'])) {
            $result['msg'] = $content ['msg'];
            $result['msg'] = '获取二维码失败';
            return $result;
        }

        file_put_contents($fielPath, $content);

        $result = [
            'status' => 1,
            'url' => $fileUrl,
            'path' => $fielPath,
            'msg' => ''
        ];

        return $result;
    }
}
