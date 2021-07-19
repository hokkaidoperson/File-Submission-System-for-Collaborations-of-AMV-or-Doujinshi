<?php
//※必ず、guest_footer.phpとセットで読み込む

//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

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
<?php
if (isset($includepart)) echo $includepart;
?>
</head>
<body class="system-guestpage"<?php if (isset($bodyincludepart)) echo $bodyincludepart; ?>>
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
<div class="container-fluid">
