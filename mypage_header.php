<?php
//※必ず、mypage_footer.phpとセットで読み込む

//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

session_validation(TRUE);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?php
if (META_NOFOLLOW) echo '<meta name="robots" content="noindex, nofollow, noarchive">';
?>
<link rel="stylesheet" href="<?php echo $siteurl; ?>plugins/bs/bootstrap-filesys.css?<?php echo urlencode(VERSION); ?>">
<link rel="stylesheet" href="<?php echo $siteurl; ?>plugins/bs/bootstrap-icons.css?<?php echo urlencode(VERSION); ?>">
<link rel="stylesheet" href="<?php echo $siteurl; ?>css/style.css?<?php echo urlencode(VERSION); ?>">
<title><?php
if (isset($titlepart)) echo $titlepart . ' - ';
echo $eventname;
?>　ファイル提出用ポータルサイト</title>
</head>
<body class="system-mypage">
<div id="noscript">
<p>当サイトではJavascript及びCookieを使用しますが、JavascriptかCookie、またはその両方が無効になっているようです。<br>
ブラウザの設定を確認の上、JavascriptとCookieを有効にして再読み込みして下さい。</p>
<p>上記を有効にしてもこの画面が表示される場合、ご利用のブラウザは当サイトが使用するJavascriptの機能を提供していない、もしくは充分にサポートしていない可能性がありますので、ブラウザを変えて再度お試し下さい（推奨環境のブラウザでこの画面が表示される場合、システム管理者までご連絡下さい）。</p>
</div>
<script>
if (navigator.cookieEnabled) document.getElementById("noscript").style.display = "none";
let file_size_max = <?php echo FILE_MAX_SIZE; ?>
</script>

<div id="scriptok" style="display:none;">
<nav class="navbar navbar-light system-nav-mypage">
<a class="navbar-brand text-truncate" href="<?php echo $siteurl; ?>mypage/index.php"><?php echo $eventname; ?></a>
<div class="d-flex flex-row-reverse">
<div class="dropdown d-flex align-items-center">
<button type="button" id="dropdownMenuButton" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">ログイン中</button>
<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
<h6 class="dropdown-header text-wrap">
<?php echo $_SESSION["nickname"]; ?>
</h6>
<p class="text-right system-menu-badge">
<?php
switch ($_SESSION["state"]) {
    case 'p':
        echo '<span class="dropdown-item-text badge badge-success text-wrap">
主催者
</span>';
        break;
    case 'c':
    echo '<span class="dropdown-item-text badge badge-warning text-wrap">
共同運営者
</span>';
        break;
    case 'g':
    echo '<span class="dropdown-item-text badge badge-info text-wrap">
一般参加者
</span>';
        break;
    default:
    echo '<span class="dropdown-item-text badge badge-secondary text-wrap">
非参加者
</span>';
}
if ($_SESSION["admin"]) echo '</p><p class="text-right system-menu-badge"><span class="dropdown-item-text badge badge-danger text-wrap">
システム管理者
</span>';
?>
</p>
<div class="dropdown-divider"></div>
<a class="dropdown-item" href="<?php echo $siteurl; ?>mypage/account/index.php"><i class="bi bi-person-fill text-dark"></i> アカウント情報編集</a>
<a class="dropdown-item" href="<?php echo $siteurl; ?>mypage/logout.php"><i class="bi bi-door-closed-fill text-dark"></i> ログアウト</a>
</div>
</div>
<ul class="navbar-nav mr-3">
<li><a class="nav-link" href='<?php echo $siteurl; ?>open/index.php' target="_blank"><i class="bi bi-question-circle-fill text-dark"></i> ヘルプ</a></li>
</ul>
</div>
</nav>
<div class="container-md">
<div class="py-2">
<?php
output_alert();
