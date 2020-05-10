<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


csrf_prevention_validate();

$blplace = DATAROOT . 'blackip.txt';

if (file_put_contents($blplace, $_POST["setting"]) === FALSE) die('リストデータの書き込みに失敗しました。');

register_alert("アカウント作成制限の設定を変更しました。", "success");

redirect("./index.php");