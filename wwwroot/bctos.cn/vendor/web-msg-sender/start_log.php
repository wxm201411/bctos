<?php
set_time_limit(0);
define("MAX_SHOW", 8192);

$log1 = ['num' => 1, 'name' => '/tmp/bc_web_log_dev1', 'file_size' => 0, 'file_size_new' => 0, 'ignore_size' => 0, 'fp' => fopen('/tmp/bc_web_log_dev1', "w+x")];
$log2 = ['num' => 2, 'name' => '/tmp/bc_web_log_dev2', 'file_size' => 0, 'file_size_new' => 0, 'ignore_size' => 0, 'fp' => fopen('/tmp/bc_web_log_dev2', "w+x")];
$log3 = ['num' => 3, 'name' => '/tmp/bc_web_log_dev3', 'file_size' => 0, 'file_size_new' => 0, 'ignore_size' => 0, 'fp' => fopen('/tmp/bc_web_log_dev3', "w+x")];

while (1) {
    clearstatcache();
    $log1 = load_log($log1);
    $log2 = load_log($log2);
    $log3 = load_log($log3);
    //sleep(1);
}
fclose($log1['fp']);
fclose($log2['fp']);
fclose($log3['fp']);

function load_log($log)
{
    $file_size_new = filesize($log['name']);
    $add_size = $file_size_new - $log['file_size'];
    if ($add_size > 0) {
        if ($add_size > MAX_SHOW) {
            $ignore_size = $add_size - MAX_SHOW;
            $add_size = MAX_SHOW;
            fseek($log['fp'], $log['file_size'] + $ignore_size);
        }
        $log['file_size'] = $file_size_new;

        $content = fread($log['fp'], $add_size);
        $content = nl2br($content);
        web_msg($content, $log['num']);
    }
    return $log;
}

function web_msg($content, $log)
{
    // 指明给谁推送，为空表示向所有在线用户推送
    $to_uid = 1;

    // 推送的url地址，使用自己的服务器地址
    $push_api_url = "http://" . SSH_IP . ":2121/";
    $post_data = array(
        "type" => "publish",
        "content" => $content . $log,
        "to" => $to_uid,
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $push_api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
    curl_exec($ch);
    curl_close($ch);

}