<?php

$siteUrl = "http://local.envie-de-queyras.com/";

function getUrlContent($url){
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    return ($httpcode>=200 && $httpcode<300) ? array('data' => $data, 'contentType' => $contentType) : false;
}

$data = getUrlContent(trim($siteUrl, '/') . '/user/cron/without-photo?launchby=cron');

header('Content-type: ' . $data['contentType']);
echo $data['data'];
exit;