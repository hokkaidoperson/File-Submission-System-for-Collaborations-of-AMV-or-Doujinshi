<?php
require_once('../../set.php');
setup_session();
$titlepart = '共通情報の入力・編集';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

if (!file_exists(DATAROOT . 'form/userinfo/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) die_mypage('<h1>準備中です</h1>
<p>必要な設定が済んでいないため、只今、共通事項の設定を受け付け出来ません。<br>
しばらくしてから、再度アクセス願います。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');


//フォーム設定ファイル読み込み
$userformdata = array();

//添付ファイルを含むかどうかの変数（添付ファイルがある場合はenctypeの設定が必要なため）
$includeattach = FALSE;

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $userformdata[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
    if ($userformdata[$i]["type"] == "attach") $includeattach = TRUE;
}

//Javascriptに持って行く用　不要な要素をunset
$tojsp = $userformdata;
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
}

$userid = $_SESSION["userid"];

//入力済み情報を読み込む
$entereddata = json_decode(file_get_contents_repeat(DATAROOT . "users/" . $userid . ".txt"), true);

//締め切り後は変更不可・例外処理
if (outofterm('userform') != FALSE) $disable = FALSE;
else $disable = TRUE;
if ($_SESSION["state"] == 'p') $disable = FALSE;
if (before_deadline()) $disable = FALSE;
if ((isset($entereddata["common_acceptance"]) and $entereddata["common_acceptance"] == 0) or (isset($entereddata["common_editing"]) and $entereddata["common_editing"] == 1)) {
    $waiting = TRUE;
    $disable = TRUE;
} else $waiting = FALSE;

//アップ済みのファイルのサイズ（jspでの引き算処理用）
$uploadedfs = array();

?>

<h1>共通情報の入力・編集</h1>
<p>以下の内容について入力して下さい。</p>
<p>情報を入力済みの場合、現在登録されている情報が入力欄に入力されています。変更したい項目のみ、入力欄の中身を変更して下さい。</p>
<p>※ニックネームの編集はこの画面では行えません。ニックネームを変更する場合は「<a href="../account/">アカウント情報編集</a>」画面で変更出来ます。</p>
<?php
if ($userformdata == array()) die_mypage('<div class="border border-danger system-border-spacer">
設定可能な項目はありません。
</div>');
if (!before_deadline() and $_SESSION["state"] == 'p') echo '<div class="border border-primary system-border-spacer">
現在ファイル提出期間外ですが、主催者は常時共通情報の編集が可能です。
</div>';
else if (!before_deadline() and !$disable) echo '<div class="border border-primary system-border-spacer">
現在ファイル提出期間外ですが、あなたは主催者から共通情報の編集を許可されています（' . date('Y年n月j日G時i分s秒', outofterm('userform')) . 'まで）。
</div>';
else if (!before_deadline()) echo '<div class="border border-danger system-border-spacer">
現在、ファイル提出期間外です。入力内容の確認は出来ますが、変更は出来ません。
</div>';
if ($waiting) {
    echo '<div class="border border-danger system-border-spacer">
現在、共通情報の確認待ちです。確認が完了するまでは、共通情報の編集が出来ません。
</div>';
}
echo '<div class="border border-primary system-border-spacer">
共通情報の承認状態：';
if (isset($entereddata["common_acceptance"])) {
    if (isset($entereddata["common_editing"]) and $entereddata["common_editing"] == 1) echo '項目編集の承認待ち<br>※変更後の内容は下記に反映されていません。';
    else switch ($entereddata["common_acceptance"]) {
        case 0:
            echo '承認待ち';
        break;
        case 1:
            echo '<span class="text-success"><b>承認</b></span>';
        break;
        case 2:
            echo '<span class="text-danger"><b>承認見送り</b></span>';
        break;
    }
} else echo '未入力';
echo '</div>';
?>
<form name="form" action="handle.php" method="post" <?php
if ($includeattach) echo 'enctype="multipart/form-data" ';
?> onSubmit="return check();">
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<?php
foreach ($userformdata as $number => $data) {
    //detail中のURLにリンクを振る（正規表現参考　https://www.megasoft.co.jp/mifes/seiki/s310.html）　あとHTMLタグが無いようにする・改行反映
    $data["detail"] = hsc($data["detail"]);
    $data["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $data["detail"]);
    $data["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $data["detail"]);
    $data["detail"] = str_replace("\n", "<br>", $data["detail"]);
    if ($data["recheck"] != "auto") $data["detail"] .= '<div><b>※この項目の変更には、運営メンバーによる承認が必要です。</b></div>';
    else echo $data["detail"] .= '<div>※この項目の変更は自動承認されます。</div>';

    switch ($data["type"]) {
        case "textbox":
            $parttitle = hsc($data["title"]);
            if ($data["max"] != "" and $data["min"] != "") $parttitle .= '（' . $data["min"] . '文字以上' . $data["max"] . '文字以内）';
            else if ($data["max"] != "" and $data["min"] == "") $parttitle .= '（' . $data["max"] . '文字以内）';
            else if ($data["max"] == "" and $data["min"] != "") $parttitle .= '（' . $data["min"] . '文字以上）';
            if ($data["required"] == "1") $parttitle .= '【必須】';
            echo_textbox($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], isset($entereddata[$data["id"]]) ? $entereddata[$data["id"]] : "", TRUE, $data["detail"], 'onBlur="check_individual(' . $number . ');"', hsc($data["prefix_a"]), hsc($data["suffix_a"]), $data["width"], $disable);
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
            echo_textbox2($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], isset($entereddata[$data["id"] . "-1"]) ? $entereddata[$data["id"] . "-1"] : "", isset($entereddata[$data["id"] . "-2"]) ? $entereddata[$data["id"] . "-2"] : "", TRUE, $horizontally, $data["detail"], 'onBlur="check_individual(' . $number . ');"', hsc($data["prefix_a"]), hsc($data["suffix_a"]), $data["width"], hsc($data["prefix_b"]), hsc($data["suffix_b"]), $data["width2"], $disable);
        break;
        case "textarea":
            $parttitle = hsc($data["title"]);
            if ($data["max"] != "" and $data["min"] != "") $parttitle .= '（' . $data["min"] . '文字以上' . $data["max"] . '文字以内）';
            else if ($data["max"] != "" and $data["min"] == "") $parttitle .= '（' . $data["max"] . '文字以内）';
            else if ($data["max"] == "" and $data["min"] != "") $parttitle .= '（' . $data["min"] . '文字以上）';
            if ($data["required"] == "1") $parttitle .= '【必須】';
            echo_textarea($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], isset($entereddata[$data["id"]]) ? $entereddata[$data["id"]] : "", TRUE, $data["detail"], 'onBlur="check_individual(' . $number . ');"', $data["width"], $data["height"], $disable);
        break;
        case "radio":
            $choices = choices_array($data["list"], TRUE);

            $parttitle = hsc($data["title"]);
            if ($data["required"] == "1") $parttitle .= '【必須】';
            $horizontally = ($data["arrangement"] == "h");
            if (isset($entereddata[$data["id"]]) and $entereddata[$data["id"]] !== "") $prefill = (string) array_search($entereddata[$data["id"]], $choices);
            else $prefill = "";
            echo_radio($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], $choices, [], $prefill, $horizontally, $data["detail"], 'onChange="check_individual(' . $number . ');"', $disable);
        break;
        case "check":
            $choices = choices_array($data["list"], TRUE);

            $parttitle = hsc($data["title"]);
            if ($data["required"] == "1") $parttitle .= '【必須】';
            $horizontally = ($data["arrangement"] == "h");
            $prefill = [];
            if (isset($entereddata[$data["id"]])) foreach ((array)$entereddata[$data["id"]] as $selected) {
                $search = array_search($selected, $choices);
                if ($search !== FALSE) $prefill[] = $search;
            }
            echo_check($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], $choices, [], $prefill, $horizontally, $data["detail"], 'onChange="check_individual(' . $number . ');"', $disable);
        break;
        case "dropdown":
            $choices = choices_array($data["list"]);

            $parttitle = hsc($data["title"]);
            if ($data["required"] == "1") $parttitle .= '【必須】';
            $horizontally = ($data["arrangement"] == "h");
            if (isset($entereddata[$data["id"]]) and $entereddata[$data["id"]] !== "") $prefill = (string) array_search($entereddata[$data["id"]], $choices);
            else $prefill = "";
            echo_dropdown($parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], $choices, [], $prefill, $data["detail"], 'onChange="check_individual(' . $number . ');"', hsc($data["prefix_a"]), hsc($data["suffix_a"]), $disable);
        break;
        case "attach":
            $uploadedfs[$data["id"]] = array();
            $exts = str_replace(",", "・", $data["ext"]);
            if ($data["size"] != '') $filesize = $data["size"];
            else $filesize = FILE_MAX_SIZE;
            $currentsize = 0;

            if ($data["filenumber"] != "") {
                if ($data["filenumber"] == "1") $filenumexp = '1個のみアップロード可能　' . $filesize . 'MBまで';
                else $filenumexp = $data["filenumber"] . '個までアップロード可能　合計' . $filesize . 'MBまで';
            }
            else $filenumexp = '複数個アップロード可能　合計' . $filesize . 'MBまで';
            echo '<div class="form-group">' . hsc($data["title"]) . '（' . $exts . 'ファイル　' . $filenumexp . '）';
            if ($data["required"] == "1") echo '【必須】';
            if (isset($entereddata[$data["id"]]) and $entereddata[$data["id"]] != array()) {
                echo '<div class="small">現在アップロードされているファイルを以下に表示します。<br>ファイル名をクリックするとそのファイルをダウンロードします。<br>ファイルを削除する場合は、そのファイルの左側にあるチェックボックスにチェックを入れて下さい。</div>';
                foreach ($entereddata[$data["id"]] as $key => $element){
                    echo '<div class="form-check">';
                    echo '<input id="custom-' . $data["id"] . '-delete-' . $key . '" class="form-check-input" type="checkbox" name="custom-' . $data["id"] . '-delete[]" value="' . $key . '"';
                    if ($disable) echo ' disabled="disabled"';
                    echo ' onChange="check_individual(' . $number . ');">';
                    echo '<a href="../fnc/filedld.php?author=' . $userid . '&genre=userform&id=' . $data["id"] . '_' . $key . '" target="_blank">' . hsc($element) . '</a>';
                    $currentsize += filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $data["id"] . '_' . $key);
                    $uploadedfs[$data["id"]][$key] = filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $data["id"] . '_' . $key);
                    echo '</div>';
                }
                echo '<input type="hidden" name="custom-' . $data["id"] . '-currentsize" value="' . $currentsize . '">';
                echo '<input type="hidden" name="custom-' . $data["id"] . '-already" value="' . count($entereddata[$data["id"]]) . '">';
            }
            else {
                echo '<div class="small">現在アップロードされているファイルはありません。</div>';
                echo '<input type="hidden" name="custom-' . $data["id"] . '-delete[]" value="none">';
                echo '<input type="hidden" name="custom-' . $data["id"] . '-already" value="0">';
                echo '<input type="hidden" name="custom-' . $data["id"] . '-currentsize" value="0">';
            }
            echo '<label for="custom-' . $data["id"] . '" class="small">ファイルを新規に追加する場合はこちらにアップロードして下さい：</label>';
            echo '<input type="file" class="form-control-file" id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '[]"';
            if ($data["filenumber"] != "1") echo ' multiple="multiple"';
            if ($disable) echo ' disabled="disabled"';
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
<button type="submit" class="btn btn-primary"<?php
if ($disable) echo ' disabled="disabled"';
?>>送信する</button>
</div>
<?php
echo_modal_alert();
echo_modal_confirm(null, null, null, null, null, null, null, null, "closesubmit();");
echo_modal_wait();
?>
</form>
<script type="text/javascript">
<!--
var changed = false;
function check_individual(id) {
  changed = true;
  var setting = <?php echo json_encode($tojsp); ?>;
  var uploadedfs = <?php echo json_encode($uploadedfs); ?>;

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
    check_attach(val, uploadedfs[val.id]);
  }
}

function check(){
  changed = true;
  var problem = 0;
  var setting = <?php echo json_encode($tojsp); ?>;
  var uploadedfs = <?php echo json_encode($uploadedfs); ?>;

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
      if (check_attach(val, uploadedfs[val.id])) problem = 1;
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
