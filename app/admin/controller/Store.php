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
 * 在线应用商店
 */
class Store extends Admin
{
    var $app_shop_web = [];

    public function initialize()
    {
        parent::initialize();

        $host_url = isset($_GET['callback']) ? urldecode($_GET ['callback']) : '';
        if ($host_url) {
            session('host_url', $host_url);
        }
        $this->assign('host_url', $host_url);
    }

    function lists()
    {
        $type = input('type', '');
        switch ($type) {
            case 'addon':
                $remote_url = '/index.php?s=/admin/store/lists&type=0';
                break;
            case 'template':
                $remote_url = '/index.php?s=/admin/store/lists&type=1';
                break;
            case 'material':
                $remote_url = '/index.php?s=/admin/store/lists&type=2';
                break;
            case 'diy':
                $remote_url = '/index.php?s=/admin/store/lists&type=1';
                break;
            case 'developer':
                $remote_url = '/index.php?s=/home/dev/index';
                break;
            case 'help':
                $remote_url = '/index.php?s=/admin/store/help';
                break;
            case 'home':
                $remote_url = '/index.php?s=/admin/store/home';
                break;
            case 'recharge':
                $remote_url = '/index.php?s=/admin/store/recharge';
                break;
            case 'buy':
                $remote_url = '/index.php?s=/admin/store/buy';
                break;
            case 'online_recharge':
                $remote_url = '/index.php?s=/admin/store/online_recharge';
                break;
            default:
                $remote_url = '/index.php?s=/admin/store/main';
        }

        $this->assign('remote_url', REMOTE_BASE_URL . $remote_url);
        return $this->fetch();
    }
    /* ======================================= 以下是远程服务器的方法 ========================================================== */
    /*
     * 0:应用 1:插件 2:在线素材 3:模板皮肤
     */
    function main()
    {
        $dao = M('app_shop');

        for ($i = 0; $i < 3; $i++) {
            // 热门
            $data ['hot_' . $i] = $dao->where('type=' . $i)->order('download_count desc')->limit(5)->select();
            $data ['hot_' . $i] = $this->checkPayAll($data ['hot_' . $i]);
            // 最新
            $data ['new_' . $i] = $dao->where('type=' . $i)->order('id desc')->limit(5)->select();
            $data ['new_' . $i] = $this->checkPayAll($data ['new_' . $i]);
        }
        $this->assign('data', $data);

        return $this->fetch();
    }

    function index()
    {
        $map ['type'] = input('type/d', 0);

        $data = M('app_shop')->field(true)->where($map)->order('id desc')->paginate(10);

        $model = $this->getModel('app_shop');
        $dataTable = D('common/Models')->getFileInfo($model);
        $list_data = $this->listGrid($model, $dataTable);

        $data = $this->parsePageData($data, $dataTable, $list_data);

        $data ['list_data'] = $this->checkPayAll($data ['list_data']);

        $this->assign($data);
        // dump ( $data );
        // exit ();
        switch ($map ['type']) {
            case 1 :
                $page_tit = '插件';
                break;
            case 2 :
                $page_tit = '模板皮肤';
                break;
            default :
                $page_tit = '应用';
        }
        $this->assign('page_tit', $page_tit . '列表');

        return $this->fetch();
    }

    // 新手安装教程
    function help()
    {
        // 目录可写权限判断
        $dirList = array(
            'Addons',
            'Application',
            'Public',
            'ThinkPHP',
            'URLRewrite',
            'Uploads'
        );
        $noWritable = array();
        foreach ($dirList as $dir) {
            $dirPath = SITE_PATH . '/' . $dir;
            if (is_dir($dirPath) && !is_writable($dirPath)) {
                $noWritable [] = $dir;
            }
        }
        $this->assign('noWritable', $noWritable);

        return $this->fetch();
    }

    function home()
    {
        $this->needLogin();

        return $this->fetch();
    }

    function recharge()
    {
        $this->needLogin();

        return $this->fetch();
    }

    function online_recharge()
    {
        $this->needLogin();

        return $this->fetch();
    }

    function buy()
    {
        $this->needLogin();
        $this->assign('check_all', false);

        $model = $this->getModel('app_shop_user');

        $map ['uid'] = $this->mid;
        session('common_condition', $map);

        return parent::commonLists($model);
    }

    function app_shop_web()
    {
        $this->needLogin();

        $model = $this->getModel('app_shop_web');
        $id = I('id');
        $map ['id'] = $id;
        $map ['uid'] = $this->mid;
        // 获取数据
        $data = M($model ['name'])->where($map)->find();

        if (IS_POST) {
            $post = input('post.');
            $host = parse_url($post ['url'], PHP_URL_HOST);
            if (empty ($post ['url']) || $post ['url'] != $host) {
                $post ['web_key'] = substr(md5(get_nickname($this->mid)), 0, 5);
                $post ['web_key'] .= md5(think_encrypt($host, $post ['web_key']));
            }

            $Model = M($model ['name']);

            $post = $this->checkData($post, $model);
            if ($id > 0) {
                $res = $Model->where('id', $id)->update($post);
            } else {
                if (isset($post['id'])) unset($post['id']);
                $res = $Model->insertGetId($post);
            }
            if ($res) {
                $this->success('保存成功！', U('home'));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $fields = get_model_attribute($model ['id']);
            $this->assign('fields', $fields);

            empty ($data ['url']) && $data ['url'] = session('host_url');
            $this->assign('data', $data);

            $this->assign('post_url', U('app_shop_web'));

            return $this->fetch('web_info');
        }
    }

    function needLogin()
    {
        if (!is_login()) {
            Cookie('__forward__', U('Admin/Store/main'));
            redirect(U('home/user/login', 'from=store'));
        } else {
            $this->mid = $map ['uid'] = session('mid');
        }

        // 是否已经登录网站信息
        $this->app_shop_web = $app_shop_web = M('app_shop_web')->where($map)->find();
        if (!$app_shop_web && strtolower(ACTION_NAME) != 'app_shop_web') {
            $this->error('请先登记网站信息', U('app_shop_web'));
        } else {
            $this->assign('web_info', $app_shop_web);
        }
    }

    function detail()
    {
        $map ['id'] = intval($_GET ['id']);
        $data = M('app_shop')->where($map)->find();

        $data ['need_buy'] = 0;
        if (!$this->checkPay($data)) {
            $data ['need_buy'] = 1;
        }
        $this->assign('data', $data);

        return $this->fetch();
    }

    function payment()
    {
        $this->needLogin();

        $map ['id'] = id();
        $info = M('app_shop')->where($map)->find();
        if (!$info) {
            $this->error('您购买的扩展不存在');
        }
        $this->assign('info', $info);

        $map ['uid'] = $this->mid;
        $shop_user = M('app_shop_user')->where($map)->find();
        if (isset($shop_user['status']) && $shop_user['status'] == 1) {
            $this->error('你已经购买过此扩展，不需要再重复购买');
        }
        // dump ( $info );

        $app_shop_web = $this->app_shop_web;
        if ($info ['price'] > $app_shop_web ['wealth']) {
            $this->error('余额不足，请充值后再购买', U('online_recharge'));
        }

        if (IS_POST) {
            $data ['remain'] = $app_shop_web ['wealth'] - $info ['price'];

            $map2 ['uid'] = $this->mid;
            $res = M('app_shop_web')->where($map2)->update(['wealth' => $data ['remain']]);
            if (!$res) {
                $this->error('余额扣除失败，请联系管理员进行处理');
                exit ();
            }

            $data['status'] = 1;
            $data ['price'] = $info ['price'];
            if (isset($shop_user['id'])) {
                $res = M('app_shop_user')->where('id', $shop_user['id'])->update($data);
            } else {
                $data ['uid'] = $this->mid;
                $data ['app_id'] = $info ['id'];
                $data ['create_at'] = time();

                $res = M('app_shop_user')->insertGetId($data);
            }

            if ($res) {
                $this->success('购买成功！', U('detail?id=' . $info ['id']));
            } else {
                M('app_shop_web')->where($map2)->update(['wealth' => $app_shop_web ['wealth']]);
                $this->error('购买失败，请联系管理员进行处理');
            }
        } else {
            return $this->fetch();
        }
    }

    // 远程下载
    function download()
    {
        $this->needLogin();

        $map ['id'] = id();
        $info = M('app_shop')->where($map)->find();

        if (empty ($info ['attach']) || !is_numeric($info ['attach'])) {
            $this->error('附件ID无效！');
        }

        if (!$this->checkPay($info)) {
            $this->error('该收费应用还没有付款');
            exit ();
        }

        M('app_shop')->where($map)->inc('download_count')->update();

        /* 下载附件 */
        $Attachment = D('File');
        $config = config('app.DOWNLOAD_UPLOAD');
        if (false === $Attachment->download($config ['rootPath'], $info ['attach'])) {
            $this->error($Attachment->getError());
        }
    }

    function checkPayAll($list)
    {
        static $_myPayList;
        if (!$_myPayList) {
            $map ['uid'] = $this->mid;
            $map ['status'] = 1;
            $app_ids = M('app_shop_user')->where($map)->field('app_id')->select();
            foreach ($app_ids as $id) {
                $_myPayList [$id ['app_id']] = 1;
            }
        }
        $list = find_data($list);
        foreach ($list as &$vo) {
            if (intval($vo ['price']) == 0) {
                $vo ['need_buy'] = 0;
            } else {
                $vo ['need_buy'] = isset($_myPayList [$vo ['id']]) ? 0 : 1;
            }
        }
        return $list;
    }

    function checkPay($info)
    {
        if (intval($info ['price']) == 0) {
            return true;
        }

        // 检查是否已成功购买
        $map ['app_id'] = $info ['id'];
        $map ['uid'] = $this->mid;
        $status = M('app_shop_user')->where($map)->value('status');
        if ($status) {
            return true;
        }

        return false;
    }

    function check_pay_by_ajax()
    {
        $map ['id'] = id();
        $info = M('app_shop')->where($map)->find();
        $res = $this->checkPay($info);
        echo intval($res);
    }
}
