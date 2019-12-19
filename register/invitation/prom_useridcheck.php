<?php
require_once('../../set.php');

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

//必須の場合のパターン・文字種・文字数
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


?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="../../css/bootstrap.css">
<title>主催者アカウント登録 - <?php echo $eventname; ?>　ファイル提出用ポータルサイト</title>
</head>
<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  problem = 0;

  probnick = 0;
  probmail = 0;
  probpw = 0;


//必須の場合のパターン・文字種・文字数
  if(document.form.nickname.value === ""){
    problem = 1;
    probnick = 1;
  } else if(document.form.nickname.value.length > 30){
    problem = 1;
    probnick = 2;
  }


//メールアドレス形式確認　必須・一致確認
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

//必須の場合のパターン・文字数・一致確認
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

//問題ありの場合はエラー表示　ない場合は確認・移動　エラー状況に応じて内容を表示
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
<h1>主催者アカウント登録</h1>
<p><div class="border" style="padding:10px;">
ご指定頂いたユーザーIDはご利用になれます。続いて、アカウントの詳細を設定します。全ての項目について、入力をお願いします。<br><br>
<b>パスワードは絶対に外部に漏れないようにして下さい。</b>第三者によって不正にアクセスされると、提出されたファイルの内容が見られたり、改ざんされたりする可能性があります。
</div></p>
<br>
<div class="border border-primary" style="padding:10px;">
<form name="form" action="prom_handle.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="2">
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
<input id="state-p" class="form-check-input" type="radio" name="state" value="p" checked>
<label class="form-check-label" for="state-p">主催者<br><font size="2">イベントに提出されるあらゆるファイルへのアクセス権や、意思決定権を有します。</font></label>
</div>
<font size="2">※主催者から他の立場（共同運営者もしくは一般参加者）へ変更する場合、代わりの主催者を任命する必要があります。</font>
</div>
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
