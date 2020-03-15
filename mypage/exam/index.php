<?php
require_once('../../set.php');
session_start();
$titlepart = '提出作品・情報の確認・承認';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'exam_submitted') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br>
他のメンバーが確認を終えるまでしばらくお待ち願います。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_submitted_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br><br>
全てのメンバーがこの作品の確認を終えました。<br>
承認しても問題無いという意見で一致したため、<b>この作品を承認しました</b>。<br>
作品の提出者に承認の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_submitted_discuss') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br><br>
全てのメンバーがこの作品の確認を終えました。<br>
メンバー間で意見が分かれたため、<b>この作品の承認・拒否について議論する必要があります</b>。<br>
以下の「議論中の作品・情報」の項目から、簡易チャット画面に移って下さい。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_submitted_reject_m') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br><br>
全てのメンバーがこの作品の確認を終えました。<br>
軽微な修正が必要であるという意見で一致したため、<b>この作品を修正待ち状態にしました</b>。<br>
作品の提出者に、修正依頼の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_submitted_reject') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br><br>
全てのメンバーがこの作品の確認を終えました。<br>
内容上問題があるという意見で一致したため、<b>この作品を拒否しました</b>。<br>
作品の提出者に拒否の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_forceclose_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
投票を強制的に締め切りました。<br><br>
既に投票されていたデータを集計しました。<br>
承認しても問題無いという意見で一致したため、<b>この作品を承認しました</b>。<br>
作品の提出者に承認の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_forceclose_discuss') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
投票を強制的に締め切りました。<br><br>
既に投票されていたデータを集計しました。<br>
メンバー間で意見が分かれたため、<b>この作品の承認・拒否について議論する必要があります</b>。<br>
以下の「議論中の作品・情報」の項目から、簡易チャット画面に移って下さい。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_forceclose_reject_m') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
投票を強制的に締め切りました。<br><br>
既に投票されていたデータを集計しました。<br>
軽微な修正が必要であるという意見で一致したため、<b>この作品を修正待ち状態にしました</b>。<br>
作品の提出者に、修正依頼の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_forceclose_reject') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
投票を強制的に締め切りました。<br><br>
既に投票されていたデータを集計しました。<br>
内容上問題があるという意見で一致したため、<b>この作品を拒否しました</b>。<br>
作品の提出者に拒否の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_discuss_closed_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
結論を送信し、議論を終了しました。<br><br>
承認しても問題無いという結論になったため、<b>この作品を承認しました</b>。<br>
作品の提出者に承認の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_discuss_closed_reject_m') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
結論を送信し、議論を終了しました。<br><br>
軽微な修正が必要であるという結論になったため、<b>この作品を修正待ち状態にしました</b>。<br>
作品の提出者に、修正依頼の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_discuss_closed_reject') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
結論を送信し、議論を終了しました。<br><br>
内容上問題があるという結論になったため、<b>この作品を拒否しました</b>。<br>
作品の提出者に拒否の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
//--------------------------------------
if ($_SESSION["situation"] == 'exam_edit_submitted_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br><br>
全てのメンバーがこの変更の確認を終えました。<br>
承認しても問題無いという意見で一致したため、<b>この変更を承認しました</b>。<br>
作品の提出者に承認の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_edit_submitted_discuss') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br><br>
全てのメンバーがこの変更の確認を終えました。<br>
メンバー間で意見が分かれたため、<b>この変更の承認・拒否について議論する必要があります</b>。<br>
以下の「議論中の作品・情報」の項目から、簡易チャット画面に移って下さい。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_edit_submitted_reject') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br><br>
全てのメンバーがこの変更の確認を終えました。<br>
問題があるという意見で一致したため、<b>この変更を拒否しました</b>。<br>
作品の提出者に拒否の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_edit_forceclose_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
投票を強制的に締め切りました。<br><br>
既に投票されていたデータを集計しました。<br>
承認しても問題無いという意見で一致したため、<b>この変更を承認しました</b>。<br>
作品の提出者に承認の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_edit_forceclose_discuss') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
投票を強制的に締め切りました。<br><br>
既に投票されていたデータを集計しました。<br>
メンバー間で意見が分かれたため、<b>この変更の承認・拒否について議論する必要があります</b>。<br>
以下の「議論中の作品・情報」の項目から、簡易チャット画面に移って下さい。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_edit_forceclose_reject') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
投票を強制的に締め切りました。<br><br>
既に投票されていたデータを集計しました。<br>
問題があるという意見で一致したため、<b>この変更を拒否しました</b>。<br>
作品の提出者に拒否の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_edit_discuss_closed_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
結論を送信し、議論を終了しました。<br><br>
承認しても問題無いという結論になったため、<b>この変更を承認しました</b>。<br>
作品の提出者に承認の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_edit_discuss_closed_reject') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
結論を送信し、議論を終了しました。<br><br>
問題があるという結論になったため、<b>この変更を拒否しました</b>。<br>
作品の提出者に拒否の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
//-----------------------------------
if ($_SESSION["situation"] == 'exam_common_submitted_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br><br>
全てのメンバーが共通情報の確認を終えました。<br>
承認しても問題無いという意見で一致したため、<b>この内容を承認しました</b>。<br>
情報の提出者に承認の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_common_submitted_discuss') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br><br>
全てのメンバーが共通情報の確認を終えました。<br>
メンバー間で意見が分かれたため、<b>この内容の承認・拒否について議論する必要があります</b>。<br>
以下の「議論中の作品・情報」の項目から、簡易チャット画面に移って下さい。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_common_submitted_reject') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
確認結果を送信しました。<br><br>
全てのメンバーが共通情報の確認を終えました。<br>
問題があるという意見で一致したため、<b>この内容を拒否しました</b>。<br>
情報の提出者に拒否の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_common_forceclose_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
投票を強制的に締め切りました。<br><br>
既に投票されていたデータを集計しました。<br>
承認しても問題無いという意見で一致したため、<b>この共通情報を承認しました</b>。<br>
情報の提出者に承認の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_common_forceclose_discuss') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
投票を強制的に締め切りました。<br><br>
既に投票されていたデータを集計しました。<br>
メンバー間で意見が分かれたため、<b>この共通情報の承認・拒否について議論する必要があります</b>。<br>
以下の「議論中の作品・情報」の項目から、簡易チャット画面に移って下さい。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_common_forceclose_reject') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
投票を強制的に締め切りました。<br><br>
既に投票されていたデータを集計しました。<br>
問題があるという意見で一致したため、<b>この共通情報を拒否しました</b>。<br>
情報の提出者に拒否の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_common_discuss_closed_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
結論を送信し、議論を終了しました。<br><br>
承認しても問題無いという結論になったため、<b>この共通情報を承認しました</b>。<br>
情報の提出者に承認の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'exam_common_discuss_closed_reject') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
結論を送信し、議論を終了しました。<br><br>
問題があるという結論になったため、<b>この共通情報を拒否しました</b>。<br>
情報の提出者に拒否の通知をしました。
</div>';
    $_SESSION["situation"] = '';
}

$accessok = 'none';

//主催・共同運営
if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c') $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>、<b>共同運営者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

if (!file_exists(DATAROOT . 'examsetting.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、ファイル確認が出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');


//意見収集中（progress）
$examlist["p"] = array();
//意見収集中・自分は回答済み（already）
$examlist["a"] = array();
//議論中（discussion）
$examlist["d"] = array();
//終了（closed）
$examlist["c"] = array();
//権限を持たない（non-allowed）
$examlist["n"] = array();

$submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
}

foreach(glob(DATAROOT . 'exam/*.txt') as $filename) {
    $filedata = json_decode(file_get_contents($filename), true);
    if ($filedata["_state"] == 2 or $filedata["_state"] == 3) $key = "c";
    else if ($filedata["_state"] == 1) $key = "d";
    else if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) $key = "a";
    else $key = "p";
    if (array_search($_SESSION["userid"], $submitmem) === FALSE) $key = "n";
    if (!isset($filedata[$_SESSION["userid"]])) {}
    else if ($filedata[$_SESSION["userid"]]["opinion"] == -1) $key = "n";
    $readname = basename($filename);
    $authorandid = basename($filename, ".txt");
    $readname = str_replace('_', '/', $readname);
    $submitdata = json_decode(file_get_contents(DATAROOT . 'submit/' . $readname), true);
    $examlist[$key][$authorandid . '_new'] = $submitdata;
}
foreach(glob(DATAROOT . 'exam_edit/*.txt') as $filename) {
    $filedata = json_decode(file_get_contents($filename), true);
    if (!isset($filedata["_membermode"])) $filedata["_membermode"] = "edit";
    $memberfile = DATAROOT . 'exammember_' . $filedata["_membermode"] . '.txt';
    $submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $submitmem);
    if ($key !== FALSE) {
        $submitmem[$key] = id_promoter();
    }
    if ($filedata["_state"] == 2 or $filedata["_state"] == 3) $key = "c";
    else if ($filedata["_state"] == 1) $key = "d";
    else if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) $key = "a";
    else $key = "p";
    if (array_search($_SESSION["userid"], $submitmem) === FALSE) $key = "n";
    if (!isset($filedata[$_SESSION["userid"]])) {}
    else if ($filedata[$_SESSION["userid"]]["opinion"] == -1) $key = "n";
    $basename = basename($filename, ".txt");
    list($author, $id, $editid) = explode("_", $basename);
    if ($id === "common") {
        $submitdata = array("title" => "【共通情報】", "id" => $editid);
        if ($filedata["_commonmode"] == "new") $editid = "new";
        $examlist[$key][$author . "_" . $id . "_$editid"] = $submitdata;
        continue;
    }
    $submitdata = json_decode(file_get_contents(DATAROOT . 'submit/' . $author . '/' . $id . '.txt'), true);
    $examlist[$key][$basename] = $submitdata;
}
?>

<h1>提出作品・情報の確認・承認</h1>
<p>本イベントに対し提出された作品・情報の確認をします。<br>
内容の確認を行い、問題が無ければ「承認」に、軽微な修正（動画の音量バランス修正など）が必要な場合は「修正待ち」に、内容上問題があれば「拒否」となります。</p>
<p>ファイル確認を行う者は、「ファイル確認に関する設定」で指定した担当者です。ただし、あなた自身の提出作品については、あなたの回答は自動的に「承認しても問題無い」になります。</p>
<p>全員の回答が出揃う前であれば、回答は何度でも変更出来ます。ただし、全員の回答が出揃った瞬間にあなたの回答は確定し、変更不可になります。</p>
<p>ファイル確認を行う者が2人以上いる場合、全員の意見が一致すればその意見が通ります。意見が分かれた場合は、簡易チャットでの議論を経て、最終判断を下します。</p>
<h2>未回答の作品・情報</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th width="30%">提出者</th><th width="40%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>
</tr>
<?php
foreach ($examlist["p"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('_', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    echo "<td>" . htmlspecialchars($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="do_common.php?author=' . $author . '&edit=' . $data["id"] . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="do.php?author=' . $author . '&id=' . $submitid . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    else echo '<td><a href="do_edit.php?author=' . $author . '&id=' . $submitid . '&edit=' . $edit . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["p"] == array()) echo '<tr><td colspan="3">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<h2>議論中の作品・情報</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th width="30%">提出者</th><th width="40%">作品名（クリックで簡易チャット画面に移ります）</th><th width="30%">種別</th>
</tr>
<?php
foreach ($examlist["d"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('_', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    echo "<td>" . htmlspecialchars($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="discuss_common.php?author=' . $author . '&edit=' . $data["id"] . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="discuss.php?author=' . $author . '&id=' . $submitid . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    else echo '<td><a href="discuss_edit.php?author=' . $author . '&id=' . $submitid . '&edit=' . $edit . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["d"] == array()) echo '<tr><td colspan="3">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<h2>回答済み（他者の回答待ち）の作品・情報</h2>
<p><a class="btn btn-primary" data-toggle="collapse" href="#toggle1" role="button" aria-expanded="false" aria-controls="toggle1">
展開する
</a></p>
<div class="table-responsive-md collapse" id="toggle1">
<table class="table table-hover table-bordered">
<tr>
<th width="30%">提出者</th><th width="40%">作品名（クリックで回答を確認・変更出来ます）</th><th width="30%">種別</th>
</tr>
<?php
foreach ($examlist["a"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('_', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    echo "<td>" . htmlspecialchars($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="do_common.php?author=' . $author . '&edit=' . $data["id"] . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="do.php?author=' . $author . '&id=' . $submitid . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    else echo '<td><a href="do_edit.php?author=' . $author . '&id=' . $submitid . '&edit=' . $edit . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["a"] == array()) echo '<tr><td colspan="3">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<h2>回答および議論が終了した作品・情報</h2>
<p><a class="btn btn-primary" data-toggle="collapse" href="#toggle2" role="button" aria-expanded="false" aria-controls="toggle2">
展開する
</a></p>
<div class="table-responsive-md collapse" id="toggle2">
<table class="table table-hover table-bordered">
<tr>
<th width="30%">提出者</th><th width="40%">作品名（クリックで回答履歴を確認出来ます）</th><th width="30%">種別</th>
</tr>
<?php
foreach ($examlist["c"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('_', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    echo "<td>" . htmlspecialchars($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="do_common.php?author=' . $author . '&edit=' . $data["id"] . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="do.php?author=' . $author . '&id=' . $submitid . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    else echo '<td><a href="do_edit.php?author=' . $author . '&id=' . $submitid . '&edit=' . $edit . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["c"] == array()) echo '<tr><td colspan="3">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<h2>確認権限を持っていない作品・情報</h2>
<p><a class="btn btn-primary" data-toggle="collapse" href="#toggle3" role="button" aria-expanded="false" aria-controls="toggle3">
展開する
</a></p>
<div class="table-responsive-md collapse" id="toggle3">
<table class="table table-hover table-bordered">
<tr>
<th width="30%">提出者</th><th width="40%">作品名（クリックで詳細を確認出来ます）</th><th width="30%">種別</th>
</tr>
<?php
foreach ($examlist["n"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('_', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    echo "<td>" . htmlspecialchars($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="do_common.php?author=' . $author . '&edit=' . $data["id"] . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="do.php?author=' . $author . '&id=' . $submitid . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    else echo '<td><a href="do_edit.php?author=' . $author . '&id=' . $submitid . '&edit=' . $edit . '">' . htmlspecialchars($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["n"] == array()) echo '<tr><td colspan="3">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
