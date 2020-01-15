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

?>

<h1>項目設定 - 全体</h1>

<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="save.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="type" value="general">
<div class="form-group">
ファイル提出期間（開始）【必須】
<div>
<label for="from_date">
日付：
</label>
<input type="date" cmanCLDat="USE:ON" name="from_date" class="form-control" id="from_date" value="<?php
if (isset($_SESSION["submitformdata"]["general"]["from"])) echo date('Y-m-d', $_SESSION["submitformdata"]["general"]["from"]);
?>">
</div>
<div>
<label for="from_time">
時刻（24時間制）：
</label>
<input type="time" name="from_time" id="from_time" value="<?php
if (isset($_SESSION["submitformdata"]["general"]["from"])) echo date('H:i', $_SESSION["submitformdata"]["general"]["from"]);
?>">
</div>
<font size="2">※日付の欄をクリックするとカレンダーから日付を選べます。<br>
※時刻の欄についてはブラウザにより表示が異なります（ポップアップ画面が表示される、入力欄の横に上下のボタンが出る　など）。<br>
　時刻の欄をクリックしても何も出ない（通常のテキストボックスのようになっている）場合は、「時:分」の形で、半角で入力して下さい。<br>
※指定時間になった瞬間に、ファイルの提出受付を開始します。</font>
</div>
<div class="form-group">
ファイル提出期間（締切）【必須】
<div>
<label for="until_date">
日付：
</label>
<input type="date" cmanCLDat="USE:ON" name="until_date" class="form-control" id="until_date" value="<?php
if (isset($_SESSION["submitformdata"]["general"]["until"])) echo date('Y-m-d', $_SESSION["submitformdata"]["general"]["until"]);
?>">
</div>
<div>
<label for="until_time">
時刻（24時間制）：
</label>
<input type="time" name="until_time" id="until_time" value="<?php
if (isset($_SESSION["submitformdata"]["general"]["until"])) echo date('H:i', $_SESSION["submitformdata"]["general"]["until"]);
?>">
</div>
<font size="2">※日付・時刻の入力方法については「ファイル提出期間（開始）」をご参照願います。<br>
※指定時間になった瞬間に、ファイルの提出受付を終了します（例えば、10月31日の、日付が変わるギリギリまで提出を受け付ける場合は、締切日時を「11月01日00時00分」に設定して下さい）。</font>
</div>
<div class="form-group">
<label for="detail">項目詳細（500文字以内）</label>
<textarea id="detail" name="detail" rows="4" cols="80" class="form-control"><?php
if (isset($_SESSION["submitformdata"]["general"]["detail"])) echo htmlspecialchars($_SESSION["submitformdata"]["general"]["detail"]);
?></textarea>
<font size="2">※ファイル提出欄の下に、このようにして小さく表示される文字です。<br>
　改行は反映されます（この入力欄で改行すると実際の提出画面でも改行されます）が、HTMLタグはお使いになれません。<br>
　ただし、URLを記載すると、自動的にリンクが張られます。<br>
※入力が無い場合は、ファイル提出欄の下に何も表示されません。</font>
</div>
<div class="form-group">
<label for="ext">ファイルの拡張子指定（半角英数字（小文字）とコロン「,」）【必須】</label>
<input type="text" name="ext" class="form-control" id="ext" value="<?php
if (isset($_SESSION["submitformdata"]["general"]["ext"])) echo htmlspecialchars($_SESSION["submitformdata"]["general"]["ext"]);
?>">
<font size="2">※ <code>jpg,png,gif</code> のように、拡張子をコロン <code>,</code> で区切って指定して下さい（ドット <code>.</code> は付けないで下さい）。<br>
※無差別に全ての種類のファイルを受け入れられるようにすると、セキュリティ的に脆弱になる恐れがあります。<br>
　イベントに応じて、アップロード出来るファイルの種類をある程度制限して下さい。<br>
※アップロード出来るファイルの種類を制限しても、悪意あるファイルの全てを防げる訳ではありません。<br>
　参加者から送られたファイルをダウンロードする際は、ウイルス対策ソフトなど、セキュリティ面の準備を万全にしておく事をお勧め致します。
</font>
</div>
<div class="form-group">
<label for="size">サーバーに直接アップロード可能な最大サイズ（1～<?php echo FILE_MAX_SIZE; ?>の間の半角数字）</label>
<div class="input-group" style="width:8em;">
<input type="text" name="size" class="form-control" id="size" value="<?php
if (isset($_SESSION["submitformdata"]["general"]["size"])) echo htmlspecialchars($_SESSION["submitformdata"]["general"]["size"]);
?>">
<div class="input-group-append">
<span class="input-group-text">MB</span>
</div>
</div>
<font size="2">※システム管理者によって、ファイルのサイズは<?php echo FILE_MAX_SIZE; ?>MBまでに制限されています。<br>
※入力が無い場合は、<?php echo FILE_MAX_SIZE; ?>MBとして設定します。
</font>
</div>
<br>
<button type="submit" class="btn btn-primary" id="submitbtn">設定変更</button> 
<a href="reload.php" class="btn btn-secondary" role="button" onclick="return window.confirm('現在の設定内容を保存せず、メニューに戻ります。よろしいですか？')">変更内容を保存しないで戻る</a>
</form>
</div>

<script src="../../../js/calendar_script.js" charset="utf-8"></script>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probfrom = 0;
  probuntil = 0;
  probdtl = 0;
  probext = 0;
  probsiz = 0;

//日付と時刻
  if(document.form.from_date.value === "" || document.form.from_time.value === ""){
    problem = 1;
    probfrom = 1;
  } else if (date_check(document.form.from_date) === false || time_check(document.form.from_time) === false){
    problem = 1;
    probfrom = 2;
  }
  if(document.form.until_date.value === "" || document.form.until_time.value === ""){
    problem = 1;
    probuntil = 1;
  } else if (date_check(document.form.until_date) === false || time_check(document.form.until_time) === false){
    problem = 1;
    probuntil = 2;
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
  if ( probfrom == 1) {
    alert( "【ファイル提出期間（開始）】\n日付・時刻の両方を入力して下さい。" );
  }
  if ( probfrom == 2) {
    alert( "【ファイル提出期間（開始）】\n日付もしくは時刻が正しく入力されていません。入力内容をご確認願います。" );
  }
  if ( probuntil == 1) {
    alert( "【ファイル提出期間（締切）】\n日付・時刻の両方を入力して下さい。" );
  }
  if ( probuntil == 2) {
    alert( "【ファイル提出期間（締切）】\n日付もしくは時刻が正しく入力されていません。入力内容をご確認願います。" );
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
    alert( "【サーバーに直接アップロード可能な最大サイズ】\n半角数字以外の文字が含まれています。" );
  }
  if ( probsiz == 2) {
    alert( "【サーバーに直接アップロード可能な最大サイズ】\n数字が小さすぎるか、大きすぎます。1～<?php echo FILE_MAX_SIZE; ?>の間で指定して下さい。" );
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

//日付チェック（参考：https://web-designer.cman.jp/html_ref/abc_list/input_sample2/）
function date_check(str){
  var ok = true;
  var wdate = str.value;
  var wresult = "";
  var wlength = "";
  var wyear = "";
  var wmonth = "";
  var wday = "";

// 数字,-以外の入力チェック
  wresult = /[^\d-]/.test(wdate);
  if (wresult){
    ok=false;
    return(ok);
  }

// 入力文字数チェック
  wlength = wdate.length;
  if (wlength!=10){
    ok=false;
    return(ok);
  }

// 年月日に分割　＆　フォーマットチェック
// yyyy-mm-dd形式の場合
  wresult = wdate.split("-");
  if (wresult.length!=1 & wresult.length!=3){
    ok=false;
    return(ok);
  }

// フォーマットチェック
  if ((wresult[0].length!=4) | (wresult[1].length!=2) | (wresult[2].length!=2)){
    ok=false;
    return(ok);
  }
  wyear=Number(wresult[0]);
  wmonth=Number(wresult[1]);
  wday=Number(wresult[2]);

// 月日範囲チェック
  if (wmonth<1 | wmonth>12){
    ok=false;
    alert("月は01～12の範囲で入力して下さい。");
    return(ok);
  }
  if (wday<1 | wday>31){
    ok=false;
    alert("日は01～31の範囲で入力して下さい。");
    return(ok);
  }
  return(ok);
}

//時刻チェック（参考：https://web-designer.cman.jp/html_ref/abc_list/input_sample2/）
function time_check(time){
  var ok = true;
  var wtime = time.value;
  var wresult = "";
  var wlength = "";
  var wyear = "";
  var wmonth = "";
  var wday = "";

// 数字,:以外の入力チェック
  wresult = /[^\d\:]/.test(wtime);
  if (wresult){
    ok=false;
    return(ok);
  }

// 入力文字数チェック
  wlength = wtime.length;
  if (wlength!=5){
    ok=false;
    return(ok);
  }

// 時分秒に分割　＆　フォーマットチェック
  wresult = wtime.split(":");
  if (wresult.length!=2){
    ok=false;
    return(ok);
  }

// 時分の桁数チェック（秒のチェックは実施しない（時分のチェック結果と同一のため))
  if (wresult[0].length!=2 | wresult[1].length!=2){
    ok=false;
    return(ok);
  }

  whour=Number(wresult[0]);
  wminute=Number(wresult[1]);

// 時分秒範囲チェック
  if (whour<0 | whour>23){
    ok=false;
    return(ok);
  }
  if (wminute<0 | wminute>59){
    ok=false;
    return(ok);
  }

  return(ok);
}
// -->
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
