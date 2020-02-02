<?php
require_once('../../set.php');
session_start();
$titlepart = 'ファイル提出（外部アップローダーサービス経由）';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//非参加者以外
if ($_SESSION["state"] != 'o') $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>非参加者以外のユーザー</b>です。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、ファイル提出を受け付け出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

//フォーム設定ファイル読み込み
$submitformdata = array();

//添付ファイルを含むかどうかの変数（添付ファイルがある場合はenctypeの設定が必要なため）
$includeattach = FALSE;

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $submitformdata[$i] = json_decode(file_get_contents(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
    if ($submitformdata[$i]["type"] == "attach") $includeattach = TRUE;
}
$submitformdata["general"] = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);

if (outofterm('submit') != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;

if ($submitformdata["general"]["from"] > time() and !$outofterm) die_mypage('提出期間外です。');
else if ($submitformdata["general"]["until"] <= time() and !$outofterm) die_mypage('提出期間外です。');

//Javascriptに持って行く用　不要な要素をunset
$tojsp = $submitformdata;
for ($i = 0; $i <= 9; $i++) {
  unset($tojsp[$i]["detail"]);
  unset($tojsp[$i]["width"]);
  unset($tojsp[$i]["width2"]);
  unset($tojsp[$i]["height"]);
  unset($tojsp[$i]["prefix_a"]);
  unset($tojsp[$i]["suffix_a"]);
  unset($tojsp[$i]["prefix_b"]);
  unset($tojsp[$i]["suffix_b"]);
  unset($tojsp[$i]["arrangement"]);
  unset($tojsp[$i]["list"]);
  unset($tojsp[$i]["recheck"]);
}
unset($tojsp["general"]);

$userid = $_SESSION["userid"];

?>

<h1>ファイル提出（外部アップローダーサービス経由）</h1>
<p>新規提出するファイルの情報を入力して下さい。</p>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="handle.php" method="post" <?php
if ($includeattach) echo 'enctype="multipart/form-data" ';
?>onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="method" value="url">
<div class="form-group">
<label for="url">提出ファイルのダウンロードURL【必須】</label>
<input type="text" name="url" class="form-control" id="url">
<?php
$submitformdata["general"]["detail"] = str_replace('&amp;', '&', htmlspecialchars($submitformdata["general"]["detail"]));
$submitformdata["general"]["detail"] = preg_replace('{https?://[\w/:%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank">$0</a>', $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace("\n", "<br>", $submitformdata["general"]["detail"]);
if ($submitformdata["general"]["detail"] != "") echo '<font size="2">' . $submitformdata["general"]["detail"] . '</font>';
?>
</div>
<div class="form-group">
<label for="dldpw">ファイルのダウンロードに必要なパスワード（あれば）</label>
<input type="text" name="dldpw" class="form-control" id="dldpw" value="">
<font size="2">※サービスによってパスワードの名称が異なります（「復号キー」など）。</font>
</div>
<div class="form-group">
<label for="email">ファイルのダウンロード期限（あれば）</label>
<div>
<label for="from_date">
日付：
</label>
<input type="date" cmanCLDat="USE:ON" name="due_date" class="form-control" id="due_date" value="">
</div>
<div>
<label for="from_time">
時刻（24時間制）：
</label>
<input type="time" name="due_time" id="due_time" value="">
</div>
<font size="2">※ダウンロードURLに有効期限がある場合は必ず入力して下さい。入力が無い場合は、URLに有効期限が無いものとして扱います。<br>
※日付の欄をクリックするとカレンダーから日付を選べます。<br>
※時刻の欄についてはブラウザにより表示が異なります（ポップアップ画面が表示される、入力欄の横に上下のボタンが出る　など）。<br>
　時刻の欄をクリックしても何も出ない（通常のテキストボックスのようになっている）場合は、「時:分」の形で、半角で入力して下さい。</font>
</div>
<div class="form-group">
<label for="title">タイトル（50文字以内）【必須】</label>
<input type="text" name="title" class="form-control" id="title" value="">
</div>
<?php
foreach ($submitformdata as $data) {
    //detail中のURLにリンクを振る（正規表現参考　https://www.megasoft.co.jp/mifes/seiki/s310.html）　あとHTMLタグが無いようにする・改行反映
    $data["detail"] = str_replace('&amp;', '&', htmlspecialchars($data["detail"]));
    $data["detail"] = preg_replace('{https?://[\w/:%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank">$0</a>', $data["detail"]);
    $data["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $data["detail"]);
    $data["detail"] = str_replace("\n", "<br>", $data["detail"]);

    switch ($data["type"]) {
        case "textbox":
            echo '<div class="form-group">
<label for="custom-' . $data["id"] . '">' . htmlspecialchars($data["title"]);
            if ($data["max"] != "" and $data["min"] != "") echo '（' . $data["min"] . '文字以上' . $data["max"] . '文字以内）';
            else if ($data["max"] != "" and $data["min"] == "") echo '（' . $data["max"] . '文字以内）';
            else if ($data["max"] == "" and $data["min"] != "") echo '（' . $data["min"] . '文字以上）';
            if ($data["required"] == "1") echo '【必須】';
            echo '</label>';
            if ($data["width"] != "") echo '<div class="input-group" style="width:' . $data["width"] . 'em;">';
            else echo '<div class="input-group">';
            if ($data["prefix_a"] != "") echo '<div class="input-group-prepend">
<span class="input-group-text">' . htmlspecialchars($data["prefix_a"]) . '</span>
</div>';
            echo '<input type="text" name="custom-' . $data["id"] . '" class="form-control" id="custom-' . $data["id"] . '">';
            if ($data["suffix_a"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . htmlspecialchars($data["suffix_a"]) . '</span>
</div>';
            echo '</div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
            echo '</div>';
        break;
        case "textbox2":
            echo '<div class="form-group">' . htmlspecialchars($data["title"]);
            if ($data["max"] != "" and $data["min"] != "") echo '（1つ目の入力欄：' . $data["min"] . '文字以上' . $data["max"] . '文字以内）';
            else if ($data["max"] != "" and $data["min"] == "") echo '（1つ目の入力欄：' . $data["max"] . '文字以内）';
            else if ($data["max"] == "" and $data["min"] != "") echo '（1つ目の入力欄：' . $data["min"] . '文字以上）';
            if ($data["max2"] != "" and $data["min2"] != "") echo '（2つ目の入力欄：' . $data["min2"] . '文字以上' . $data["max2"] . '文字以内）';
            else if ($data["max2"] != "" and $data["min2"] == "") echo '（2つ目の入力欄：' . $data["max2"] . '文字以内）';
            else if ($data["max2"] == "" and $data["min2"] != "") echo '（2つ目の入力欄：' . $data["min2"] . '文字以上）';
            if ($data["required"] == "1") echo '【どちらも必須】';
            else if ($data["required"] == "2") echo '【いずれか必須】';
            if ($data["arrangement"] == "h") echo '<div class="form-row"><div class="col">';
            if ($data["width"] != "") echo '<div class="input-group" style="width:' . $data["width"] . 'em;">';
            else echo '<div class="input-group">';
            if ($data["prefix_a"] != "") echo '<div class="input-group-prepend">
<span class="input-group-text">' . htmlspecialchars($data["prefix_a"]) . '</span>
</div>';
            echo '<input type="text" name="custom-' . $data["id"] . '-1" class="form-control" id="custom-' . $data["id"] . '-1">';
            if ($data["suffix_a"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . htmlspecialchars($data["suffix_a"]) . '</span>
</div>';
            echo '</div>';
            if ($data["arrangement"] == "h") echo '</div><div class="col">';
            if ($data["width2"] != "") echo '<div class="input-group" style="width:' . $data["width2"] . 'em;">';
            else echo '<div class="input-group">';
            if ($data["prefix_b"] != "") echo '<div class="input-group-prepend">
<span class="input-group-text">' . htmlspecialchars($data["prefix_b"]) . '</span>
</div>';
            echo '<input type="text" name="custom-' . $data["id"] . '-2" class="form-control" id="custom-' . $data["id"] . '-2">';
            if ($data["suffix_b"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . htmlspecialchars($data["suffix_b"]) . '</span>
</div>';
            echo '</div>';
            if ($data["arrangement"] == "h") echo '</div></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
            echo '</div>';
        break;
        case "textarea":
            echo '<div class="form-group">
<label for="custom-' . $data["id"] . '">' . htmlspecialchars($data["title"]);
            if ($data["max"] != "" and $data["min"] != "") echo '（' . $data["min"] . '文字以上' . $data["max"] . '文字以内）';
            else if ($data["max"] != "" and $data["min"] == "") echo '（' . $data["max"] . '文字以内）';
            else if ($data["max"] == "" and $data["min"] != "") echo '（' . $data["min"] . '文字以上）';
            if ($data["required"] == "1") echo '【必須】';
            echo '</label>';
            if ($data["width"] != "") echo '<div class="input-group" style="width:' . $data["width"] . 'em;">';
            else echo '<div class="input-group">';
            if ($data["height"] != "") echo '<textarea id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '" rows="' . $data["height"] . '" cols="80" class="form-control"></textarea>';
            else echo '<textarea id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '" rows="4" cols="80" class="form-control"></textarea>';
            echo '</div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
            echo '</div>';
        break;
        case "radio":
            //選択肢一覧を取得、配列へ（変なスペースを取ったり空行を取ったり）
            $choices = str_replace(array("\r\n", "\r", "\n"), "\n", $data["list"]);
            $choices = explode("\n", $choices);
            $choices = array_map('trim', $choices);
            //参考　https://www.hachi-log.com/php-arrayfilter-arrayvalue/
            $choices = array_filter($choices);
            $choices = array_values($choices);

            echo '<div class="form-group">' . htmlspecialchars($data["title"]);
            if ($data["required"] == "1") echo '【必須】';
            if ($data["arrangement"] == "h") echo '<div>';
            foreach ($choices as $num => $choice) {
                $choice = htmlspecialchars($choice);
                if ($data["arrangement"] == "h") echo '<div class="form-check form-check-inline">';
                else echo '<div class="form-check">';
                echo '<input id="custom-' . $data["id"] . '-' . $num . '" class="form-check-input" type="radio" name="custom-' . $data["id"] . '" value="' . $choice . '">';
                echo '<label class="form-check-label" for="custom-' . $data["id"] . '-' . $num . '">' . $choice . '</label>';
                echo '</div>';
            }
            if ($data["arrangement"] == "h") echo '</div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
            echo '</div>';
        break;
        case "check":
            //選択肢一覧を取得、配列へ（変なスペースを取ったり空行を取ったり）
            $choices = str_replace(array("\r\n", "\r", "\n"), "\n", $data["list"]);
            $choices = explode("\n", $choices);
            $choices = array_map('trim', $choices);
            //参考　https://www.hachi-log.com/php-arrayfilter-arrayvalue/
            $choices = array_filter($choices);
            $choices = array_values($choices);

            echo '<div class="form-group">' . htmlspecialchars($data["title"]);
            if ($data["required"] == "1") echo '【必須】';
            if ($data["arrangement"] == "h") echo '<div>';
            foreach ($choices as $num => $choice) {
                $choice = htmlspecialchars($choice);
                if ($data["arrangement"] == "h") echo '<div class="form-check form-check-inline">';
                else echo '<div class="form-check">';
                echo '<input id="custom-' . $data["id"] . '-' . $num . '" class="form-check-input" type="checkbox" name="custom-' . $data["id"] . '[]" value="' . $choice . '">';
                echo '<label class="form-check-label" for="custom-' . $data["id"] . '-' . $num . '">' . $choice . '</label>';
                echo '</div>';
            }
            if ($data["arrangement"] == "h") echo '</div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
            echo '</div>';
        break;
        case "dropdown":
            //選択肢一覧を取得、配列へ（変なスペースを取ったり空行を取ったり）
            $choices = str_replace(array("\r\n", "\r", "\n"), "\n", $data["list"]);
            $choices = explode("\n", $choices);
            $choices = array_map('trim', $choices);
            //参考　https://www.hachi-log.com/php-arrayfilter-arrayvalue/
            $choices = array_filter($choices);
            $choices = array_values($choices);

            echo '<div class="form-group">
<label for="custom-' . $data["id"] . '">' . htmlspecialchars($data["title"]);
            if ($data["required"] == "1") echo '【必須】';
            echo '</label>';
            echo '<div class="input-group">';
            if ($data["prefix_a"] != "") echo '<div class="input-group-prepend">
<span class="input-group-text">' . htmlspecialchars($data["prefix_a"]) . '</span>
</div>';
            echo '<select id="custom-' . $data["id"] . '" class="form-control" name="custom-' . $data["id"] . '">';
            echo '<option value="">【選択して下さい】</option>';
            foreach ($choices as $choice) {
                $choice = htmlspecialchars($choice);
                echo '<option value="' . $choice . '">' . $choice . '</option>';
            }
            echo '</select>';
            if ($data["suffix_a"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . htmlspecialchars($data["suffix_a"]) . '</span>
</div>';
            echo '</div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
            echo '</div>';
        break;
        case "attach":
            $exts = str_replace(",", "・", $data["ext"]);
            if ($data["size"] != '') $filesize = $data["size"];
            else $filesize = FILE_MAX_SIZE;

            echo '<div class="form-group">
<label for="custom-' . $data["id"] . '">' . htmlspecialchars($data["title"]) . '（' . $exts . 'ファイル　' . $filesize . 'MBまで）';
            if ($data["required"] == "1") echo '【必須】';
            echo '</label>';
            echo '<input type="file" class="form-control-file" id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '">';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
            echo '</div>';
        break;
    }
}
?>
<br>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary" id="submitbtn">送信する</button>
</form>
</div>
<script type="text/javascript">
<!--
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

//チェック系関数　問題無ければ0を、そうでなければエラーメッセージを返す（エラーメッセージをため込んで後で表示）
//必須・任意関連（テキストボックス、エリア）
function check_required(type, item, title) {
  if (type == "1" && item === "") return "【" + title + "】\n入力されていません。";
  return 0;
}

//必須・任意関連（テキストボックス×2）
function check_required2(type, item, item2, title) {
  if (type == "1") {
    if (item === "" || item2 === "")
    return "【" + title + "】\nいずれかの入力欄が入力されていません。";
  }
  if (type == "2") {
    if (item === "" && item2 === "")
    return "【" + title + "】\nいずれの入力欄も入力されていません。";
  }
  return 0;
}

//テキスト系の最大最小（0だとチェックしない）
function check_maxmin(max, min, item, title) {
  if (max != 0) {
    if (item.length > max) return "【" + title + "】\n文字数が多すぎます（現在" + item.length + "文字）。" + max + "文字以内に抑えて下さい。";
  }
  if (min != 0) {
    if (item.length < min && item.length > 0) return "【" + title + "】\n文字数が少なすぎます（現在" + item.length + "文字）。" + min + "文字以上になるようにして下さい。";
  }
  return 0;
}

//添付ファイル拡張子　参考　https://zukucode.com/2017/12/javascript-input-file-ext.html
function check_ext(name, reg, title) {
  if (!name.toUpperCase().match(reg)) {
    return "【" + title + "】\n指定した拡張子でないため、このファイルはアップロード出来ません。";
  }
  return 0;
}

//添付ファイルサイズ　参考：http://www.openspc2.org/reibun/javascript2/FileAPI/files/0003/index.html
function check_size(filelist, maxsize, title){
  var list = "";
  // MB, KB, B
  maxsizeb = maxsize * 1024 * 1024;
  for(var i=0; i<filelist.length; i++){
    list += filelist[i].size;
  }
  if (parseInt(list) > maxsizeb) return "【" + title + "】\nファイルサイズが大きすぎます（現在" + list / 1024 / 1024 + "MB）。" + maxsize + "MB以内のファイルをアップロードして下さい。";
  return 0;
}


function check(){
  var problem = 0;
  var probsubm = 0;
  var probtitle = 0;
  var probdue = 0;
  var probcus = [];
  var setting = <?php echo json_encode($tojsp); ?>;

  var reg = /^https?:\/\/[\w\/:%#\$&\?\(\)~\.=\+\-]+$/;

  if(document.form.url.value === ""){
    problem = 1;
    probsubm = 1;
  } else if(!document.form.url.value.match(reg)){
    problem = 1;
    probsubm = 2;
  }

//日付と時刻
  if(document.form.due_date.value === "" && document.form.due_time.value === ""){
  } else if (date_check(document.form.due_date) === false || time_check(document.form.due_time) === false){
    problem = 1;
    probdue = 1;
  }


  if(document.form.title.value === ""){
    problem = 1;
    probtitle = 1;
  } else if(document.form.title.value.length > 50){
    problem = 1;
    probtitle = 2;
  }


  //カスタム内容についてチェック
  var val;
  var item;
  var item2;
  var vmax;
  var vmin;
  var result;
  var f;
  var name;
  var filelist;
  var ext;
  var reg;
  var size;
  for( var i=0; i<setting.length; i++) {
    val = setting[i];
    if (val.type == "textbox2") {
      item = document.getElementById("custom-" + val.id + "-1").value;
      item2 = document.getElementById("custom-" + val.id + "-2").value;
      result = check_required2(val.required, item, item2, val.title);
      if (result != 0) {
          problem = 1;
          probcus.push(result);
      }
      if (item != "") {
        if (val.max != "") vmax = parseInt(val.max);
        else vmax = 9999;
        if (val.min != "") vmin = parseInt(val.min);
        else vmin = 0;
        result = check_maxmin(vmax, vmin, item, val.title + "（1つ目の入力欄）");
        if (result != 0) {
            problem = 1;
            probcus.push(result);
        }
      }
      if (item2 != "") {
        if (val.max2 != "") vmax = parseInt(val.max2);
        else vmax = 9999;
        if (val.min2 != "") vmin = parseInt(val.min2);
        else vmin = 0;
        result = check_maxmin(vmax, vmin, item2, val.title + "（2つ目の入力欄）");
        if (result != 0) {
            problem = 1;
            probcus.push(result);
        }
      }
    } else if (val.type == "textbox" || val.type == "textarea") {
        item = document.getElementById("custom-" + val.id).value;
        result = check_required(val.required, item, val.title);
        if (result != 0) {
            problem = 1;
            probcus.push(result);
        } else {
          if (val.max != "") vmax = parseInt(val.max);
          else vmax = 9999;
          if (val.min != "") vmin = parseInt(val.min);
          else vmin = 0;
          result = check_maxmin(vmax, vmin, item, val.title);
          if (result != 0) {
              problem = 1;
              probcus.push(result);
          }
        }
    } else if (val.type == "check") {
        // 参考　http://javascript.pc-users.net/browser/form/checkbox.html
        f = document.getElementsByName("custom-" + val.id + "[]");
        result = '';
        for(var j = 0; j < f.length; j++ ){
      		if(f[j].checked ){
      			result = result +' '+ f[j].value;
      		}
      	}
      	if(result == '' && val.required == "1"){
          problem = 1;
          probcus.push("【" + val.title + "】\nいずれかを選択して下さい。");
      	}
    } else if (val.type == "radio") {
        if(typeof document.form["custom-" + val.id].innerHTML === 'string') {
          if(document.form["custom-" + val.id].checked) item = document.form["custom-" + val.id].value;
          else item = "";
        } else item = document.form["custom-" + val.id].value;
        result = check_required(val.required, item, val.title);
        if (result != 0) {
            problem = 1;
            probcus.push("【" + val.title + "】\nいずれかを選択して下さい。");
        }
    } else if (val.type == "dropdown") {
        item = document.form["custom-" + val.id].value;
        result = check_required(val.required, item, val.title);
        if (result != 0) {
            problem = 1;
            probcus.push("【" + val.title + "】\nいずれかを選択して下さい。");
        }
    } else if (val.type == "attach") {
        name = document.getElementById("custom-" + val.id).value;
        result = check_required(val.required, name, val.title);
        if (name == "") {
            if (result != 0) {
                problem = 1;
                probcus.push("【" + val.title + "】\nファイルを選択して下さい。");
            }
        } else {
          ext = val.ext;
          ext = ext.replace(/,/g, "|");
          ext = ext.toUpperCase();
          reg = new RegExp('\.(' + ext + ')$', 'i');
          result = check_ext(name, reg, val.title);
          if (result != 0) {
              problem = 1;
              probcus.push(result);
          } else {
            filelist = document.getElementById("custom-" + val.id).files;
            if (val.size != "") size = parseInt(val.size);
            else size = <?php echo FILE_MAX_SIZE; ?>;
            result = check_size(filelist, parseInt(size), val.title);
            if (result != 0) {
                problem = 1;
                probcus.push(result);
            }
          }
        }
    }
  }

if ( problem == 1 ) {
  alert( "入力内容に問題があります。\nエラー内容を順に表示しますので、お手数ですが入力内容の確認をお願いします。" );
  if ( probsubm == 1) {
    alert( "【提出ファイルのダウンロードURL】\n入力されていません。" );
  }
  if ( probsubm == 2) {
    alert( "【提出ファイルのダウンロードURL】\n正しく入力されていません。入力されたURLをご確認下さい。" );
  }
  if ( probdue == 1) {
    alert( "【ファイルのダウンロード期限】\n日付もしくは時刻が正しく入力されていません。入力内容をご確認願います。" );
  }
  if ( probtitle == 1) {
    alert( "【タイトル】\n入力されていません。" );
  }
  if ( probtitle == 2) {
    alert( "【タイトル】\n文字数が多すぎます（現在" + document.form.title.value.length + "文字）。50文字以内に抑えて下さい。" );
  }
  probcus.forEach(function(val){
    alert(val);
  });
  return false;
}
  if(window.confirm('入力内容に問題は見つかりませんでした。\n現在の入力内容を送信します。よろしいですか？')){
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
