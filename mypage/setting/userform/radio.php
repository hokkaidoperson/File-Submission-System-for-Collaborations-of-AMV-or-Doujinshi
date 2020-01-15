<?php
require_once('../../../set.php');
session_start();
$titlepart = 'ユーザー登録画面 項目設定';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>のみです。</p>
<p><a href="../../index.php">マイページトップに戻る</a></p>');

$number = $_GET['number'];

if (!isset($_GET['number']) or !isset($_SESSION["userformdata"][$number]["id"]) or ('radio' != $_SESSION["userformdata"][$number]["type"]))
    die_mypage('<h1>エラーが発生しました</h1>
<p><a href="index.php">こちらをクリックして編集画面トップに戻って下さい。</a></p>');

?>

<h1>項目設定 - ラジオボタン</h1>

<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="save.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="number" value="<?php echo $number; ?>">
<input type="hidden" name="id" value="<?php echo $_SESSION["userformdata"][$number]["id"]; ?>">
<input type="hidden" name="type" value="radio">
<div class="form-group">
<label for="title">項目名（50文字以内）【必須】</label>
<input type="text" name="title" class="form-control" id="title" value="<?php
if (isset($_SESSION["userformdata"][$number]["title"])) echo htmlspecialchars($_SESSION["userformdata"][$number]["title"]);
?>">
</div>
<div class="form-group">
必須かどうか【必須】
<div class="form-check">
<input id="required-0" class="form-check-input" type="radio" name="required" value="0" <?php
if (isset($_SESSION["userformdata"][$number]["required"]) and $_SESSION["userformdata"][$number]["required"] == "0") echo 'checked="checked"';
?>>
<label class="form-check-label" for="required-0">任意</label>
</div>
<div class="form-check">
<input id="required-1" class="form-check-input" type="radio" name="required" value="1" <?php
if (isset($_SESSION["userformdata"][$number]["required"]) and $_SESSION["userformdata"][$number]["required"] == "1") echo 'checked="checked"';
?>>
<label class="form-check-label" for="required-1">必須</label>
</div>
<font size="2">※必須項目には「必須」と付記されます。</font>
</div>
<div class="form-group">
<label for="detail">項目詳細（500文字以内）</label>
<textarea id="detail" name="detail" rows="4" cols="80" class="form-control"><?php
if (isset($_SESSION["userformdata"][$number]["detail"])) echo htmlspecialchars($_SESSION["userformdata"][$number]["detail"]);
?></textarea>
<font size="2">※選択肢の下に、このようにして小さく表示される文字です。<br>
　改行は反映されます（この入力欄で改行すると実際の登録画面でも改行されます）が、HTMLタグはお使いになれません。<br>
　ただし、URLを記載すると、自動的にリンクが張られます。<br>
※入力が無い場合は、選択肢の下に何も表示されません。</font>
</div>
<div class="form-group">
<label for="list">選択肢のリスト【必須】</label>
<textarea id="list" name="list" rows="4" cols="80" class="form-control"><?php
if (isset($_SESSION["userformdata"][$number]["list"])) echo htmlspecialchars($_SESSION["userformdata"][$number]["list"]);
?></textarea>
<font size="2">※選択肢をこの入力欄に、1行につき1つ入力して下さい。選択肢は、ここで入力した順に並びます。<br>
　例えば、<br>
　　　りんご<br>
　　　みかん<br>
　　　ぶどう<br>
　と入力した場合、「りんご」「みかん」「ぶどう」の中から1つ選ぶ項目になります。<br>
※ラジオボタンは、「●●●●の場合左にチェックして下さい」のような使い方は出来ません（チェックボックスをご利用下さい）。</font>
</div>
<div class="form-group">
選択肢の並べ方
<div class="form-check">
<input id="arrangement" class="form-check-input" type="checkbox" name="arrangement" value="h" <?php
if (isset($_SESSION["userformdata"][$number]["arrangement"]) and $_SESSION["userformdata"][$number]["arrangement"] == "h") echo 'checked="checked"';
?>>
<label class="form-check-label" for="arrangement">選択肢を横に並べる場合は、左のチェックボックスにチェックして下さい。</label>
</div>
<font size="2">※チェックが無い場合は、選択肢を縦に並べます。</font>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">設定変更</button> 
<a href="reload.php" class="btn btn-secondary" role="button" onclick="return window.confirm('現在の設定内容を保存せず、メニューに戻ります。よろしいですか？')">変更内容を保存しないで戻る</a>
</form>
</div>

<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probtitle = 0;
  probreq = 0;
  probdtl = 0;
  problst = 0;


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

//必須の場合
  if(document.form.list.value === ""){
    problem = 1;
    problst = 1;
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
  if ( problst == 1) {
    alert( "【選択肢のリスト】\n入力されていません。" );
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
?>
