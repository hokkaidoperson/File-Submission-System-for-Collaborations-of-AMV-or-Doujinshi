<?php
require_once('../../set.php');

$deny = FALSE;

if (file_exists(DATAROOT . 'mail/invitation/_promoter.txt')) {
    $filedata = json_decode(file_get_contents(DATAROOT . 'mail/invitation/_promoter.txt'), true);
    $email = $filedata["to"];
    if ($filedata["expire"] <= time()) {
        unlink(DATAROOT . 'mail/invitation/_promoter.txt');
        $deny = TRUE;
    }
    if ($filedata["sectok"] !== $_GET["sectok"]) $deny = TRUE;
} else $deny = TRUE;

if ($deny) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>認証エラー</title>
</head>
<body>
<p>認証に失敗しました。以下が原因として考えられます。<br>
1. 設定リンクの有効期限が切れている。<br>
2. 設定リンクのURLのうち一部分だけが切り取られ、サーバー側で正常に認識されなかった。</p>
</body>
</html>');

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

  probid = 0;

//必須の場合のパターン・文字種・文字数
  if(document.form.userid.value === ""){
    problem = 1;
    probid = 1;
  } else if(!document.form.userid.value.match(/^[0-9a-zA-Z]*$/)){
    problem = 1;
    probid = 2;
  } else if(document.form.userid.value.length > 20){
    problem = 1;
    probid = 3;
  }

//問題ありの場合はエラー表示　ない場合は移動　エラー状況に応じて内容を表示
if ( problem == 1 ) {
  if ( probid == 1) {
    alert( "【ユーザーID】\n入力されていません。" );
  }
  if ( probid == 2) {
    alert( "【ユーザーID】\n半角英数字以外の文字が含まれています（記号も使用出来ません）。別のIDを指定して下さい。" );
  }
  if ( probid == 3) {
    alert( "【ユーザーID】\n文字数が多すぎます（現在" + document.form.userid.value.length + "文字）。20文字以内に抑えて下さい。" );
  }
  return false;
}

  submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";
  return true;
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
<div class="border" style="padding:10px; margin-top:1em; margin-bottom:1em;">
最初に、主催者アカウントの登録をします。募集期間など、イベントの詳細事項については、アカウントの登録後に行えるようになります。<br><br>
当サイトではJavascript及びCookieを使用します。現在は有効になっていますが、アクセス途中でこれらを無効化するとサイトの動作に支障をきたす可能性がありますのでお控え下さい。
</div>
<br>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<form name="form" action="prom_useridcheck.php" method="post" onSubmit="return check()">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="sectok" value="<?php echo $_GET["sectok"]; ?>">
<input type="hidden" name="email" value="<?php echo $email; ?>">
<div class="form-group">
<label for="userid">ユーザーID（半角英数字のみ　20文字以内　<b>後から変更出来ません</b>）</label>
<input type="text" name="userid" class="form-control" id="userid">
<font size="2">※ログインの際にこのユーザーIDを使用します。</font>
</div>
※入力頂いたユーザーIDが既に使われていないかどうか、チェックを行います。<br>
<button type="submit" class="btn btn-primary" id="submitbtn">ユーザーIDをチェック</button>
</form>
</div>
</div>
</div>
<script>if (val) document.getElementById("scriptok").style.display = "block";</script>
<script type="text/javascript" src="../../js/jquery-3.4.1.js"></script>
<script type="text/javascript" src="../../js/bootstrap.bundle.js"></script>
</body>
</html>
