<?php
require_once('../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


csrf_prevention_validate();

$invalid = FALSE;
if (!user_exists($_POST["userid"])) $invalid = TRUE;
if ($_POST["fncs"] != "" and !is_array($_POST["fncs"])) $invalid = TRUE;
if ($_POST["files"] != "" and !is_array($_POST["files"])) $invalid = TRUE;

list($Yf, $mf, $df) = explode('-', $_POST["time_date"]);
list($hrf, $mnf) = explode(':', $_POST["time_time"]);
$dueunix = mktime($hrf, $mnf, 0, $mf, $df, $Yf);

if (checkdate($mf, $df, $Yf) !== true) $invalid = TRUE;

if ($hrf < 0 and $hrf > 23) $invalid = TRUE;
if ($mnf < 0 and $mnf > 59) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

$userid = $_POST["userid"];

//ディレクトリ作成
if (!file_exists(DATAROOT . 'outofterm/')) {
    if (!mkdir(DATAROOT . 'outofterm/')) die('ディレクトリの作成に失敗しました。');
}

$acldata = array_merge((array)$_POST["fncs"], (array)$_POST["files"]);

$acldata["expire"] = $dueunix;

$acldatajson =  json_encode($acldata);
if (file_put_contents_repeat(DATAROOT . 'outofterm/' . $userid . '.txt', $acldatajson) === FALSE) die('ACLデータの書き込みに失敗しました。');

//何が許可されているのかメールに書く
$whatok = [];
foreach ($acldata as $key => $value) {
    if ($value == 'userform') {
        $whatok[] = '共通情報（ニックネーム含む）の編集';
        continue;
    }
    if ($value == 'submit') {
        $whatok[] = 'ファイルの新規提出';
        continue;
    }
    if ((string)$key === 'expire') continue;
    $workdata = json_decode(file_get_contents_repeat(DATAROOT . 'submit/' . $userid . '/' . $value . '.txt'), true);
    $whatok[] = '作品「' . $workdata["title"] . '」の編集';
}

if ($whatok != [] and $dueunix > time()){
    $whatokj = implode("\n", $whatok);

    //対象者にメール
    $nickname = nickname($userid);
    $expirej = date('Y年n月j日G時i分s秒', $acldata["expire"]);
    $content = "$nickname 様

$eventname のポータルサイトにて、提出期間外ではありますが、主催者が以下の機能を許可しました。
ご確認頂き、必要な操作をなるべく早めに行って下さい。


　操作期限：$expirej

【許可された機能】
$whatokj
";
    //内部関数で送信
    sendmail(email($userid), '主催者に許可された機能があります', $content);
}
register_alert("操作権の変更が完了しました。", "success");

redirect("./index.php");
