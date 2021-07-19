<?php
require_once('../../set.php');
setup_session();
$titlepart = '提出物確認の履歴';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c"), TRUE);

if (!file_exists(DATAROOT . 'examsetting.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、ファイル確認が出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');


//意見収集中（progress） _state=0
$examlist["p"] = array();
//意見収集中・自分は回答済み（already）
$examlist["a"] = array();
//議論中（discussion） _state=1
$examlist["d"] = array();
//理由取りまとめ待ち（reason） _state=4
$examlist["r"] = array();
//終了（closed） _state=2（議論後） _state=3（即決）
$examlist["c"] = array();
//権限を持たない（non-allowed）
$examlist["n"] = array();

$submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
}
$editmem = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $editmem);
if ($key !== FALSE) {
    $editmem[$key] = id_promoter();
}

foreach(glob(DATAROOT . 'exam/*.txt') as $filename) {
    $filedata = json_unpack($filename);
    if ($filedata["_state"] == 2 or $filedata["_state"] == 3) $key = "c";
    else if ($filedata["_state"] == 1) $key = "d";
    else if ($filedata["_state"] == 4) $key = "r";
    else if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) $key = "a";
    else $key = "p";
    if (array_search($_SESSION["userid"], $submitmem) === FALSE) $key = "n";
    $authorandid = $filedata["_realid"];
    if (!file_exists(DATAROOT . 'submit/' . $authorandid . '.txt')) {
        unlink($filename);
        continue;
    }
    $submitdata = json_unpack(DATAROOT . 'submit/' . $authorandid . '.txt');
    $examlist[$key][$authorandid . '/new'] = $submitdata;
    $examlist[$key][$authorandid . '/new']["examrealid"] = basename($filename, ".txt");
}
foreach(glob(DATAROOT . 'exam_edit/*.txt') as $filename) {
    $filedata = json_unpack($filename);
    if (!isset($filedata["_membermode"])) $filedata["_membermode"] = "edit";
    if ($filedata["_membermode"] == "edit") $thismem = $editmem;
    else $thismem = $submitmem;
    if ($filedata["_state"] == 2 or $filedata["_state"] == 3) $key = "c";
    else if ($filedata["_state"] == 1) $key = "d";
    else if ($filedata["_state"] == 4) $key = "r";
    else if (isset($filedata[$_SESSION["userid"]]["opinion"]) and $filedata[$_SESSION["userid"]]["opinion"] != 0) $key = "a";
    else $key = "p";
    if (array_search($_SESSION["userid"], $thismem) === FALSE) $key = "n";
    $basename = $filedata["_realid"];
    list($author, $id, $editid) = explode("/", $basename);
    if ($id === "common") {
        $submitdata = array("title" => "【共通情報】", "id" => $editid);
        if ($filedata["_commonmode"] == "new") $editid = "new";
        $examlist[$key][$author . "/" . $id . "/$editid"] = $submitdata;
        $examlist[$key][$author . "/" . $id . "/$editid"]["examrealid"] = basename($filename, ".txt");
        continue;
    }
    if (!file_exists(DATAROOT . 'submit/' . $author . '/' . $id . '.txt')) {
        unlink($filename);
        continue;
    }
    $submitdata = json_decode(file_get_contents_repeat(DATAROOT . 'submit/' . $author . '/' . $id . '.txt'), true);
    $examlist[$key][$basename] = $submitdata;
    $examlist[$key][$basename]["examrealid"] = basename($filename, ".txt");
}
?>

<h1>提出物確認の履歴</h1>
<p>本イベントに当たって行われた、提出物確認の全履歴です。</p>
<h2>未回答の作品・情報</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<?php
if (exam_anonymous()) {
    echo '<th width="70%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
    $colspan = "2";
}
else {
    echo '<th width="30%">提出者</th><th width="40%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
    $colspan = "3";
}
?>
</tr>
<?php
foreach ($examlist["p"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('/', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    if (!exam_anonymous()) echo "<td>" . hsc($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="do_common.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="do.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else echo '<td><a href="do_edit.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["p"] == array()) echo '<tr><td colspan="' . $colspan . '">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<h2>議論中の作品・情報</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<?php
if ($colspan == "2") echo '<th width="70%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
else echo '<th width="30%">提出者</th><th width="40%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
?>
</tr>
<?php
foreach ($examlist["d"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('/', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    if (!exam_anonymous()) echo "<td>" . hsc($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="discuss_common.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="discuss.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else echo '<td><a href="discuss_edit.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["d"] == array()) echo '<tr><td colspan="' . $colspan . '">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<h2>非承認理由未入力の作品・情報</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<?php
if ($colspan == "2") echo '<th width="70%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
else echo '<th width="30%">提出者</th><th width="40%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
?>
</tr>
<?php
foreach ($examlist["r"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('/', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    if (!exam_anonymous()) echo "<td>" . hsc($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="frame_common.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="frame.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else echo '<td><a href="frame_edit.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["r"] == array()) echo '<tr><td colspan="' . $colspan . '">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<h2>回答済み（他者の回答待ち）の作品・情報</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<?php
if ($colspan == "2") echo '<th width="70%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
else echo '<th width="30%">提出者</th><th width="40%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
?>
</tr>
<?php
foreach ($examlist["a"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('/', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    if (!exam_anonymous()) echo "<td>" . hsc($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="do_common.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="do.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else echo '<td><a href="do_edit.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["a"] == array()) echo '<tr><td colspan="' . $colspan . '">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<h2>回答および議論が終了した作品・情報</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<?php
if ($colspan == "2") echo '<th width="70%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
else echo '<th width="30%">提出者</th><th width="40%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
?>
</tr>
<?php
foreach ($examlist["c"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('/', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    if (!exam_anonymous()) echo "<td>" . hsc($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="do_common.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="do.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else echo '<td><a href="do_edit.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["c"] == array()) echo '<tr><td colspan="' . $colspan . '">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<h2>確認権限を持っていない作品・情報</h2>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<?php
if ($colspan == "2") echo '<th width="70%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
else echo '<th width="30%">提出者</th><th width="40%">作品名（クリックで回答画面に移ります）</th><th width="30%">種別</th>';
?>
</tr>
<?php
foreach ($examlist["n"] as $authorandid => $data) {
    list($author, $submitid, $edit) = explode('/', $authorandid);
    $nickname = nickname($author);
    echo "<tr>\n";
    if (!exam_anonymous()) echo "<td>" . hsc($nickname) . "</td>";
    if ($submitid === 'common') echo '<td><a href="do_common.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else if ($edit == 'new') echo '<td><a href="do.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    else echo '<td><a href="do_edit.php?examname=' . $data["examrealid"] . '">' . hsc($data["title"]) . '</a></td>';
    if ($edit == 'new') echo "<td>新規提出</td>";
    else echo "<td>編集（" . date('Y年n月j日G時i分s秒', $edit) . "）</td>";
    echo "</tr>\n";
}
if ($examlist["n"] == array()) echo '<tr><td colspan="' . $colspan . '">現在、該当する作品はありません。</td></tr>';
?>
</table>
</div>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
