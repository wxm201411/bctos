<?php

namespace app\home\model;

use app\common\model\Base;

use QL\QueryList;

/**
 * News模型
 */
class News extends Base
{

    function grab_news_list()
    {
        $list = M('weiba')->where('rule_url is not null')->select();
        foreach ($list as $vo) {
            $urls = wp_explode($vo['rule_url'], "\r\n");
            $this->grab_news($vo['id'], $vo['social_id'], $urls);
        }
    }

    function grab_news($cateId, $social_id = '', $urls = [])
    {
        if (empty($urls) || empty($social_id)) {
            $cate = M('weiba')->where(array('id' => $cateId))->find();
            empty($urls) && $urls = wp_explode($cate['rule_url'], "\r\n");
            empty($social_id) && $social_id = $cate['social_id'];
        }
        set_time_limit(0);
        //获取文章，标题和时间  md5标识
        $map1['collect_url'] = array('exp', 'is not null and collect_url!=""');
        $newsData = M('feed')->where(wp_where($map1))->column('id', 'collect_url');
        $addCount = 0;

        foreach ($urls as $url) {
            $rules = [
                // 采集文章标题
                'title' => ['.article-list .item-title>a', 'title'],

                'content' => ['.article-list .item-content .item-excerpt', 'text'],
                // 采集文章作者
                'collect_url' => ['.article-list .item-title>a', 'href']
            ];
            $list = QueryList::get($url)->rules($rules)->queryData();

            $content_rules = [
                // 采集文章标题
                'content' => ['.entry-content clearfix', 'html'],
                'cTime' => ['entry-info span', 'text']
            ];

            foreach ($list as $k => &$v) {
                if (isset($newsData[$v['collect_url']])) {
                    unset($list[$k]);
                }
                $v['uid'] = 1;
                $v['weiba_id'] = $cateId;
                $v['feed_type'] = 3;
                $v['cTime'] = NOW_TIME;
            }
            dump($list);
            if (!empty($list)) {
                M('feed')->insertAll($list);
            }
        }
    }

    function grab_content()
    {
        set_time_limit(0);
        //获取文章，标题和时间  md5标识
        $map1['has_collect'] = 0;
        $map1['collect_url'] = array('exp', 'is not null and collect_url!=""');
        $lists = M('feed')->where(wp_where($map1))->field('id,collect_url')->limit(1)->select();
        if (empty($lists)) return false;

        $ids = getSubByKey($lists, 'id');
        $has = M('feed_content')->whereIn('id', $ids)->column('id', 'id');

        $dao = D('social/Feed');
        foreach ($lists as $vo) {
            $rules = [
                // 采集文章标题
                'copyright' => ['.entry-copyright', 'html'],
                'content' => ['.entry-content', 'html', '-.entry-copyright'],
                'cTime' => ['.entry-info span:eq(1)', 'text']

            ];
            $info = QueryList::get($vo['collect_url'])->rules($rules)->queryData();
            //dump($info);
            isset($info[0]) && $info = $info[0];
            if (!isset($info['content'])) {
                continue;
            }

            $data = $dao->saveContentImgToLocal($info['content']);
            //dump($data);
            if (strpos($info['cTime'], 'pm')) {
                $info['cTime'] = str_replace(['年', '月', '日', 'pm'], ['-', '-', '', ''], $info['cTime']);
                $info['cTime'] = strtotime($info['cTime']) + 43200;
            } else {
                $info['cTime'] = str_replace(['年', '月', '日', 'am'], ['-', '-', '', ''], $info['cTime']);
                $info['cTime'] = strtotime($info['cTime']);
            }
            $is_del = 0;
            if (!strpos($info['copyright'], '转转请注明出处')) {
                $is_del = 1;
            }
            dump(['img_ids' => $data['img_ids'], 'cTime' => $info['cTime'], 'has_collect' => 1, 'is_del' => $is_del]);
            //dump(['img_ids' => $data['img_ids'], 'cTime' => $info['cTime'], 'has_collect' => 1]);
            M('feed')->where('id', $vo['id'])->update(['img_ids' => $data['img_ids'], 'cTime' => $info['cTime'], 'has_collect' => 1, 'is_del' => $is_del]);

            $data['content'] .= '<div class="entry-copyright"><p>本文转载自<span> 发抖网（' . $vo['collect_url'] . '）</span>，观点不代表本站立场。如有任何版权问题，请联系我们处理。</p></div';
            //dump($data);
            if (in_array($vo['id'], $has)) {
                $res = M('feed_content')->where('id', $vo['id'])->update(['content' => $data['content']]);
            } else {
                $res = M('feed_content')->insert(['id' => $vo['id'], 'content' => $data['content']]);
            }
            dump(M('feed_content')->getLastSql());
            dump($res);
        }
    }

    function codeToUtf8($str, $by = 'gb2312')
    {
        $str1 = iconv($by, "UTF-8//IGNORE", $str);
        $str1 = empty($str1) ? $str : $str1;
        return $str1;
    }

    function grab_news_uu($cateId, $social_id = '', $urls = [])
    {
        if (empty($urls) || empty($social_id)) {
            $cate = M('weiba')->where(array('id' => $cateId))->find();
            empty($urls) && $urls = wp_explode($cate['rule_url'], "\r\n");
            empty($social_id) && $social_id = $cate['social_id'];
        }
        set_time_limit(0);
        //获取文章，标题和时间  md5标识
        $map1['social_id'] = $social_id;
        $map1['content_md5'] = array('exp', 'is not null and content_md5!=""');
        $newsData = M('feed')->where(wp_where($map1))->column('id', 'content_md5');
        $addCount = 0;

        foreach ($urls as $url) {
            $data = [];
            \phpQuery::$defaultCharset = 'utf-8';
            $doc = \phpQuery::newDocumentFile($url);

            foreach ($doc->find('.article-list .item-title') as $mod) {
                $url = pq($mod)->find('a')->attr('href');
                $title = pq($mod)->find('a')->attr('title');
                dump($url);
                dump($title);
                continue;
                $content_doc = \phpQuery::newDocumentFile($url);

                $content_html = file_get_contents($url);
                $content_html = $this->codeToUtf8($content_html);
                $titleArr['（图）'] = '';
                $titleArr['(图)'] = '';
//                 $title=pq('#pagetitle h2')->text();
//                 dump($title);continue;
                if (empty($title)) {
                    preg_match_all('/<h1 class="entry-title">(.*)<\/h1>/i', $content_html, $titleArrRes);
                    $title = $titleArrRes[1][0];
                    if (empty($title)) {
                        continue;
                    }
                }
                //标题
                $data['title'] = strtr($title, $titleArr);

                preg_match_all('/<div class="entry-info">(.*)<span>(.*)<\/span>(.*)<\/div>/i', $content_html, $elementArr);

                /* $element=pq('#element')->text();
                $fromStr=strstr($element, '文章来源');
                $timeStr1=strstr($element, '发布时间');
                $len=strlen($timeStr1)-strlen($fromStr)-4;
                $timeStr=substr($timeStr1, 0,$len);
                $time=substr($timeStr, strlen('发布时间：'));
                $from=substr($fromStr, strlen('文章来源：')); */
                $from = $elementArr[3][0];

                //发布时间
                $data['cTime'] = strtotime($elementArr[2][0]);
                //新闻标识
                //$strmd5 = $data['title'] . '_' . $data['cTime'];
                $data['news_md5'] = $md5 = md5($url);

                if (isset($newsData[$md5]) && !empty($newsData[$md5])) {
                    continue;
                }

                //内容
//                 $content = pq(".neirong")->html();
//                 $data['content']=$this->codeToUtf8($content);
                $arr = explode('<div class="neirong">', $content_html);
                $arr2 = explode('<div class="pps">', $arr[1]);
                $data['content'] = trim($arr2[0]);
                $data['content'] = rtrim($data['content'], '</div>');
                $data['content'] = trim($data['content']);
                $data['content'] = rtrim($data['content'], '</div>');
                $data['content'] = trim($data['content']);

                if (empty($data['content'])) {
                    continue;
                }
                //来源
                $data['author'] = $from;

                $data['pbid'] = $pbid;
                $data['cate_id'] = $cateId;
                if (strlen($title) != strlen($data['title'])) {
                    preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $data['content'], $match);
                    //封面图，截取三张
                    $count = 1;
                    $strimgid = '';
                    foreach ($match[1] as $imgUrl) {
                        if ($count > 3) {
                            break;
                        } else {
                            $imgid = do_down_image(0, $imgUrl);
                            if ($imgid > 0) {
                                $strimgid .= $imgid . ',';
                                $count++;
                            }
                        }

                    }
                    $data['cover'] = rtrim($strimgid, ",");
                }

                //                 dump($data);
                $res = $this->insertGetId($data);
                if ($res > 0) {
                    $addCount++;
                }
                /*  $addAll[]=$data;
                 if (count($addAll)>=10){
                     dump('1');
                     dump($addAll);
                     $res = $this->addAll($addAll);
                     if ($res){
                         $addCount+=$res;
                         unset($addAll);
                     }
                 } */
//                 unset($data);

            }
            /*   if (!empty($addAll)){
                  dump('2');
                  dump($addAll);
                  $res = $this->addAll($addAll);
                  $addCount+=$res;
              } */
        }
        return $addCount;
    }

    function grab_news_bar($cateId, $social_id = '', $urls = [])
    {
        if (empty($urls) || empty($social_id)) {

            $cate = M('weiba')->where(array('id' => $cateId))->find();
            empty($urls) && $urls = wp_explode($cate['rule_url'], "\r\n");
            empty($social_id) && $social_id = $cate['social_id'];
        }
        set_time_limit(0);
        //获取文章，标题和时间  md5标识
        $map1['social_id'] = $social_id;
        $map1['content_md5'] = array('exp', 'is not null and content_md5!=""');
        $newsData = M('feed')->where(wp_where($map1))->column('id', 'content_md5');
        $addCount = 0;

        foreach ($urls as $url) {
            $data = [];
            \phpQuery::$defaultCharset = 'utf-8';
            $doc = \phpQuery::newDocumentFile($url);

            foreach ($doc->find('.article-list .item-title') as $mod) {
                $url = pq($mod)->find('a')->attr('href');
                $title = pq($mod)->find('a')->attr('title');
                dump($url);
                dump($title);
                continue;
//                 $content_doc = \phpQuery::newDocumentFile ( $url );

                $content_html = file_get_contents($url);
                $content_html = $this->codeToUtf8($content_html);
                $titleArr['（图）'] = '';
                $titleArr['(图)'] = '';
//                 $title=pq('#pagetitle h2')->text();
//                 dump($title);continue;
                if (empty($title)) {
                    preg_match_all('/<h1 class="entry-title">(.*)<\/h1>/i', $content_html, $titleArrRes);
                    $title = $titleArrRes[1][0];
                    if (empty($title)) {
                        continue;
                    }
                }
                //标题
                $data['title'] = strtr($title, $titleArr);

                preg_match_all('/<div class="entry-info">(.*)<span>(.*)<\/span>(.*)<\/div>/i', $content_html, $elementArr);

                /* $element=pq('#element')->text();
                $fromStr=strstr($element, '文章来源');
                $timeStr1=strstr($element, '发布时间');
                $len=strlen($timeStr1)-strlen($fromStr)-4;
                $timeStr=substr($timeStr1, 0,$len);
                $time=substr($timeStr, strlen('发布时间：'));
                $from=substr($fromStr, strlen('文章来源：')); */
                $from = $elementArr[3][0];

                //发布时间
                $data['cTime'] = strtotime($elementArr[2][0]);
                //新闻标识
                //$strmd5 = $data['title'] . '_' . $data['cTime'];
                $data['news_md5'] = $md5 = md5($url);

                if (isset($newsData[$md5]) && !empty($newsData[$md5])) {
                    continue;
                }

                //内容
//                 $content = pq(".neirong")->html();
//                 $data['content']=$this->codeToUtf8($content);
                $arr = explode('<div class="neirong">', $content_html);
                $arr2 = explode('<div class="pps">', $arr[1]);
                $data['content'] = trim($arr2[0]);
                $data['content'] = rtrim($data['content'], '</div>');
                $data['content'] = trim($data['content']);
                $data['content'] = rtrim($data['content'], '</div>');
                $data['content'] = trim($data['content']);

                if (empty($data['content'])) {
                    continue;
                }
                //来源
                $data['author'] = $from;

                $data['pbid'] = $pbid;
                $data['cate_id'] = $cateId;
                if (strlen($title) != strlen($data['title'])) {
                    preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $data['content'], $match);
                    //封面图，截取三张
                    $count = 1;
                    $strimgid = '';
                    foreach ($match[1] as $imgUrl) {
                        if ($count > 3) {
                            break;
                        } else {
                            $imgid = do_down_image(0, $imgUrl);
                            if ($imgid > 0) {
                                $strimgid .= $imgid . ',';
                                $count++;
                            }
                        }

                    }
                    $data['cover'] = rtrim($strimgid, ",");
                }

                //                 dump($data);
                $res = $this->insertGetId($data);
                if ($res > 0) {
                    $addCount++;
                }
                /*  $addAll[]=$data;
                 if (count($addAll)>=10){
                     dump('1');
                     dump($addAll);
                     $res = $this->addAll($addAll);
                     if ($res){
                         $addCount+=$res;
                         unset($addAll);
                     }
                 } */
//                 unset($data);

            }
            /*   if (!empty($addAll)){
                  dump('2');
                  dump($addAll);
                  $res = $this->addAll($addAll);
                  $addCount+=$res;
              } */
        }
        return $addCount;
    }
}
