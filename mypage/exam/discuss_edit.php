<?php
require_once('../../set.php');
session_start();
$titlepart = '提出作品（項目変更）の確認・承認 - 議論画面';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'exam_discuss_added') {
    echo '<p><div class="border border-success" style="padding:10px;">
コメントを追加しました。
</div></p>';
    $_SESSION["situation"] = '';
}

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

//投票した時の回答データ
if (!file_exists(DATAROOT . 'exam_edit/' . $author . '_' . $id . '_' . $editid . '.txt')) die_mypage('ファイルが存在しません。');
$filedata = json_decode(file_get_contents(DATAROOT . 'exam_edit/' . $author . '_' . $id . '_' . $editid . '.txt'), true);

//入力内容（after）を読み込む
if ($filedata["_state"] == 1) {
    if (!file_exists(DATAROOT . "edit/" . $author . "/" . $id . ".txt")) die_mypage('ファイルが存在しません。');
    $changeddata = json_decode(file_get_contents(DATAROOT . "edit/" . $author . "/" . $id . ".txt"), true);
}

$nopermission = FALSE;
if (!isset($filedata[$_SESSION["userid"]])) $nopermission = TRUE;
else if ($filedata[$_SESSION["userid"]]["opinion"] == -1) $nopermission = TRUE;

if ($filedata["_state"] == 1) echo '<h1>提出作品（項目変更）の確認・承認 - 議論画面</h1>
<p>この作品への対応について、意見が分かれたため、以下の簡易チャットを用いて議論を行って下さい。</p>
<p>意見がまとまったら、主催者が最終的な対応を入力します。</p>
';
else if ($filedata["_state"] == 2) echo '<h1>提出作品（項目変更）の確認・承認 - 議論履歴</h1>
<p>この作品への対応について議論し、対応を決定しました。<br>
最終的な対応及び議論の履歴を以下に表示します。</p>
';
else die_mypage('この作品への議論は行われていません。');

//議論ログ
if (!file_exists(DATAROOT . 'exam_edit_discuss/')) {
    if (!mkdir(DATAROOT . 'exam_edit_discuss/')) die_mypage('ディレクトリの作成に失敗しました。');
}
if (file_exists(DATAROOT . 'exam_edit_discuss/' . $author . '_' . $id . '_' . $editid . '.txt')) $discussdata = json_decode(file_get_contents(DATAROOT . 'exam_edit_discuss/' . $author . '_' . $id . '_' . $editid . '.txt'), true);
else {
    $discussdata = array(
        "read" => array(),
        "comments" => array()
    );
    foreach ($filedata as $key => $data) {
        if (strpos($key, '_') !== FALSE) continue;
        if ($data["opinion"] == -1) continue;
        $discussdata["read"][$key] = 1;
    }
    $filedatajson = json_encode($discussdata);
    if (file_put_contents(DATAROOT . 'exam_edit_discuss/' . $author . '_' . $id . '_' . $editid . '.txt', $filedatajson) === FALSE) die('議論データの書き込みに失敗しました。');
}
//read...既読？（0未読　1既読）　comments...ログ

//既読の処理
if (isset($discussdata["read"][$_SESSION["userid"]]) and $discussdata["read"][$_SESSION["userid"]] == 0) {
    $discussdata["read"][$_SESSION["userid"]] = 1;
    $filedatajson = json_encode($discussdata);
    if (file_put_contents(DATAROOT . 'exam_edit_discuss/' . $author . '_' . $id . '_' . $editid . '.txt', $filedatajson) === FALSE) die('議論データの書き込みに失敗しました。');
}

//主催者がいなかったりする？（主催者が代わった時とか）
$prom = id_state('p');
$noprom = FALSE;
if (!isset($discussdata["read"][$prom[0]])) $noprom = TRUE;

if ($filedata["_state"] == 1 and $author != $_SESSION["userid"]) {
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
<p><a class="btn btn-primary" data-toggle="collapse" href="#toggle" role="button" aria-expanded="false" aria-controls="toggle">
展開する
</a></p>
<div class="table-responsive-md collapse" id="toggle">
<table class="table table-hover table-bordered">
<?php
if ($filedata["_state"] == 1) {

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
if ($filedata["_state"] == 1) {

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
if ($filedata["_state"] == 1) {
    echo '<h2>変更内容</h2>';
    echo '<p><a class="btn btn-primary" data-toggle="collapse" href="#toggle2" role="button" aria-expanded="false" aria-controls="toggle2">
展開する
</a></p>';
    echo '<div class="table-responsive-md collapse" id="toggle2">
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
<h2>メンバーの回答</h2>
<div class="table-responsive-md">
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
    // opinion 0...未回答　1...承認 2...拒否
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
<h2>簡易チャット</h2>
<div class="border border-primary" style="padding:10px;">
<?php
if ($discussdata["comments"] != array()) {
    echo "<ul>";
    foreach ($discussdata["comments"] as $key => $log) {
        list($comid, $date) = explode('_', $key);
        $log = str_replace('&amp;', '&', htmlspecialchars($log));
        $log = preg_replace('{https?://[\w/:%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank">$0</a>', $log);
        $log = str_replace(array("\r\n", "\r", "\n"), "\n", $log);
        $log = str_replace("\n", "<br>", $log);
        if ($comid == "-system") $nickname = "<u>システム</u>";
        else $nickname = htmlspecialchars(nickname($comid));

        echo "<li>";
        echo "<b>" . $nickname . "</b>（" . date('Y/m/d H:i:s', $date) . "）<br>" . $log;
        echo "</li>";
    }
    echo "</ul>";
}
if ($filedata["_state"] != '1') die_mypage('</div>');
if ($nopermission) echo 'あなたはこのファイルに対する発言権を持っていません。<br>
あなたが主催者あるいは共同運営者になる前に提出されたファイルであるため、確認権が与えられませんでした。';
else {
?>
<form name="form" action="discuss_edit_handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="subject" value="<?php echo $author . '_' . $id . '_' . $editid; ?>">
<div class="form-group">
<label for="add">新規コメント追加（500文字以内）</label>
<textarea id="add" name="add" rows="4" cols="80" class="form-control"></textarea>
<font size="2">※改行は反映されます（この入力欄で改行すると実際のコメントでも改行されます）が、HTMLタグはお使いになれません。<br>
　ただし、URLを記載すると、自動的にリンクが張られます。</font>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">コメントを追加</button>
</form>
<?php } ?>
</div>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probadd = 0;


//必須の場合・文字数
  if(document.form.add.value === ""){
    problem = 1;
    probadd = 1;
  } else if(document.form.add.value.length > 500){
    problem = 1;
    probadd = 2;
  }


//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probadd == 1) {
    alert( "【コメント】\n入力されていません。" );
  }
  if ( probadd == 2) {
    alert( "【コメント】\n文字数が多すぎます（現在" + document.form.add.value.length + "文字）。500文字以内に抑えて下さい。" );
  }

  return false;
}

  submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";
  return true;

}
// -->
</script>

<?php if ($_SESSION["state"] != 'p' and $noprom == FALSE) die_mypage(); ?>
<h2>最終判断</h2>
<p>結論が固まりましたら、以下にその結論を入力して、議論を終了して下さい。<br>
トラブル防止のため、結論が固まっていない段階で入力を行うのはお控え下さい。</p>
<?php if ($noprom) echo '<p><u>本来、この機能は主催者向けですが、主催者が交代したなどの要因により、議論参加メンバーに主催者がいないため、この機能が共同運営者にも開放されています。</u></p>'; ?>
<div class="border border-primary" style="padding:10px;">
<form name="form_decide" action="discuss_edit_decide.php" method="post" onSubmit="return check_decide()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="subject" value="<?php echo $author . '_' . $id . '_' . $editid; ?>">
<div class="form-group">
議論の末の判断を以下から選んで下さい。
<div class="form-check">
<input id="ans-1" class="form-check-input" type="radio" name="ans" value="1">
<label class="form-check-label" for="ans-1">この変更を承認する</label>
</div>
<div class="form-check">
<input id="ans-2" class="form-check-input" type="radio" name="ans" value="2">
<label class="form-check-label" for="ans-2">この変更を拒否する</label>
</div>
</div>
<div class="form-group">
<label for="reason">「拒否する」と答えた場合は、その理由を入力して下さい。（500文字以内）</label>
<textarea id="reason" name="reason" rows="4" cols="80" class="form-control"></textarea>
<font size="2"><?php
if ($formsetting["general"]["reason"] == "notice") echo "※<b>ここで記入した理由は、ファイル提出者本人宛に送信するメールに記載される可能性があります。</b>";
else echo "※ここで記入した理由は、ファイル提出者本人宛に送信するメールに直接的に記載されません。";
?></font>
</div>
<br>
<button type="submit" class="btn btn-warning" id="submitbtn_decide">回答を送信し、議論を終了する</button>
</form>
</div>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check_decide(){

  problem = 0;

  probans = 0;
  probrea = 0;


//必須の場合
  if(document.form_decide.ans.value === ""){
    problem = 1;
    probans = 1;
  }

//文字数 条件必須
  if(document.form_decide.reason.value === ""){
    if(document.form_decide.ans.value === "2"){
      problem = 1;
      probrea = 1;
    }
  } else if(document.form_decide.reason.value.length > 500){
    problem = 1;
    probrea = 2;
  }


//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probans == 1) {
    alert( "【回答内容】\nいずれかを選択して下さい。" );
  }
  if ( probrea == 1) {
    alert( "【理由】\n「軽微な修正を求める」もしくは「拒否する」と答えた場合は、入力が必要です。" );
  }
  if ( probrea == 2) {
    alert( "【理由】\n文字数が多すぎます（現在" + document.form_decide.reason.value.length + "文字）。500文字以内に抑えて下さい。" );
  }

  return false;
}

  if(window.confirm('入力内容に問題は見つかりませんでした。\n現在の回答内容を登録し、議論を終了します。よろしいですか？')){
    submitbtn = document.getElementById("submitbtn_decide");
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
