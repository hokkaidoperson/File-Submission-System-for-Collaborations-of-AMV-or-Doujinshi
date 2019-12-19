<?php
require_once('../../set.php');
session_start();
$titlepart = 'アカウント情報変更（パスワード以外）';
require_once(PAGEROOT . 'mypage_header.php');

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

//非参加者の場合はカスタムフォームを表示しない
//提出期間外だとメールアドレス以外変更不可にする
if (outofterm('userform') != FALSE) $disable = FALSE;
else $disable = TRUE;
if ($_SESSION["state"] == 'p') $disable = FALSE;
if (before_deadline()) $disable = FALSE;
if ($_SESSION["state"] == 'o' or $disable) $tojsp = array();

?>

<h1>アカウント情報変更（パスワード以外）</h1>
<p>現在登録されている情報が入力欄に入力されています。変更したい項目のみ、入力欄の中身を変更して下さい（ユーザーIDは変更出来ません）。</p>
<?php
if (!before_deadline() and $disable) echo '<p><div class="border border-danger" style="padding:10px;">
現在、ファイル提出期間外です。入力内容の確認は出来ますが、メールアドレス・パスワード以外は変更出来ません。
</div></p>';
else {
    if (!before_deadline() and $_SESSION["state"] == 'p') echo '<p><div class="border border-primary" style="padding:10px;">
現在ファイル提出期間外ですが、主催者は常時アカウント情報の編集が可能です。
</div></p>';
    else if (!before_deadline()) echo '<p><div class="border border-primary" style="padding:10px;">
現在ファイル提出期間外ですが、あなたは主催者からアカウント情報の編集を許可されています（' . date('Y年n月j日G時i分s秒', outofterm('userform')) . 'まで）。
</div></p>';
}
?>
<div class="border border-primary" style="padding:10px;">
<form name="form" action="others_handle.php" method="post" <?php
if ($includeattach) echo 'enctype="multipart/form-data" ';
?>onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<div class="form-group">
<label for="password">現在のパスワード（本人確認の為ご入力願います）</label>
<input type="password" name="password" class="form-control" id="password">
</div>
<div class="form-group">
<label for="userid_dummy">ユーザーID（変更出来ません）</label>
<input type="text" name="userid_dummy" class="form-control" id="userid_dummy" value="<?php echo $userid; ?>" disabled>
</div>
<div class="form-group">
<label for="nickname">ニックネーム（30文字以内）【必須】</label>
<input type="text" name="nickname" class="form-control" id="nickname" value="<?php
if (isset($entereddata["nickname"])) echo htmlspecialchars($entereddata["nickname"]);
?>"<?php if ($disable) echo ' disabled="disabled"'; ?>>
<font size="2">※クレジット表記などの際にはこちらのニックネームが用いられます。普段ニコニコ動画やPixivなどでお使いのニックネーム（ペンネーム）で構いません。</font>
</div>
<div class="form-group">
<label for="email">メールアドレス【必須】</label>
<input type="email" name="email" class="form-control" id="email" value="<?php
if (isset($entereddata["email"])) echo htmlspecialchars($entereddata["email"]);
?>">
<font size="2">※このイベントに関する連絡に使用します。イベント期間中は、メールが届いているかどうかを定期的に確認して下さい。</font>
</div>
<div class="form-group">
<label for="emailagn">メールアドレス（確認の為再入力）【必須】</label>
<input type="email" name="emailagn" class="form-control" id="emailagn" value="<?php
if (isset($entereddata["email"])) echo htmlspecialchars($entereddata["email"]);
?>">
</div>
<?php
//非参加者の場合はカスタムフォームを表示しない

if ($_SESSION["state"] != 'o') {

foreach ($userformdata as $data) {
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
            echo '<input type="text" name="custom-' . $data["id"] . '" class="form-control" id="custom-' . $data["id"] . '"';
            if (isset($entereddata[$data["id"]])) echo ' value="' . htmlspecialchars($entereddata[$data["id"]]) . '"';
            if ($disable) echo ' disabled="disabled"';
            echo '>';
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
            echo '<input type="text" name="custom-' . $data["id"] . '-1" class="form-control" id="custom-' . $data["id"] . '-1"';
            if (isset($entereddata[$data["id"] . "-1"])) echo ' value="' . htmlspecialchars($entereddata[$data["id"] . "-1"]) . '"';
            if ($disable) echo ' disabled="disabled"';
            echo '>';
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
            echo '<input type="text" name="custom-' . $data["id"] . '-2" class="form-control" id="custom-' . $data["id"] . '-2"';
            if (isset($entereddata[$data["id"] . "-2"])) echo ' value="' . htmlspecialchars($entereddata[$data["id"] . "-2"]) . '"';
            if ($disable) echo ' disabled="disabled"';
            echo '>';
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
            if ($data["height"] != "") echo '<textarea id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '" rows="' . $data["height"] . '" cols="80" class="form-control">';
            else echo '<textarea id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '" rows="4" cols="80" class="form-control"';
            if ($disable) echo ' disabled="disabled"';
            echo '>';
            if (isset($entereddata[$data["id"]])) echo htmlspecialchars($entereddata[$data["id"]]);
            echo '</textarea>';
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
                echo '<input id="custom-' . $data["id"] . '-' . $num . '" class="form-check-input" type="radio" name="custom-' . $data["id"] . '" value="' . $choice . '"';
                if (isset($entereddata[$data["id"]]) and htmlspecialchars($entereddata[$data["id"]]) == $choice) echo ' checked="checked"';
                if ($disable) echo ' disabled="disabled"';
                echo '>';
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
                $choiceh = htmlspecialchars($choice);
                if ($data["arrangement"] == "h") echo '<div class="form-check form-check-inline">';
                else echo '<div class="form-check">';
                echo '<input id="custom-' . $data["id"] . '-' . $num . '" class="form-check-input" type="checkbox" name="custom-' . $data["id"] . '[]" value="' . $choiceh . '"';
                if (isset($entereddata[$data["id"]]) and array_search($choice, $entereddata[$data["id"]]) !== FALSE) echo ' checked="checked"';
                if ($disable) echo ' disabled="disabled"';
                echo '>';
                echo '<label class="form-check-label" for="custom-' . $data["id"] . '-' . $num . '">' . $choiceh . '</label>';
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
            echo '<select id="custom-' . $data["id"] . '" class="form-control" name="custom-' . $data["id"] . '"';
            if ($disable) echo ' disabled="disabled"';
            echo '>';
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
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
            echo '</div>';
        break;
        case "attach":
            $exts = str_replace(",", "・", $data["ext"]);
            if ($data["size"] != '') $filesize = $data["size"];
            else $filesize = FILE_MAX_SIZE;

            echo '<div class="form-group">' . htmlspecialchars($data["title"]) . '（' . $exts . 'ファイル　' . $filesize . 'MBまで）';
            if ($data["required"] == "1") echo '【必須】';
            echo '<div>現在アップロードされているファイル：';
            if (isset($entereddata[$data["id"]]) and $entereddata[$data["id"]] != '') {
                echo '<a href="../fnc/filedld.php?author=' . $userid . '&genre=userform&id=' . $data["id"] . '" target="_blank">' . htmlspecialchars($entereddata[$data["id"]]) . 'ファイル（クリックでダウンロード）</a>';
                echo '<input type="hidden" name="custom-' . $data["id"] . '-already" value="1">';
            }
            else {
                echo '無し';
                echo '<input type="hidden" name="custom-' . $data["id"] . '-already" value="0">';
            }
            echo '</div>';
            echo '<label for="custom-' . $data["id"] . '">ファイルを変更する場合はこちらにアップロードして下さい：</label>';
            echo '<input type="file" class="form-control-file" id="custom-' . $data["id"] . '" name="custom-' . $data["id"] . '"';
            if ($disable) echo ' disabled="disabled"';
            echo '>';
            if ($data["required"] == "0") echo '<div class="form-check">
<input id="custom-' . $data["id"] . '-delete" class="form-check-input" type="checkbox" name="custom-' . $data["id"] . '-delete" value="1">
<label class="form-check-label" for="custom-' . $data["id"] . '-delete">ファイルを新規アップロードせず削除する場合は、左のチェックボックスにチェックして下さい。</label>
</div>
';
            if ($data["detail"] != "") echo '<font size="2">' . $data["detail"] . '</font>';
            echo '</div>';
        break;
    }
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
  var probnick = 0;
  var probmail = 0;
  var probpw = 0;
  var probcus = [];
  var setting = <?php echo json_encode($tojsp); ?>;

  if(document.form.nickname.value === ""){
    problem = 1;
    probnick = 1;
  } else if(document.form.nickname.value.length > 30){
    problem = 1;
    probnick = 2;
  }
  if(document.form.email.value === ""){
    problem = 1;
    probmail = 1;
  } else if(!document.form.email.value.match(/.+@.+\..+/)){
    problem = 1;
    probmail = 2;
  } else if(document.form.email.value !== document.form.emailagn.value){
    problem = 1;
    probmail = 3;
  }
  if(document.form.password.value === ""){
    problem = 1;
    probpw = 1;
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
    } else if (val.type == "radio" || val.type == "dropdown") {
        item = document.form["custom-" + val.id].value;
        result = check_required(val.required, item, val.title);
        if (result != 0) {
            problem = 1;
            probcus.push("【" + val.title + "】\nいずれかを選択して下さい。");
        }
    } else if (val.type == "attach") {
        name = document.getElementById("custom-" + val.id).value;
        result = check_required(val.required, name, val.title);
        if (result != 0) {
            if (document.form["custom-" + val.id + "-already"].value == 0) {
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
  if ( probnick == 1) {
    alert( "【ニックネーム】\n入力されていません。" );
  }
  if ( probnick == 2) {
    alert( "【ニックネーム】\n文字数が多すぎます（現在" + document.form.nickname.value.length + "文字）。30文字以内に抑えて下さい。" );
  }
  if ( probmail == 1) {
    alert( "【メールアドレス】\n入力されていません。" );
  }
  if ( probmail == 2) {
    alert( "【メールアドレス】\n正しく入力されていません。入力されたメールアドレスをご確認下さい。メールアドレスは間違っていませんか？" );
  }
  if ( probmail == 3) {
    alert( "【メールアドレス】\n再入力のメールアドレスが違います。もう一度入力して下さい。メールアドレスは間違っていませんか？" );
  }
  if ( probpw == 1) {
    alert( "【現在のパスワード】\n入力されていません。" );
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
