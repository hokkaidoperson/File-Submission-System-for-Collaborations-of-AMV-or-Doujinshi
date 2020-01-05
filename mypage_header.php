<?php
//※必ず、mypage_footer.phpとセットで読み込む

//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

//ログインしてない場合はログインページへ
$currenturl = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
$redirtopass = str_replace($siteurl, '', $currenturl);
$redirtopass = urlencode($redirtopass);

if (!isset($_SESSION['userid'])) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'' . $siteurl . 'index.php?redirto=' . $redirtopass . '\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

//ブロックされてたら強制ログアウト
if (blackuser($_SESSION['userid'])) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'' . $siteurl . 'mypage/logout.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}
//if (blackip($_SESSION['admin'], $_SESSION['state'])) {
//    die('<!DOCTYPE html>
//<html>
//<head>
//<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
//<meta name="viewport" content="width=device-width,initial-scale=1">
//<meta http-equiv="refresh" content="0; URL=\'' . $siteurl . 'mypage/logout.php\'" />
//<title>リダイレクト中…</title>
//</head>
//<body>
//しばらくお待ち下さい…
//</body>
//</html>');
//}


//セッション切れ起こしてない？
if ($_SESSION['expire'] <= time()) {
    //ログアウト処理
    //情報をリセット
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="10; URL=\'' . $siteurl . 'index.php?redirto=' . $redirtopass . '\'" />
<title>セッション・エラー（タイムアウト）</title>
</head>
<body>
<p>しばらくの間アクセスが無かったため、セキュリティの観点から接続を中断しました。<br>
再度ログインして下さい。</p>
<p>10秒後にログインページに自動的に移動します。<br>
<a href="' . $siteurl . 'index.php?redirto=' . $redirtopass . '">移動しない場合、あるいはお急ぎの場合はこちらをクリックして下さい。</a></p>
</body>
</html>');
} else $_SESSION['expire'] = time() + (30 * 60);

//ブラウザがなぜか変わってたりしない？（セッションハイジャック？）
if ($_SESSION['useragent'] != $_SERVER['HTTP_USER_AGENT']) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ユーザーエージェント認証失敗</title>
</head>
<body>
<p>ログイン時のユーザーエージェントと異なるため、接続出来ません。<br>
不正ログインしようとした可能性があります（セッション・ハイジャック　など）。</p>
</body>
</html>');
}

//ログイン情報更新
$refresh_userdata = id_array($_SESSION["userid"]);
$_SESSION['nickname'] = $refresh_userdata["nickname"];
$_SESSION['email'] = $refresh_userdata["email"];
$_SESSION['state'] = $refresh_userdata["state"];

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="<?php echo $siteurl; ?>css/bootstrap.css">
<title><?php
if (isset($titlepart)) echo $titlepart . ' - ';
echo $eventname;
?>　ファイル提出用ポータルサイト</title>
</head>
<script type="text/javascript">
<!--
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
<nav class="navbar navbar-light bg-light">
<a class="navbar-brand" href="<?php echo $siteurl; ?>mypage/index.php"><?php echo $eventname; ?></a>
<div class="dropdown">
<button type="button" id="dropdownMenuButton"
class="btn btn-primary dropdown-toggle"
data-toggle="dropdown"
aria-haspopup="true"
aria-expanded="false">
ログイン中
</button>
<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="z-index:9999;">
<h6 class="dropdown-header text-wrap">
<?php echo $_SESSION["nickname"]; ?>
</h6>
<p class="text-right" style="margin-bottom: 0rem; margin-right: 0.7rem;">
<?php
switch ($_SESSION["state"]) {
    case 'p':
        echo '<span class="dropdown-item-text badge badge-success text-wrap" style="width: 3rem;">
主催者
</span>';
        break;
    case 'c':
    echo '<span class="dropdown-item-text badge badge-warning text-wrap" style="width: 5rem;">
共同運営者
</span>';
        break;
    case 'g':
    echo '<span class="dropdown-item-text badge badge-info text-wrap" style="width: 5rem;">
一般参加者
</span>';
        break;
    default:
    echo '<span class="dropdown-item-text badge badge-secondary text-wrap" style="width: 4rem;">
非参加者
</span>';
}
if ($_SESSION["admin"]) echo '</p><p class="text-right" style="margin-bottom: 0rem; margin-right: 0.7rem;"><span class="dropdown-item-text badge badge-danger text-wrap" style="width: 7rem;">
システム管理者
</span>';
?>
</p>
<div class="dropdown-divider"></div>
<a class="dropdown-item" href="<?php echo $siteurl; ?>mypage/account/index.php"><img src="<?php echo $siteurl; ?>images/account.svg" style="width: 1em; height: 1em;"> アカウント情報編集</a>
<a class="dropdown-item" href="<?php echo $siteurl; ?>mypage/logout.php"><img src="<?php echo $siteurl; ?>images/logout.svg" style="width: 1em; height: 1em;"> ログアウト</a>
</div>
</div>
</nav>
<div class="container">
<?php
if ($_SESSION["situation"] == 'loggedin') {
    echo '<p><div class="border border-primary" style="padding:10px;">
ログインしました。
</div></p>';
    echo '<p><div class="border border-warning" style="padding:10px;">
当サイトに30分以上アクセスが無い場合は、セキュリティの観点から自動的にログアウトします。<br>
特に、情報入力画面など、同じページにしばらく留まり続ける場面ではご注意願います。</div></p>';
    $_SESSION["situation"] = '';
}
