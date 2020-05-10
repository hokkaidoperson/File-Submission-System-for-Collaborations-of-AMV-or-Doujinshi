<?php
require_once('../set.php');

$dsp = array();
$tagcloud = array();
foreach (glob('faq/*.php') as $filename) {
    $id = basename($filename, ".php");
    require_once($filename);
    $dsp[$id] = array(
        "title" => $title,
        "pron" => $pron,
        "tags" => $tags,
        "content" => $content
    );
    foreach ($tags as $tag) $tagcloud[$tag] = 1;
}

if (isset($_GET["strict"]) and $_GET["strict"] == "1") $strict = TRUE;
else $strict = FALSE;
if (isset($_GET["mode"]) and $_GET["mode"] == "or") $ormode = TRUE;
else $ormode = FALSE;

//・クエリの余分なスペースを除く
//・日本語は全角、英数字は半角
//・厳密検索でない場合はひらがなに揃える
if (isset($_GET["query"]) and trim($_GET["query"]) != "") {
    if ($strict) $option = "asKV"; //英数字とスペースを半角に　半角カタカナを全角に
    else $option = "asHcV"; //英数字とスペースを半角に　半角・全角カタカナを全角ひらがなに
    $query = mb_convert_kana($_GET["query"], $option, "UTF-8");
    $query = trim($query);
    $query = preg_replace('/\s\s+/', ' ', $query);
    if (!$strict) $query = mb_strtolower($query, "UTF-8");
    $queries = explode(" ", $query);

    //探す
    $hitnumber = count($queries); //AND検索の時はクエリの数とヒット数が一致している時だけ
    foreach ($dsp as $id => $subject) {
        $foundwords = 0;
        $hits = 0;
        foreach ($queries as $keyword) {
            $wordfound = FALSE;
            $titlesearch = mb_convert_kana($subject["title"], $option, "UTF-8");
            if (!$strict) $titlesearch = mb_strtolower($titlesearch, "UTF-8");
            $titlehits = mb_substr_count($titlesearch, $keyword, "UTF-8");
            if ($titlehits > 0) {
                $wordfound = TRUE;
                $hits += $titlehits;
            }
            $pronsearch = mb_convert_kana($subject["pron"], $option, "UTF-8");
            if (!$strict) $pronsearch = mb_strtolower($pronsearch, "UTF-8");
            $pronhits = mb_substr_count($pronsearch, $keyword, "UTF-8");
            if ($pronhits > 0) {
                $wordfound = TRUE;
                $hits += $pronhits;
            }
            $contentsearch = mb_convert_kana(strip_tags($subject["content"]), $option, "UTF-8");
            if (!$strict) $contentsearch = mb_strtolower($contentsearch, "UTF-8");
            $contenthits = mb_substr_count($contentsearch, $keyword, "UTF-8");
            if ($contenthits > 0) {
                $wordfound = TRUE;
                $hits += $contenthits;
            }
            if ($wordfound) $foundwords++;
        }
        if (!($ormode and $foundwords > 0) and !(!$ormode and $foundwords >= $hitnumber)) unset($dsp[$id]);
        else $dsp[$id]["hits"] = $hits;
    }
}

if (is_array($_GET["tag"]) and $_GET["tag"] != array()) {
    foreach ($dsp as $id => $subject) {
        $found = TRUE;
        foreach ($_GET["tag"] as $keyword) {
            if (!in_array($keyword, $subject["tags"])) $found = FALSE;
        }
        if (!$found) unset($dsp[$id]);
    }

}

$titlepart = 'FAQ 検索結果';
require_once(PAGEROOT . 'help_header.php');
?>

<h1>FAQ（よくある質問） - <?php
if (isset($_GET["query"]) and trim($_GET["query"]) != "") {
    echo '「' . hsc($_GET["query"]) . '」での検索結果';
    if (is_array($_GET["tag"]) and $_GET["tag"] != array()) echo '・タグで絞り込み';
}
else if (is_array($_GET["tag"]) and $_GET["tag"] != array()) echo 'タグで絞り込み';
else echo '全件表示';
?></h1>
<form name="form" action="faq_search.php" method="get">
    <div class="form-group">
        <label for="query">検索キーワード</label>
        <input type="text" name="query" class="form-control" id="query"<?php
        if (isset($_GET["query"]) and $_GET["query"] != "") echo ' value="' . hsc($_GET["query"]) . '"';
        ?>>
    </div>
    <div class="form-group">
        <div>検索モード</div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="mode" id="mode_and" value="and"<?php
            if (!$ormode) echo ' checked="checked"';
            ?>>
            <label class="form-check-label" for="mode_and">AND検索</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="mode" id="mode_or" value="or"<?php
            if ($ormode) echo ' checked="checked"';
            ?>>
            <label class="form-check-label" for="mode_or">OR検索</label>
        </div>
    </div>
    <div class="form-group">
        <div>アルファベットの大文字と小文字、日本語のひらがなとカタカナを…</div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="strict" id="strict_0" value="0"<?php
            if (!$strict) echo ' checked="checked"';
            ?>>
            <label class="form-check-label" for="strict_0">区別しない</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="strict" id="strict_1" value="1"<?php
            if ($strict) echo ' checked="checked"';
            ?>>
            <label class="form-check-label" for="strict_1">区別する</label>
        </div>
    </div>
    <div class="form-group">
        <div>タグで絞り込む</div>
        <div class="btn-group-toggle" data-toggle="buttons">
<?php
foreach ($tagcloud as $tag => $dummy) {
    if (is_array($_GET["tag"]) and array_search($tag, $_GET["tag"]) !== FALSE) $checked = TRUE;
    else $checked = FALSE;
    if ($checked) echo '<label class="btn btn-outline-secondary btn-sm active" style="margin:0.1em;">';
    else echo '<label class="btn btn-outline-secondary btn-sm" style="margin:0.1em;">';
    echo '<input class="form-check-input" type="checkbox" name="tag[]" id="tag_' . urlencode($tag) . '" value="' . hsc($tag) . '" autocomplete="off"';
    if ($checked) echo ' checked="checked"';
    echo '>' . hsc($tag) . '</label>';
}
?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">再検索</button>
</form>
<hr>
<div style="margin-top:1em; margin-bottom:1em;">
<?php
if (isset($_GET["query"]) and trim($_GET["query"]) != "") uasort($dsp, "faq_callback_fnc");

foreach ($dsp as $id => $subject) {
    $id = urlencode($id);
    $title = hsc($subject["title"]);
    $content = hsc(strip_tags($subject["content"]));
    if (isset($subject["hits"])) $hits = '<div class="card-footer text-muted">ヒット数：' . hsc($subject["hits"]) . '</div>';
    else $hits = '';
    $trimmed = mb_substr($content, 0, 100);
    if ($trimmed !== $content) $content = $trimmed . '…';
    echo <<<EOT
<div class="card">
  <div class="card-body">
    <h5 class="card-title"><a href="faq_read.php?id=$id" class="stretched-link">$title</a></h5>
    <p class="card-text"><small>$content</small></p>
  </div>
  $hits
</div>
EOT;
}

if (count($dsp) == 0) echo <<<EOT
<p>お探しの条件でFAQが見付かりませんでした。</p>
<p>以下をお試し頂くと、お探しのFAQが見付かる可能性があります。</p>
<ul>
    <li>検索キーワードを別の言い方に変えてみる。</li>
    <li>検索キーワードのひらがな・カタカナを漢字に直したり、漢字をひらがな・カタカナに直したりしてみる。</li>
    <li>検索キーワードをスペースで区切ってみる。</li>
    <li>キーワード以外の検索条件を変えてみる。</li>
</ul>
EOT;
?>
</div>

<?php
require_once(PAGEROOT . 'help_footer.php');
