<?php
require_once('../set.php');
require_once(PAGEROOT . 'help_header.php');

$tagcloud = array();
foreach (glob('faq/*.php') as $filename) {
    require_once($filename);
    foreach ($tags as $tag) $tagcloud[$tag] = 1;
}

?>

<h1>ヘルプ機能　トップ</h1>
<p>※このヘルプ機能は、ログイン不要でご覧になれます。<br>
※画面上部の「ヘルプ機能（<?php echo $eventname; ?>）」部分をクリック・タップすると、いつでもこのトップ画面に戻れます。<br>
※問題・疑問点が解決したら、ヘルプ画面はこのまま閉じても構いません。</p>
<h2>ご利用ガイド</h2>
<ul>
    <li><a href="guide_promoter.php">主催者の方はこちら</a></li>
    <li><a href="guide_co.php">共同運営者の方はこちら</a></li>
    <li><a href="guide_general.php">一般参加者の方はこちら</a></li>
</ul>
<ul>
    <li><a href="https://www.hkdyukkuri.space/filesystem/doc" target="_blank" rel="noopener">システム管理者の方はこちらも併せてお読み下さい（本システムのドキュメントページを新しいタブで開きます）。</a></li>
</ul>
<h2>FAQ（よくある質問）</h2>
<p>※キーワードで絞り込まずに全件見る場合は、検索欄に何も入力せず「検索」を押下して下さい。</p>
<div class="border border-primary system-border-spacer">
    <form name="form" action="faq_search.php" method="get">
        <div class="form-group">
            <label for="query">検索キーワード</label>
            <input type="text" name="query" class="form-control" id="query">
        </div>
        <div class="form-group">
            <div>検索モード</div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="mode" id="mode_and" value="and" checked="checked">
                <label class="form-check-label" for="mode_and">AND検索</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="mode" id="mode_or" value="or">
                <label class="form-check-label" for="mode_or">OR検索</label>
            </div>
        </div>
        <div class="form-group">
            <div>アルファベットの大文字と小文字、日本語のひらがなとカタカナを…</div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="strict" id="strict_0" value="0" checked="checked">
                <label class="form-check-label" for="strict_0">区別しない</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="strict" id="strict_1" value="1">
                <label class="form-check-label" for="strict_1">区別する</label>
            </div>
        </div>
        <div class="form-group">
            <div>タグで絞り込む</div>
            <div class="btn-group-toggle" data-toggle="buttons">
<?php
foreach ($tagcloud as $tag => $dummy) {
    echo '<label class="btn btn-outline-secondary btn-sm system-tagbtn">
    <input class="form-check-input" type="checkbox" name="tag[]" id="tag_' . urlencode($tag) . '" value="' . hsc($tag) . '" autocomplete="off">' . hsc($tag) . '</label>';
}
?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">検索</button>
    </form>
</div>
<?php
require_once(PAGEROOT . 'help_footer.php');
