<?php
require_once('../../set.php');
$titlepart = 'システムの基本的な使い方';
require_once(PAGEROOT . 'help_header.php');
?>

<h1>システムの基本的な使い方</h1>
<ol>
    <li><a href="#login">ログインする</a></li>
    <li><a href="#whileloggedin">ログイン中</a></li>
    <li><a href="#logout">ログアウトする</a></li>
</ol>
<h2 id="login">1. ログインする</h2>
<p>ログインしていない状態で本システムにアクセスすると、このような画面が表示されます。</p>
<p><img src="images/topscreen.png" class="img-fluid"><br><span class="small text-muted">※実際の画面と異なる場合があります。</span></p>
<p>既にユーザー登録がお済みの場合、ユーザーIDとパスワードを入力し、ログインして下さい。<br>
ユーザー登録がお済みでない、パスワードを忘れてしまった等の場合は、ログインボタン下部の青字部分を選択し、画面の指示に従って下さい。</p>
<h2 id="whileloggedin">2. ログイン中</h2>
<p>ログイン直後、このような画面が表示されます（マイページトップ画面でなく各種操作画面がいきなり表示される場合もあります）。</p>
<p><img src="images/mypagetop.png" class="img-fluid"><br><span class="small text-muted">※実際の画面では、上部「テストケース」の部分には「<?php echo $eventname; ?>」と表示されます。また、他にも、実際の画面と異なる場合があります。</span></p>
<p>行いたい操作を選び、画面の指示に従って下さい。<br>
また、各ページ最上部の「<?php echo $eventname; ?>」をクリック・タップすると、いつでもマイページトップ画面に戻れます。</p>
<h2 id="logout">3. ログアウトする</h2>
<p>ログイン中、各ページ右上の「ログイン中」部分をクリック・タップすると、このようなメニューが展開します。</p>
<p><img src="images/logout.png" class="img-fluid"><br><span class="small text-muted">※実際の画面では、あなたのニックネームや立場（主催者、一般参加者など）が表示されます。</span></p>
<p>「ログアウト」（画像中、赤枠で囲われた場所）を選択し、ログイン画面に戻って来たら、ログアウト完了です。</p>


<?php
require_once(PAGEROOT . 'help_footer.php');
