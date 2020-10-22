<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace app\material\model;

use app\common\model\Base;

/**
 * 分类模型
 */
class Material extends Base
{

    protected $table = DB_PREFIX . 'material_news';

    /**
     * 获取导航列表，支持多级导航
     *
     * @param boolean $field
     *            要列出的字段
     * @return array 导航树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getMediaIdByGroupId($group_id)
    {
        $map['group_id'] = $group_id;
        $list = $this->where(wp_where($map))
            ->order('id asc')
            ->select();
        if (!empty($list[0]['media_id']))
            return $list[0]['media_id'];

        // 自动同步到微信端
        foreach ($list as $vo) {
            $data['title'] = $vo['title'];
            $data['thumb_media_id'] = empty($vo['thumb_media_id']) ? $this->_thumb_media_id($vo['cover_id']) : $vo['thumb_media_id'];
            $data['author'] = $vo['author'];
            $data['digest'] = $vo['intro'];
            $data['show_cover_pic'] = 0;
            $vo['content'] = $this->getNewContent($vo['content']);
            $data['content'] = str_replace('"', '\'', $vo['content']);

            //$data['content'] = $vo['content'];
            $data['content_source_url'] = !empty($vo['link']) ? $vo['link'] : U('material/Wap/news_detail', array(
                'id' => $vo['id']
            ));

            $articles[] = $data;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=' . get_access_token();
        $param['articles'] = $articles;

        $res = post_data($url, $param);
        if (isset($res['errcode']) && $res['errcode'] != 0) {
            return false;
        } else {
            $this->where(wp_where($map))->update(['media_id' => $res['media_id']]);
            return $res['media_id'];
        }
    }

    function _thumb_media_id($cover_id)
    {
        $media_id = D('common/Custom')->get_image_media_id($cover_id, 'thumb');
        if (!empty($media_id)) {
            $res = M('material_news')->where('cover_id', $cover_id)->update(['thumb_media_id' => $media_id]);
        }
        return $media_id;
    }

    // 图文消息的内容图片，上传到微信并获取新的链接覆盖
    public function getNewContent($content)
    {
        if (!$content) {
            return;
        }

        $newUrl = [];
        // 获取文章中图片img标签
        // $match=$this->getImgSrc($content);
        preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $content, $match);
        foreach ($match[1] as $mm) {
            $oldUrl = $mm;

            if (!preg_match("/^(http:\/\/|https:\/\/).*$/", $mm)) {
                //没有
                $mm = SITE_URL . $mm;
                $mm = str_replace('public./', '', $mm);
            }

            $newUrl[$oldUrl] = uploadimg($mm);
        }

        if (count($newUrl)) {
            $content_new = strtr($content, $newUrl);
        }
        return empty($content_new) ? $content : $content_new;
    }

    // 根据id获取图片素材,设置欢迎语用到
    public function ajax_picture_by_id($id, $width = '', $height = '')
    {
        $images = M('material_image')->where('id', $id)->find();
        $imgpath = get_cover_url($images['cover_id'], $width = '', $height = '');
        if (isset($only_body) && $only_body) {
            echo $imgpath;
            exit;
        } else {
            return $imgpath;
        }
    }

    public function get_news_by_group_id($group_id)
    {
        $map['group_id'] = $group_id;

        $map['pbid'] = get_pbid();
        $appMsgData = find_data(M('material_news')->where(wp_where($map))->select());
        if (empty($appMsgData)) {
            return '';
        }
        foreach ($appMsgData as $vo) {
            if ($vo['id'] == $map['group_id']) {
                $articles[] = array(
                    'id' => $vo['id'],
                    'title' => $vo['title'],
                    'intro' => empty($vo['description']) ? '' : $vo['description'],
                    'img_url' => get_cover_url($vo['cover_id'])
                );
            } else {
                // 文章内容
                $art['id'] = $vo['id'];
                $art['title'] = $vo['title'];
                $art['intro'] = empty($vo['description']) ? '' : $vo['description'];
                $art['img_url'] = get_cover_url($vo['cover_id']);
                $articles[] = $art;
            }
        }
        return $articles;
    }

    // 根据id获取图片素材,设置欢迎语用到
    public function ajax_voice_by_id($id)
    {
        $voiceMaterial = M('material_file')->where('id', $id)->find();
        if ($voiceMaterial) {
            $voiceMaterial['file_path'] = get_file_url($voiceMaterial['file_id']);
            $voiceMaterial['playtime'] = '未知时长';
            $file = M('file')->where('id', $voiceMaterial['file_id'])->find();
            $voiceMaterial['title'] = $voiceMaterial['title'] ? $voiceMaterial['title'] : $file['name'];
            $path = './storage/download' . $file['savepath'] . $file['savename'];
            $path = realpath($path);
            require_once SITE_PATH . '/vendor/getID3/getid3/getid3.php';
            $getID3 = new \getID3(); // 实例化类
            $voiceMaterial['playtime'] = '未知时长';
            if (file_exists($path)) {
                $info = $getID3->analyze($path);
                // 以下算法只适用于1个小时以内的时长显示
                if (isset($info['playtime_seconds']) && !empty($info['playtime_seconds'])) {
                    $voiceMaterial['playtime'] = date("i:s", $info['playtime_seconds']);
                }
            }
        }
        return $voiceMaterial;
    }

    // 根据id获取图片素材,设置欢迎语用到
    public function ajax_video_by_id($id)
    {
        $videoMaterial = M('material_file')->where('id', $id)->find();
        if (!empty($videoMaterial)) {
            $videoMaterial['file_url'] = get_file_url($videoMaterial['file_id']);
            $videoMaterial['cTime'] = time_format($videoMaterial['cTime']);
        }

        return $videoMaterial;
    }
}
