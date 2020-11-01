<?php
require_once('../../set.php');
setup_session();
$titlepart = 'ファイル提出（外部アップローダーサービス経由）';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

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
    $submitformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
    if ($submitformdata[$i]["type"] == "attach") $includeattach = TRUE;
}
$submitformdata["general"] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);

//共通情報の項目名取得
$commonitems = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $tmp = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
    $commonitems[$i] = $tmp["title"];
}

if (outofterm('submit') != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;

if ($submitformdata["general"]["from"] > time() and !$outofterm) die_mypage('提出期間外です。');
else if ($submitformdata["general"]["until"] <= time() and !$outofterm) die_mypage('提出期間外です。');
if (isset($submitformdata["general"]["worknumber"]) and $submitformdata["general"]["worknumber"] != "") {
    $myworks = count_works();
    $submitleft = (int)$submitformdata["general"]["worknumber"] - $myworks;
    if ($submitleft <= 0) die_mypage('提出可能な作品数の上限に達しています。');
}

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
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="method" value="url">
<?php
$submitformdata["general"]["detail"] = hsc($submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace("\n", "<br>", $submitformdata["general"]["detail"]);
echo_textbox("提出ファイルのダウンロードURL【必須】", "url", "url", "", FALSE, $submitformdata["general"]["detail"], 'onBlur="check_individual(&quot;url&quot;);"');
echo_textbox("ファイルのダウンロードに必要なパスワード（あれば）", "dldpw", "dldpw", "", FALSE, "※サービスによってパスワードの名称が異なります（「復号キー」など）。", 'onBlur="check_individual(&quot;dldpw&quot;);"');
?>
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
<div id="due-errortext" class="system-form-error"></div>
<small class="form-text">※ダウンロードURLに有効期限がある場合は必ず入力して下さい。入力が無い場合は、URLに有効期限が無いものとして扱います。<br>
※日付の欄をクリックするとカレンダーから日付を選べます。<br>
※時刻の欄についてはブラウザにより表示が異なります（ポップアップ画面が表示される、入力欄の横に上下のボタンが出る　など）。<br>
　時刻の欄をクリックしても何も出ない（通常のテキストボックスのようになっている）場合は、「時:分」の形で、半角で入力して下さい。</small>
</div>
<?php
echo_textbox("タイトル（50文字以内）【必須】", "title", "title", "", TRUE, "", 'onBlur="check_individual(&quot;title&quot;);"');

foreach ($submitformdata as $number => $data) {
    if ($data["type"] === "general") continue;
    //detail中のURLにリンクを振る（正規表現参考　https://www.megasoft.co.jp/mifes/seiki/s310.html）　あとHTMLタグが無いようにする・改行反映
    $data["detail"] = hsc($data["detail"]);
    $data["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $data["detail"]);
    $data["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $data["detail"]);
    $data["detail"] = str_replace("\n", "<br>", $data["detail"]);

    switch ($data["type"]) {
        case "textbox":
            $parttitle = hsc($data["title"]);
            if ($data["max"] != "" and $data["min"] != "") $parttitle .= '（' . $data["min"] . '文字以上' . $data["max"] . '文字以内）';
            else if ($data["max"] != "" and $data["min"] == "") $parttitle .= '（' . $data["max"] . '文字以内）';
            else if ($data["max"] == "" and $data["min"] != "") $parttitle .= '（' . $data["min"] . '文字以上）';
            if ($data["required"] == "1") $parttitle .= '【必須】';
            echo_textbox($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], "", TRUE, $data["detail"], 'onBlur="check_individual(' . $number . ');"', hsc($data["prefix_a"]), hsc($data["suffix_a"]), $data["width"]);
        break;
        case "textbox2":
            $parttitle = hsc($data["title"]);
            if ($data["max"] != "" and $data["min"] != "") $parttitle .= '（1つ目の入力欄：' . $data["min"] . '文字以上' . $data["max"] . '文字以内）';
            else if ($data["max"] != "" and $data["min"] == "") $parttitle .= '（1つ目の入力欄：' . $data["max"] . '文字以内）';
            else if ($data["max"] == "" and $data["min"] != "") $parttitle .= '（1つ目の入力欄：' . $data["min"] . '文字以上）';
            if ($data["max2"] != "" and $data["min2"] != "") $parttitle .= '（2つ目の入力欄：' . $data["min2"] . '文字以上' . $data["max2"] . '文字以内）';
            else if ($data["max2"] != "" and $data["min2"] == "") $parttitle .= '（2つ目の入力欄：' . $data["max2"] . '文字以内）';
            else if ($data["max2"] == "" and $data["min2"] != "") $parttitle .= '（2つ目の入力欄：' . $data["min2"] . '文字以上）';
            if ($data["required"] == "1") $parttitle .= '【どちらも必須】';
            else if ($data["required"] == "2") $parttitle .= '【いずれか必須】';
            $horizontally = ($data["arrangement"] == "h");
            echo_textbox2($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], "", "", TRUE, $horizontally, $data["detail"], 'onBlur="check_individual(' . $number . ');"', hsc($data["prefix_a"]), hsc($data["suffix_a"]), $data["width"], hsc($data["prefix_b"]), hsc($data["suffix_b"]), $data["width2"]);
        break;
        case "textarea":
            $parttitle = hsc($data["title"]);
            if ($data["max"] != "" and $data["min"] != "") $parttitle .= '（' . $data["min"] . '文字以上' . $data["max"] . '文字以内）';
            else if ($data["max"] != "" and $data["min"] == "") $parttitle .= '（' . $data["max"] . '文字以内）';
            else if ($data["max"] == "" and $data["min"] != "") $parttitle .= '（' . $data["min"] . '文字以上）';
            if ($data["required"] == "1") $parttitle .= '【必須】';
            echo_textarea($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], "", TRUE, $data["detail"], 'onBlur="check_individual(' . $number . ');"', $data["width"], $data["height"]);
        break;
        case "radio":
            $choices = choices_array($data["list"], TRUE);

            $parttitle = hsc($data["title"]);
            if ($data["required"] == "1") $parttitle .= '【必須】';
            $horizontally = ($data["arrangement"] == "h");
            echo_radio($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], $choices, [], "", $horizontally, $data["detail"], 'onChange="check_individual(' . $number . ');"');
        break;
        case "check":
            $choices = choices_array($data["list"], TRUE);

            $parttitle = hsc($data["title"]);
            if ($data["required"] == "1") $parttitle .= '【必須】';
            $horizontally = ($data["arrangement"] == "h");
            echo_check($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], $choices, [], [], $horizontally, $data["detail"], 'onChange="check_individual(' . $number . ');"');
        break;
        case "dropdown":
            $choices = choices_array($data["list"]);

            $parttitle = hsc($data["title"]);
            if ($data["required"] == "1") $parttitle .= '【必須】';
            $horizontally = ($data["arrangement"] == "h");
            echo_dropdown($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], $choices, [], "", $data["detail"], 'onChange="check_individual(' . $number . ');"', hsc($data["prefix_a"]), hsc($data["suffix_a"]));
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
            echo '<div class="form-group"><label for="custom-' . $data["id"] . '">' . hsc($data["title"]) . '（' . $exts . 'ファイル　' . $filenumexp . '）';
            if ($data["required"] == "1") echo '【必須】</label>';
            echo '<input type="hidden" name="custom-' . $data["id"] . '-delete[]" value="none">';
            echo '<input type="hidden" name="custom-' . $data["id"] . '-already" value="0">';
            echo '<input type="hidden" name="custom-' . $data["id"] . '-currentsize" value="0">';
            echo '<input type="file" class="form-control-file" id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '[]"';
            if ($data["filenumber"] != "1") echo ' multiple="multiple"';
            echo ' onChange="check_individual(' . $number . ');">';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="system-form-error"></div>';
            if ($data["detail"] != "") echo '<small class="form-text">' . $data["detail"] . '</small>';
            echo '</div>';
        break;
    }
}
?>
<br>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary">送信する</button>
</div>
<?php
$modaltext = "<p>入力内容に問題は見つかりませんでした。</p><p>現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。</p>";
if ($commonitems != array()) {
    $commonitems = implode("、", $commonitems);
    $modaltext .= '<div class="form-check small">
<input id="jumptocommonpage" class="form-check-input" type="checkbox" name="jumptocommonpage" value="1">
<label class="form-check-label" for="jumptocommonpage">提出完了後に共通情報（' . $commonitems . '）の入力・編集画面にジャンプする場合は、左のチェックボックスにチェックして下さい。</label>
</div>';
}

echo_modal_alert();
echo_modal_confirm($modaltext, null, null, null, null, null, null, null, "closesubmit();");
echo_modal_wait();
?>
</form>
<script type="text/javascript">
<!--
var changed = false;
function check_individual(id) {
  changed = true;
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
  changed = true;
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

window.addEventListener('beforeunload', function (e) {
  if (changed) {
    e.preventDefault();
    e.returnValue = '';
  }
});

// -->
</script>
<?php
include(PAGEROOT . 'validate_script.php');
require_once(PAGEROOT . 'mypage_footer.php');
