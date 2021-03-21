<?php
//スクリプト内からの呼び出しでなければ終了
if (!defined('DATAROOT')) die();

$title = "パスワードはどのようなものが望ましいですか？";
$pron = "パスワードはどのようなものがのぞましいですか";
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
<p>一定以上の堅牢性を確保するため、本システムでは最低<strong>8文字以上</strong>のパスワードを設定する必要があります。</p>
<p>その他、一般的に、パスワードを設定する際は以下のようにすると望ましいとされています。パスワードを考える際の参考にしてみて下さい。</p>
<ul>
<li>生年月日など、他人が知りうる情報をパスワードに使用すると、パスワードを類推されやすくなるため避ける。</li>
<li>長めのパスワードにする。</li>
<li>英字だけ、数字だけのパスワードではなく、英数字・記号を織り交ぜた複雑なパスワードにする。</li>
<li>他のサービスのパスワードを使い回すのは避ける。</li>
</ul>
EOT;
