<?php
require_once('../../set.php');
session_start();
$titlepart = '提出作品（項目変更）の確認・承認 - 回答画面';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催・共同運営
if ($_SESSION["state"] == 'p' or $_SESSION["state"] == 'c') $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>、<b>共同運営者</b>のみです。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

//ファイル提出者のユーザーID
$author = $_GET["author"];

//提出ID
$id = $_GET["id"];

//編集ID
$editid = $_GET["edit"];

if ($author == "" or $id == "" or $editid == "") die_mypage('パラメーターエラー');


//入力内容（before）を読み込む
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die_mypage('ファイルが存在しません。');
$formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);

//フォーム設定データ
$formsetting = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $formsetting[$i] = json_decode(file_get_contents(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}
$formsetting["general"] = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);

//回答データ
if (!file_exists(DATAROOT . 'exam_edit/' . $author . '_' . $id . '_' . $editid . '.txt')) die_mypage('ファイルが存在しません。');
$filedata = json_decode(file_get_contents(DATAROOT . 'exam_edit/' . $author . '_' . $id . '_' . $editid . '.txt'), true);

//入力内容（after）を読み込む
if ($filedata["_state"] == 0) {
    if (!file_exists(DATAROOT . "edit/" . $author . "/" . $id . ".txt")) die_mypage('ファイルが存在しません。');
    $changeddata = json_decode(file_get_contents(DATAROOT . "edit/" . $author . "/" . $id . ".txt"), true);
}

$nopermission = FALSE;
$bymyself = FALSE;
if (!isset($filedata[$_SESSION["userid"]])) $nopermission = TRUE;
else if ($filedata[$_SESSION["userid"]]["opinion"] == -1) $nopermission = TRUE;
if ($author == $_SESSION["userid"]) {
    $bymyself = TRUE;
    $nopermission = TRUE;
}

if ($filedata["_state"] == 0) echo '<h1>提出作品（項目変更）の確認・承認 - 回答画面</h1>
<p>下記の作品について、変更内容をご確認下さい。<br>
その後、変更内容への判断について、下記の入力フォームに回答して下さい。<br>
変更内容が承認されれば、変更をファイルに適用します。変更内容が拒否されれば、変更は適用されず、変更前のファイル内容が維持されます。</p>
<p>回答済みの場合、保存されている回答内容が入力されています。変更する場合は、新しい回答内容に変更し、送信して下さい。</p>
<p>ファイル確認を行う者が2人以上いる場合、全員の意見が一致すればその意見が通ります。意見が分かれた場合は、簡易チャットでの議論を経て、主催者が最終判断を下します。</p>
';
else if ($filedata["_state"] == 1) echo '<h1>提出作品（項目変更）の確認・承認 - 回答履歴</h1>
<p>この作品の項目変更への意見回答は締め切られました。</p>
<p>この作品は、最終判断について議論中です。<br>
<a href="discuss_edit.php?author=' . $author . '&id=' . $id . '&edit=' . $editid . '">議論画面はこちら</a></p>
';
else if ($filedata["_state"] == 2) echo '<h1>提出作品（項目変更）の確認・承認 - 回答履歴</h1>
<p>この作品の項目変更への意見回答は締め切られました。</p>
<p>この作品の最終判断について議論が行われ、対応が決定しました。<br>
<a href="discuss_edit.php?author=' . $author . '&id=' . $id . '&edit=' . $editid . '">議論履歴はこちら</a></p>
';
else echo '<h1>提出作品（項目変更）の確認・承認 - 回答履歴</h1>
<p>この作品の項目変更への意見回答は締め切られました。</p>
<p>回答者の意見が一致し、以下の通り対応が即決しました。</p>
';

if ($filedata["_state"] == 0 and $author != $_SESSION["userid"]) {
if (!isset($_SESSION["dld_caution"])) {
    echo '<p><div class="border border-warning" style="padding:10px;">
<b>【第三者のファイルをダウンロードするにあたっての注意事項】</b><br>
第三者が作成したファイルのダウンロードには、セキュリティ上のリスクを孕んでいる可能性があります。<br>
アップロード出来るファイルの拡張子を制限する事により、悪意あるファイルをある程度防いでいますが、悪意あるファイルの全てを防げる訳ではありません。<br>
<u>第三者が作成したファイルをダウンロードする際は、ウイルス対策ソフトなど、セキュリティを万全に整える事をお勧め致します</u>。
</div></p>';
    $_SESSION["dld_caution"] = 'ok';
}
}

if ($filedata["_state"] == 0) echo '<h2>作品の詳細（変更前）</h2>';
else echo '<h2>作品の詳細</h2>';
?>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<?php
if ($filedata["_state"] == 0) {

if (isset($formdata["submit"]) and $formdata["submit"] != "") echo '<tr>
<th>提出ファイル</th><td><a href="../fnc/filedld.php?author=' . $author . '&genre=submitmain&id=' . $id . '" target="_blank">' . $formdata["submit"] . 'ファイル（クリックでダウンロード）</a></td>
</tr>';
else {
    echo '<tr>
<th>提出ファイルダウンロード先</th><td><a href="' . htmlspecialchars($formdata["url"]) . '" target="_blank">クリックすると新しいウィンドウで開きます</a>';
    if (isset($formdata["dldpw"]) and $formdata["dldpw"] != "") echo '<br><font size="2">※パスワード等の入力を求められた場合は、次のパスワードを入力して下さい。<code>' . htmlspecialchars($formdata["dldpw"]) . '</code></font>';
    if (isset($formdata["due"]) and $formdata["due"] != "") echo '<br><font size="2">※ダウンロードURLの有効期限は <b>' . date('Y年n月j日G時i分', $formdata["due"]) . '</b> までです。お早めにダウンロード願います。</font>';
    echo '<br><font size="2">※<u>このファイルは、作品一覧画面の一括ダウンロード機能でダウンロードする事が出来ません</u>。ダウンロードが必要な場合は、必ずリンク先からダウンロードして下さい。</font>';
    echo '</td></tr>';
}

}
?>
<tr>
<th>提出者</th><td><?php echo htmlspecialchars(nickname($author)); ?></td>
</tr>
<tr>
<th>タイトル</th><td><?php echo htmlspecialchars($formdata["title"]); ?></td>
</tr>
<?php
if ($filedata["_state"] == 0) {

foreach ($formsetting as $key => $array) {
    if ($key === "general") continue;
    echo "<tr>\n";
    echo "<th>" . htmlspecialchars($array["title"]) . "</th>";
    echo "<td>";
    if ($array["type"] == "attach") {
        if ($formdata[$array["id"]] != "") echo '<a href="../fnc/filedld.php?author=' . $author . '&genre=submitform&id=' . $id . '&partid=' . $array["id"] . '" target="_blank">' . htmlspecialchars($formdata[$array["id"]]) . 'ファイル（クリックでダウンロード）</a>';
    }
    else if ($array["type"] == "check") {
        $dsp = implode("\n", $formdata[$array["id"]]);
        $dsp = htmlspecialchars($dsp);
        echo str_replace("\n", '<br>', $dsp);
    } else if ($array["type"] == "textbox2") {
        echo htmlspecialchars($formdata[$array["id"] . "-1"]);
        echo '<br>';
        echo htmlspecialchars($formdata[$array["id"] . "-2"]);
    } else echo htmlspecialchars($formdata[$array["id"]]);
    echo '</td>';
    echo "</tr>\n";
}

}
?>
</table>
</div>
<?php
if ($filedata["_state"] == 0) {
    echo '<h2>変更内容</h2>';
    echo '<div class="table-responsive-md">
<table class="table table-hover table-bordered">';
    if (isset($changeddata["submit"])) echo '<tr>
<th>提出ファイル</th><td><a href="../fnc/filedld.php?author=' . $author . '&genre=submitmain_edit&id=' . $id . '&edit=' . $editid . '" target="_blank">' . $changeddata["submit"] . 'ファイル（クリックでダウンロード）</a></td>
</tr>';
    else if (isset($changeddata["url"])) {
        echo '<tr>
<th>提出ファイルダウンロード先</th><td><a href="' . htmlspecialchars($changeddata["url"]) . '" target="_blank">クリックすると新しいウィンドウで開きます</a>';
        if (isset($changeddata["dldpw"])) echo '<br><font size="2">※パスワード等の入力を求められた場合は、次のパスワードを入力して下さい。<code>' . htmlspecialchars($changeddata["dldpw"]) . '</code></font>';
        if (isset($changeddata["due"])) echo '<br><font size="2">※ダウンロードURLの有効期限は <b>' . date('Y年n月j日G時i分', $changeddata["due"]) . '</b> までです。お早めにダウンロード願います。</font>';
        echo '<br><font size="2">※<u>このファイルは、作品一覧画面の一括ダウンロード機能でダウンロードする事が出来ません</u>。ダウンロードが必要な場合は、必ずリンク先からダウンロードして下さい。</font>';
        echo '</td></tr>';
    }
    if (isset($changeddata["title"])) echo '<tr>
<th>タイトル</th><td>' . htmlspecialchars($changeddata["title"]) . '</td>
</tr>';
    foreach ($formsetting as $key => $array) {
        if ($key === "general") continue;
        if (isset($changeddata[$array["id"]])) {
            echo "<tr>\n";
            echo "<th>" . htmlspecialchars($array["title"]) . "</th>";
            echo "<td>";
            if ($array["type"] == "attach") {
                if ($changeddata[$array["id"]] != "") echo '<a href="../fnc/filedld.php?author=' . $author . '&genre=submitform_edit&id=' . $id . '&partid=' . $array["id"] . '&edit=' . $editid . '" target="_blank">' . htmlspecialchars($changeddata[$array["id"]]) . 'ファイル（クリックでダウンロード）</a>';
            }
            else if ($array["type"] == "check") {
                $dsp = implode("\n", $changeddata[$array["id"]]);
                $dsp = htmlspecialchars($dsp);
                echo str_replace("\n", '<br>', $dsp);
            } else if ($array["type"] == "textbox2") {
                echo htmlspecialchars($changeddata[$array["id"] . "-1"]);
                echo '<br>';
                echo htmlspecialchars($changeddata[$array["id"] . "-2"]);
            } else echo htmlspecialchars($changeddata[$array["id"]]);
            echo '</td>';
            echo "</tr>\n";
        }
    }
    echo '</table>
</div>';
}
?>
<h2>回答状況</h2>
<p><a class="btn btn-primary" data-toggle="collapse" href="#toggle" role="button" aria-expanded="false" aria-controls="toggle">
展開する
</a></p>
<div class="table-responsive-md collapse" id="toggle">
<table class="table table-hover table-bordered">
<tr>
<?php
if ($filedata["_state"] == 0) echo '<th>回答者</th><th>回答状況</th>';
else echo '<th>回答者</th><th>回答内容</th><th>理由</th>';
?>
</tr>
<?php
foreach ($filedata as $key => $data) {
    if (strpos($key, '_') !== FALSE) continue;
    $nickname = nickname($key);
    echo "<tr>\n";
    echo "<td>" . htmlspecialchars($nickname) . "</td>";
    if ($filedata["_state"] == 0) {
        if ($data["opinion"] == -1) echo '<td class="text-muted">一般参加者への切り替えにより回答権喪失</td>';
        else if ($data["opinion"] != 0) echo '<td class="text-success">回答済み</td>';
        else echo '<td>未回答</td>';
    } else {
        // opinion 0...未回答　1...承認 2...拒否 -1...主催or共催を降りた
        switch ($data["opinion"]) {
            case -1:
                echo '<td class="text-muted">一般参加者への切り替えにより回答権喪失</td>';
            break;
            case 1:
                echo '<td>承認する</td>';
            break;
            case 2:
                echo '<td>拒否する</td>';
            break;
            default:
                echo '<td>未回答</td>';
            break;
        }
        echo '<td>' . htmlspecialchars($data["reason"]) . '</td>';
    }
    echo "</tr>\n";
}
if (isset($filedata["_result"]) and $filedata["_result"] != "") {
    echo '<tr class="table-primary"><th>最終結果</th>';
    switch ($filedata["_result"]) {
      case 1:
          echo '<td colspan="2"><b>承認</b></td>';
      break;
      case 2:
          echo '<td colspan="2"><b>拒否</b></td>';
      break;
    }
    echo '</tr>';
}
?>
</table>
</div>
<?php if ($filedata["_state"] != 0) die_mypage(); ?>
<h2>回答する</h2>
<div class="border border-primary" style="padding:10px;">
<?php if (!$nopermission) { ?>
<form name="form" action="do_edit_handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="subject" value="<?php echo $author . '_' . $id . '_' . $editid; ?>">
<div class="form-group">
この変更を承認してもよいと思いますか？
<div class="form-check">
<input id="ans-1" class="form-check-input" type="radio" name="ans" value="1" <?php
if ($filedata[$_SESSION["userid"]]["opinion"] == 1) echo 'checked="checked"';
?>>
<label class="form-check-label" for="ans-1">はい、問題ありません。</label>
</div>
<div class="form-check">
<input id="ans-2" class="form-check-input" type="radio" name="ans" value="2" <?php
if ($filedata[$_SESSION["userid"]]["opinion"] == 2) echo 'checked="checked"';
?>>
<label class="form-check-label" for="ans-2">いいえ、問題があります。</label>
</div>
</div>
<div class="form-group">
<label for="reason">「問題がある」と答えた場合は、その理由を入力して下さい。（500文字以内）</label>
<textarea id="reason" name="reason" rows="4" cols="80" class="form-control"><?php
echo htmlspecialchars($filedata[$_SESSION["userid"]]["reason"]);
?></textarea>
<font size="2"><?php
if ($formsetting["general"]["reason"] == "notice") echo "※<b>ここで記入した理由は、ファイル提出者本人宛に送信するメールに記載される可能性があります。</b>";
else echo "※ここで記入した理由は、ファイル提出者本人宛に送信するメールに直接的に記載されません。";
?></font>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">回答を送信する</button>
</form>
<?php } else if ($bymyself) echo 'あなたはこのファイルの提出者であるため、「問題無い」に自動投票されています。';
else echo 'あなたはこのファイルに対する回答権を持っていません。<br>
あなたが主催者あるいは共同運営者になる前に提出された変更であるため、確認権が与えられませんでした。'; ?>
</div>
<?php
$echoforceclose = FALSE;
if ($_SESSION["state"] == 'p') {
    if (!isset($filedata[$_SESSION["userid"]]["opinion"])) $echoforceclose = TRUE;
    else if ($filedata[$_SESSION["userid"]]["opinion"] != 0) $echoforceclose = TRUE;
}

if ($echoforceclose) {
    echo '<h2>投票を強制的に締め切る</h2>
<p><b>原則としては、メンバー全員の投票が終わるのを待って下さい。</b><br>
<u>メンバーの誰かが投票をしておらず、かつそのメンバーと連絡が取れない場合</u>は、作業を長引かせないために、以下のボタンを押して、投票を終了して下さい。</p>
<p><b>この機能は、あくまでも最終手段としてご利用願います。</b></p>
<p><a href="do_edit_forceclose.php?author=' . $author . '&id=' . $id . '&edit=' . $editid . '" class="btn btn-danger" role="button" onclick="return window.confirm(\'投票を強制的に締め切ります。この操作を取り消す事は出来ませんが、よろしいですか？\')">投票を強制的に締め切る</a></p>';
}
?>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probans = 0;
  probrea = 0;


//必須の場合
  if(document.form.ans.value === ""){
    problem = 1;
    probans = 1;
  }

//文字数 条件必須
  if(document.form.reason.value === ""){
    if(document.form.ans.value === "2"){
      problem = 1;
      probrea = 1;
    }
  } else if(document.form.reason.value.length > 500){
    problem = 1;
    probrea = 2;
  }


//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probans == 1) {
    alert( "【回答内容】\nいずれかを選択して下さい。" );
  }
  if ( probrea == 1) {
    alert( "【理由】\n「問題がある」と答えた場合は、入力が必要です。" );
  }
  if ( probrea == 2) {
    alert( "【理由】\n文字数が多すぎます（現在" + document.form.reason.value.length + "文字）。500文字以内に抑えて下さい。" );
  }

  return false;
}

  if(window.confirm('入力内容に問題は見つかりませんでした。\n現在の回答内容を登録します。よろしいですか？')){
    submitbtn = document.getElementById("submitbtn");
    submitbtn.disabled = "disabled";
    return true;
  } else{
    return false;
  }

}
// -->
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
