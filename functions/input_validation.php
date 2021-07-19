<?php
//検証関連

//CSRF（クロスサイトリクエストフォージェリ）対策用
//セッションIDからトークンを作成し、処理スクリプト（handle.php等）で照合
//参考　https://qiita.com/mpyw/items/8f8989f8575159ce95fc
function csrf_prevention_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) return false;
    return hash("sha256", session_id());
}

//CSRF対策用のhiddenパーツ
function csrf_prevention_in_form() {
    echo '<input type="hidden" name="csrf_prevention_token" value="' . csrf_prevention_token() . '">';
}

//検証（上のcsrf_prevention_in_formとセットで）
function csrf_prevention_validate($dont_die = FALSE) {
    if (!isset($_POST["csrf_prevention_token"]) or $_POST["csrf_prevention_token"] !== csrf_prevention_token()) {
        if (!$dont_die) die("フォームが入力されていないか、CSRF（クロスサイトリクエストフォージェリ）の可能性があると判定されたため、操作を停止しました。");
        return FALSE;
    } else return TRUE;
}

//チェック系関数　問題無ければ0を、そうでなければ1を返す（ユーザーフォームの入力事項確認に使う）
//テキスト系の最大最小（0だとチェックしない）
function check_maxmin($max, $min, $item) {
    $item = str_replace(array("\r\n", "\r", "\n"), " ", $item);
    if ($max != 0) {
        if (mb_strlen($item) > $max) return 1;
    }
    if ($min != 0) {
        if (mb_strlen($item) < $min && mb_strlen($item) > 0) return 1;
    }
    return 0;
}

//--------
//入力内容検証ショートカット
function check_textbox($array) {
    $item = $_POST["custom-" . $array["id"]];
    $require_val = 0;
    for ($i = 0; $i < (int)$array["quantity"]; $i++) {
        if ($item[$i] != "") {
            $require_val++;
            if ($array["max"][$i] != "") $vmax = (int) $array["max"][$i];
            else $vmax = 9999;
            if ($array["min"][$i] != "") $vmin = (int) $array["min"][$i];
            else $vmin = 0;
            if (check_maxmin($vmax, $vmin, $item[$i]) != 0) return TRUE;
        }
    }
    switch ($array["required"]) {
        case "1":
            if ($require_val < 1) return TRUE;
            break;
        case "2":
            if ($require_val < (int)$array["quantity"]) return TRUE;
            break;
    }
    return FALSE;
}

function check_checkbox($array) {
    $f = $_POST["custom-" . $array["id"]];
    if ($f == "") $f = array();
    if((array)$f == array() && $array["required"] == "1") return TRUE;
    $choices = choices_array($array["list"]);
    $compare = count($choices);
    foreach ($f as $value) {
        if ((int)$value >= $compare) return TRUE;
    }
    return FALSE;
}

function check_radio($array) {
    $item = $_POST["custom-" . $array["id"]][0];
    if ($array["required"] == "1" && $item === "") return TRUE;
    $choices = choices_array($array["list"]);
    $compare = count($choices);
    if ((int)$item >= $compare) return TRUE;
    return FALSE;
}

function check_dropdown($array) {
    $item = $_POST["custom-" . $array["id"]];
    $require_val = 0;
    $choices = choices_array($array["list"]);
    $compare = count($choices);
    for ($i = 0; $i < (int)$array["quantity"]; $i++) {
        if ($item[$i] != "") {
            $require_val++;
            if ((int)$item[$i] >= $compare) return TRUE;
        }
    }
    switch ($array["required"]) {
        case "1":
            if ($require_val < 1) return TRUE;
            break;
        case "2":
            if ($require_val < (int)$array["quantity"]) return TRUE;
            break;
    }
    return FALSE;
}

function check_attachments($array, $elementid, $uploadedfs, $currentsize) {
    $sizesum = 0;
    if ($array["filenumber"] == "") $filemax = 100;
    else $filemax = (int) $array["filenumber"];
    $ext = $array["ext"];
    $ext = str_replace(",", "|", $ext);
    $ext = strtoupper($ext);
    $reg = '/\.(' . $ext . ')$/i';
    $upped = 0;
    $lengthsum = 0;
    if ($array["size"] != "") $oksize = (int) $array["size"];
    else $oksize = FILE_MAX_SIZE;
    $oksize = $oksize * 1024 * 1024;
    if ($array["reso"][0] != "") $widthmax = (int)$array["reso"][0];
    else $widthmax = 99999999;
    if ($array["reso"][1] != "") $heightmax = (int)$array["reso"][1];
    else $heightmax = 99999999;
    if ($array["length"] != "") $lengthmax = (int)$array["length"];
    else $lengthmax = 99999999;
    if ($array["worklength"] != "") $worklengthmax = (int)$array["worklength"];
    else $worklengthmax = 99999999;

    for ($j=0; $j<count($_FILES[$elementid]['name']); $j++) {
        if ($_FILES[$elementid]['error'][$j] == 4) break;
        else if ($_FILES[$elementid]['error'][$j] == 1) die_error_html('ファイル　アップロードエラー', '<p>ファイルのアップロードに失敗しました。アップロードしようとしたファイルのサイズが、サーバーで扱えるファイルサイズを超えていました。<br>
お手数ですが、サーバーの管理者にお問い合わせ下さい。</p>
<p>問い合わせの際、サーバーの管理者に以下の事項をお伝え下さい。<br>
<strong>ユーザーがアップロードしようとしたファイルのサイズが、php.ini の upload_max_filesize ディレクティブの値を超えていたため、アップロードが遮断されました。<br>
php.ini の設定を見直して下さい。</strong></p>');
        else if ($_FILES[$elementid]['error'][$j] == 3) die_error_html('ファイル　アップロードエラー', '<p>ファイルのアップロードに失敗しました。通信環境が悪かったために、アップロードが中止された可能性があります。<br>
通信環境を見直したのち、再度送信願います。</p>');
        else if ($_FILES[$elementid]['error'][$j] == 0) {
            if (!preg_match($reg, $_FILES[$elementid]['name'][$j])) return TRUE;
            $sizesum += $_FILES[$elementid]['size'][$j];
            $upped++;
            if (preg_match('/\.MP4$/i', $_FILES[$elementid]['name'][$j])) {
                $reso = get_resolution($_FILES[$elementid]["tmp_name"][$j]);
                if ($reso[0] > $widthmax || $reso[1] > $heightmax) return TRUE;
                $lengthsum += get_playtime($_FILES[$elementid]["tmp_name"][$j]);
            }
        }
    }
    $sizesum += $currentsize;
    $deletenum = 0;
    foreach((array)$_POST[$elementid . "-delete"] as $key){
        if ($key === "none") break;
        if (!isset($uploadedfs[basename($key)])) return TRUE;
        $sizesum -= $uploadedfs[basename($key)]["size"];
        $lengthsum -= $uploadedfs[basename($key)]["playtime"];
        $deletenum++;
    }
    $filenumber = $upped + count($uploadedfs) - $deletenum;
    if($filenumber <= 0 && $array["required"] == "1") return TRUE;
    if($filenumber > $filemax) return TRUE;
    if ($sizesum > $oksize) return TRUE;
    if ($lengthsum > $lengthmax) return TRUE;
    if ($elementid == "submitfile" and $lengthsum + get_length_sum() > $worklengthmax) return TRUE;
    return FALSE;
}
