<?php
require_once('../../../set.php');
session_start();
$titlepart = 'ユーザー登録画面 項目の並べ替え・削除';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//主催者だけ
if ($_SESSION["state"] == 'p') $accessok = 'p';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>主催者</b>のみです。</p>
<p><a href="../../index.php">マイページトップに戻る</a></p>');


if (!isset($_SESSION["userformdata"])) die_mypage('<h1>エラーが発生しました</h1>
<p><a href="index.php">こちらをクリックして編集画面トップに戻って下さい。</a></p>');

?>

<h1>項目の並べ替え・削除</h1>
<p>項目の傍にある「↑」・「↓」ボタンを押すと、当該項目が上もしくは下に移動します。また、項目の右端にある「削除」ボタンを押すと、当該項目が削除されます。<br>
最後に、「決定」ボタンを押して、変更内容を確定して下さい。
項目を誤って削除してしまった場合などには、「変更内容を保存しないで戻る」ボタンを押すと、設定内容が元に戻ります。<br>
「ユーザーID」「ニックネーム」「メールアドレス」「パスワード」は変更出来ません。</p>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th>上へ</th><th>下へ</th><th>項目名</th><th>入力形式</th><th>削除</th>
</tr>
<tr>
<td>×</td><td>×</td><td>ユーザーID</td><td>テキストボックス</td><td>×</td>
</tr>
<tr>
<td>×</td><td>×</td><td>ニックネーム</td><td>テキストボックス</td><td>×</td>
</tr>
<tr>
<td>×</td><td>×</td><td>メールアドレス</td><td>テキストボックス</td><td>×</td>
</tr>
<tr>
<td>×</td><td>×</td><td>パスワード</td><td>テキストボックス</td><td>×</td>
</tr>
<?php
for ($i = 0; $i <= 9; $i++) {
    if (!isset($_SESSION["userformdata"][$i])) break;
    echo "<tr>\n";
    if ($i == 0 and isset($_SESSION["userformdata"][$i + 1])) {
        echo '<td>×</td><td><a href="orderanddel_handle.php?number=' . "$i" . '&do=down" class="btn btn-light" role="button">↓</a></td>';
    } else if ($i == 0 and !isset($_SESSION["userformdata"][$i + 1])) {
            echo '<td>×</td><td>×</td>';
    } else if (!isset($_SESSION["userformdata"][$i + 1])){
        echo '<td><a href="orderanddel_handle.php?number=' . "$i" . '&do=up" class="btn btn-light" role="button">↑</a></td><td>×</td>';
    } else {
        echo '<td><a href="orderanddel_handle.php?number=' . "$i" . '&do=up" class="btn btn-light" role="button">↑</a></td><td><a href="orderanddel_handle.php?number=' . "$i" . '&do=down" class="btn btn-light" role="button">↓</a></td>';
    }
    echo '<td>' . htmlspecialchars($_SESSION["userformdata"][$i]["title"]) . '</td>';
    switch ($_SESSION["userformdata"][$i]["type"]) {
        case 'textbox':
            echo "<td>テキストボックス</td>";
            break;
        case 'textbox2':
            echo "<td>テキストボックス×2</td>";
            break;
        case 'textarea':
            echo "<td>テキストエリア</td>";
            break;
        case 'radio':
            echo "<td>ラジオボタン</td>";
            break;
        case 'check':
            echo "<td>チェックボックス</td>";
            break;
        case 'dropdown':
            echo "<td>ドロップダウンリスト</td>";
            break;
        case 'attach':
            echo "<td>添付ファイル</td>";
            break;
    }
    echo '<td><a href="orderanddel_handle.php?number=' . "$i" . '&do=delete" class="btn btn-light" role="button">削除</a></td>';
    echo "</tr>\n";
}
?>
</table>
</div>
<p><a href="orderanddel_save.php" class="btn btn-primary" role="button" onclick="return window.confirm('変更内容を保存します。よろしいですか？')">決定</a>
<a href="reload.php" class="btn btn-secondary" role="button" onclick="return window.confirm('現在の設定内容を保存せず、メニューに戻ります。よろしいですか？')">変更内容を保存しないで戻る</a></p>

<?php
require_once(PAGEROOT . 'mypage_footer.php');
?>