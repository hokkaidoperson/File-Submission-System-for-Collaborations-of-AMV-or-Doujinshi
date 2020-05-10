<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "メッセージの既読状況を送信者が見る事は出来ますか？";
$pron = "メッセージのきどくじょうきょうをそうしんしゃがみることはできますか";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "マイページ",
    "メッセージ機能"
);

$content = <<<EOT
<p>はい、出来ます。<br>
「送信BOX」からあなたが送信したメッセージの詳細画面に移ると、送信先の既読状況を見る事が出来ます。</p>
EOT;