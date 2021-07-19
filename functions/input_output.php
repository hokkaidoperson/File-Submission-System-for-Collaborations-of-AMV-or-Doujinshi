<?php
//入出力関連関数

//マイページ表示画面向けのdie関数（テキストを表示して、フッターをちゃんと出してからdieする）
//マイページ画面向けでないやつ（handle.phpとか）だったらdieで充分
function die_mypage($echo = "") {
    global $eventname;
    global $siteurl;
    echo $echo;
    require_once(PAGEROOT . 'mypage_footer.php');
    die();
}

//普通のmb_strlenだと改行が2文字扱いになるのでそれを避ける
function length_with_lb($string) {
    $string = str_replace(array("\r\n", "\r", "\n"), " ", $string);
    return mb_strlen($string);
}

//文字列をHTML出力しても大丈夫なようにし、更に改行タグを付与
function give_br_tag($string) {
    $string = hsc($string);
    $string = str_replace(array("\r\n", "\r", "\n"), "\n", $string);
    return str_replace("\n", "<br>", $string);
}

//htmlspecialcharsのショートカット
function hsc($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

//リダイレクトして終了
function redirect($to) {
    header("Location: $to");
    exit;
}

//確認modalのエコー
function echo_modal_confirm($body = null, $title = null, $dismiss = null, $dismiss_class = null, $send = null, $send_class = null, $meta_modal_id = null, $meta_send_id = null, $meta_send_onclick = null) {
    if (is_null($body)) $body = "<p>入力内容に問題は見つかりませんでした。</p><p>現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。</p>";
    if (is_null($title)) $title = "送信確認";
    if (is_null($dismiss)) $dismiss = '<i class="bi bi-x"></i> 戻る';
    if (is_null($dismiss_class)) $dismiss_class = "secondary";
    if (is_null($send)) $send = '<i class="bi bi-check-circle-fill"></i> 送信する';
    if (is_null($send_class)) $send_class = "primary";
    if (is_null($meta_modal_id)) $meta_modal_id = "confirmmodal";
    if (is_null($meta_send_id)) $meta_send_id = "submitbtn";
    if (is_null($meta_send_onclick)) $meta_send_onclick = 'document.getElementById("submitbtn").disabled = "disabled"; document.form.submit();';
    echo <<<EOT
<div class="modal fade" id="$meta_modal_id" tabindex="-1" role="dialog" aria-labelledby="{$meta_modal_id}title" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="{$meta_modal_id}title">$title</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
$body
</div>
<div class="modal-footer">
<button type="button" class="btn btn-$dismiss_class" data-dismiss="modal">$dismiss</button>
<button type="button" class="btn btn-$send_class" id="$meta_send_id" onClick='$meta_send_onclick'>$send</button>
</div>
</div>
</div>
</div>
EOT;
}

//アラートmodalのエコー（大体フォームの内容エラーだと思われ）
function echo_modal_alert($body = null, $title = null, $dismiss = null, $dismiss_class = null, $meta_modal_id = null, $meta_dismiss_id = null) {
    if (is_null($body)) $body = "<p>入力内容に問題が見つかりました。<br>お手数ですが、表示されているエラー内容を参考に、入力内容の確認・修正をお願いします。</p><p>修正後、再度「送信する」を押して下さい。</p>";
    if (is_null($title)) $title = "入力内容の修正が必要です";
    if (is_null($dismiss)) $dismiss = "OK";
    if (is_null($dismiss_class)) $dismiss_class = "primary";
    if (is_null($meta_modal_id)) $meta_modal_id = "errormodal";
    if (is_null($meta_dismiss_id)) $meta_dismiss_id = "dismissbtn";
    echo <<<EOT
<div class="modal fade" id="$meta_modal_id" tabindex="-1" role="dialog" aria-labelledby="{$meta_modal_id}title" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="{$meta_modal_id}title">$title</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
$body
</div>
<div class="modal-footer">
<button type="button" class="btn btn-$dismiss_class" data-dismiss="modal" id="$meta_dismiss_id">$dismiss</button>
</div>
</div>
</div>
</div>
EOT;
}

//待機modal（処理に時間が掛かる時）
//他と違い、ユーザーの操作では消失不可
function echo_modal_wait($body = null, $title = null, $meta_modal_id = null) {
    if (is_null($body)) $body = "入力内容・ファイルを送信中です。<br>画面が自動的に推移するまでしばらくお待ち下さい。";
    if (is_null($title)) $title = "送信中…";
    if (is_null($meta_modal_id)) $meta_modal_id = "sendingmodal";
    echo <<<EOT
<div class="modal fade" id="$meta_modal_id" tabindex="-1" role="dialog" aria-labelledby="{$meta_modal_id}title" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="{$meta_modal_id}title">$title</h5>
</div>
<div class="modal-body">
$body
</div>
</div>
</div>
</div>
EOT;
}

//上部表示用のアラートを登録（handle系のページで使う用）
function register_alert($body, $class = "primary") {
    if (!isset($_SESSION["alerts_holder"])) $_SESSION["alerts_holder"] = array();
    $_SESSION["alerts_holder"][] = array("body" => $body, "class" => $class);
}

//register_alertで登録したアラートを表示
function output_alert() {
    if (!is_array($_SESSION["alerts_holder"])) return;
    foreach ($_SESSION["alerts_holder"] as $contents) {
        echo <<<EOT
<div class="alert alert-{$contents["class"]} alert-dismissible fade show system-alert-closable-spacer" role="alert">
{$contents["body"]}
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
EOT;
    }
    unset($_SESSION["alerts_holder"]);
}

//普通にアラートを表示
function echo_alert($body, $class = "primary", $not_dismissable = FALSE) {
    if (!$not_dismissable) echo <<<EOT
<div class="alert alert-$class alert-dismissible fade show system-alert-closable-spacer" role="alert">
$body
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
EOT;
    else echo <<<EOT
<div class="alert alert-$class system-alert-spacer" role="alert">
$body
</div>
EOT;
}

//エラー表示
function die_error_html($title, $body, $head_include = "") {
    echo '<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">';
    if (isset($head_include)) echo $head_include;
    echo '<title>' . $title . '</title>
</head>
<body>' . $body . '</body>
</html>';
    die();
}

//タイトルと接頭・接尾辞、詳細はHTMLタグ使える（呼び出す側でエスケープ）
//配列指定（title、name、idは必須）
//title, name, id, prefill, type, showcounter(boolean), detail, jspart, prefix, suffix, width, disabled(boolean), confirmation(boolean), additional_feedback
function echo_textbox($array) {
    if (!isset($array["prefill"])) $array["prefill"] = "";
    if (!isset($array["type"])) $array["type"] = "text";
    echo '<div class="form-group"><label for="' . hsc($array["id"]) . '">' . $array["title"] . '</label>';
    if (isset($array["confirmation"]) and $array["confirmation"]) echo '<div class="mb-1">';
    if (isset($array["width"]) and $array["width"] != "") echo '<div class="system-variable-input-out"><div class="input-group system-variable-input-in">';
    else echo '<div class="input-group">';
    if (isset($array["prefix"]) and $array["prefix"] != "") echo '<div class="input-group-prepend"><span class="input-group-text">' . $array["prefix"] . '</span></div>';
    echo '<input type="' . hsc($array["type"]) . '" name="' . hsc($array["name"]) . '" class="form-control system-variable-input-toggle" id="' . hsc($array["id"]) . '" value="' . hsc($array["prefill"]) . '"';
    if (isset($array["width"]) and $array["width"] != "") echo ' style="width:' . hsc($array["width"]) . 'em;"';
    if (isset($array["showcounter"]) and $array["showcounter"]) echo ' onkeyup="ShowLength(value, &quot;' . hsc($array["id"]) . '-counter&quot;);"';
    if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
    if (isset($array["jspart"]) and $array["jspart"] != "") echo ' ' . $array["jspart"];
    echo ">";
    if (isset($array["suffix"]) and $array["suffix"] != "") echo '<div class="input-group-append"><span class="input-group-text">' . $array["suffix"] . '</span></div>';
    if (isset($array["width"]) and $array["width"] != "") echo '</div></div>';
    else echo "</div>";
    if (isset($array["showcounter"]) and $array["showcounter"]) echo '<div id="' . hsc($array["id"]) . '-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>';
    if (isset($array["confirmation"]) and $array["confirmation"]) {
        echo '</div>';
        if (isset($array["width"]) and $array["width"] != "") echo '<div class="system-variable-input-out"><div class="input-group system-variable-input-in">';
        else echo '<div class="input-group">';
        if (isset($array["prefix"]) and $array["prefix"] != "") echo '<div class="input-group-prepend"><span class="input-group-text">' . $array["prefix"] . '</span></div>';
        echo '<input type="' . hsc($array["type"]) . '" name="' . hsc($array["name"]) . '_confirmation" class="form-control system-variable-input-toggle" id="' . hsc($array["id"]) . '_confirmation" value="' . hsc($array["prefill"]) . '" placeholder="確認の為再入力…"';
        if (isset($array["width"]) and $array["width"] != "") echo ' style="width:' . hsc($array["width"]) . 'em;"';
        if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
        if (isset($array["jspart"]) and $array["jspart"] != "") echo ' ' . $array["jspart"];
        echo ">";
        if (isset($array["suffix"]) and $array["suffix"] != "") echo '<div class="input-group-append"><span class="input-group-text">' . $array["suffix"] . '</span></div>';
        if (isset($array["width"]) and $array["width"] != "") echo '</div></div>';
        else echo "</div>";
    }
    echo '<div id="' . hsc($array["id"]) . '-errortext" class="system-form-error"></div>';
    if (isset($array["additional_feedback"])) echo $array["additional_feedback"];
    if (isset($array["detail"])) echo '<small class="form-text">' . $array["detail"] . '</small>';
    echo '</div>';
}

function echo_textbox_mlt($settings, $title, $id, $name, $detail = "", $type = "text", $horizontally = FALSE) {
    echo '<div class="form-group">' . $title;
    if ($horizontally) echo '<div class="d-flex flex-wrap">';
    foreach ($settings as $num => $array) {
        if ($horizontally) echo '<div class="mb-1 mr-1">';
        else echo '<div class="mb-1">';
        if (!isset($array["prefill"])) $array["prefill"] = "";
        if (isset($array["width"]) and $array["width"] != "") echo '<div class="system-variable-input-out"><div class="input-group system-variable-input-in">';
        else echo '<div class="input-group">';
        if (isset($array["prefix"]) and $array["prefix"] != "") echo '<div class="input-group-prepend"><span class="input-group-text">' . $array["prefix"] . '</span></div>';
        echo '<input type="' . hsc($type) . '" name="' . hsc($name) . '[' . $num . ']" class="form-control system-variable-input-toggle" id="' . hsc($id) . '[' . $num . ']" value="' . hsc($array["prefill"]) . '"';
        if (isset($array["placeholder"]) and $array["placeholder"] != "") echo ' placeholder="' . hsc($array["placeholder"]) . '"';
        if (isset($array["width"]) and $array["width"] != "") echo ' style="width:' . hsc($array["width"]) . 'em;"';
        if (isset($array["showcounter"]) and $array["showcounter"]) echo ' onkeyup="ShowLength(value, &quot;' . hsc($id) . '-' . $num . '-counter&quot;);"';
        if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
        if (isset($array["jspart"]) and $array["jspart"] != "") echo ' ' . $array["jspart"];
        echo ">";
        if (isset($array["suffix"]) and $array["suffix"] != "") echo '<div class="input-group-append"><span class="input-group-text">' . $array["suffix"] . '</span></div>';
        if (isset($array["width"]) and $array["width"] != "") echo '</div></div>';
        else echo "</div>";
        if (isset($array["showcounter"]) and $array["showcounter"]) echo '<div id="' . hsc($id) . '-' . $num . '-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>';
        echo '<div id="' . hsc($id) . '[' . $num . ']-errortext" class="system-form-error"></div>';
        echo '</div>';
    }
    if ($horizontally) echo '</div>';
    if ($detail != "") echo '<small class="form-text">' . $detail . '</small>';
    echo '</div>';
}

//title, name, id, prefill, showcounter(boolean), detail, jspart, width, height, disabled(boolean)
function echo_textarea($array) {
    if (!isset($array["prefill"])) $array["prefill"] = "";
    if (!isset($array["height"])) $array["height"] = "";
    echo '<div class="form-group"><label for="' . hsc($array["id"]) . '">' . $array["title"] . '</label>';
    if (isset($array["width"]) and $array["width"] != "") echo '<div class="input-group" style="width:' . hsc($array["width"]) . 'em;">';
    else echo '<div class="input-group">';
    if ($array["height"] == "") $array["height"] = "5";
    echo '<textarea id="' . hsc($array["id"]) . '" name="' . hsc($array["name"]) . '" rows="' . hsc($array["height"]) . '" class="form-control"';
    if (isset($array["showcounter"]) and $array["showcounter"]) echo ' onkeyup="ShowLength(value, &quot;' . hsc($array["id"]) . '-counter&quot;);"';
    if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
    if (isset($array["jspart"]) and $array["jspart"] != "") echo ' ' . $array["jspart"];
    echo ">";
    echo hsc($array["prefill"]) . '</textarea>';
    echo '</div>';
    if (isset($array["showcounter"]) and $array["showcounter"]) echo '<div id="' . hsc($array["id"]) . '-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>';
    echo '<div id="' . hsc($array["id"]) . '-errortext" class="system-form-error"></div>';
    if (isset($array["detail"])) echo '<small class="form-text">' . $array["detail"] . '</small>';
    echo '</div>';
}

function echo_textarea_mlt($settings, $title, $id, $name, $detail = "") {
    echo '<div class="form-group">' . $title;
    foreach ($settings as $num => $array) {
        echo '<div class="mb-1">';
        if (!isset($array["prefill"])) $array["prefill"] = "";
        if (!isset($array["height"])) $array["height"] = "";
        if (isset($array["width"]) and $array["width"] != "") echo '<div class="input-group" style="width:' . hsc($array["width"]) . 'em;">';
        else echo '<div class="input-group">';
        if ($array["height"] == "") $array["height"] = "5";
        echo '<textarea id="' . hsc($id) . '[' . $num . ']" name="' . hsc($name) . '[' . $num . ']" rows="' . hsc($array["height"]) . '" class="form-control"';
        if (isset($array["placeholder"]) and $array["placeholder"] != "") echo ' placeholder="' . hsc($array["placeholder"]) . '"';
        if (isset($array["showcounter"]) and $array["showcounter"]) echo ' onkeyup="ShowLength(value, &quot;' . hsc($id) . '-' . $num . '-counter&quot;);"';
        if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
        if (isset($array["jspart"]) and $array["jspart"] != "") echo ' ' . $array["jspart"];
        echo ">";
        echo hsc($array["prefill"]) . '</textarea>';
        echo "</div>";
        if (isset($array["showcounter"]) and $array["showcounter"]) echo '<div id="' . hsc($id) . '-' . $num . '-counter" class="small text-right text-md-left text-muted">現在 - 文字</div>';
        echo '<div id="' . hsc($id) . '[' . $num . ']-errortext" class="system-form-error"></div>';
        echo '</div>';
    }
    if ($detail != "") echo '<small class="form-text">' . $detail . '</small>';
    echo '</div>';
}

//title, name, id, choices, values, prefill, horizontally(boolean), detail, jspart, disabled(boolean)
//choicesは配列、HTMLタグ可　valueは指定無しの場合選択肢番号0から　prefillはvaluesと照らし合わせ
function echo_radio($array) {
    if (!isset($array["prefill"])) $array["prefill"] = "";
    echo '<div class="form-group" id="' . $array["id"] . '">' . $array["title"];
    if (isset($array["horizontally"]) and $array["horizontally"]) echo '<div>';
    foreach ($array["choices"] as $num => $choice) {
        if (isset($array["horizontally"]) and $array["horizontally"]) echo '<div class="form-check form-check-inline">';
        else echo '<div class="form-check">';
        if (isset($array["values"])) $value = $array["values"][$num];
        else $value = $num;
        echo '<input id="' . hsc($array["id"]) . '[' . $num . ']" class="form-check-input" type="radio" name="' . hsc($array["name"]) . '" value="' . hsc($value) . '"';
        if ($array["prefill"] !== "" and $value == $array["prefill"]) echo ' checked="checked"';
        if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
        if (isset($array["jspart"]) and $array["jspart"] != "") echo ' ' . $array["jspart"];
        echo ">";
        echo '<label class="form-check-label" for="' . hsc($array["id"]) . '[' . $num . ']">' . $choice . '</label>';
        echo '</div>';
    }
    if (isset($array["horizontally"]) and $array["horizontally"]) echo '</div>';
    echo '<div id="' . hsc($array["id"]) . '-errortext" class="system-form-error"></div>';
    if (isset($array["detail"])) echo '<small class="form-text">' . $array["detail"] . '</small>';
    echo '</div>';
}

//title, name, id, choices, values, prefill, horizontally(boolean), detail, jspart, disabled(boolean)
function echo_check($array) {
    if (!isset($array["prefill"])) $array["prefill"] = [];
    echo '<div class="form-group" id="' . $array["id"] . '[]">' . $array["title"];
    if (isset($array["horizontally"]) and $array["horizontally"]) echo '<div>';
    foreach ($array["choices"] as $num => $choice) {
        if (isset($array["horizontally"]) and $array["horizontally"]) echo '<div class="form-check form-check-inline">';
        else echo '<div class="form-check">';
        if (isset($array["values"])) $value = $array["values"][$num];
        else $value = $num;
        echo '<input id="' . hsc($array["id"]) . '[' . $num . ']" class="form-check-input" type="checkbox" name="' . hsc($array["name"]) . '[]" value="' . hsc($value) . '"';
        if (array_search($value, $array["prefill"]) !== FALSE and array_search($value, $array["prefill"]) !== NULL) echo ' checked="checked"';
        if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
        if (isset($array["jspart"]) and $array["jspart"] != "") echo ' ' . $array["jspart"];
        echo ">";
        echo '<label class="form-check-label" for="' . hsc($array["id"]) . '[' . $num . ']">' . $choice . '</label>';
        echo '</div>';
    }
    if (isset($array["horizontally"]) and $array["horizontally"]) echo '</div>';
    echo '<div id="' . hsc($array["id"]) . '[]-errortext" class="system-form-error"></div>';
    if (isset($array["detail"])) echo '<small class="form-text">' . $array["detail"] . '</small>';
    echo '</div>';
}

//title, name, id, choices, values, prefill, detail, jspart, prefix, suffix, disabled(boolean)
//このchoiceはHTML不可
function echo_dropdown($array) {
    if (!isset($array["prefill"])) $array["prefill"] = "";
    echo '<div class="form-group"><label for="' . hsc($array["id"]) . '">' . $array["title"] . '</label>';
    echo '<div class="input-group">';
    if (isset($array["prefix"]) and $array["prefix"] != "") echo '<div class="input-group-prepend"><span class="input-group-text">' . $array["prefix"] . '</span></div>';
    echo '<select id="' . hsc($array["id"]) . '" class="form-control" name="' . hsc($array["name"]) . '"';
    if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
    if (isset($array["jspart"]) and $array["jspart"] != "") echo ' ' . $array["jspart"];
    echo ">";
    echo '<option value="">【選択して下さい】</option>';
    foreach ($array["choices"] as $num => $choice) {
        if (isset($array["values"])) $value = $array["values"][$num];
        else $value = $num;
        echo '<option value="' . hsc($value) . '"';
        if ($array["prefill"] !== "" and $array["prefill"] == $value) echo ' selected';
        echo '>' . hsc($choice) . '</option>';
    }
    echo '</select>';
    if (isset($array["suffix"]) and $array["suffix"] != "") echo '<div class="input-group-append"><span class="input-group-text">' . $array["suffix"] . '</span></div>';
    echo '</div>';
    echo '<div id="' . hsc($array["id"]) . '-errortext" class="system-form-error"></div>';
    if (isset($array["detail"])) echo '<small class="form-text">' . $array["detail"] . '</small>';
    echo '</div>';
}

function echo_dropdown_mlt($settings, $title, $id, $name, $detail = "") {
    echo '<div class="form-group">' . $title;
    foreach ($settings as $num => $array) {
        echo '<div class="mb-1">';
        if (!isset($array["prefill"])) $array["prefill"] = "";
        echo '<div class="input-group">';
        if (isset($array["prefix"]) and $array["prefix"] != "") echo '<div class="input-group-prepend"><span class="input-group-text">' . $array["prefix"] . '</span></div>';
        echo '<select id="' . hsc($id) . '[' . $num . ']" class="form-control" name="' . hsc($name) . '[' . $num . ']"';
        if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
        if (isset($array["jspart"]) and $array["jspart"] != "") echo ' ' . $array["jspart"];
        echo ">";
        echo '<option value="">【選択して下さい】</option>';
        foreach ($array["choices"] as $num2 => $choice) {
            if (isset($array["values"])) $value = $array["values"][$num2];
            else $value = $num2;
            echo '<option value="' . hsc($value) . '"';
            if ($array["prefill"] !== "" and $array["prefill"] == $value) echo ' selected';
            echo '>' . hsc($choice) . '</option>';
        }
        echo '</select>';
        if (isset($array["suffix"]) and $array["suffix"] != "") echo '<div class="input-group-append"><span class="input-group-text">' . $array["suffix"] . '</span></div>';
        echo '</div>';
        echo '<div id="' . hsc($id) . '[' . $num . ']-errortext" class="system-form-error"></div>';
        echo '</div>';
    }
    if ($detail != "") echo '<small class="form-text">' . $detail . '</small>';
    echo '</div>';
}

//title, name, id, prefill[date, time], detail, jspart[date, time], disabled(boolean)
function echo_datetime($array) {
    if (!isset($array["prefill"])) $array["prefill"] = ["", ""];
    echo '<div class="form-group"><div>' . $array["title"] . '</div>';
    echo '<label for="' . $array["id"] . '_date">日付：</label><input type="date" cmanCLDat="USE:ON" name="' . $array["name"] . '_date" id="' . $array["id"] . '_date" class="form-control w-auto d-inline-block" value="' . $array["prefill"][0] . '"';
    if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
    if (isset($array["jspart"][0]) and $array["jspart"][0] != "") echo ' ' . $array["jspart"][0];
    echo ">";
    echo '<div id="' . $array["id"] . '_date-errortext" class="system-form-error"></div><label for="' . $array["id"] . '_time">時刻（24時間制）：</label><input type="time" name="' . $array["name"] . '_time" id="' . $array["id"] . '_time" class="form-control w-auto d-inline-block" value="' . $array["prefill"][1] . '"';
    if (isset($array["disabled"]) and $array["disabled"]) echo ' disabled="disabled"';
    if (isset($array["jspart"][1]) and $array["jspart"][1] != "") echo ' ' . $array["jspart"][1];
    echo ">";
    echo '<div id="' . $array["id"] . '_time-errortext" class="system-form-error"></div>';
    if (isset($array["detail"])) echo '<small class="form-text">' . $array["detail"] . '</small>';
    echo '</div>';
}

function echo_buttons($colors, $types, $texts, $attention = NULL, $onclick = [], $disable = []) {
    echo '<div class="pt-3">';
    if ($attention !== NULL) echo $attention . '<br>';
    foreach ($texts as $key => $text) {
        echo '<button type="' . $types[$key] . '" class="btn btn-' . $colors[$key] . '"';
        if (isset($onclick[$key])) echo ' onClick="' . $onclick[$key] . '"';
        if (isset($disable[$key]) and $disable[$key]) echo ' disabled="disabled"';
        echo '>' . $text . '</button> ';
    }
    echo '</div>';
}

function choices_array($choices_string, $escape = FALSE) {
    $choices = str_replace(array("\r\n", "\r", "\n"), "\n", $choices_string);
    $choices = explode("\n", $choices);
    $choices = array_map('trim', $choices);
    if ($escape) $choices = array_map('hsc', $choices);
    //参考　https://www.hachi-log.com/php-arrayfilter-arrayvalue/
    $choices = array_filter($choices);
    $choices = array_values($choices);
    return $choices;
}

//カスタムフォーム出力
function echo_custom_item($data, $editing = FALSE, $disable = FALSE, $prefilleddata = [], $workid = "common") {
    //detail中のURLにリンクを振る（正規表現参考　https://www.megasoft.co.jp/mifes/seiki/s310.html）　あとHTMLタグが無いようにする・改行反映
    $data["detail"] = hsc($data["detail"]);
    $data["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $data["detail"]);
    $data["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $data["detail"]);
    $data["detail"] = str_replace("\n", "<br>", $data["detail"]);
    if ($editing) {
        if ($data["recheck"][0] != "auto") $data["detail"] .= '<div><strong>※この項目の変更には、運営メンバーによる承認が必要です。</strong></div>';
        else $data["detail"] .= '<div>※この項目の変更は自動承認されます。</div>';
    }

    switch ($data["type"]) {
        case "textbox":
            $parttitle = hsc($data["title"]);
            if ($data["quantity"] > 1) {
                $settings = [];
                for ($i = 0; $i < $data["quantity"]; $i++) {
                    $placeholder = "";
                    if ($data["min"][$i] != "") $placeholder .= $data["min"][$i] . '文字以上';
                    if ($data["max"][$i] != "") $placeholder .= $data["max"][$i] . '文字以内';
                    $settings[$i] = [
                        "prefill" => isset($prefilleddata[$i]) ? $prefilleddata[$i] : "",
                        "showcounter" => TRUE,
                        "jspart" => 'onChange="validation_call_custom(&quot;custom-' . $data["id"] . '[' . $i . ']&quot;);"',
                        "prefix" => hsc($data["prefix"][$i]),
                        "suffix" => hsc($data["suffix"][$i]),
                        "width" => $data["width"][$i],
                        "placeholder" => $placeholder,
                        "disabled" => $disable
                    ];
                }
                if ($data["required"] == "1") $parttitle .= '【いずれか必須】';
                else if ($data["required"] == "2") $parttitle .= '【全て必須】';
                echo_textbox_mlt($settings, $parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], $data["detail"], "text", ($data["arrangement"][0] == "h"));
            } else {
                $placeholder = "";
                if ($data["min"][0] != "") $placeholder .= $data["min"][0] . '文字以上';
                if ($data["max"][0] != "") $placeholder .= $data["max"][0] . '文字以内';
                if ($placeholder != "") $parttitle .= '（' . $placeholder . '）';
                if ($data["required"] != "0") $parttitle .= '【必須】';
                echo_textbox([
                    "title" => $parttitle,
                    "name" => 'custom-' . $data["id"] . '[0]',
                    "id" => 'custom-' . $data["id"] . '[0]',
                    "prefill" => isset($prefilleddata[0]) ? $prefilleddata[0] : "",
                    "showcounter" => TRUE,
                    "detail" => $data["detail"],
                    "jspart" => 'onChange="validation_call_custom(&quot;custom-' . $data["id"] . '[0]&quot;);"',
                    "prefix" => hsc($data["prefix"][0]),
                    "suffix" => hsc($data["suffix"][0]),
                    "width" => $data["width"][0],
                    "disabled" => $disable
                ]);
            }
        break;
        case "textarea":
            $parttitle = hsc($data["title"]);
            if ($data["quantity"] > 1) {
                $settings = [];
                for ($i = 0; $i < $data["quantity"]; $i++) {
                    $placeholder = "";
                    if ($data["min"][$i] != "") $placeholder .= $data["min"][$i] . '文字以上';
                    if ($data["max"][$i] != "") $placeholder .= $data["max"][$i] . '文字以内';
                    $settings[$i] = [
                        "prefill" => isset($prefilleddata[$i]) ? $prefilleddata[$i] : "",
                        "showcounter" => TRUE,
                        "jspart" => 'onChange="validation_call_custom(&quot;custom-' . $data["id"] . '[' . $i . ']&quot;);"',
                        "width" => $data["width"][$i],
                        "height" => $data["height"][$i],
                        "placeholder" => $placeholder,
                        "disabled" => $disable
                    ];
                }
                if ($data["required"] == "1") $parttitle .= '【いずれか必須】';
                else if ($data["required"] == "2") $parttitle .= '【全て必須】';
                echo_textarea_mlt($settings, $parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], $data["detail"]);
            } else {
                $placeholder = "";
                if ($data["min"][0] != "") $placeholder .= $data["min"][0] . '文字以上';
                if ($data["max"][0] != "") $placeholder .= $data["max"][0] . '文字以内';
                if ($placeholder != "") $parttitle .= '（' . $placeholder . '）';
                if ($data["required"] != "0") $parttitle .= '【必須】';
                echo_textarea([
                    "title" => $parttitle,
                    "name" => 'custom-' . $data["id"] . '[0]',
                    "id" => 'custom-' . $data["id"] . '[0]',
                    "prefill" => isset($prefilleddata[0]) ? $prefilleddata[0] : "",
                    "showcounter" => TRUE,
                    "detail" => $data["detail"],
                    "jspart" => 'onChange="validation_call_custom(&quot;custom-' . $data["id"] . '[0]&quot;);"',
                    "width" => $data["width"][0],
                    "height" => $data["height"][0],
                    "disabled" => $disable
                ]);
            }
        break;
        case "radio":
            $choices = choices_array($data["list"], TRUE);

            $parttitle = hsc($data["title"]);
            if ($data["required"] == "1") $parttitle .= '【必須】';
            $horizontally = ($data["arrangement"][0] == "h");
            if (isset($prefilleddata[0]) and $prefilleddata[0] !== "") $prefill = (string) array_search($prefilleddata[0], $choices);
            else $prefill = "";
            echo_radio([
                "title" => $parttitle,
                "name" => 'custom-' . $data["id"] . '[0]',
                "id" => 'custom-' . $data["id"] . '[0]',
                "choices" => $choices,
                "prefill" => $prefill,
                "horizontally" => $horizontally,
                "detail" => $data["detail"],
                "jspart" => 'onChange="validation_call_custom(&quot;custom-' . $data["id"] . '[0]&quot;);"',
                "disabled" => $disable
            ]);
        break;
        case "check":
            $choices = choices_array($data["list"], TRUE);

            $parttitle = hsc($data["title"]);
            if ($data["required"] == "1") $parttitle .= '【必須】';
            $horizontally = ($data["arrangement"][0] == "h");
            $prefill = [];
            if (isset($prefilleddata)) foreach ((array)$prefilleddata as $selected) {
                $search = array_search($selected, $choices);
                if ($search !== FALSE) $prefill[] = $search;
            }
            echo_check([
                "title" => $parttitle,
                "name" => 'custom-' . $data["id"],
                "id" => 'custom-' . $data["id"],
                "choices" => $choices,
                "prefill" => $prefill,
                "horizontally" => $horizontally,
                "detail" => $data["detail"],
                "jspart" => 'onChange="validation_call_custom(&quot;custom-' . $data["id"] . '[]&quot;);"',
                "disabled" => $disable
            ]);
        break;
        case "dropdown":
            $choices = choices_array($data["list"]);

            $parttitle = hsc($data["title"]);
            if ($data["quantity"] > 1) {
                $settings = [];
                for ($i = 0; $i < $data["quantity"]; $i++) {
                    if (isset($prefilleddata[$i]) and $prefilleddata[$i] !== "") $prefill = (string) array_search($prefilleddata[$i], $choices);
                    else $prefill = "";
                    $settings[$i] = [
                        "choices" => $choices,
                        "prefill" => $prefill,
                        "jspart" => 'onChange="validation_call_custom(&quot;custom-' . $data["id"] . '[' . $i . ']&quot;);"',
                        "prefix" => hsc($data["prefix"][$i]),
                        "suffix" => hsc($data["suffix"][$i]),
                        "disabled" => $disable
                    ];
                }
                if ($data["required"] == "1") $parttitle .= '【いずれか必須】';
                else if ($data["required"] == "2") $parttitle .= '【全て必須】';
                echo_dropdown_mlt($settings, $parttitle, 'custom-' . $data["id"], 'custom-' . $data["id"], $data["detail"]);
            } else {
                if ($data["required"] != "0") $parttitle .= '【必須】';
                if (isset($prefilleddata[0]) and $prefilleddata[0] !== "") $prefill = (string) array_search($prefilleddata[0], $choices);
                else $prefill = "";
                echo_dropdown([
                    "title" => $parttitle,
                    "name" => 'custom-' . $data["id"] . '[0]',
                    "id" => 'custom-' . $data["id"] . '[0]',
                    "choices" => $choices,
                    "prefill" => $prefill,
                    "detail" => $data["detail"],
                    "jspart" => 'onChange="validation_call_custom(&quot;custom-' . $data["id"] . '[0]&quot;);"',
                    "prefix" => hsc($data["prefix"][0]),
                    "suffix" => hsc($data["suffix"][0]),
                    "disabled" => $disable
                ]);
            }
        break;
        case "attach":
            $uploadedfs = array();
            $exts = str_replace(",", "・", $data["ext"]);
            if ($data["size"] != '') $filesize = $data["size"];
            else $filesize = FILE_MAX_SIZE;

            if ($data["filenumber"] != "") {
                if ($data["filenumber"] == "1") $filenumexp = '1個のみアップロード可能';
                else $filenumexp = $data["filenumber"] . '個までアップロード可能';
            }
            else $filenumexp = '複数個アップロード可能';
            echo '<div class="form-group">';
            if ($editing) echo hsc($data["title"]) . '（' . $exts . 'ファイル　' . $filenumexp . '）';
            else echo '<label for="custom-' . $data["id"] . '">' . hsc($data["title"]) . '（' . $exts . 'ファイル　' . $filenumexp . '）';
            if ($data["required"] == "1") echo '【必須】';
            if (!$editing) echo '</label>';
            echo '<dl class="small row"><dt class="col-sm-3">最大ファイルサイズ</dt><dd class="col-sm-9">合計' . $filesize . 'MBまで</dd>';
            if ($data["reso"][0] != "" and $data["reso"][1] != "") echo '<dt class="col-sm-3">最大解像度（mp4のみ）</dt><dd class="col-sm-9">横' . $data["reso"][0] . 'px　縦' . $data["reso"][1] . 'pxまで</dd>';
            if ($data["length"] != "") echo '<dt class="col-sm-3">最大再生時間（mp4のみ）</dt><dd class="col-sm-9">合計 ' . (int)((int)$data["length"] / 60) . '分 ' . ((int)$data["length"] % 60) . '秒まで</dd>';
            echo '</dl>';

            if ($editing) {
                if (isset($prefilleddata) and $prefilleddata != array()) {
                    echo '<div class="small">現在アップロードされているファイルを以下に表示します。<br>ファイル名をクリックするとそのファイルをダウンロードします。<br>ファイルを削除する場合は、そのファイルの左側にあるチェックボックスにチェックを入れて下さい。</div>';
                    foreach ($prefilleddata as $key => $element){
                        echo '<div class="form-check">';
                        echo '<input id="custom-' . $data["id"] . '-delete-' . $key . '" class="form-check-input system-form-attachment-checkbox" type="checkbox" name="custom-' . $data["id"] . '-delete[]" value="' . $key . '"';
                        if ($disable) echo ' disabled="disabled"';
                        echo ' onChange="validation_call_custom(&quot;custom-' . $data["id"] . '&quot;);">';
                        if ($workid == "common") {
                            echo '<a href="../fnc/filedld.php?author=' . $_SESSION["userid"] . '&genre=userform&id=' . $data["id"] . '_' . $key . '" target="_blank">' . hsc($element) . '</a>';
                            $uploadedfs[$key] = [
                                "size" => filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $data["id"] . '_' . $key),
                                "playtime" => preg_match('/\.mp4$/i', $element) ? get_playtime(DATAROOT . 'files/' . $_SESSION["userid"] . '/common/' . $data["id"] . '_' . $key) : 0
                            ];
                        } else {
                            echo '<a href="../fnc/filedld.php?author=' . $_SESSION["userid"] . '&genre=submitform&id=' . $workid . '&partid=' . $data["id"] . '_' . $key . '" target="_blank">' . hsc($element) . '</a>';
                            $uploadedfs[$key] = [
                                "size" => filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $workid . '/' . $data["id"] . '_' . $key),
                                "playtime" => preg_match('/\.mp4$/i', $element) ? get_playtime(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $workid . '/' . $data["id"] . '_' . $key) : 0
                            ];
                        }
                        echo '</div>';
                    }
                } else {
                    echo '<div class="small">現在アップロードされているファイルはありません。</div>';
                    echo '<input type="hidden" name="custom-' . $data["id"] . '-delete[]" value="none">';
                }
                echo '<label for="custom-' . $data["id"] . '" class="small">ファイルを新規に追加する場合はこちらにアップロードして下さい：</label>';
            } else {
                echo '<input type="hidden" name="custom-' . $data["id"] . '-delete[]" value="none">';
            }
            echo '<input type="file" class="form-control-file" id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '[]"';
            if ($data["filenumber"] != "1") echo ' multiple="multiple"';
            if ($disable) echo ' disabled="disabled"';
            echo ' data-current=\'' . json_encode($uploadedfs) . '\'';
            echo ' onChange="validation_call_custom(&quot;custom-' . $data["id"] . '&quot;);">';
            echo '<div id="custom-' . $data["id"] . '-errortext" class="system-form-error"></div>';
            if ($data["detail"] != "") echo '<small class="form-text">' . $data["detail"] . '</small>';
            echo '</div>';
        break;
    }
}

function echo_submitfile_section($data, $editing = FALSE, $disable = FALSE, $prefilleddata = [], $id = "xxxxxxxx") {
    //detail中のURLにリンクを振る（正規表現参考　https://www.megasoft.co.jp/mifes/seiki/s310.html）　あとHTMLタグが無いようにする・改行反映
    $data["detail"] = hsc($data["detail"]);
    $data["detail"] = preg_replace('{https?://[\w/:;%#\$&\?\(\)~\.=\+\-]+}', '<a href="$0" target="_blank" class="text-break" rel="noopener">$0</a>', $data["detail"]);
    $data["detail"] = str_replace(array("\r\n", "\r", "\n"), "\n", $data["detail"]);
    $data["detail"] = str_replace("\n", "<br>", $data["detail"]);
    if ($editing) {
        $data["detail"] .= '<div><strong>※この項目の変更には、運営メンバーによる承認が必要です。</strong></div>';
    }

    $uploadedfs = array();
    $exts = str_replace(",", "・", $data["ext"]);
    if ($data["size"] != '') $filesize = $data["size"];
    else $filesize = FILE_MAX_SIZE;

    if ($data["filenumber"] != "") {
        if ($data["filenumber"] == "1") $filenumexp = '1個のみアップロード可能';
        else $filenumexp = $data["filenumber"] . '個までアップロード可能';
    }
    else $filenumexp = '複数個アップロード可能';
    echo '<div class="form-group">';
    if ($editing) echo '提出ファイル（' . $exts . 'ファイル　' . $filenumexp . '）【必須】';
    else echo '<label for="submitfile">提出ファイル（' . $exts . 'ファイル　' . $filenumexp . '）【必須】</label>';
    echo '<dl class="small row"><dt class="col-sm-3">最大ファイルサイズ</dt><dd class="col-sm-9">合計' . $filesize . 'MBまで</dd>';
    if ($data["reso"][0] != "" and $data["reso"][1] != "") echo '<dt class="col-sm-3">最大解像度（mp4のみ）</dt><dd class="col-sm-9">横' . $data["reso"][0] . 'px　縦' . $data["reso"][1] . 'pxまで</dd>';
    if ($data["length"] != "") echo '<dt class="col-sm-3">最大再生時間（mp4のみ）</dt><dd class="col-sm-9">合計 ' . (int)((int)$data["length"] / 60) . '分 ' . ((int)$data["length"] % 60) . '秒まで</dd>';
    echo '</dl>';

    if ($editing) {
        if (isset($prefilleddata) and $prefilleddata != array()) {
            echo '<div class="small">現在アップロードされているファイルを以下に表示します。<br>ファイル名をクリックするとそのファイルをダウンロードします。<br>ファイルを削除する場合は、そのファイルの左側にあるチェックボックスにチェックを入れて下さい。</div>';
            foreach ($prefilleddata as $key => $element){
                echo '<div class="form-check">';
                echo '<input id="submitfile-delete-' . $key . '" class="form-check-input system-form-attachment-checkbox" type="checkbox" name="submitfile-delete[]" value="' . $key . '"';
                if ($disable) echo ' disabled="disabled"';
                echo ' onChange="validation_call_custom(&quot;submitfile&quot;);">';
                echo '<a href="../fnc/filedld.php?author=' . $_SESSION["userid"] . '&genre=submitmain&id=' . $id . '&partid=' . $key . '" target="_blank">' . hsc($element) . '</a>';
                $uploadedfs[$key] = [
                    "size" => filesize(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $id . '/main_' . $key),
                    "playtime" => get_playtime(DATAROOT . 'files/' . $_SESSION["userid"] . '/' . $id . '/main_' . $key)
                ];
                echo '</div>';
            }
        } else {
            echo '<div class="small">現在アップロードされているファイルはありません。</div>';
            echo '<input type="hidden" name="submitfile-delete[]" value="none">';
        }
        echo '<label for="submitfile" class="small">ファイルを新規に追加する場合はこちらにアップロードして下さい：</label>';
    } else {
        echo '<input type="hidden" name="submitfile-delete[]" value="none">';
    }
    echo '<input type="file" class="form-control-file" id="submitfile" name="submitfile[]"';
    if ($data["filenumber"] != "1") echo ' multiple="multiple"';
    if ($disable) echo ' disabled="disabled"';
    echo ' data-current=\'' . json_encode($uploadedfs) . '\'';
    echo ' data-current-length-sum="' . get_length_sum() . '"';
    echo ' onChange="validation_call_custom(&quot;submitfile&quot;);">';
    echo '<div id="submitfile-errortext" class="system-form-error"></div>';
    if ($data["detail"] != "") echo '<small class="form-text">' . $data["detail"] . '</small>';
    echo '</div>';
}

function generate_validation_params($settings) {
    $return1 = [];
    $return2 = [];
    foreach($settings as $key => $data) {
        $paramsdata = [];
        switch($data["type"]) {
            case "textbox":
            case "textarea":
            case "dropdown":
                for ($i = 0; $i < $data["quantity"]; $i++) {
                    $paramsdata = [];
                    if ($data["required"] == 0) $paramsdata[] = "present";
                    else {
                        if ($data["quantity"] >= 2 and $data["required"] == 1) {
                            $add = [];
                            for ($j = 0; $j < $data["quantity"]; $j++) {
                                if ($i == $j) continue;
                                $add[] = "custom-" . $data["id"] . "[$j]";
                            }
                            $paramsdata[] = "required_without_all:" . implode(",", $add);
                        } else $paramsdata[] = "required";
                    }
                    if (isset($data["max"][$i]) and $data["max"][$i] !== "") $paramsdata[] = "max:" . $data["max"][$i];
                    else {
                        if ($data["type"] == "textbox" or $data["type"] == "textarea") $paramsdata[] = "max:9999";
                    }
                    if (isset($data["min"][$i]) and $data["min"][$i] !== "") $paramsdata[] = "min:" . $data["min"][$i];
                    $return2[] = '"custom-' . $data["id"] . '[' . $i . ']": "' . implode("|", $paramsdata) . '"';
                    $return1[] = '"custom-' . $data["id"] . '[' . $i . ']": "' . $data["type"] . '"';
                }
                break;
            case "radio":
                if ($data["required"] == 0) $return2[] = '"custom-' . $data["id"] . '[0]": "present"';
                else $return2[] = '"custom-' . $data["id"] . '[0]": "required"';
                $return1[] = '"custom-' . $data["id"] . '[0]": "radio"';
                break;
            case "check":
                if ($data["required"] == 0) $return2[] = '"custom-' . $data["id"] . '": "present"';
                else $return2[] = '"custom-' . $data["id"] . '[]": "required"';
                $return1[] = '"custom-' . $data["id"] . '[]": "check"';
                break;
            case "attach":
                $return2[] = '"custom-' . $data["id"] . '": "attachment_validation:' . $key . '"';
                $return1[] = '"custom-' . $data["id"] . '": "attach"';
                break;
        }
    }
    return [implode(",", $return1), implode(",", $return2)];
}

function echo_desc_list($array) {
    echo '<dl class="row">';
    foreach ($array as $items) {
        foreach ($items as $key => $item) {
            if ($key == 0) echo '<dt class="col-md-3">' . $item . '</dt>';
            else echo '<dd class="col-md-9">' . $item . '</dd>';
        }
    }
    echo '</dl>';
}
