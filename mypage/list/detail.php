<?php
require_once('../../set.php');
session_start();
$titlepart = 'データ詳細';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//非参加者以外
if ($_SESSION["state"] != 'o') $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>非参加者以外のユーザー</b>です。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

//ファイル提出者のユーザーID
$author = basename($_GET["author"]);

//提出ID
$id = basename($_GET["id"]);

if ($author == "" or $id == "") die_mypage('パラメーターエラー');

//ファイルの閲覧権があるか確認
$allowed = FALSE;
switch($_SESSION["state"]) {
    case 'p':
        //主催者は基本的にアクセスおｋ
        $allowed = TRUE;
    break;
    case 'c':
        //主催がアクセス権を与えていたらおｋ
        $aclplace = DATAROOT . 'fileacl/' . $_SESSION["userid"] . '.txt';
        if (file_exists($aclplace)) {
            $acldata = json_decode(file_get_contents($aclplace), true);
            if (array_search($author . '_' . $id, $acldata) !== FALSE) $allowed = TRUE;
        }
        //breakしない、下へ行く
    case 'g':
        //自分のファイルだけ（共同運営者も同じく）
        if ($author == $_SESSION['userid']) $allowed = TRUE;
    break;
}
if (!$allowed) die_mypage('このファイルの閲覧権限がありません。');


//入力内容を読み込む
switch($id) {
    case 'userform':
        if (!file_exists(DATAROOT . "users/" . $author . ".txt")) die_mypage('ファイルが存在しません。');
        $formdata = json_decode(file_get_contents(DATAROOT . "users/" . $author . ".txt"), true);
    break;
    default:
        if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die_mypage('ファイルが存在しません。');
        $formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
}

//フォーム設定データ
$formsetting = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $formsetting[$i] = json_decode(file_get_contents(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}
$formsetting["general"] = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);

//ユーザーフォーム設定ファイル読み込み
$userformdata = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $userformdata[$i] = json_decode(file_get_contents(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
}

switch($id) {
    case 'userform':
        $disp = "ユーザーの詳細";
    break;
    default:
        $disp = "作品の詳細";
}

if ($author != $_SESSION["userid"]) {
if (!isset($_SESSION["dld_caution"])) {
    echo '<div class="border border-warning" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<b>【第三者のファイルをダウンロードするにあたっての注意事項】</b><br>
第三者が作成したファイルのダウンロードには、セキュリティ上のリスクを孕んでいる可能性があります。<br>
アップロード出来るファイルの拡張子を制限する事により、悪意あるファイルをある程度防いでいますが、悪意あるファイルの全てを防げる訳ではありません。<br>
<u>第三者が作成したファイルをダウンロードする際は、ウイルス対策ソフトなど、セキュリティを万全に整える事をお勧め致します</u>。
</div>';
    $_SESSION["dld_caution"] = 'ok';
}
}
if (blackuser($author)) echo '<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">
このユーザーは凍結されています。
</div>';
?>

<h1><?php echo $disp; ?></h1>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<?php
if ($id != "userform") {

if (isset($formdata["submit"]) and $formdata["submit"] != array()) {
    echo '<tr><th width="30%">提出ファイル</th><td width="70%">ファイル名をクリックするとそのファイルをダウンロードします。<br>';
    foreach ($formdata["submit"] as $filename => $title)
    echo '<a href="../fnc/filedld.php?author=' . $author . '&genre=submitmain&id=' . $id . '&partid=' . $filename . '" target="_blank">' . htmlspecialchars($title) . '</a><br>';
    echo '</td></tr>';
} else {
    echo '<tr>
<th width="30%">提出ファイルダウンロード先</th><td width="70%"><a href="' . htmlspecialchars($formdata["url"]) . '" target="_blank">クリックすると新しいウィンドウで開きます</a>';
    if (isset($formdata["dldpw"]) and $formdata["dldpw"] != "") echo '<br><font size="2">※パスワード等の入力を求められた場合は、次のパスワードを入力して下さい。<code>' . htmlspecialchars($formdata["dldpw"]) . '</code></font>';
    if (isset($formdata["due"]) and $formdata["due"] != "") echo '<br><font size="2">※ダウンロードURLの有効期限は <b>' . date('Y年n月j日G時i分', $formdata["due"]) . '</b> までです。お早めにダウンロード願います。</font>';
    echo '<br><font size="2">※<u>このファイルは、一括ダウンロード機能でダウンロードする事が出来ません</u>。ダウンロードが必要な場合は、必ずリンク先からダウンロードして下さい。</font>';
    echo '</td></tr>';
}
?>
<tr>
<th>提出者</th><td><?php echo htmlspecialchars(nickname($author)); ?></td>
</tr>
<tr>
<th>タイトル</th><td><?php echo htmlspecialchars($formdata["title"]); ?></td>
</tr>
<tr>
<th>提出日時</th><td><?php echo date('Y年n月j日G時i分s秒', $id); ?></td>
</tr>
<tr>
<th>最終更新日時</th><td><?php if (isset($formdata["editdate"])) echo date('Y年n月j日G時i分s秒', $formdata["editdate"]); else echo date('Y年n月j日G時i分s秒', $id); ?></td>
</tr>
<?php
foreach ($formsetting as $key => $array) {
    if ($key === "general") continue;
    echo "<tr>\n";
    echo "<th>" . htmlspecialchars($array["title"]) . "</th>";
    echo "<td>";
    if ($array["type"] == "attach") {
        if (isset($formdata[$array["id"]]) and $formdata[$array["id"]] != array()) {
            echo 'ファイル名をクリックするとそのファイルをダウンロードします。<br>';
            foreach ($formdata[$array["id"]] as $filename => $title)
            echo '<a href="../fnc/filedld.php?author=' . $author . '&genre=submitform&id=' . $id . '&partid=' . $array["id"] . '_' . $filename . '" target="_blank">' . htmlspecialchars($title) . '</a><br>';
        }
    }
    else if ($array["type"] == "check") {
        $dsp = implode("\n", $formdata[$array["id"]]);
        $dsp = htmlspecialchars($dsp);
        echo str_replace("\n", '<br>', $dsp);
    } else if ($array["type"] == "textbox2") {
        echo htmlspecialchars($formdata[$array["id"] . "-1"]);
        echo '<br>';
        echo htmlspecialchars($formdata[$array["id"] . "-2"]);
    } else echo give_br_tag($formdata[$array["id"]]);
    echo '</td>';
    echo "</tr>\n";
}
?>
<tr><th>承認の状態</th><?php
if (isset($formdata["editing"]) and $formdata["editing"] == 1) echo '<td>項目編集の承認待ち<br>※変更後の内容は上記表に反映されていません。</td>';
else switch ($formdata["exam"]) {
    case 0:
        echo '<td>承認待ち</td>';
    break;
    case 1:
        echo '<td class="text-success"><b>承認</b></td>';
    break;
    case 2:
        echo '<td class="text-warning"><b>修正待ち</b></td>';
    break;
    case 3:
        echo '<td class="text-danger"><b>承認見送り</b></td>';
    break;
}
echo "</tr>";

} else {

?>
<tr>
<th width="30%">ニックネーム</th><td width="70%"><?php echo htmlspecialchars(nickname($author)); ?></td>
</tr>
<?php
foreach ($userformdata as $key => $array) {
    echo "<tr>\n";
    echo "<th>" . htmlspecialchars($array["title"]) . "</th>";
    echo "<td>";
    if ($array["type"] == "attach") {
        if (isset($formdata[$array["id"]]) and $formdata[$array["id"]] != array()) {
            echo 'ファイル名をクリックするとそのファイルをダウンロードします。<br>';
            foreach ($formdata[$array["id"]] as $filename => $title)
            echo '<a href="../fnc/filedld.php?author=' . $author . '&genre=userform&id=' . $array["id"] . '_' . $filename . '" target="_blank">' . htmlspecialchars($title) . '</a><br>';
        }
    }
    else if ($array["type"] == "check") {
        $dsp = implode("\n", $formdata[$array["id"]]);
        $dsp = htmlspecialchars($dsp);
        echo str_replace("\n", '<br>', $dsp);
    } else if ($array["type"] == "textbox2") {
        echo htmlspecialchars($formdata[$array["id"] . "-1"]);
        echo '<br>';
        echo htmlspecialchars($formdata[$array["id"] . "-2"]);
    } else echo give_br_tag($formdata[$array["id"]]);
    echo '</td>';
    echo "</tr>\n";
}

}
?>
</table>
</div>
<?php if ($id == "userform" or $author != $_SESSION["userid"]) die_mypage(); ?>
<h2>ファイル操作</h2>
<?php
if (outofterm($id) != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;

if (!in_term() and !$outofterm) die_mypage('<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在、ファイル提出期間外のため、ファイル操作（編集・削除）は行えません。
</div>');
else {
    if (!in_term() and $_SESSION["state"] == 'p') echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在ファイル提出期間外ですが、主催者は常時ファイル操作（編集・削除）が可能です。
</div>';
    else if (!in_term()) echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在ファイル提出期間外ですが、あなたは主催者からこのファイルの操作（編集・削除）を許可されています（' . date('Y年n月j日G時i分s秒', outofterm($id)) . 'まで）。
</div>';
}
?>
<?php
if ($formdata["exam"] == 0 or $formdata["editing"] == 1) echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在、ファイルの確認待ちです。確認が完了するまでは、ファイルの編集が出来ません（削除は出来ます）。
</div><p>';
else echo '<p><a href="edit.php?author=' . $author . '&id=' . $id . '" class="btn btn-primary" role="button">内容を編集する（運営チームによる再確認が必要になる場合があります）</a> ';
?>
<a href="delete.php?author=<?php echo $author; ?>&id=<?php echo $id; ?>" class="btn btn-danger" role="button">この作品を削除する</a></p>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
