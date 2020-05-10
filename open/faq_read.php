<?php
require_once('../set.php');

$id = basename($_GET["id"]);

if ($id == "") die("パラメーターエラー");
if (!file_exists("faq/$id.php")) die("対応ファイルが存在しません。");

require_once("faq/$id.php");

$titlepart = $title;
require_once(PAGEROOT . 'help_header.php');
?>

<h1>FAQ（よくある質問） - 回答</h1>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<h3>Q：<?php echo $title; ?></h3>
<img src="../images/tag.svg" style="width: 1em; height: 1em;">
<?php
foreach ($tags as $tag) echo '<a href="faq_search.php?tag%5B%5D=' . urlencode($tag) . '" class="badge badge-secondary">' . hsc($tag) . '</a>' . "\n";
?>
</div>
<?php echo $content; ?>
<hr>
<p><a class="btn btn-secondary" href="#" onclick="javascript:window.history.back(-1);return false;" role="button">前のページに戻る</a></p>
<?php
require_once(PAGEROOT . 'help_footer.php');
