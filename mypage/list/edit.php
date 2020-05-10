<?php
require_once('../../set.php');
setup_session();
$titlepart = 'ファイル編集';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

//ファイル提出者のユーザーID
$author = basename($_GET["author"]);

//提出ID
$id = basename($_GET["id"]);

if ($author == "" or $id == "") die_mypage('パラメーターエラー');


//自分のファイルのみ編集可
if ($author != $_SESSION['userid']) die_mypage('ご自身のファイルのみ、編集が可能です。');

if (outofterm($id) != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;
if (!in_term() and !$outofterm) die_mypage('現在、ファイル提出期間外のため、ファイル操作は行えません。');


//入力済み情報を読み込む
$entereddata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
if ($entereddata["exam"] == 0 or $entereddata["editing"] == 1) die_mypage('現在、ファイルの確認待ちです。確認が完了するまでは、ファイルの編集が出来ません。');


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

if (isset($entereddata["submit"]) and $entereddata["submit"] != "") {
    $includeattach = TRUE;
    $method = 'direct';
} else $method = 'url';


//Javascriptに持って行く用　不要な要素をunset
$tojsp = $submitformdata;
$tojsp2 = json_encode($submitformdata["general"]);
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

//アップ済みのファイルのサイズ（jspでの引き算処理用）
$uploadedfs = array();

?>

<h1>ファイル編集</h1>
<p>変更したい項目のみ、入力欄の内容を変更して下さい（変更する内容によっては、運営チームによる承認が必要な可能性があります）。</p>
<form name="form" action="edit_handle.php" method="post" <?php
if ($includeattach) echo 'enctype="multipart/form-data" ';
?> onSubmit="return check();">
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="method" value="<?php echo $method; ?>">
<input type="hidden" name="author" value="<?php echo $author; ?>">
<input type="hidden" name="workid" value="<?php echo $id; ?>">
<?php
$submitformdata["general"]["detail"] = hsc($submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $submitformdata["general"]["detail"]);
$submitformdata["general"]["detail"] = str_replace("\n", "<br>", $submitformdata["general"]["detail"]);

if ($method == 'direct') {
    $uploadedfs["submitfile"] = array();
    $exts = str_replace(",", "・", $submitformdata["general"]["ext"]);
    if ($submitformdata["general"]["size"] != '') $filesize = $submitformdata["general"]["size"];
    else $filesize = FILE_MAX_SIZE;
    $currentsize = 0;

    if ($submitformdata["general"]["filenumber"] != "") {
        if ($submitformdata["general"]["filenumber"] == "1") $filenumexp = '1個のみアップロード可能　' . $filesize . 'MBまで';
        else $filenumexp = $submitformdata["general"]["filenumber"] . '個までアップロード可能　合計' . $filesize . 'MBまで';
    }
    else $filenumexp = '複数個アップロード可能　合計' . $filesize . 'MBまで';
    echo '<div class="form-group">提出ファイル（' . $exts . 'ファイル　' . $filenumexp . '）';
    echo '【必須】';
    echo '<font size="2">';
    if (isset($entereddata["submit"]) and $entereddata["submit"] != array()) {
        echo '<div>現在アップロードされているファイルを以下に表示します。<br>ファイル名をクリックするとそのファイルをダウンロードします。<br>ファイルを削除する場合は、そのファイルの左側にあるチェックボックスにチェックを入れて下さい。</div></font>';
        foreach ($entereddata["submit"] as $key => $element){
            echo '<div class="form-check">';
            echo '<input id="submitfile-delete-' . $key . '" class="form-check-input" type="checkbox" name="submitfile-delete[]" value="' . $key . '"';
            echo ' onChange="check_individual(&quot;submitfile&quot;);">';
            echo '<a href="../fnc/filedld.php?author=' . $userid . '&genre=submitmain&id=' . $id . '&partid=' . $key . '" target="_blank">' . hsc($element) . '</a>';
            $currentsize += filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $id . '/main_' . $key);
            $uploadedfs["submitfile"][$key] = filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $id . '/main_' . $key);
            echo '</div>';
        }
        echo '<input type="hidden" name="submitfile-currentsize" value="' . $currentsize . '">';
        echo '<input type="hidden" name="submitfile-already" value="' . count($entereddata["submit"]) . '">';
    }
    else {
        echo '<div>現在アップロードされているファイルはありません。</div></font>';
        echo '<input type="hidden" name="submitfile-delete[]" value="none">';
        echo '<input type="hidden" name="submitfile-already" value="0">';
        echo '<input type="hidden" name="submitfile-currentsize" value="0">';
    }
    echo '<font size="2"><label for="submitfile">ファイルを新規に追加する場合はこちらにアップロードして下さい：</label></font>';
    echo '<input type="file" class="form-control-file" id="submitfile" name="submitfile[]"';
    if ($submitformdata["general"]["filenumber"] != "1") echo ' multiple="multiple"';
    echo ' onChange="check_individual(&quot;submitfile&quot;);">';
    echo '<div id="submitfile-errortext" class="invalid-feedback" style="display: block;"></div>';
    if ($submitformdata["general"]["detail"] != "") echo '<font size="2">' . $submitformdata["general"]["detail"] . '</font>';
    echo '<div><font size="2"><b>※この項目の変更には、運営メンバーによる承認が必要です。</b></font></div>';
    echo '</div>';
} else {
    ?>
<div class="form-group">
<label for="url">提出ファイルのダウンロードURL【必須】</label>
<input type="text" name="url" class="form-control" id="url" value="<?php
echo hsc($entereddata["url"]);
?>" onBlur="check_individual(&quot;url&quot;);">
<div id="url-errortext" class="invalid-feedback" style="display: block;"></div>
<?php
if ($submitformdata["general"]["detail"] != "") echo '<font size="2">' . $submitformdata["general"]["detail"] . '</font>';
?>
<div><font size="2"><b>※この項目の変更には、運営メンバーによる承認が必要です。</b></font></div>
</div>
<div class="form-group">
<label for="dldpw">ファイルのダウンロードに必要なパスワード（あれば）</label>
<input type="text" name="dldpw" class="form-control" id="dldpw" value="<?php
if (isset($entereddata["dldpw"])) echo hsc($entereddata["dldpw"]);
?>" onBlur="check_individual(&quot;dldpw&quot;);">
<font size="2">※サービスによってパスワードの名称が異なります（「復号キー」など）。</font>
<div><font size="2">※この項目の変更は自動承認されます。</font></div>
</div>
<div class="form-group">
<label for="email">ファイルのダウンロード期限（あれば）</label>
<div>
<label for="from_date">
日付：
</label>
<input type="date" cmanCLDat="USE:ON" name="due_date" class="form-control" id="due_date" value="<?php
if (isset($entereddata["due"])) echo date('Y-m-d', $entereddata["due"]);
?>" onBlur="check_individual(&quot;due&quot;);">
</div>
<div>
<label for="from_time">
時刻（24時間制）：
</label>
<input type="time" name="due_time" id="due_time" value="<?php
if (isset($entereddata["due"])) echo date('H:i', $entereddata["due"]);
?>" onBlur="check_individual(&quot;due&quot;);">
</div>
<div id="due-errortext" class="invalid-feedback" style="display: block;"></div>
<font size="2">※ダウンロードURLに有効期限がある場合は必ず入力して下さい。入力が無い場合は、URLに有効期限が無いものとして扱います。<br>
※日付の欄をクリックするとカレンダーから日付を選べます。<br>
※時刻の欄についてはブラウザにより表示が異なります（ポップアップ画面が表示される、入力欄の横に上下のボタンが出る　など）。<br>
　時刻の欄をクリックしても何も出ない（通常のテキストボックスのようになっている）場合は、「時:分」の形で、半角で入力して下さい。</font>
 <div><font size="2">※この項目の変更は自動承認されます。</font></div>
</div>
<?php
}
?>
<div class="form-group">
<label for="title">タイトル（50文字以内）【必須】</label>
<input type="text" name="title" class="form-control" id="title" value="<?php
echo hsc($entereddata["title"]);
?>" onkeyup="ShowLength(value, &quot;title-counter&quot;);" onBlur="check_individual(&quot;title&quot;);">
<font size="2"><div id="title-counter" class="text-right text-md-left text-muted">現在 - 文字</div></font>
<div id="title-errortext" class="invalid-feedback" style="display: block;"></div>
<font size="2"><b>※この項目の変更には、運営メンバーによる承認が必要です。</b></font>
</div>
<?php
foreach ($submitformdata as $number => $data) {
    if ($data["type"] == "general") continue;
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
            if (isset($entereddata[$data["id"]])) echo ' value="' . hsc($entereddata[$data["id"]]) . '"';
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
            if (isset($entereddata[$data["id"] . "-1"])) echo ' value="' . hsc($entereddata[$data["id"] . "-1"]) . '"';
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
            if (isset($entereddata[$data["id"] . "-2"])) echo ' value="' . hsc($entereddata[$data["id"] . "-2"]) . '"';
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
            if (isset($entereddata[$data["id"]])) echo hsc($entereddata[$data["id"]]);
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
                if (isset($entereddata[$data["id"]]) and hsc($entereddata[$data["id"]]) == $choice) echo ' checked="checked"';
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
                if (isset($entereddata[$data["id"]]) and array_search($choice, $entereddata[$data["id"]]) !== FALSE) echo ' checked="checked"';
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
                if (isset($entereddata[$data["id"]]) and hsc($entereddata[$data["id"]]) == $choice) echo ' selected';
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
            echo '<font size="2">';
            if (isset($entereddata[$data["id"]]) and $entereddata[$data["id"]] != array()) {
                echo '<div>現在アップロードされているファイルを以下に表示します。<br>ファイル名をクリックするとそのファイルをダウンロードします。<br>ファイルを削除する場合は、そのファイルの左側にあるチェックボックスにチェックを入れて下さい。</div></font>';
                foreach ($entereddata[$data["id"]] as $key => $element){
                    echo '<div class="form-check">';
                    echo '<input id="custom-' . $data["id"] . '-delete-' . $key . '" class="form-check-input" type="checkbox" name="custom-' . $data["id"] . '-delete[]" value="' . $key . '"';
                    echo ' onChange="check_individual(' . $number . ');">';
                    echo '<a href="../fnc/filedld.php?author=' . $userid . '&genre=submitform&id=' . $id . '&partid=' . $data["id"] . '_' . $key . '" target="_blank">' . hsc($element) . '</a>';
                    $currentsize += filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $id . '/' . $data["id"] . '_' . $key);
                    $uploadedfs[$data["id"]][$key] = filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $id . '/' . $data["id"] . '_' . $key);
                    echo '</div>';
                }
                echo '<input type="hidden" name="custom-' . $data["id"] . '-currentsize" value="' . $currentsize . '">';
                echo '<input type="hidden" name="custom-' . $data["id"] . '-already" value="' . count($entereddata[$data["id"]]) . '">';
            }
            else {
                echo '<div>現在アップロードされているファイルはありません。</div></font>';
                echo '<input type="hidden" name="custom-' . $data["id"] . '-delete[]" value="none">';
                echo '<input type="hidden" name="custom-' . $data["id"] . '-already" value="0">';
                echo '<input type="hidden" name="custom-' . $data["id"] . '-currentsize" value="0">';
            }
            echo '<font size="2"><label for="custom-' . $data["id"] . '">ファイルを新規に追加する場合はこちらにアップロードして下さい：</label></font>';
            echo '<input type="file" class="form-control-file" id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '[]"';
            if ($data["filenumber"] != "1") echo ' multiple="multiple"';
            echo ' onChange="check_individual(' . $number . ');">';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="invalid-feedback" style="display: block;"></div>';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
        break;
    }
    if ($data["recheck"] != "auto") echo '<div><font size="2"><b>※この項目の変更には、運営メンバーによる承認が必要です。</b></font></div>';
    else echo '<div><font size="2">※この項目の変更は自動承認されます。</font></div>';
    echo '</div>';
}
?>
<br>
※送信前に、入力内容の確認をお願い致します。<br>
<button type="submit" class="btn btn-primary">送信する</button>
</div>
<?php
echo_modal_alert();
echo_modal_confirm(null, null, null, null, null, null, null, null, "closesubmit();");
echo_modal_wait();
?>
</form>
<script type="text/javascript">
<!--
function check_individual(id) {
  var valid = 1;
  var setting = <?php echo json_encode($tojsp); ?>;
  var uploadedfs = <?php echo json_encode($uploadedfs); ?>;

  if (id === "submitfile") {
    var val = <?php echo $tojsp2; ?>;
    check_submitfile(val, uploadedfs["submitfile"]);
    return;
  }
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
    check_attach(val, uploadedfs[val.id]);
  }
}

function check(){
  var problem = 0;
  var valid = 1;
  var setting = <?php echo json_encode($tojsp);?>;
  var uploadedfs = <?php echo json_encode($uploadedfs); ?>;


<?php if ($method == "direct") echo <<<EOT
  var val = $tojsp2;
  if (check_submitfile(val, uploadedfs["submitfile"])) problem = 1;
EOT;
else echo <<<EOT
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
EOT;
?>

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
