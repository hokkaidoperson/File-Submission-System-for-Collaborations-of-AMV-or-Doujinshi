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

//共通情報の項目名取得
$commonitems = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $tmp = json_decode(file_get_contents(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
    $commonitems[$i] = $tmp["title"];
}

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
<form name="form" action="handle.php" method="post"<?php
if ($includeattach) echo ' enctype="multipart/form-data"';
?> onSubmit="return check();">
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="method" value="url">
<div class="form-group">
<label for="url">提出ファイルのダウンロードURL【必須】</label>
<input type="text" name="url" class="form-control" id="url" onBlur="check_individual(&quot;url&quot;);">
<div id="url-errortext" class="invalid-feedback" style="display: block;"></div>
<?php
$submitformdata["general"]["detail"] = str_replace('&amp;', '&', htmlspecialchars($submitformdata["general"]["detail"]));
$submitformdata["general"]["detail"] = preg_replace('{https?://[\w/:%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break">$0</a>', $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace("\n", "<br>", $submitformdata["general"]["detail"]);
if ($submitformdata["general"]["detail"] != "") echo '<font size="2">' . $submitformdata["general"]["detail"] . '</font>';
?>
</div>
<div class="form-group">
<label for="dldpw">ファイルのダウンロードに必要なパスワード（あれば）</label>
<input type="text" name="dldpw" class="form-control" id="dldpw" value="" onBlur="check_individual(&quot;dldpw&quot;);">
<font size="2">※サービスによってパスワードの名称が異なります（「復号キー」など）。</font>
</div>
<div class="form-group">
ファイルのダウンロード期限（あれば）
<div>
<label for="from_date">
日付：
</label>
<input type="date" cmanCLDat="USE:ON" name="due_date" class="form-control" id="due_date" value="" onBlur="check_individual(&quot;due&quot;);">
</div>
<div>
<label for="from_time">
時刻（24時間制）：
</label>
<input type="time" name="due_time" id="due_time" value="" onBlur="check_individual(&quot;due&quot;);">
</div>
<div id="due-errortext" class="invalid-feedback" style="display: block;"></div>
<font size="2">※ダウンロードURLに有効期限がある場合は必ず入力して下さい。入力が無い場合は、URLに有効期限が無いものとして扱います。<br>
※日付の欄をクリックするとカレンダーから日付を選べます。<br>
※時刻の欄についてはブラウザにより表示が異なります（ポップアップ画面が表示される、入力欄の横に上下のボタンが出る　など）。<br>
　時刻の欄をクリックしても何も出ない（通常のテキストボックスのようになっている）場合は、「時:分」の形で、半角で入力して下さい。</font>
</div>
<div class="form-group">
<label for="title">タイトル（50文字以内）【必須】</label>
<input type="text" name="title" class="form-control" id="title" value="" onkeyup="ShowLength(value, &quot;title-counter&quot;);" onBlur="check_individual(&quot;title&quot;);">
<font size="2"><div id="title-counter" class="text-right">現在 - 文字</div></font>
<div id="title-errortext" class="invalid-feedback" style="display: block;"></div>
</div>
<?php
foreach ($submitformdata as $number => $data) {
    if ($data["type"] === "general") continue;
    //detail中のURLにリンクを振る（正規表現参考　https://www.megasoft.co.jp/mifes/seiki/s310.html）　あとHTMLタグが無いようにする・改行反映
    $data["detail"] = str_replace('&amp;', '&', htmlspecialchars($data["detail"]));
    $data["detail"] = preg_replace('{https?://[\w/:%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break">$0</a>', $data["detail"]);
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
            echo '<input type="text" name="custom-' . $data["id"] . '" class="form-control" id="custom-' . $data["id"] . '"';
            echo ' onkeyup="ShowLength(value, &quot;custom-' . $data["id"] . '-counter&quot;);" onBlur="check_individual(' . $number . ');">';
            if ($data["suffix_a"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . htmlspecialchars($data["suffix_a"]) . '</span>
</div>';
            echo '</div>';
            echo '<font size="2"><div id="custom-' . $data["id"] . '-counter" class="text-right">現在 - 文字</div></font>';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="invalid-feedback" style="display: block;"></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
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
            echo '<input type="text" name="custom-' . $data["id"] . '-1" class="form-control" id="custom-' . $data["id"] . '-1"';
            echo ' onkeyup="ShowLength(value, &quot;custom-' . $data["id"] . '-1-counter&quot;);" onBlur="check_individual(' . $number . ');">';
            if ($data["suffix_a"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . htmlspecialchars($data["suffix_a"]) . '</span>
</div>';
            echo '</div>';
            echo '<font size="2"><div id="custom-' . $data["id"] . '-1-counter" class="text-right">現在 - 文字</div></font>';
            if ($data["arrangement"] == "h") echo '</div><div class="col">';
            if ($data["width2"] != "") echo '<div class="input-group" style="width:' . $data["width2"] . 'em;">';
            else echo '<div class="input-group">';
            if ($data["prefix_b"] != "") echo '<div class="input-group-prepend">
<span class="input-group-text">' . htmlspecialchars($data["prefix_b"]) . '</span>
</div>';
            echo '<input type="text" name="custom-' . $data["id"] . '-2" class="form-control" id="custom-' . $data["id"] . '-2"';
            echo ' onkeyup="ShowLength(value, &quot;custom-' . $data["id"] . '-2-counter&quot;);" onBlur="check_individual(' . $number . ');">';
            if ($data["suffix_b"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . htmlspecialchars($data["suffix_b"]) . '</span>
</div>';
            echo '</div>';
            echo '<font size="2"><div id="custom-' . $data["id"] . '-2-counter" class="text-right">現在 - 文字</div></font>';
            if ($data["arrangement"] == "h") echo '</div></div>';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="invalid-feedback" style="display: block;"></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
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
            if ($data["height"] != "") echo '<textarea id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '" rows="' . $data["height"] . '" cols="80" class="form-control"';
            else echo '<textarea id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '" rows="4" cols="80" class="form-control"';
            echo ' onkeyup="ShowLength(value, &quot;custom-' . $data["id"] . '-counter&quot;);" onBlur="check_individual(' . $number . ');">';
            echo '</textarea>';
            echo '</div>';
            echo '<font size="2"><div id="custom-' . $data["id"] . '-counter" class="text-right">現在 - 文字</div></font>';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="invalid-feedback" style="display: block;"></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
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
                echo '<input id="custom-' . $data["id"] . '-' . $num . '" class="form-check-input" type="radio" name="custom-' . $data["id"] . '" value="' . $choice . '"';
                echo ' onChange="check_individual(' . $number . ');">';
                echo '<label class="form-check-label" for="custom-' . $data["id"] . '-' . $num . '">' . $choice . '</label>';
                echo '</div>';
            }
            if ($data["arrangement"] == "h") echo '</div>';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="invalid-feedback" style="display: block;"></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
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
                $choiceh = htmlspecialchars($choice);
                if ($data["arrangement"] == "h") echo '<div class="form-check form-check-inline">';
                else echo '<div class="form-check">';
                echo '<input id="custom-' . $data["id"] . '-' . $num . '" class="form-check-input" type="checkbox" name="custom-' . $data["id"] . '[]" value="' . $choiceh . '"';
                echo ' onChange="check_individual(' . $number . ');">';
                echo '<label class="form-check-label" for="custom-' . $data["id"] . '-' . $num . '">' . $choiceh . '</label>';
                echo '</div>';
            }
            if ($data["arrangement"] == "h") echo '</div>';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="invalid-feedback" style="display: block;"></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
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
            echo '<select id="custom-' . $data["id"] . '" class="form-control" name="custom-' . $data["id"] . '"';
            echo ' onChange="check_individual(' . $number . ');">';
            echo '<option value="">【選択して下さい】</option>';
            foreach ($choices as $choice) {
                $choice = htmlspecialchars($choice);
                echo '<option value="' . $choice . '"';
                echo '>' . $choice . '</option>';
            }
            echo '</select>';
            if ($data["suffix_a"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . htmlspecialchars($data["suffix_a"]) . '</span>
</div>';
            echo '</div>';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="invalid-feedback" style="display: block;"></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
        break;
        case "attach":
            $exts = str_replace(",", "・", $data["ext"]);
            if ($data["size"] != '') $filesize = $data["size"];
            else $filesize = FILE_MAX_SIZE;
            $currentsize = 0;

            if ($data["filenumber"] != "") {
                if ($data["filenumber"] == "1") $filenumexp = '1個のみアップロード可能　' . $filesize . 'MBまで';
                else $filenumexp = $data["filenumber"] . '個までアップロード可能　合計' . $filesize . 'MBまで';
            }
            else $filenumexp = '複数個アップロード可能　合計' . $filesize . 'MBまで';
            echo '<div class="form-group"><label for="custom-' . $data["id"] . '">' . htmlspecialchars($data["title"]) . '（' . $exts . 'ファイル　' . $filenumexp . '）';
            if ($data["required"] == "1") echo '【必須】</label>';
            echo '<input type="hidden" name="custom-' . $data["id"] . '-delete[]" value="none">';
            echo '<input type="hidden" name="custom-' . $data["id"] . '-already" value="0">';
            echo '<input type="hidden" name="custom-' . $data["id"] . '-currentsize" value="0">';
            echo '<input type="file" class="form-control-file" id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '[]"';
            if ($data["filenumber"] != "1") echo ' multiple="multiple"';
            echo ' onChange="check_individual(' . $number . ');">';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="invalid-feedback" style="display: block;"></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
        break;
    }
    echo '</div>';
}
?>
<br>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary">送信する</button>
<!-- エラーModal -->
<div class="modal fade" id="errormodal" tabindex="-1" role="dialog" aria-labelledby="errormodaltitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="errormodaltitle">入力内容の修正が必要です</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
入力内容に問題が見つかりました。<br>
お手数ですが、表示されているエラー内容を参考に、入力内容の確認・修正をお願いします。<br><br>
修正後、再度「送信する」を押して下さい。
</div>
<div class="modal-footer">
<button type="button" class="btn btn-primary" data-dismiss="modal" id="dismissbtn">OK</button>
</div>
</div>
</div>
</div>
<!-- 送信確認Modal -->
<div class="modal fade" id="confirmmodal" tabindex="-1" role="dialog" aria-labelledby="confirmmodaltitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="confirmmodaltitle">送信確認</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
入力内容に問題は見つかりませんでした。<br><br>
現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>
入力内容の修正を行う場合は「戻る」を押して下さい。
<?php
if ($commonitems != array()) {
    $commonitems = implode("、", $commonitems);
    echo '<br><br><div class="form-check"><font size="2">
<input id="jumptocommonpage" class="form-check-input" type="checkbox" name="jumptocommonpage" value="1">
<label class="form-check-label" for="jumptocommonpage">提出完了後に共通情報（' . $commonitems . '）の入力・編集画面にジャンプする場合は、左のチェックボックスにチェックして下さい。</label>
</font></div>';
}
?>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
<button type="button" id="submitbtn" onclick="closesubmit();" class="btn btn-primary">送信する</button>
</div>
</div>
</div>
</div>
</div>
<!-- 送信中Modal -->
<div class="modal fade" id="sendingmodal" tabindex="-1" role="dialog" aria-labelledby="sendingmodaltitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="sendingmodaltitle">送信中…</h5>
</div>
<div class="modal-body">
入力内容・ファイルを送信中です。<br>
画面が自動的に推移するまでしばらくお待ち下さい。
</div>
</div>
</div>
</div>
</form>
<script type="text/javascript">
<!--
function check_individual(id) {
  var valid = 1;
  var setting = <?php echo json_encode($tojsp); ?>;

  if (id === "url") {
    var reg = /^https?:\/\/[\w\/:%#\$&\?\(\)~\.=\+\-]+$/;

    document.getElementById("url-errortext").innerHTML = "";
    if(document.form.url.value === ""){
      valid = 0;
      document.getElementById("url-errortext").innerHTML = "入力されていません。";
    } else if(!document.form.url.value.match(reg)){
      valid = 0;
      document.getElementById("url-errortext").innerHTML = "正しく入力されていません。入力されたURLをご確認下さい。";
    }
    if (valid) {
      document.form.url.classList.add("is-valid");
      document.form.url.classList.remove("is-invalid");
    } else {
      document.form.url.classList.add("is-invalid");
      document.form.url.classList.remove("is-valid");
    }
    return;
  }

  if (id === "title") {
    document.getElementById("title-errortext").innerHTML = "";
    if(document.form.title.value === ""){
      valid = 0;
      document.getElementById("title-errortext").innerHTML = "入力されていません。";
    } else if(document.form.title.value.length > 50){
      valid = 0;
      document.getElementById("title-errortext").innerHTML = "文字数が多すぎます。50文字以内に抑えて下さい。";
    }
    if (valid) {
      document.form.title.classList.add("is-valid");
      document.form.title.classList.remove("is-invalid");
    } else {
      document.form.title.classList.add("is-invalid");
      document.form.title.classList.remove("is-valid");
    }
    return;
  }

  if (id === "dldpw") {
    document.form.dldpw.classList.add("is-valid");
    return;
  }

  if (id === "due") {
    document.getElementById("due-errortext").innerHTML = "";
    if(document.form.due_date.value === "" && document.form.due_time.value === ""){
    } else if (date_check(document.form.due_date) === false || time_check(document.form.due_time) === false){
      valid = 0;
      document.getElementById("due-errortext").innerHTML = "日付もしくは時刻が正しく入力されていません。入力された日時をご確認下さい。";
    }
    if (valid) {
      document.form.due_date.classList.add("is-valid");
      document.form.due_date.classList.remove("is-invalid");
      document.form.due_time.classList.add("is-valid");
      document.form.due_time.classList.remove("is-invalid");
    } else {
      document.form.due_date.classList.add("is-invalid");
      document.form.due_date.classList.remove("is-valid");
      document.form.due_time.classList.add("is-invalid");
      document.form.due_time.classList.remove("is-valid");
    }
    return;
  }

  //submitfileでもtitleでもなければカスタム内容
  var val = setting[id];
  document.getElementById("custom-" + val.id + "-errortext").innerHTML = "";
  if (val.type == "textbox2") {
    check_textbox2(val);
  } else if (val.type == "textbox" || val.type == "textarea") {
    check_textbox(val);
  } else if (val.type == "check") {
    check_checkbox(val);
  } else if (val.type == "radio") {
    check_radio(val);
  } else if (val.type == "dropdown") {
    check_dropdown(val);
  } else if (val.type == "attach") {
    check_attach(val, []);
  }
}

function check(){
  var problem = 0;
  var valid = 1;
  var setting = <?php echo json_encode($tojsp); ?>;

  var reg = /^https?:\/\/[\w\/:%#\$&\?\(\)~\.=\+\-]+$/;

  document.getElementById("url-errortext").innerHTML = "";
  if(document.form.url.value === ""){
    problem = 1;
    valid = 0;
    document.getElementById("url-errortext").innerHTML = "入力されていません。";
  } else if(!document.form.url.value.match(reg)){
    problem = 1;
    valid = 0;
    document.getElementById("url-errortext").innerHTML = "正しく入力されていません。入力されたURLをご確認下さい。";
  }
  if (valid) {
    document.form.url.classList.add("is-valid");
    document.form.url.classList.remove("is-invalid");
  } else {
    document.form.url.classList.add("is-invalid");
    document.form.url.classList.remove("is-valid");
  }
  valid = 1;

  document.form.dldpw.classList.add("is-valid");

//日付と時刻
  document.getElementById("due-errortext").innerHTML = "";
  if(document.form.due_date.value === "" && document.form.due_time.value === ""){
  } else if (date_check(document.form.due_date) === false || time_check(document.form.due_time) === false){
    problem = 1;
    valid = 0;
    document.getElementById("due-errortext").innerHTML = "日付もしくは時刻が正しく入力されていません。入力された日時をご確認下さい。";
  }
  if (valid) {
    document.form.due_date.classList.add("is-valid");
    document.form.due_date.classList.remove("is-invalid");
    document.form.due_time.classList.add("is-valid");
    document.form.due_time.classList.remove("is-invalid");
  } else {
    document.form.due_date.classList.add("is-invalid");
    document.form.due_date.classList.remove("is-valid");
    document.form.due_time.classList.add("is-invalid");
    document.form.due_time.classList.remove("is-valid");
  }
  valid = 1;

  document.getElementById("title-errortext").innerHTML = "";
  if(document.form.title.value === ""){
    problem = 1;
    valid = 0;
    document.getElementById("title-errortext").innerHTML = "入力されていません。";
  } else if(document.form.title.value.length > 50){
    problem = 1;
    valid = 0;
    document.getElementById("title-errortext").innerHTML = "文字数が多すぎます。50文字以内に抑えて下さい。";
  }

  if (valid) {
    document.form.title.classList.add("is-valid");
    document.form.title.classList.remove("is-invalid");
  } else {
    document.form.title.classList.add("is-invalid");
    document.form.title.classList.remove("is-valid");
  }
  valid = 1;

  //カスタム内容についてチェック
  var val;
  for( var i=0; i<setting.length; i++) {
    val = setting[i];
    document.getElementById("custom-" + val.id + "-errortext").innerHTML = "";
    if (val.type == "textbox2") {
      if (check_textbox2(val)) problem = 1;
    } else if (val.type == "textbox" || val.type == "textarea") {
      if (check_textbox(val)) problem = 1;
    } else if (val.type == "check") {
      if (check_checkbox(val)) problem = 1;
    } else if (val.type == "radio") {
      if (check_radio(val)) problem = 1;
    } else if (val.type == "dropdown") {
      if (check_dropdown(val)) problem = 1;
    } else if (val.type == "attach") {
      if (check_attach(val, [])) problem = 1;
    }
  }

  if ( problem == 1 ) {
    $('#errormodal').modal();
    $('#errormodal').on('shown.bs.modal', function () {
        document.getElementById("dismissbtn").focus();
    });
  } else {
    $('#confirmmodal').modal();
    $('#confirmmodal').on('shown.bs.modal', function () {
        document.getElementById("submitbtn").focus();
    });
  }
  return false;

}

// -->
</script>
<?php
include(PAGEROOT . 'validate_script.php');
require_once(PAGEROOT . 'mypage_footer.php');
