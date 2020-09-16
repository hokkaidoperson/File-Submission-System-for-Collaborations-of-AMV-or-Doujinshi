<?php
require_once('../set.php');
setup_session();
$titlepart = 'マイページ トップ';
require_once(PAGEROOT . 'mypage_header.php');

//主催者の登録が完了していない場合（招待リンクの期限チェック）
if (file_exists(DATAROOT . 'mail/invitation/_promoter.txt')) {
    $filedata = json_decode(file_get_contents_repeat(DATAROOT . 'mail/invitation/_promoter.txt'), true);
    if ($filedata["expire"] <= time()) unlink(DATAROOT . 'mail/invitation/_promoter.txt');
}
if (!file_exists(DATAROOT . 'users/_promoter.txt') and $_SESSION["admin"]) {
    if (!file_exists(DATAROOT . 'mail/invitation/_promoter.txt')) echo_alert('<b>主催者が登録されていません。</b><br><a href="invite/index.php">こちらのリンクをクリックして、主催者にアカウント登録の招待メールを送信して下さい。</a>', "danger", TRUE);
    else echo_alert('現在、主催者の登録を待機しています。<br><a href="invite/index.php">メールの送付先を誤った事に気付いた場合は、こちらのリンクをクリックして、正しい送付先にリンクを送り直して下さい。</a>', "warning", TRUE);
}

//フォーム登録してない場合
if ($_SESSION["state"] == 'p' and !file_exists(DATAROOT . 'form/userinfo/done.txt')) echo_alert('<b>共通情報の記入事項が登録されていません。</b><br><a href="setting/userform/index.php">こちらのリンクをクリックして、記入事項を登録して下さい。</a>', "danger", TRUE);
if ($_SESSION["state"] == 'p' and !file_exists(DATAROOT . 'form/submit/done.txt')) echo_alert('<b>ファイル提出時の記入事項や、ファイル提出期間が登録されていません。</b><br><a href="setting/submitform/index.php">こちらのリンクをクリックして、記入事項と提出期間を登録して下さい。</a>', "danger", TRUE);
if ($_SESSION["state"] == 'p' and !file_exists(DATAROOT . 'examsetting.txt')) echo_alert('<b>ファイル確認に関する設定をしていません。</b><br><a href="setting/exam/index.php">こちらのリンクをクリックして、設定を完了させて下さい。</a>', "danger", TRUE);

if (file_exists(DATAROOT . 'form/submit/done.txt')) {
    //受付3日前・受付中・締め切り後の表示
    $general = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);
    $fromdisp = date('Y年n月j日G時i分s秒', $general["from"]);
    $untildisp = date('Y年n月j日G時i分s秒', $general["until"]);
    if ($general["from"] > time()) {
        if ($general["from"] <= time() + 3 * 24 * 60 * 60) echo_alert('ファイル提出受付は、' . $fromdisp . 'より始まります。', "primary", TRUE);
    }
    else if ($general["until"] <= time()) {
        if ($_SESSION["state"] == 'p') echo_alert('ファイル提出受付は、' . $untildisp . 'に締め切りました。<br>
また、提出済みのファイル情報や、共通情報、ニックネームの編集機能も停止しました（メールアドレス、パスワードは編集可）。<br><br>
「参加者・作品の一覧・編集」ページから、提出されたファイルや入力内容を一括ダウンロード出来ます。<br><br>
ただし、主催者が認めれば、締め切り後の提出・編集が可能です（主催者自身の情報編集は常時可能です）。<br>
参加者からの要請を受諾して提出・編集機能を限定的に解禁する場合は、「権限コントロール」ページから設定して下さい。', "danger", TRUE);
        else echo_alert('ファイル提出受付は、' . $untildisp . 'に締め切りました。<br>
また、提出済みのファイル情報や、共通情報、ニックネームの編集も出来なくなりました（メールアドレス、パスワードは編集可）。<br>
もし事情があって提出・編集したい場合は、主催者にご相談下さい。主催者が認めれば、締め切り後の提出・編集が可能です。', "danger", TRUE);
    }
    else {
        if ($general["until"] <= time() + 3 * 24 * 60 * 60) echo_alert('ファイル提出受付は、' . $untildisp . 'に締め切られます。<br>
締切と同時に、提出済みのファイル情報や、共通情報、ニックネームの編集も出来なくなります（ただし、主催者が認めれば、締め切り後の提出・編集が可能です）。<br>
ファイル提出や情報編集が必要な方は、お早めにお願い致します。', "warning", TRUE);
        else echo_alert('現在、ファイル提出受付中です（' . $untildisp . 'まで）。', "success", TRUE);
    }
}

$formdata = id_array($_SESSION["userid"]);
//共通情報書いた？
if (file_exists(DATAROOT . 'form/userinfo/0.txt') and !isset($formdata["common_acceptance"])) echo_alert('共通情報の記入が必要です。「共通情報の入力・編集」をご確認下さい。', "primary", TRUE);

//ファイル確認のタスクが残ってないか
if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c') {
    $remain_p = FALSE;
    $remain_d = FALSE;
    $remain_r = FALSE;
    $leader = ["submit" => id_leader("submit"), "edit" => id_leader("edit")];
    if (file_exists(DATAROOT . 'exammember_submit.txt')) {
        $submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $key = array_search("_promoter", $submitmem);
        if ($key !== FALSE) {
            $submitmem[$key] = id_promoter();
        }

        foreach(glob(DATAROOT . 'exam/*.txt') as $filename) {
            if (array_search($_SESSION["userid"], $submitmem) === FALSE) break;
            $filedata = json_decode(file_get_contents_repeat($filename), true);
            if ($filedata["_state"] == 2 or $filedata["_state"] == 3) continue;
            else if ($filedata["_state"] == 1) $remain_d = TRUE;
            else if ($filedata["_state"] == 4 and $leader["submit"] != NULL and $leader["submit"] == $_SESSION["userid"]) $remain_r = TRUE;
            else if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) continue;
            else $remain_p = TRUE;
        }
    }
    foreach(glob(DATAROOT . 'exam_edit/*.txt') as $filename) {
        $filedata = json_decode(file_get_contents_repeat($filename), true);
        if (!isset($filedata["_membermode"])) $filedata["_membermode"] = "edit";
        $memberfile = DATAROOT . 'exammember_' . $filedata["_membermode"] . '.txt';
        $submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $key = array_search("_promoter", $submitmem);
        if ($key !== FALSE) {
            $submitmem[$key] = id_promoter();
        }
        if (array_search($_SESSION["userid"], $submitmem) === FALSE) continue;
        if ($filedata["_state"] == 2 or $filedata["_state"] == 3) continue;
        else if ($filedata["_state"] == 1) $remain_d = TRUE;
        else if ($filedata["_state"] == 4 and $leader[$filedata["_membermode"]] != NULL and $leader[$filedata["_membermode"]] == $_SESSION["userid"]) $remain_r = TRUE;
        else if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) continue;
        else $remain_p = TRUE;
    }
    $echotext = [];
    if ($remain_p) $echotext[] = "確認を行っていない提出物があります。";
    if ($remain_d) $echotext[] = "議論中の提出物があります。";
    if ($remain_r) $echotext[] = "提出者への理由通知を行っていない提出物があります。";
    $inalert = "";
    if (isset($echotext[0])) $inalert .= $echotext[0];
    if (isset($echotext[1])) $inalert .= "また、" . $echotext[1];
    if (isset($echotext[2])) $inalert .= "更に、" . $echotext[2];
    if ($inalert != "") echo_alert($inalert . '<br>「提出物の確認・承認」からご確認願います。', "primary", TRUE);
}

//未読メッセージ
$unread = 0;
foreach(glob(DATAROOT . 'messages/*.txt') as $filename) {
    $filedata = json_decode(file_get_contents_repeat($filename), true);
    if (isset($filedata[$_SESSION["userid"]]) and $filedata[$_SESSION["userid"]] == 0) $unread++;
}
if ($unread > 0) echo_alert('未読のメッセージが <b>' . $unread . '件</b> あります。<br>
「メッセージ機能」からご確認願います。', "primary", TRUE);

?>

<h1>マイページ　トップ</h1>

<h2>作品提出・閲覧・管理</h2>
<div class="row system-mytop-spacer">
<?php
if ($_SESSION["state"] == 'p') echo '<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/upload.svg">
<div class="media-body">
<a href="submit/index.php" class="stretched-link"><h5>作品を提出する</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">作品の新規提出はこちらから行って下さい。</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/common.svg">
<div class="media-body">
<a href="common/index.php" class="stretched-link"><h5>共通情報の入力・編集</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">共通情報の設定を行った場合は、こちらから入力・編集して下さい。</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/list.svg">
<div class="media-body">
<a href="list/index.php" class="stretched-link"><h5>参加者・作品の一覧・編集</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">参加者から提出された作品、及びご自身の作品はこちらから確認・ダウンロード出来ます。ご自身の作品の編集も行えます。</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/exam.svg">
<div class="media-body">
<a href="exam/index.php" class="stretched-link"><h5>提出物の確認・承認</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">作品・情報が提出されたら、こちらから確認・承認を行って下さい。</span></p></div></div>
</div>
';
if ($_SESSION["state"] == 'c') echo '<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/upload.svg">
<div class="media-body">
<a href="submit/index.php" class="stretched-link"><h5>作品を提出する</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">作品の新規提出はこちらから行って下さい。</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/common.svg">
<div class="media-body">
<a href="common/index.php" class="stretched-link"><h5>共通情報の入力・編集</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">必要に応じて、こちらの情報も入力・編集を行って下さい。</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/list.svg">
<div class="media-body">
<a href="list/index.php" class="stretched-link"><h5>提出済み作品一覧・編集</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">提出済みのご自身の作品はこちらから確認・ダウンロード・編集出来ます。<br>主催者から特定の作品の閲覧権が与えられている場合、その作品も閲覧出来ます。</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/exam.svg">
<div class="media-body">
<a href="exam/index.php" class="stretched-link"><h5>提出物の確認・承認</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">作品・情報が提出されたら、こちらから確認・承認を行って下さい。</span></p></div></div>
</div>';
if ($_SESSION["state"] == 'g') echo '<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/upload.svg">
<div class="media-body">
<a href="submit/index.php" class="stretched-link"><h5>作品を提出する</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">作品の新規提出はこちらから行って下さい。</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/common.svg">
<div class="media-body">
<a href="common/index.php" class="stretched-link"><h5>共通情報の入力・編集</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">必要に応じて、こちらの情報も入力・編集を行って下さい。</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/list.svg">
<div class="media-body">
<a href="list/index.php" class="stretched-link"><h5>提出済み作品一覧・編集</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">提出済みのご自身の作品はこちらから確認・ダウンロード・編集出来ます。</span></p></div></div>
</div>';
if ($_SESSION["state"] == 'o') echo '<p class="system-mytop-spacer">表示可能な項目がありません。</p>';
?>
</div>
<h2>やり取り</h2>
<div class="row system-mytop-spacer">
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/message.svg">
<div class="media-body">
<a href="message/index.php" class="stretched-link"><h5>メッセージ機能</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">あなた宛てに届いたメッセージの確認、及びメッセージの新規作成を行えます。</span></p></div></div>
</div>
</div>
<h2>各種設定・その他</h2>
<div class="row system-mytop-spacer">
<?php
if ($_SESSION["state"] == 'p') echo '<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/acl.svg">
<div class="media-body">
<a href="auth/index.php" class="stretched-link"><h5>権限コントロール</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">以下の設定を行えます。<br>共同運営者の他者ファイル閲覧権限 ／ 提出期間外のファイル提出・情報編集権限</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/edit.svg">
<div class="media-body">
<a href="setting/index.php" class="stretched-link"><h5>イベント情報編集</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">以下の設定を行えます。<br>共通情報・ファイル提出時の記入事項 ／ ファイルの提出期間 ／ ファイル確認に関する設定</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/invite.svg">
<div class="media-body">
<a href="invite/index.php" class="stretched-link"><h5>共同運営者の追加・招待</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">登録済みのユーザーを共同運営者にする事が出来ます。また、共同運営者の招待（アカウント作成）リンクをメール送信出来ます。</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/mail.svg">
<div class="media-body">
<a href="schedule/index.php" class="stretched-link"><h5>受付開始・締切メールの自動配信</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">提出受付開始前・開始直後、締切前・締切直後に、通知メールを送信出来ます。</span></p></div></div>
</div>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/ban.svg">
<div class="media-body">
<a href="ban/index.php" class="stretched-link"><h5>ブラックリスト・アカウント作成制限</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">登録済みユーザーの凍結、及びアカウント新規作成の制限を行えます。</span></p></div></div>
</div>';

if ($_SESSION["admin"]) echo '<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/setting.svg">
<div class="media-body">
<a href="system/index.php" class="stretched-link"><h5>システム設定</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">初期設定時に入力したシステム設定を変更出来ます。</span></p></div></div>
</div>';
?>
<div class="system-cardindv">
<div class="card"><div class="card-body"><div class="media d-flex align-items-center">
<img class="align-self-center mr-3 system-mytop-icon" src="../images/account.svg">
<div class="media-body">
<a href="account/index.php" class="stretched-link"><h5 class="card-title">アカウント情報編集</h5></a>
</div></div><hr>
<p class="card-text"><span class="text-decoration-none text-body">ユーザー登録時に入力した情報の変更はこちらから行えます。</span></p></div></div>
</div>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
