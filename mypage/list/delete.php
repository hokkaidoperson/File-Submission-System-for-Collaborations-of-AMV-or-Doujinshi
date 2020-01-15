<?php
require_once('../../set.php');
session_start();
$titlepart = 'ファイル削除';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//非参加者以外
if ($_SESSION["state"] != 'o') $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>非参加者以外のユーザー</b>です。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

//ファイル提出者のユーザーID
$author = $_GET["author"];

//提出ID
$id = $_GET["id"];

if ($author == "" or $id == "") die_mypage('パラメーターエラー');


//自分のファイルのみ編集可
if ($author != $_SESSION['userid']) die_mypage('ご自身のファイルのみ、編集が可能です。');

if (outofterm($id) != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;
if (!in_term() and !$outofterm) die_mypage('現在、ファイル提出期間外のため、ファイル操作は行えません。');


//入力済み情報を読み込む
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die_mypage('ファイルが存在しません。');
$formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);

//フォーム設定データ
$formsetting = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $formsetting[$i] = json_decode(file_get_contents(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}
$formsetting["general"] = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);

?>

<h1>ファイル削除</h1>
<p>作品 <b><?php echo htmlspecialchars($formdata["title"]); ?></b> を削除します。</p>
<p>入力情報を削除し、サーバーにアップロードしたファイルがあればそれも削除されます。<br>
<b>この操作を取り消す事は出来ませんのでご注意願います。</b></p>
<p>削除しようとしている作品を今一度ご確認願います。</p>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<?php
if (isset($formdata["submit"]) and $formdata["submit"] != "") echo '<tr>
<th>提出ファイル</th><td><a href="../fnc/filedld.php?author=' . $author . '&genre=submitmain&id=' . $id . '" target="_blank">' . $formdata["submit"] . 'ファイル（クリックでダウンロード）</a></td>
</tr>';
else {
    echo '<tr>
<th>提出ファイルダウンロード先</th><td><a href="' . htmlspecialchars($formdata["url"]) . '" target="_blank">クリックすると新しいウィンドウで開きます</a>';
    if (isset($formdata["dldpw"]) and $formdata["dldpw"] != "") echo '<br><font size="2">※パスワード等の入力を求められた場合は、次のパスワードを入力して下さい。<code>' . htmlspecialchars($formdata["dldpw"]) . '</code></font>';
    if (isset($formdata["due"]) and $formdata["due"] != "") echo '<br><font size="2">※ダウンロードURLの有効期限は <b>' . date('Y年n月j日G時i分', $formdata["due"]) . '</b> までです。お早めにダウンロード願います。</font>';
    echo '<br><font size="2">※<u>このファイルは、一括ダウンロード機能でダウンロードする事が出来ません</u>。ダウンロードが必要な場合は、必ずリンク先からダウンロードして下さい。</font>';
    echo '</td></tr>';
}
?>
<tr>
<th>タイトル</th><td><?php echo htmlspecialchars($formdata["title"]); ?></td>
</tr>
<?php
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
?>
<tr><th>承認の状態</th><?php
if (isset($formdata["editing"]) and $formdata["editing"] == 1) echo '<td>項目編集の承認待ち</td>';
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
?>
</table>
</div>
<p>削除してもよろしければ、現在のパスワードを入力して「削除する」ボタンを押して下さい。</p>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="delete_exec.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="author" value="<?php echo $author; ?>">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<div class="form-group">
<label for="password">現在のパスワード</label>
<input type="password" name="password" class="form-control" id="password">
</div>
<br>
<button type="submit" id="submitbtn" class="btn btn-danger">削除する</button>
</form>
</div>
<script type="text/javascript">
<!--
function check(){

  problem = 0;

  if(document.form.password.value === ""){
    problem = 1;
  }

//問題ありの場合はエラー表示　ない場合は移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  alert( "【現在のパスワード】\n入力されていません。" );
  return false;
}

if(window.confirm('作品の削除を行います。これが最後の確認です。\n本当によろしいですか？')){
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
