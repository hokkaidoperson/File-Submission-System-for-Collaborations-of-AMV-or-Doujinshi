<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "パスワードはシステム内部に平文で保存されますか？";
$pron = "パスワードはシステムないぶにひらぶんでほぞんされますか";
$tags = array(
    "主催者",
    "共同運営者",
    "一般参加者",
    "非参加者",
    "システム全般",
    "ユーザー登録",
    "アカウント情報編集"
);

$content = <<<EOT
<p>いいえ、パスワードは平文でない形式で保存されます。</p>
<p>パスワードは、ハッシュ化（復号出来ないように暗号化する事）された状態で、システム内に保持されます。<br>
入力されたパスワードとハッシュ化されたパスワードを照合し、パスワードが合っているか合っていないかを確かめる事は出来ますが、ハッシュ化されたパスワードから元のパスワードを直接割り出す事は出来ません。</p>
EOT;
