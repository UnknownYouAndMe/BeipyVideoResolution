<?php
header('Content-Type:text/html;charset=utf-8');
$url = isset($_POST['titurl']) ? trim($_POST['titurl']) : '';
//$url = 'http://www.beipy.com/';//url链接地址
echo getTitle($url);
 	
function getTitle($url){
    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        return '标题获取失败：链接无效';
    }
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if (!in_array($scheme, array('http', 'https'), true)) {
        return '标题获取失败：不支持的协议';
    }
    $data = curl_https($url);
    if ($data === '') {
        return '标题获取失败：请求超时';
    }
    if (!preg_match('/<title[^>]*>(.*?)<\/title>/is', $data, $matches)) {
        return '标题获取失败：未找到标题';
    }
    $title = trim($matches[1]);
    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
    return $title === '' ? '标题获取失败：未找到标题' : $title;
}
/** curl 获取 https 请求 
* @param String $url        请求的url 
* @param Array  $data       要發送的數據 
* @param Array  $header     请求时发送的header 
* @param int    $timeout    超时时间，默认30s 
*/  
function curl_https($url, $data=array(), $header=array(), $timeout=15){ 
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在  
    curl_setopt($ch, CURLOPT_URL, $url);  
    $defaultHeader = array('user-agent:'.(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'BeipyTitleBot/1.0'));
    curl_setopt($ch, CURLOPT_HTTPHEADER, empty($header) ? $defaultHeader : $header);  
    //curl_setopt($ch, CURLOPT_POST, true);  
    //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //2019-2-23 13:09:31 修复报错title curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);  
    $response = curl_exec($ch);  
    if($error=curl_error($ch)){  
        curl_close($ch);
        return '';
    }  
    curl_close($ch);  
    return $response;  
}  

?>
