<?php
require_once('../../../set.php');
session_start();
$titlepart = 'ファイル提出画面 項目設定';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>のみです。</p>
<p><a href="../../index.php">マイページトップに戻る</a></p>');

$number = $_GET['number'];

if (!isset($_GET['number']) or !isset($_SESSION["submitformdata"][$number]["id"]) or ('attach' != $_SESSION["submitformdata"][$number]["type"]))
    die_mypage('<h1>エラーが発生しました</h1>
<p><a href="index.php">こちらをクリックして編集画面トップに戻って下さい。</a></p>');

?>

<h1>項目設定 - 添付ファイル</h1>

<div class="border border-primary" style="padding:10px;">
<form name="form" action="save.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="number" value="<?php echo $number; ?>">
<input type="hidden" name="id" value="<?php echo $_SESSION["submitformdata"][$number]["id"]; ?>">
<input type="hidden" name="type" value="attach">
<div class="form-group">
<label for="title">項目名（50文字以内）【必須】</label>
<input type="text" name="title" class="form-control" id="title" value="<?php
if (isset($_SESSION["submitformdata"][$number]["title"])) echo htmlspecialchars($_SESSION["submitformdata"][$number]["title"]);
?>">
</div>
<div class="form-group">
必須かどうか【必須】
<div class="form-check">
<input id="required-0" class="form-check-input" type="radio" name="required" value="0" <?php
if (isset($_SESSION["submitformdata"][$number]["required"]) and $_SESSION["submitformdata"][$number]["required"] == "0") echo 'checked="checked"';
?>>
<label class="form-check-label" for="required-0">任意</label>
</div>
<div class="form-check">
<input id="required-1" class="form-check-input" type="radio" name="required" value="1" <?php
if (isset($_SESSION["submitformdata"][$number]["required"]) and $_SESSION["submitformdata"][$number]["required"] == "1") echo 'checked="checked"';
?>>
<label class="form-check-label" for="required-1">必須</label>
</div>
<font size="2">※必須項目には「必須」と付記されます。</font>
</div>
<div class="form-group">
<label for="detail">項目詳細（500文字以内）</label>
<textarea id="detail" name="detail" rows="4" cols="80" class="form-control"><?php
if (isset($_SESSION["submitformdata"][$number]["detail"])) echo htmlspecialchars($_SESSION["submitformdata"][$number]["detail"]);
?></textarea>
<font size="2">※選択欄の下に、このようにして小さく表示される文字です。<br>
　改行は反映されます（この入力欄で改行すると実際の登録画面でも改行されます）が、HTMLタグはお使いになれません。<br>
　ただし、URLを記載すると、自動的にリンクが張られます。<br>
※入力が無い場合は、選択欄の下に何も表示されません。</font>
</div>
<div class="form-group">
<label for="ext">ファイルの拡張子指定（半角英数字（小文字）とコロン「,」）【必須】</label>
<input type="text" name="ext" class="form-control" id="ext" value="<?php
if (isset($_SESSION["submitformdata"][$number]["ext"])) echo htmlspecialchars($_SESSION["submitformdata"][$number]["ext"]);
?>">
<font size="2">※ <code>jpg,png,gif</code> のように、拡張子をコロン <code>,</code> で区切って指定して下さい（ドット <code>.</code> は付けないで下さい）。<br>
※無差別に全ての種類のファイルを受け入れられるようにすると、セキュリティ的に脆弱になる恐れがあります。<br>
　項目の用途に応じて、アップロード出来るファイルの種類をある程度制限して下さい。<br>
※アップロード出来るファイルの種類を制限しても、悪意あるファイルの全てを防げる訳ではありません。<br>
　参加者から送られたファイルをダウンロードする際は、ウイルス対策ソフトなど、セキュリティ面の準備を万全にしておく事をお勧め致します。
</font>
</div>
<div class="form-group">
<label for="size">アップロード可能な最大サイズ（1～<?php echo FILE_MAX_SIZE; ?>の間の半角数字）</label>
<div class="input-group" style="width:8em;">
<input type="text" name="size" class="form-control" id="size" value="<?php
if (isset($_SESSION["submitformdata"][$number]["size"])) echo htmlspecialchars($_SESSION["submitformdata"][$number]["size"]);
?>">
<div class="input-group-append">
<span class="input-group-text">MB</span>
</div>
</div>
<font size="2">※システム管理者によって、ファイルのサイズは<?php echo FILE_MAX_SIZE; ?>MBまでに制限されています。<br>
※入力が無い場合は、<?php echo FILE_MAX_SIZE; ?>MBとして設定します。
</font>
</div>
<div class="form-group">
入力内容の変更の自動承認について
<div class="form-check">
<input id="recheck" class="form-check-input" type="checkbox" name="recheck" value="auto" <?php
if (isset($_SESSION["submitformdata"][$number]["recheck"]) and $_SESSION["submitformdata"][$number]["recheck"] == "auto") echo 'checked="checked"';
?>>
<label class="form-check-label" for="recheck">この項目の入力内容の変更を自動承認する場合は、左のチェックボックスにチェックして下さい。</label>
</div>
<font size="2">※自動承認する項目のみ変更する場合は、主催者の確認を経ずに入力内容を変更します。自動承認しない項目も併せて変更する場合は、主催者の確認が必要となります。</font>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">設定変更</button>
</form>
<a href="reload.php" class="btn btn-secondary" role="button" onclick="return window.confirm('現在の設定内容を保存せず、メニューに戻ります。よろしいですか？')">変更内容を保存しないで戻る</a></p>
</div>

<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probtitle = 0;
  probreq = 0;
  probdtl = 0;
  probext = 0;
  probsiz = 0;


//必須の場合のパターン・文字数
  if(document.form.title.value === ""){
    problem = 1;
    probtitle = 1;
  } else if(document.form.title.value.length > 50){
    problem = 1;
    probtitle = 2;
  }

//必須の場合
  if(document.form.required.value === ""){
    problem = 1;
    probreq = 1;
  }

//文字数 必須でない
  if(document.form.detail.value === ""){
  } else if(document.form.detail.value.length > 500){
    problem = 1;
    probdtl = 1;
  }

//必須の場合のパターン・文字種・文字数
  if(document.form.ext.value === ""){
    problem = 1;
    probext = 1;
  } else if(!document.form.ext.value.match(/^[0-9a-z,]*$/)){
    problem = 1;
    probext = 2;
  }

//文字種・数字の大きさ　必須でない
  if(document.form.size.value === ""){
  } else if(!document.form.size.value.match(/^[0-9]*$/)){
    problem = 1;
    probsiz = 1;
  } else if(parseInt(document.form.size.value) < 1 | parseInt(document.form.size.value) > <?php echo FILE_MAX_SIZE; ?>){
    problem = 1;
    probsiz = 2;
  }


//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  alert( "入力内容に問題があります。\nエラー内容を順に表示しますので、お手数ですが入力内容の確認をお願いします。" );
  if ( probtitle == 1) {
    alert( "【項目名】\n入力されていません。" );
  }
  if ( probtitle == 2) {
    alert( "【項目名】\n文字数が多すぎます（現在" + document.form.title.value.length + "文字）。50文字以内に抑えて下さい。" );
  }
  if ( probreq == 1) {
    alert( "【必須かどうか】\nいずれかを選択して下さい。" );
  }
  if ( probdtl == 1) {
    alert( "【項目詳細】\n文字数が多すぎます（現在" + document.form.detail.value.length + "文字）。500文字以内に抑えて下さい。" );
  }
  if ( probext == 1) {
    alert( "【ファイルの拡張子指定】\n入力されていません。" );
  }
  if ( probext == 2) {
    alert( "【ファイルの拡張子指定】\n半角英数字（小文字）とコロン以外の文字が含まれています。" );
  }
  if ( probsiz == 1) {
    alert( "【アップロード可能な最大サイズ】\n半角数字以外の文字が含まれています。" );
  }
  if ( probsiz == 2) {
    alert( "【アップロード可能な最大サイズ】\n数字が小さすぎるか、大きすぎます。1～<?php echo FILE_MAX_SIZE; ?>の間で指定して下さい。" );
  }

  return false;
}

  if(window.confirm('入力内容に問題は見つかりませんでした。\n現在の入力内容の通りに設定を変更します。よろしいですか？')){
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