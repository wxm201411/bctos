<?php
namespace app\admin\controller;

use think\facade\Db;
use anqiniu\Anqiniu;//七牛
class Tools extends Admin
{
    /*定义一个初始数组*/
    protected $info = array(
        'code'=>808,
        'tips'=>'提示',
        'data'=>''
    );

    //获取七牛云token
    public function getQiniuToken($name){
        $qiniu = new Anqiniu();//实例化工具箱
        $tok= $qiniu->getToken($name);//获取token
        $this->info['data']=$tok;
        return json($this->info);
    }

    //用户登录
    public function login(){
        if(input('?post.user')){
            $pt = input('post.');
            $admin = model('admin');
            $arr = $admin->login($pt);
            return json($arr);
        }
    }

    //用户退出
    public  function out_admin($pass = 1){
        session_start();//开启session
        cookie('uniquen_id', null);

        $log = [
            'user_name'=>cookie('admin_user'),
            'operation'=>'退出后台',
            'create_time'=>date('y-m-d h:i:s',time()),
            'notes'=>'',
        ];
        db('log')->insert($log);

        if($pass==2){
            $this->success('密码修改成功，请重新登录', 'admin/other/login');
        }else{
            $this->success('退出成功', 'admin/other/login');
        }
        
    }

    //ajax根据唯一性ID获取管理员信息
    public function ajax_get_admin_info(){
        $info = Model('admin')->getAdminInfo(cookie('uniquen_id'));
        return json($info);
    }  

    //ajax更改头像
    public function ajax_up_head_img(){
        $pt = input("post.");
        $info = Model('admin')->upHeadImg($pt);
        return json($info);
    }

    //修改密码
    public function ajax_up_pass(){
        $pt = input("post.");
        $info = Model('admin')->upAdminPass($pt);
        return json($info);
    }
    //修改昵称、用户名
    public function ajax_up_info(){
        $pt = input("post.");
        $info = Model('admin')->upAdminInfo($pt);
        return json($info);
    }

    //导出数据库
    public function sql(){
        header("Content-type:text/html;charset=utf-8");
        $infoMsg = array(
            'code'=>808,
            'tips'=>'提示',
            'data'=>''
        );
        $path = ROOT_PATH.'/public/static/mysql/';
        $database = config('database')['database'];
        //echo "运行中，请耐心等待...<br/>";
        $info = "-- ----------------------------\r\n";
        $info .= "-- 日期：".date("Y-m-d H:i:s",time())."\r\n";
        $info .= "-- MySQL - 5.5.52-MariaDB : Database - ".$database."\r\n";
        $info .= "-- ----------------------------\r\n\r\n";
        $info .= "CREATE DATAbase IF NOT EXISTS `".$database."` DEFAULT CHARACTER SET utf8 ;\r\n\r\n";
        $info .= "USE `".$database."`;\r\n\r\n";

        // 检查目录是否存在
        if(is_dir($path)){
            // 检查目录是否可写
            if(is_writable($path)){
            //echo '目录可写';exit;
            }else{
                //echo '目录不可写';exit;
                chmod($path,0777);
            }
        }else{
            //echo '目录不存在';exit;
            // 新建目录
            mkdir($path, 0777, true);
            //chmod($path,0777);
        }

        // 检查文件是否存在
        $file_name = $path.$database.'-'.date("Y-m-d",time()).'.sql';
        if(file_exists($file_name)){
            $infoMsg['code'] = 400;
            $infoMsg['tips'] = '备份文件已存在';
            return json($infoMsg);
        exit;
        }
        file_put_contents($file_name,$info,FILE_APPEND);

        //查询数据库的所有表
        $result = Db::query('show tables');
        //print_r($result);exit;
        foreach ($result as $k=>$v) {
            //查询表结构
            $val = $v['Tables_in_'.$database];
            $sql_table = "show create table ".$val;
            $res = Db::query($sql_table);
            //print_r($res);exit;
            $info_table = "-- ----------------------------\r\n";
            $info_table .= "-- Table structure for `".$val."`\r\n";
            $info_table .= "-- ----------------------------\r\n\r\n";
            $info_table .= "DROP TABLE IF EXISTS `".$val."`;\r\n\r\n";
            $info_table .= $res[0]['Create Table'].";\r\n\r\n";
            //查询表数据
            $info_table .= "-- ----------------------------\r\n";
            $info_table .= "-- Data for the table `".$val."`\r\n";
            $info_table .= "-- ----------------------------\r\n\r\n";
            file_put_contents($file_name,$info_table,FILE_APPEND);
            $sql_data = "select * from ".$val;
            $data = Db::query($sql_data);
            //print_r($data);exit;
            $count= count($data);
            //print_r($count);exit;
            if($count<1) continue;
            foreach ($data as $key => $value){
                $sqlStr = "INSERT INTO `".$val."` VALUES (";
                foreach($value as $v_d){
                    $v_d = str_replace("'","\'",$v_d);
                    $sqlStr .= "'".$v_d."', ";
                }
                //需要特别注意对数据的单引号进行转义处理
                //去掉最后一个逗号和空格
                $sqlStr = substr($sqlStr,0,strlen($sqlStr)-2);
                $sqlStr .= ");\r\n";
                file_put_contents($file_name,$sqlStr,FILE_APPEND);
            }
            $info = "\r\n";
            file_put_contents($file_name,$info,FILE_APPEND);
        }
        $infoMsg['code'] = 200;
        $infoMsg['tips'] = '备份成功';
        return json($infoMsg);
    }


}
