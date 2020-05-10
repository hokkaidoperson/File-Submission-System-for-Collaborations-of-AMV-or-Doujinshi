<?php
require_once('../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

//メッセージID
$id = basename($_POST["name"]);

if ($id == "") die('パラメーターエラー');
list($from, $time) = explode('_', $id);
if ($from != $_SESSION["userid"]) die("他人のメッセージは削除出来ません。");

unlink(DATAROOT . 'messages/' . $id . '.txt');

register_alert("メッセージを削除しました。", "success");

redirect("./index.php");
