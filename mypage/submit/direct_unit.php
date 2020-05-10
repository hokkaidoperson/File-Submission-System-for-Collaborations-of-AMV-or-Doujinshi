<?php
require_once('../../set.php');
setup_session();
$titlepart = 'ファイル提出（サーバーに直接アップロード）';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、ファイル提出を受け付け出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

//フォーム設定ファイル読み込み
$submitformdata = array();

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $submitformdata[$i] = json_decode(file_get_contents(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
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
if (isset($submitformdata["general"]["worknumber"]) and $submitformdata["general"]["worknumber"] != "") {
    $myworks = count_works();
    $submitleft = (int)$submitformdata["general"]["worknumber"] - $myworks;
    if ($submitleft <= 0) die_mypage('提出可能な作品数の上限に達しています。');
}

//Javascriptに持って行く用　不要な要素をunset
$tojsp = $submitformdata;
$tojsp2 = $submitformdata["general"];
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

<h1>ファイル提出（サーバーに直接アップロード）</h1>
<p>新規提出するファイルの情報を入力して下さい。</p>
<form name="form" action="handle.php" method="post" enctype="multipart/form-data" onSubmit="return check();">
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="method" value="direct">
<?php
$submitformdata["general"]["detail"] = hsc($submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace("\n", "<br>", $submitformdata["general"]["detail"]);

$exts = str_replace(",", "・", $submitformdata["general"]["ext"]);
if ($submitformdata["general"]["size"] != '') $filesize = $submitformdata["general"]["size"];
else $filesize = FILE_MAX_SIZE;

if ($submitformdata["general"]["filenumber"] != "") {
    if ($submitformdata["general"]["filenumber"] == "1") $filenumexp = '1個のみアップロード可能　' . $filesize . 'MBまで';
    else $filenumexp = $submitformdata["general"]["filenumber"] . '個までアップロード可能　合計' . $filesize . 'MBまで';
}
else $filenumexp = '複数個アップロード可能　合計' . $filesize . 'MBまで';
echo '<div class="form-group"><label for="submitfile">提出ファイル（' . $exts . 'ファイル　' . $filenumexp . '）';
echo '【必須】</label>';
echo '<input type="hidden" name="submitfile-delete[]" value="none">';
echo '<input type="hidden" name="submitfile-already" value="0">';
echo '<input type="hidden" name="submitfile-currentsize" value="0">';
echo '<input type="file" class="form-control-file" id="submitfile" name="submitfile[]"';
if ($submitformdata["general"]["filenumber"] != "1") echo ' multiple="multiple"';
echo ' onChange="check_individual(&quot;submitfile&quot;);">';
echo '<div id="submitfile-errortext" class="invalid-feedback" style="display: block;"></div>';
if ($submitformdata["general"]["detail"] != "") echo '<font size="2">' . $submitformdata["general"]["detail"] . '</font>';
echo '</div>';
?>
<div class="form-group">
<label for="title">タイトル（50文字以内）【必須】</label>
<input type="text" name="title" class="form-control" id="title" value="" onkeyup="ShowLength(value, &quot;title-counter&quot;);" onBlur="check_individual(&quot;title&quot;);">
<font size="2"><div id="title-counter" class="text-right text-md-left text-muted">現在 - 文字</div></font>
<div id="title-errortext" class="invalid-feedback" style="display: block;"></div>
</div>
<?php
foreach ($submitformdata as $number => $data) {
    if ($data["type"] === "general") continue;
    //detail中のURLにリンクを振る（正規表現参考　https://www.megasoft.co.jp/mifes/seiki/s310.html）　あとHTMLタグが無いようにする・改行反映
    $data["detail"] = hsc($data["detail"]);
    $data["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $data["detail"]);
    $data["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $data["detail"]);
    $data["detail"] = str_replace("\n", "<br>", $data["detail"]);

    switch ($data["type"]) {
        case "textbox":
            echo '<div class="form-group">
<label for="custom-' . $data["id"] . '">' . hsc($data["title"]);
            if ($data["max"] != "" and $data["min"] != "") echo '（' . $data["min"] . '文字以上' . $data["max"] . '文字以内）';
            else if ($data["max"] != "" and $data["min"] == "") echo '（' . $data["max"] . '文字以内）';
            else if ($data["max"] == "" and $data["min"] != "") echo '（' . $data["min"] . '文字以上）';
            if ($data["required"] == "1") echo '【必須】';
            echo '</label>';
            if ($data["width"] != "") echo '<div class="input-group" style="width:' . $data["width"] . 'em;">';
            else echo '<div class="input-group">';
            if ($data["prefix_a"] != "") echo '<div class="input-group-prepend">
<span class="input-group-text">' . hsc($data["prefix_a"]) . '</span>
</div>';
            echo '<input type="text" name="custom-' . $data["id"] . '" class="form-control" id="custom-' . $data["id"] . '"';
            echo ' onkeyup="ShowLength(value, &quot;custom-' . $data["id"] . '-counter&quot;);" onBlur="check_individual(' . $number . ');">';
            if ($data["suffix_a"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . hsc($data["suffix_a"]) . '</span>
</div>';
            echo '</div>';
            echo '<font size="2"><div id="custom-' . $data["id"] . '-counter" class="text-right text-md-left text-muted">現在 - 文字</div></font>';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="invalid-feedback" style="display: block;"></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
        break;
        case "textbox2":
            echo '<div class="form-group">' . hsc($data["title"]);
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
<span class="input-group-text">' . hsc($data["prefix_a"]) . '</span>
</div>';
            echo '<input type="text" name="custom-' . $data["id"] . '-1" class="form-control" id="custom-' . $data["id"] . '-1"';
            echo ' onkeyup="ShowLength(value, &quot;custom-' . $data["id"] . '-1-counter&quot;);" onBlur="check_individual(' . $number . ');">';
            if ($data["suffix_a"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . hsc($data["suffix_a"]) . '</span>
</div>';
            echo '</div>';
            echo '<font size="2"><div id="custom-' . $data["id"] . '-1-counter" class="text-right text-md-left text-muted">現在 - 文字</div></font>';
            if ($data["arrangement"] == "h") echo '</div><div class="col">';
            if ($data["width2"] != "") echo '<div class="input-group" style="width:' . $data["width2"] . 'em;">';
            else echo '<div class="input-group">';
            if ($data["prefix_b"] != "") echo '<div class="input-group-prepend">
<span class="input-group-text">' . hsc($data["prefix_b"]) . '</span>
</div>';
            echo '<input type="text" name="custom-' . $data["id"] . '-2" class="form-control" id="custom-' . $data["id"] . '-2"';
            echo ' onkeyup="ShowLength(value, &quot;custom-' . $data["id"] . '-2-counter&quot;);" onBlur="check_individual(' . $number . ');">';
            if ($data["suffix_b"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . hsc($data["suffix_b"]) . '</span>
</div>';
            echo '</div>';
            echo '<font size="2"><div id="custom-' . $data["id"] . '-2-counter" class="text-right text-md-left text-muted">現在 - 文字</div></font>';
            if ($data["arrangement"] == "h") echo '</div></div>';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="invalid-feedback" style="display: block;"></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
        break;
        case "textarea":
            echo '<div class="form-group">
<label for="custom-' . $data["id"] . '">' . hsc($data["title"]);
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
            echo '<font size="2"><div id="custom-' . $data["id"] . '-counter" class="text-right text-md-left text-muted">現在 - 文字</div></font>';
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

            echo '<div class="form-group">' . hsc($data["title"]);
            if ($data["required"] == "1") echo '【必須】';
            if ($data["arrangement"] == "h") echo '<div>';
            foreach ($choices as $num => $choice) {
                $choice = hsc($choice);
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

            echo '<div class="form-group">' . hsc($data["title"]);
            if ($data["required"] == "1") echo '【必須】';
            if ($data["arrangement"] == "h") echo '<div>';
            foreach ($choices as $num => $choice) {
                $choiceh = hsc($choice);
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
<label for="custom-' . $data["id"] . '">' . hsc($data["title"]);
            if ($data["required"] == "1") echo '【必須】';
            echo '</label>';
            echo '<div class="input-group">';
            if ($data["prefix_a"] != "") echo '<div class="input-group-prepend">
<span class="input-group-text">' . hsc($data["prefix_a"]) . '</span>
</div>';
            echo '<select id="custom-' . $data["id"] . '" class="form-control" name="custom-' . $data["id"] . '"';
            echo ' onChange="check_individual(' . $number . ');">';
            echo '<option value="">【選択して下さい】</option>';
            foreach ($choices as $choice) {
                $choice = hsc($choice);
                echo '<option value="' . $choice . '"';
                echo '>' . $choice . '</option>';
            }
            echo '</select>';
            if ($data["suffix_a"] != "") echo '<div class="input-group-append">
<span class="input-group-text">' . hsc($data["suffix_a"]) . '</span>
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
            echo '<div class="form-group"><label for="custom-' . $data["id"] . '">' . hsc($data["title"]) . '（' . $exts . 'ファイル　' . $filenumexp . '）';
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
</div>
<?php
$modaltext = "入力内容に問題は見つかりませんでした。<br><br>現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。";
if ($commonitems != array()) {
    $commonitems = implode("、", $commonitems);
    $modaltext .= '<br><br><div class="form-check"><font size="2">
<input id="jumptocommonpage" class="form-check-input" type="checkbox" name="jumptocommonpage" value="1">
<label class="form-check-label" for="jumptocommonpage">提出完了後に共通情報（' . $commonitems . '）の入力・編集画面にジャンプする場合は、左のチェックボックスにチェックして下さい。</label>
</font></div>';
}

echo_modal_alert();
echo_modal_confirm($modaltext, null, null, null, null, null, null, null, "closesubmit();");
echo_modal_wait();
?>
</form>
<script type="text/javascript">
<!--
function check_individual(id) {
  var valid = 1;
  var setting = <?php echo json_encode($tojsp); ?>;

  if (id === "submitfile") {
    var val = <?php echo json_encode($tojsp2); ?>;
    check_submitfile(val, []);
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

  var val = <?php echo json_encode($tojsp2); ?>;

  if (check_submitfile(val, [])) problem = 1;

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
