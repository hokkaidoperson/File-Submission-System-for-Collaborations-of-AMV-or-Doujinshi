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
<h2 class="h3 d-flex"><span>Q：</span><span><?php echo $title; ?></span></h2>
<div class="mt-n2 mb-3 ml-2 d-flex">
<i class="bi bi-tags text-dark pr-1"></i>
<span>
<?php
foreach ($tags as $tag) echo '<a href="faq_search.php?tag%5B%5D=' . urlencode($tag) . '" class="badge badge-secondary">' . hsc($tag) . '</a>' . "\n";
?>
</span>
</div>
<?php echo $content; ?>
<hr>
<p><a class="btn btn-secondary" href="#" onclick="javascript:window.history.back(-1);return false;" role="button">前のページに戻る</a></p>
<?php
require_once(PAGEROOT . 'help_footer.php');
