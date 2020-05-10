<?php
require_once('../../../set.php');
setup_session();
$titlepart = '共通情報入力画面 項目設定';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

$number = basename($_GET['number']);

if (!isset($_GET['number']) or !isset($_SESSION["userformdata"][$number]["id"]) or ('textbox2' != $_SESSION["userformdata"][$number]["type"]))
    die_mypage('<h1>エラーが発生しました</h1>
<p><a href="index.php">こちらをクリックして編集画面トップに戻って下さい。</a></p>');

?>

<h1>項目設定 - テキストボックス×2</h1>

<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="save.php" method="post" onSubmit="return check()">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="number" value="<?php echo $number; ?>">
<input type="hidden" name="id" value="<?php echo $_SESSION["userformdata"][$number]["id"]; ?>">
<input type="hidden" name="type" value="textbox2">
<div class="form-group">
<label for="title">項目名（50文字以内）【必須】</label>
<input type="text" name="title" class="form-control" id="title" value="<?php
if (isset($_SESSION["userformdata"][$number]["title"])) echo hsc($_SESSION["userformdata"][$number]["title"]);
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
<input id="required-2" class="form-check-input" type="radio" name="required" value="2" <?php
if (isset($_SESSION["userformdata"][$number]["required"]) and $_SESSION["userformdata"][$number]["required"] == "2") echo 'checked="checked"';
?>>
<label class="form-check-label" for="required-2">いずれか必須</label>
</div>
<div class="form-check">
<input id="required-1" class="form-check-input" type="radio" name="required" value="1" <?php
if (isset($_SESSION["userformdata"][$number]["required"]) and $_SESSION["userformdata"][$number]["required"] == "1") echo 'checked="checked"';
?>>
<label class="form-check-label" for="required-1">どちらも必須</label>
</div>
<font size="2">※必須項目には「いずれか必須」もしくは「どちらも必須」と付記されます。</font>
</div>
<div class="form-group">
<label for="detail">項目詳細（500文字以内）</label>
<textarea id="detail" name="detail" rows="4" cols="80" class="form-control"><?php
if (isset($_SESSION["userformdata"][$number]["detail"])) echo hsc($_SESSION["userformdata"][$number]["detail"]);
?></textarea>
<font size="2">※入力欄の下に、このようにして小さく表示される文字です。<br>
　改行は反映されます（この入力欄で改行すると実際の登録画面でも改行されます）が、HTMLタグはお使いになれません。<br>
　ただし、URLを記載すると、自動的にリンクが張られます。<br>
※入力が無い場合は、入力欄の下に何も表示されません。</font>
</div>
<div class="form-group">
最大文字数（1～9999の間の半角数字）
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">1つ目のテキストボックス：</span>
</div>
<input type="text" name="max" class="form-control" id="max" style="width:5em;" value="<?php
if (isset($_SESSION["userformdata"][$number]["max"])) echo hsc($_SESSION["userformdata"][$number]["max"]);
?>">
</div>
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">2つ目のテキストボックス：</span>
</div>
<input type="text" name="max2" class="form-control" id="max2" style="width:5em;" value="<?php
if (isset($_SESSION["userformdata"][$number]["max2"])) echo hsc($_SESSION["userformdata"][$number]["max2"]);
?>">
</div>
<font size="2">※入力内容が、ここで指定する文字数を超えている場合に、警告を発して再入力を促します。<br>
　入力が無い場合は、9999文字が最大となります。</font>
</div>
<div class="form-group">
最小文字数（1～9999の間の半角数字）
<div class="input-group" style="width:19em;">
<div class="input-group-prepend">
<span class="input-group-text">1つ目のテキストボックス：</span>
</div>
<input type="text" name="min" class="form-control" id="min" value="<?php
if (isset($_SESSION["userformdata"][$number]["min"])) echo hsc($_SESSION["userformdata"][$number]["min"]);
?>">
</div>
<div class="input-group" style="width:19em;">
<div class="input-group-prepend">
<span class="input-group-text">2つ目のテキストボックス：</span>
</div>
<input type="text" name="min2" class="form-control" id="min2" value="<?php
if (isset($_SESSION["userformdata"][$number]["min2"])) echo hsc($_SESSION["userformdata"][$number]["min2"]);
?>">
</div>
<font size="2">※入力内容が、ここで指定する文字数を下回っている場合に、警告を発して再入力を促します。<br>
　入力が無い場合は、最小文字数を設けません。</font>
</div>
<div class="form-group">
入力欄の幅（半角数字）
<div class="input-group" style="width:24em;">
<div class="input-group-prepend">
<span class="input-group-text">1つ目のテキストボックス：</span>
</div>
<input type="text" name="width" class="form-control" id="width" style="width:5em;" value="<?php
if (isset($_SESSION["userformdata"][$number]["width"])) echo hsc($_SESSION["userformdata"][$number]["width"]);
?>">
<div class="input-group-append">
<span class="input-group-text">em</span>
</div>
</div>
<div class="input-group" style="width:24em;">
<div class="input-group-prepend">
<span class="input-group-text">2つ目のテキストボックス：</span>
</div>
<input type="text" name="width2" class="form-control" id="width2" style="width:5em;" value="<?php
if (isset($_SESSION["userformdata"][$number]["width2"])) echo hsc($_SESSION["userformdata"][$number]["width2"]);
?>">
<div class="input-group-append">
<span class="input-group-text">em</span>
</div>
</div>
<font size="2">※指定が無い場合は、入力欄は画面の端から端まで表示されます（テキストボックスを縦に並べる場合）。<br>
　数文字を入力するだけの欄など、入力欄が短くてもよい場合は、ここで調節して下さい。<br>
※「em」はフォントサイズを基準とした単位です（1em＝大体1文字分　と認識してよいと思います）。
</font>
</div>
<div class="form-group">
入力欄の前に表示する文字（接頭辞）（50文字以内）
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">1つ目のテキストボックス：</span>
</div>
<input type="text" name="prefix_a" class="form-control" id="prefix_a" value="<?php
if (isset($_SESSION["userformdata"][$number]["prefix_a"])) echo hsc($_SESSION["userformdata"][$number]["prefix_a"]);
?>">
</div>
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">2つ目のテキストボックス：</span>
</div>
<input type="text" name="prefix_b" class="form-control" id="prefix_b" value="<?php
if (isset($_SESSION["userformdata"][$number]["prefix_b"])) echo hsc($_SESSION["userformdata"][$number]["prefix_b"]);
?>">
</div>
<font size="2">※例えば、「https://www.nicovideo.jp/watch/sm」と指定すると、入力欄は、<br>
　　https://www.nicovideo.jp/watch/sm[　　　　]<br>
　のような見た目となります。</font>
</div>
<div class="form-group">
入力欄の後に表示する文字（接尾辞）（50文字以内）
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">1つ目のテキストボックス：</span>
</div>
<input type="text" name="suffix_a" class="form-control" id="suffix_a" value="<?php
if (isset($_SESSION["userformdata"][$number]["suffix_a"])) echo hsc($_SESSION["userformdata"][$number]["suffix_a"]);
?>">
</div>
<div class="input-group">
<div class="input-group-prepend">
<span class="input-group-text">2つ目のテキストボックス：</span>
</div>
<input type="text" name="suffix_b" class="form-control" id="suffix_b" value="<?php
if (isset($_SESSION["userformdata"][$number]["suffix_b"])) echo hsc($_SESSION["userformdata"][$number]["suffix_b"]);
?>">
</div>
<font size="2">※例えば、「年」と指定すると、入力欄は、<br>
　　[　　　　]年<br>
　のような見た目となります。<br>
※接頭辞と接尾辞を組み合わせる事も出来ます。例えば、接頭辞に「令和」、接尾辞に「年」と指定すると、<br>
　　令和[　　　　]年<br>
　のような見た目となります。</font>
</div>
<div class="form-group">
テキストボックスの並べ方
<div class="form-check">
<input id="arrangement" class="form-check-input" type="checkbox" name="arrangement" value="h" <?php
if (isset($_SESSION["userformdata"][$number]["arrangement"]) and $_SESSION["userformdata"][$number]["arrangement"] == "h") echo 'checked="checked"';
?>>
<label class="form-check-label" for="arrangement">テキストボックスを横に並べる場合は、左のチェックボックスにチェックして下さい。</label>
</div>
<font size="2">※チェックが無い場合は、テキストボックスを縦に並べます。</font>
</div>
<div class="form-group">
入力内容の変更の自動承認について
<div class="form-check">
<input id="recheck" class="form-check-input" type="checkbox" name="recheck" value="auto" <?php
if (isset($_SESSION["userformdata"][$number]["recheck"]) and $_SESSION["userformdata"][$number]["recheck"] == "auto") echo 'checked="checked"';
?>>
<label class="form-check-label" for="recheck">この項目の入力内容の変更を自動承認する場合は、左のチェックボックスにチェックして下さい。</label>
</div>
<font size="2">※自動承認する項目のみ変更する場合は、運営メンバーによる確認を経ずに入力内容を変更します。自動承認しない項目も併せて変更する場合は、運営メンバーによる確認が必要となります。</font>
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
  probpre = 0;
  probsuf = 0;
  probmax2 = 0;
  probmin2 = 0;
  probwid2 = 0;
  probpre2 = 0;
  probsuf2 = 0;


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
  if(document.form.max2.value === ""){
  } else if(!document.form.max2.value.match(/^[0-9]*$/)){
    problem = 1;
    probmax2 = 1;
  } else if(parseInt(document.form.max2.value) < 1 | parseInt(document.form.max2.value) > 9999){
    problem = 1;
    probmax2 = 2;
  }
  if(document.form.min2.value === ""){
  } else if(!document.form.min2.value.match(/^[0-9]*$/)){
    problem = 1;
    probmin2 = 1;
  } else if(parseInt(document.form.min2.value) < 1 | parseInt(document.form.min2.value) > 9999){
    problem = 1;
    probmin2 = 2;
  }

//文字種　必須でない
  if(document.form.width.value === ""){
  } else if(!document.form.width.value.match(/^[0-9]*$/)){
    problem = 1;
    probwid = 1;
  }
  if(document.form.width2.value === ""){
  } else if(!document.form.width2.value.match(/^[0-9]*$/)){
    problem = 1;
    probwid2 = 1;
  }

//文字数 必須でない
  if(document.form.prefix_a.value === ""){
  } else if(document.form.prefix_a.value.length > 50){
    problem = 1;
    probpre = 1;
  }
  if(document.form.suffix_a.value === ""){
  } else if(document.form.suffix_a.value.length > 50){
    problem = 1;
    probsuf = 1;
  }
  if(document.form.prefix_b.value === ""){
  } else if(document.form.prefix_b.value.length > 50){
    problem = 1;
    probpre2 = 1;
  }
  if(document.form.suffix_b.value === ""){
  } else if(document.form.suffix_b.value.length > 50){
    problem = 1;
    probsuf2 = 1;
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
    alert( "【最大文字数（1つ目のテキストボックス）】\n半角数字以外の文字が含まれています。" );
  }
  if ( probmax == 2) {
    alert( "【最大文字数（1つ目のテキストボックス）】\n数字が小さすぎるか、大きすぎます。1～9999の間で指定して下さい。" );
  }
  if ( probmin == 1) {
    alert( "【最小文字数（1つ目のテキストボックス）】\n半角数字以外の文字が含まれています。" );
  }
  if ( probmin == 2) {
    alert( "【最小文字数（1つ目のテキストボックス）】\n数字が小さすぎるか、大きすぎます。1～9999の間で指定して下さい。" );
  }
  if ( probwid == 1) {
    alert( "【入力欄の幅（1つ目のテキストボックス）】\n半角数字以外の文字が含まれています。" );
  }
  if ( probpre == 1) {
    alert( "【入力欄の前に表示する文字（接頭辞）（1つ目のテキストボックス）】\n文字数が多すぎます（現在" + document.form.prefix_a.value.length + "文字）。50文字以内に抑えて下さい。" );
  }
  if ( probsuf == 1) {
    alert( "【入力欄の後に表示する文字（接尾辞）（1つ目のテキストボックス）】\n文字数が多すぎます（現在" + document.form.suffix_a.value.length + "文字）。50文字以内に抑えて下さい。" );
  }
  if ( probmax2 == 1) {
    alert( "【最大文字数（2つ目のテキストボックス）】\n半角数字以外の文字が含まれています。" );
  }
  if ( probmax2 == 2) {
    alert( "【最大文字数（2つ目のテキストボックス）】\n数字が小さすぎるか、大きすぎます。1～9999の間で指定して下さい。" );
  }
  if ( probmin2 == 1) {
    alert( "【最小文字数（2つ目のテキストボックス）】\n半角数字以外の文字が含まれています。" );
  }
  if ( probmin2 == 2) {
    alert( "【最小文字数（2つ目のテキストボックス）】\n数字が小さすぎるか、大きすぎます。1～9999の間で指定して下さい。" );
  }
  if ( probwid2 == 1) {
    alert( "【入力欄の幅（2つ目のテキストボックス）】\n半角数字以外の文字が含まれています。" );
  }
  if ( probpre2 == 1) {
    alert( "【入力欄の前に表示する文字（接頭辞）（2つ目のテキストボックス）】\n文字数が多すぎます（現在" + document.form.prefix_b.value.length + "文字）。50文字以内に抑えて下さい。" );
  }
  if ( probsuf2 == 1) {
    alert( "【入力欄の後に表示する文字（接尾辞）（2つ目のテキストボックス）】\n文字数が多すぎます（現在" + document.form.suffix_b.value.length + "文字）。50文字以内に抑えて下さい。" );
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
