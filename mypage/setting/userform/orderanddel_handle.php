<?php
require_once('../../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

if (no_access_right(array("p"))) redirect("./index.php");


if (!isset($_POST['number']) or !isset($_POST['do'])) {
    redirect("./index.php");
}

$i = (int) $_POST['number'];

if ($i < 0 or $i > 9) redirect("./index.php");
if (!isset($_SESSION["userformdata"])) redirect("./index.php");

if ($_POST['do'] == 'up') {
    if (($i - 1) < 0) redirect("./index.php");
    $tmp = $_SESSION["userformdata"][$i - 1];
    $_SESSION["userformdata"][$i - 1] = $_SESSION["userformdata"][$i];
    $_SESSION["userformdata"][$i] = $tmp;
    register_alert("項目を移動しました。<br>実際の入力欄にはまだ反映されていません。<strong>設定を完了する場合は、「変更内容を保存し適用する」ボタンを押して下さい</strong>。");
    $moveto = $i - 1;
    redirect("./index.php#custom-item-$moveto");
}
if ($_POST['do'] == 'down') {
    if (($i + 1) > 9) redirect("./index.php");
    $tmp = $_SESSION["userformdata"][$i + 1];
    $_SESSION["userformdata"][$i + 1] = $_SESSION["userformdata"][$i];
    $_SESSION["userformdata"][$i] = $tmp;
    register_alert("項目を移動しました。<br>実際の入力欄にはまだ反映されていません。<strong>設定を完了する場合は、「変更内容を保存し適用する」ボタンを押して下さい</strong>。");
    $moveto = $i + 1;
    redirect("./index.php#custom-item-$moveto");
}
if ($_POST['do'] == 'delete') {
    unset($_SESSION["userformdata"][$i]);
    for(; $i <= 9; $i++){
        if (!isset($_SESSION["userformdata"][$i + 1])) {
            unset($_SESSION["userformdata"][$i]);
            break;
        }
        $_SESSION["userformdata"][$i] = $_SESSION["userformdata"][$i + 1];
    }
    register_alert("項目を削除しました。<br>実際の入力欄にはまだ反映されていません。<strong>設定を完了する場合は、「変更内容を保存し適用する」ボタンを押して下さい</strong>。");
    redirect("./index.php");
}
