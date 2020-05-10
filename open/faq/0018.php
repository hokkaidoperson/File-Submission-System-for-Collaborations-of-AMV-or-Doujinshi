<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "「一般参加者」とはどのような立場ですか？";
$pron = "いっぱんさんかしゃとはどのようなたちばですか";
$tags = array(
    "一般参加者",
    "システム全般"
);

$content = <<<EOT
<p>イベントの運営スタッフでない参加者が就く事を想定している立場です。<br>
多くのユーザーがこの立場になると思われます。<br>
他者の作品へのアクセス権は無く、本人の作品のみ閲覧・操作可能です。</p>
EOT;
