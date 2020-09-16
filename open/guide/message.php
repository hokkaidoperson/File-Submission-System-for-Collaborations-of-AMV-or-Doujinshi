<?php
require_once('../../set.php');
$titlepart = 'メッセージ機能について';
require_once(PAGEROOT . 'help_header.php');
?>

<h1>メッセージ機能について</h1>
<p>本システムに備わっているメッセージ機能を用いて、ユーザー同士のやり取りを行う事が出来ます。</p>
<p>メッセージ機能を利用するには、マイページトップから「メッセージ機能」を選択して下さい。</p>
<ol>
    <li><a href="#read">メッセージを読む</a></li>
    <li><a href="#write">メッセージを送信する</a></li>
    <li><a href="#simultaneously">全ユーザーにメッセージを一斉送信する（主催者、システム管理者限定）</a></li>
    <li><a href="#reply">受け取ったメッセージに返信する</a></li>
    <li><a href="#delete">送信したメッセージを削除する</a></li>
</ol>
<h2 id="read">1. メッセージを読む</h2>
<p>あなたが他ユーザーから受け取ったメッセージは「受信BOX」に、あなたが送信したメッセージは「送信BOX」に一覧表示されています。</p>
<p><img src="images/message_topscreen.png" class="img-fluid"><br><span class="small text-muted">※実際の画面と異なる場合があります。</span></p>
<p>件名をクリックすると、メッセージの詳細を閲覧出来ます。</p>

<h2 id="write">2. メッセージを送信する</h2>
<p>「主催者にメッセージを送信する」（主催者、共同運営者の場合は「宛先を選んでメッセージを送信する」）を選択すると、メッセージの新規作成画面に移ります。</p>
<p><img src="images/message_writescreen.png" class="img-fluid"><br><span class="small text-muted">※実際の画面と異なる場合があります。</span></p>
<p>メッセージの宛先を選択し、件名（省略可）とメッセージ内容を入力して、<b>最後に必ず「送信」を押下して下さい</b>（押下しないと送信されません）。</p>
<p>「メッセージを送信しました。」と表示されれば、送信完了です。</p>

<h2 id="simultaneously">3. 全ユーザーにメッセージを一斉送信する（主催者、システム管理者限定）</h2>
<p>「全員にメッセージを一斉送信する」を選択すると、メッセージの新規作成画面に移ります。</p>
<p><img src="images/message_writescreen.png" class="img-fluid"><br><span class="small text-muted">※実際の画面と異なる場合があります。</span></p>
<p>件名（省略可）とメッセージ内容を入力し、<b>最後に必ず「送信」を押下して下さい</b>（押下しないと送信されません）。</p>
<p>「メッセージを送信しました。」と表示されれば、送信完了です。<br>
同じ内容のメッセージが、送信時点で登録されている全ユーザーに送られます。</p>

<h2 id="reply">4. 受け取ったメッセージに返信する</h2>
<p>返信したいメッセージを選択し、青枠で囲われた入力欄をご覧下さい。</p>
<p>メッセージ内容を入力し（件名は必要に応じて変更出来ます）、<b>最後に必ず「送信」を押下して下さい</b>（押下しないと送信されません）。</p>
<p>「メッセージを送信しました。」と表示されれば、送信完了です。</p>

<h2 id="delete">5. 送信したメッセージを削除する</h2>
<p class="text-danger">送信したメッセージを削除すると、送信者自身だけでなく、宛先のユーザーもそのメッセージを読めなくなります。<b>一度削除したメッセージは復元出来ませんので、注意して操作して下さい。</b></p>
<p>削除したいメッセージを選択し、ページ下部に移動して、「このメッセージを削除する」ボタンを押下して下さい。</p>
<p>「メッセージを削除しました。」と表示されれば、削除完了です。</p>

<?php
require_once(PAGEROOT . 'help_footer.php');
