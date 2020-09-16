<?php
require_once('set.php');
//サイトのURLを取得
$url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
$url = preg_replace('/reget_url\.php$/', '', $url);

//サイト名を保管
if (file_get_contents_repeat(DATAROOT . 'siteurl.txt') != $url) {
    if (file_put_contents_repeat(DATAROOT . 'siteurl.txt', $url) === FALSE) die('サイトURLの書き込みに失敗しました。');
}

redirect("./index.php");
