<?php
require_once('../../set.php');

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

//必須の場合のパターン・文字種・文字数
if($_POST["towhom"] == "") $invalid = TRUE;
if($_POST["sectok"] == "") $invalid = TRUE;

if($_POST["userid"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9a-zA-Z]*$/', $_POST["userid"])) $invalid = TRUE;
else if(mb_strlen($_POST["userid"]) > 20) $invalid = TRUE;

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

$email = $_POST["email"];
$userid = $_POST["userid"];
$IP = getenv("REMOTE_ADDR");

$conflict = FALSE;

//登録中のユーザーID横取り阻止（保証期間は30分）
if (file_exists(DATAROOT . 'users_reserve/')) {
    foreach (glob(DATAROOT . 'users_reserve/*.txt') as $filename) {
        $filedata = json_decode(file_get_contents($filename), true);
        if ($filedata["expire"] <= time()) {
            unlink($filename);
            continue;
        }
        // 自分自身だったら通してあげる
        if ($filedata["ip"] == $IP) continue;
        if (basename($filename, ".txt") == $userid) {
            $conflict = TRUE;
            break;
        }
    }
}

//登録済みの中から探す
foreach (glob(DATAROOT . 'users/*.txt') as $filename) {
    if (basename($filename, ".txt") == $userid) {
        $conflict = TRUE;
        break;
    }
}

if ($conflict) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ユーザーID重複</title>
</head>
<body>
<p>大変申し訳ございませんが、このユーザーIDは既に使用されているため、登録出来ません。<br>
お手数をお掛けしますが、別のユーザーIDをご利用願います。</p>
<p><a href="#" onclick="javascript:window.history.back(-1);return false;">こちらをクリックして、ユーザーID入力画面にお戻り下さい。</a></p>
</body>
</html>');

if (!file_exists(DATAROOT . 'users_reserve/')) mkdir(DATAROOT . 'users_reserve/');

$reserve = array(
    "expire" => time() + (30 * 60),
    "ip" => $IP,
);

$filedatajson = json_encode($reserve);
$fileplace = DATAROOT . 'users_reserve/' . $userid . '.txt';

if (file_put_contents($fileplace, $filedatajson) === FALSE) die('ID予約データの書き込みに失敗しました。');

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

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="../../css/bootstrap.css">
<title>共同運営者アカウント登録 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
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
  } else if(document.form.password.value.length > 30){
    problem = 1;
    probpw = 2;
  } else if(document.form.password.value.length < 8){
    problem = 1;
    probpw = 3;
  } else if(document.form.password.value !== document.form.passwordagn.value){
    problem = 1;
    probpw = 4;
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
    alert( "【パスワード】\n入力されていません。" );
  }
  if ( probpw == 2) {
    alert( "【パスワード】\n文字数が多すぎます（現在" + document.form.password.value.length + "文字）。30文字以内に抑えて下さい。" );
  }
  if ( probpw == 3) {
    alert( "【パスワード】\n文字数が少なすぎます（現在" + document.form.password.value.length + "文字）。8文字以上のパスワードにして下さい。" );
  }
  if ( probpw == 4) {
    alert( "【パスワード】\n再入力のパスワードが違います。もう一度入力して下さい。パスワードは間違っていませんか？" );
  }
  probcus.forEach(function(val){
    alert(val);
  });
  return false;
}
  if(window.confirm('入力内容に問題は見つかりませんでした。\n現在の入力内容を送信します。よろしいですか？（特に、ユーザーIDはこれ以降変更出来なくなりますのでご注意下さい。）')){
    submitbtn = document.getElementById("submitbtn");
    submitbtn.disabled = "disabled";
    return true;
  } else{
    return false;
  }
}

//Cookie判定（参考：https://qiita.com/tatsuyankmura/items/8e09cbd5ee418d35f169）
var setCookie = function(cookieName, value){
  var cookie = cookieName + "=" + value + ";";
  document.cookie = cookie;
}

var getCookie = function(cookieName){
  var l = cookieName.length + 1 ;
  var cookieAry = document.cookie.split("; ") ;
  var str = "" ;
  for(i=0; i < cookieAry.length; i++){
    if(cookieAry[i].substr(0, l) === cookieName + "="){
      str = cookieAry[i].substr(l, cookieAry[i].length) ;
      break ;
    }
  }
  return str;
}

setCookie('check_cookie', true);
var val = getCookie('check_cookie');

// -->
</script>
<body>
<div id="noscript">
<p>当サイトではJavascript及びCookieを使用しますが、JavascriptかCookie、またはその両方が無効になっているようです。<br>
ブラウザの設定を確認の上、JavascriptとCookieを有効にして再読み込みして下さい。</p>
</div>
<script>if (val) document.getElementById("noscript").style.display = "none";</script>

<div id="scriptok" style="display:none;">
<div class="container">
<h1>共同運営者アカウント登録</h1>
<p><div class="border" style="padding:10px;">
ご指定頂いたユーザーIDはご利用になれます。続いて、アカウントの詳細を設定します。全ての項目について、入力をお願いします。<br><br>
<b>パスワードは絶対に外部に漏れないようにして下さい。</b>第三者によって不正にアクセスされると、提出されたファイルの内容が見られたり、改ざんされたりする可能性があります。
</div></p>
<br>
<div class="border border-primary" style="padding:10px;">
<form name="form" action="co_handle.php" method="post" <?php
if ($includeattach) echo 'enctype="multipart/form-data" '; ?>onSubmit="return check()">
<input type="hidden" name="successfully" value="2">
<input type="hidden" name="towhom" value="<?php echo $_POST["towhom"]; ?>">
<input type="hidden" name="sectok" value="<?php echo $_POST["sectok"]; ?>">
<input type="hidden" name="userid" value="<?php echo $userid; ?>">
<div class="form-group">
<label for="userid_dummy">ユーザーID</label>
<input type="text" name="userid_dummy" class="form-control" id="userid_dummy" value="<?php echo $userid; ?>" disabled>
<font size="2">※<a href="#" onclick="javascript:window.history.back(-1);return false;">使用するユーザーIDを変更したい場合は、こちらをクリックしてユーザーIDを再指定して下さい。</a>
</font>
</div>
<div class="form-group">
<label for="nickname">ニックネーム（30文字以内）</label>
<input type="text" name="nickname" class="form-control" id="nickname">
<font size="2">※クレジット表記などの際にはこちらのニックネームが用いられます。普段ニコニコ動画やPixivなどでお使いのニックネーム（ペンネーム）で構いません。</font>
</div>
<div class="form-group">
<label for="email">メールアドレス</label>
<input type="email" name="email" class="form-control" id="email" value="<?php echo $email; ?>">
<font size="2">※登録用リンクを受け取ったメールアドレスが入力されています。必要に応じて変更して下さい。<br>
※このイベントに関する連絡に使用します。イベント期間中は、メールが届いているかどうかを定期的に確認して下さい。</font>
</div>
<div class="form-group">
<label for="emailagn">メールアドレス（確認の為再入力）</label>
<input type="email" name="emailagn" class="form-control" id="emailagn">
</div>
<div class="form-group">
<label for="password">パスワード（8文字以上30文字以内）</label>
<input type="password" name="password" class="form-control" id="password">
<font size="2">※ログインの際にこのパスワードを使用します。パスワードはハッシュ化された状態（復号出来ないように変換された状態）で保存されます。</font>
</div>
<div class="form-group">
<label for="passwordagn">パスワード（確認の為再入力）</label>
<input type="password" name="passwordagn" class="form-control" id="passwordagn">
</div>
<div class="form-group">
あなたの立場
<div class="form-check">
<input id="state-c" class="form-check-input" type="radio" name="state" value="c" checked="checked">
<label class="form-check-label" for="state-c">共同運営者<br><font size="2">一部の意思決定権を有します。自分が提出したファイルへのみアクセス権を有しますが、主催者が認めた場合は他人のファイルへアクセス出来る可能性があります。</font></label>
</div>
<font size="2">※共同運営者から一般参加者に変更するには主催者の承認が必要です。</font>
</div>
<?php
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
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../../js/bootstrap.bundle.js"></script>
</body>
</html>
