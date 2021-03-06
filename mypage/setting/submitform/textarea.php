<?php
require_once('../../../set.php');
setup_session();
$titlepart = 'ファイル提出画面 項目設定';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

$number = basename($_GET['number']);

if (!isset($_GET['number']) or !isset($_SESSION["submitformdata"][$number]["id"]) or ('textarea' != $_SESSION["submitformdata"][$number]["type"]))
    die_mypage('<h1>エラーが発生しました</h1>
<p><a href="index.php">こちらをクリックして編集画面トップに戻って下さい。</a></p>');

?>

<h1>項目設定 - テキストエリア</h1>

<div class="border border-primary system-border-spacer">
<form name="form" action="save.php" method="post" onSubmit="return check()">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="number" value="<?php echo $number; ?>">
<input type="hidden" name="id" value="<?php echo $_SESSION["submitformdata"][$number]["id"]; ?>">
<input type="hidden" name="type" value="textarea">
<div class="form-group">
<label for="title">項目名（50文字以内）【必須】</label>
<input type="text" name="title" class="form-control" id="title" value="<?php
if (isset($_SESSION["submitformdata"][$number]["title"])) echo hsc($_SESSION["submitformdata"][$number]["title"]);
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
<small class="form-text">※必須項目には「必須」と付記されます。</small>
</div>
<div class="form-group">
<label for="detail">項目詳細（500文字以内）</label>
<textarea id="detail" name="detail" rows="4" class="form-control"><?php
if (isset($_SESSION["submitformdata"][$number]["detail"])) echo hsc($_SESSION["submitformdata"][$number]["detail"]);
?></textarea>
<small class="form-text">※入力欄の下に、このようにして小さく表示される文字です。<br>
　改行は反映されます（この入力欄で改行すると実際の登録画面でも改行されます）が、HTMLタグはお使いになれません。<br>
　ただし、URLを記載すると、自動的にリンクが張られます。<br>
※入力が無い場合は、入力欄の下に何も表示されません。</small>
</div>
<div class="form-group">
<label for="max">最大文字数（1～9999の間の半角数字）</label>
<input type="text" name="max" class="form-control" id="max" style="width:5em;" value="<?php
if (isset($_SESSION["submitformdata"][$number]["max"])) echo hsc($_SESSION["submitformdata"][$number]["max"]);
?>">
<small class="form-text">※入力内容が、ここで指定する文字数を超えている場合に、警告を発して再入力を促します。<br>
　入力が無い場合は、9999文字が最大となります。</small>
</div>
<div class="form-group">
<label for="min">最小文字数（1～9999の間の半角数字）</label>
<input type="text" name="min" class="form-control" id="min" style="width:5em;" value="<?php
if (isset($_SESSION["submitformdata"][$number]["min"])) echo hsc($_SESSION["submitformdata"][$number]["min"]);
?>">
<small class="form-text">※入力内容が、ここで指定する文字数を下回っている場合に、警告を発して再入力を促します。<br>
　入力が無い場合は、最小文字数を設けません。</small>
</div>
<div class="form-group">
<label for="width">入力欄の幅（半角数字）</label>
<div class="input-group" style="width:8em;">
<input type="text" name="width" class="form-control" id="width" value="<?php
if (isset($_SESSION["submitformdata"][$number]["width"])) echo hsc($_SESSION["submitformdata"][$number]["width"]);
?>">
<div class="input-group-append">
<span class="input-group-text">em</span>
</div>
</div>
<small class="form-text">※指定が無い場合は、入力欄は画面の端から端まで表示されます。<br>
　数文字を入力するだけの欄など、入力欄が短くてもよい場合は、ここで調節して下さい。<br>
※「em」はフォントサイズを基準とした単位です（1em＝大体1文字分　と認識してよいと思います）。
</small>
</div>
<div class="form-group">
<label for="height">入力欄の高さ（半角数字）</label>
<div class="input-group" style="width:8em;">
<input type="text" name="height" class="form-control" id="height" value="<?php
if (isset($_SESSION["submitformdata"][$number]["height"])) echo hsc($_SESSION["submitformdata"][$number]["height"]);
?>">
<div class="input-group-append">
<span class="input-group-text">行</span>
</div>
</div>
<small class="form-text">※指定が無い場合は、入力欄の高さは4行となります。
</small>
</div>
<div class="form-group">
入力内容の変更の自動承認について
<div class="form-check">
<input id="recheck" class="form-check-input" type="checkbox" name="recheck" value="auto" <?php
if (isset($_SESSION["submitformdata"][$number]["recheck"]) and $_SESSION["submitformdata"][$number]["recheck"] == "auto") echo 'checked="checked"';
?>>
<label class="form-check-label" for="recheck">この項目の入力内容の変更を自動承認する場合は、左のチェックボックスにチェックして下さい。</label>
</div>
<small class="form-text">※自動承認する項目のみ変更する場合は、運営メンバーによる確認を経ずに入力内容を変更します。自動承認しない項目も併せて変更する場合は、運営メンバーによる確認が必要となります。</small>
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
  probmax = 0;
  probmin = 0;
  probwid = 0;
  probhei = 0;


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


//文字種・数字の大きさ　必須でない
  if(document.form.max.value === ""){
  } else if(!document.form.max.value.match(/^[0-9]*$/)){
    problem = 1;
    probmax = 1;
  } else if(parseInt(document.form.max.value) < 1 | parseInt(document.form.max.value) > 9999){
    problem = 1;
    probmax = 2;
  }
  if(document.form.min.value === ""){
  } else if(!document.form.min.value.match(/^[0-9]*$/)){
    problem = 1;
    probmin = 1;
  } else if(parseInt(document.form.min.value) < 1 | parseInt(document.form.min.value) > 9999){
    problem = 1;
    probmin = 2;
  }

//文字種　必須でない
  if(document.form.width.value === ""){
  } else if(!document.form.width.value.match(/^[0-9]*$/)){
    problem = 1;
    probwid = 1;
  }
//文字種　必須でない
  if(document.form.height.value === ""){
  } else if(!document.form.height.value.match(/^[0-9]*$/)){
    problem = 1;
    probhei = 1;
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
  if ( probmax == 1) {
    alert( "【最大文字数】\n半角数字以外の文字が含まれています。" );
  }
  if ( probmax == 2) {
    alert( "【最大文字数】\n数字が小さすぎるか、大きすぎます。1～9999の間で指定して下さい。" );
  }
  if ( probmin == 1) {
    alert( "【最小文字数】\n半角数字以外の文字が含まれています。" );
  }
  if ( probmin == 2) {
    alert( "【最小文字数】\n数字が小さすぎるか、大きすぎます。1～9999の間で指定して下さい。" );
  }
  if ( probwid == 1) {
    alert( "【入力欄の幅】\n半角数字以外の文字が含まれています。" );
  }
  if ( probhei == 1) {
    alert( "【入力欄の高さ】\n半角数字以外の文字が含まれています。" );
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
