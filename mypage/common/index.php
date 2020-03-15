<?php
require_once('../../set.php');
session_start();
$titlepart = '共通情報の入力・編集';
require_once(PAGEROOT . 'mypage_header.php');

if ($_SESSION["situation"] == 'file_submitted') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
ファイルの提出が完了しました。<br>
ファイル内容を運営チームが確認するまでしばらくお待ち願います。<br><br>
ファイル確認の結果、ファイルの再提出が必要になる可能性がありますので、<b>制作に使用した素材などは、しばらくの間消去せずに残しておいて下さい</b>。<br><br>
続いて、共通情報の入力を行って下さい。
</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'file_submitted_auto_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
ファイルの提出が完了しました。<br>
ファイル確認の権限があるユーザー（主催者・共同運営者）があなたの他にいないため、この作品は<b>自動的に承認されました</b>。<br><br>
続いて、共通情報の入力を行って下さい。
</div>';
    $_SESSION["situation"] = '';
}

if ($_SESSION["situation"] == 'common_submitted') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
共通情報の変更が完了しました。<br>
変更内容を運営チームが確認するまでしばらくお待ち願います。</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'common_autoaccept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
共通情報の変更が完了しました。<br>
自動承認される項目のみ変更されていたため、変更は自動的に承認されました。</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'common_submitted_auto_accept') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
共通情報の変更が完了しました。<br>
共通情報確認の権限があるユーザー（主催者・共同運営者）があなたの他にいないため、変更は自動的に承認されました。</div>';
    $_SESSION["situation"] = '';
}
if ($_SESSION["situation"] == 'common_nochange') {
    echo '<div class="border border-success" style="padding:10px; margin-top:1em; margin-bottom:1em;">
入力内容の変更はありませんでした。</div>';
    $_SESSION["situation"] = '';
}

//非参加者以外
if ($_SESSION["state"] != 'o') $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>非参加者以外のユーザー</b>です。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

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
    $userformdata[$i] = json_decode(file_get_contents(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
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
$entereddata = json_decode(file_get_contents(DATAROOT . "users/" . $userid . ".txt"), true);

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
if ($userformdata == array()) die_mypage('<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">
設定可能な項目はありません。
</div>');
if (!before_deadline() and $disable) echo '<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在、ファイル提出期間外です。入力内容の確認は出来ますが、変更は出来ません。
</div>';
else {
    if (!before_deadline() and $_SESSION["state"] == 'p') echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在ファイル提出期間外ですが、主催者は常時共通情報の編集が可能です。
</div>';
    else if (!before_deadline()) echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在ファイル提出期間外ですが、あなたは主催者から共通情報の編集を許可されています（' . date('Y年n月j日G時i分s秒', outofterm('userform')) . 'まで）。
</div>';
}
if ($waiting) {
    echo '<div class="border border-danger" style="padding:10px; margin-top:1em; margin-bottom:1em;">
現在、共通情報の確認待ちです。確認が完了するまでは、共通情報の編集が出来ません。
</div>';
}
echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
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
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<input type="hidden" name="successfully" value="1">
<?php
foreach ($userformdata as $number => $data) {
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
            if (isset($entereddata[$data["id"]])) echo ' value="' . htmlspecialchars($entereddata[$data["id"]]) . '"';
            if ($disable) echo ' disabled="disabled"';
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
            if (isset($entereddata[$data["id"] . "-1"])) echo ' value="' . htmlspecialchars($entereddata[$data["id"] . "-1"]) . '"';
            if ($disable) echo ' disabled="disabled"';
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
            if (isset($entereddata[$data["id"] . "-2"])) echo ' value="' . htmlspecialchars($entereddata[$data["id"] . "-2"]) . '"';
            if ($disable) echo ' disabled="disabled"';
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
            if ($disable) echo ' disabled="disabled"';
            echo ' onkeyup="ShowLength(value, &quot;custom-' . $data["id"] . '-counter&quot;);" onBlur="check_individual(' . $number . ');">';
            if (isset($entereddata[$data["id"]])) echo htmlspecialchars($entereddata[$data["id"]]);
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
                if (isset($entereddata[$data["id"]]) and htmlspecialchars($entereddata[$data["id"]]) == $choice) echo ' checked="checked"';
                if ($disable) echo ' disabled="disabled"';
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
                if (isset($entereddata[$data["id"]]) and array_search($choice, $entereddata[$data["id"]]) !== FALSE) echo ' checked="checked"';
                if ($disable) echo ' disabled="disabled"';
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
            if ($disable) echo ' disabled="disabled"';
            echo ' onChange="check_individual(' . $number . ');">';
            echo '<option value="">【選択して下さい】</option>';
            foreach ($choices as $choice) {
                $choice = htmlspecialchars($choice);
                echo '<option value="' . $choice . '"';
                if (isset($entereddata[$data["id"]]) and htmlspecialchars($entereddata[$data["id"]]) == $choice) echo ' selected';
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
            echo '<div class="form-group">' . htmlspecialchars($data["title"]) . '（' . $exts . 'ファイル　' . $filenumexp . '）';
            if ($data["required"] == "1") echo '【必須】';
            echo '<font size="2">';
            if (isset($entereddata[$data["id"]]) and $entereddata[$data["id"]] != array()) {
                echo '<div>現在アップロードされているファイルを以下に表示します。<br>ファイル名をクリックするとそのファイルをダウンロードします。<br>ファイルを削除する場合は、そのファイルの左側にあるチェックボックスにチェックを入れて下さい。</div></font>';
                foreach ($entereddata[$data["id"]] as $key => $element){
                    echo '<div class="form-check">';
                    echo '<input id="custom-' . $data["id"] . '-delete-' . $key . '" class="form-check-input" type="checkbox" name="custom-' . $data["id"] . '-delete[]" value="' . $key . '"';
                    if ($disable) echo ' disabled="disabled"';
                    echo ' onChange="check_individual(' . $number . ');">';
                    echo '<a href="../fnc/filedld.php?author=' . $userid . '&genre=userform&id=' . $data["id"] . '_' . $key . '" target="_blank">' . htmlspecialchars($element) . '</a>';
                    $currentsize += filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $data["id"] . '_' . $key);
                    $uploadedfs[$data["id"]][$key] = filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $data["id"] . '_' . $key);
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
            if ($disable) echo ' disabled="disabled"';
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
<button type="submit" class="btn btn-primary"<?php
if ($disable) echo ' disabled="disabled"';
?>>送信する</button>
</div>
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
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
<button type="button" id="submitbtn" onclick="closesubmit();" class="btn btn-primary">送信する</button>
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

// -->
</script>
<?php
include(PAGEROOT . 'validate_script.php');
require_once(PAGEROOT . 'mypage_footer.php');
