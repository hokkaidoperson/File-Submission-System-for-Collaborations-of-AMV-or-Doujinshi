<?php
require_once('../../set.php');
session_start();
$titlepart = 'ファイル削除';
require_once(PAGEROOT . 'mypage_header.php');

$accessok = 'none';

//非参加者以外
if ($_SESSION["state"] != 'o') $accessok = 'ok';

if ($accessok == 'none') die_mypage('<h1>権限エラー</h1>
<p>この機能にアクセス出来るのは、<b>非参加者以外のユーザー</b>です。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');

//ファイル提出者のユーザーID
$author = basename($_GET["author"]);

//提出ID
$id = basename($_GET["id"]);

if ($author == "" or $id == "") die_mypage('パラメーターエラー');


//自分のファイルのみ編集可
if ($author != $_SESSION['userid']) die_mypage('ご自身のファイルのみ、編集が可能です。');

if (outofterm($id) != FALSE) $outofterm = TRUE;
else $outofterm = FALSE;
if ($_SESSION["state"] == 'p') $outofterm = TRUE;
if (!in_term() and !$outofterm) die_mypage('現在、ファイル提出期間外のため、ファイル操作は行えません。');


//入力済み情報を読み込む
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die_mypage('ファイルが存在しません。');
$formdata = json_decode(file_get_contents(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);

//フォーム設定データ
$formsetting = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $formsetting[$i] = json_decode(file_get_contents(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}
$formsetting["general"] = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);

?>

<h1>ファイル削除</h1>
<p>作品 <b><?php echo htmlspecialchars($formdata["title"]); ?></b> を削除します。</p>
<p>入力情報を削除し、サーバーにアップロードしたファイルがあればそれも削除されます。<br>
<b>この操作を取り消す事は出来ませんのでご注意願います。</b></p>
<p>削除しようとしている作品を今一度ご確認願います。</p>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<?php
if (isset($formdata["submit"]) and $formdata["submit"] != array()) {
    echo '<tr><th width="30%">提出ファイル</th><td width="70%">ファイル名をクリックするとそのファイルをダウンロードします。<br>';
    foreach ($formdata["submit"] as $filename => $title)
    echo '<a href="../fnc/filedld.php?author=' . $author . '&genre=submitmain&id=' . $id . '&partid=' . $filename . '" target="_blank">' . htmlspecialchars($title) . '</a><br>';
    echo '</td></tr>';
} else {
    echo '<tr>
<th>提出ファイルダウンロード先</th><td><a href="' . htmlspecialchars($formdata["url"]) . '" target="_blank">クリックすると新しいウィンドウで開きます</a>';
    if (isset($formdata["dldpw"]) and $formdata["dldpw"] != "") echo '<br><font size="2">※パスワード等の入力を求められた場合は、次のパスワードを入力して下さい。<code>' . htmlspecialchars($formdata["dldpw"]) . '</code></font>';
    if (isset($formdata["due"]) and $formdata["due"] != "") echo '<br><font size="2">※ダウンロードURLの有効期限は <b>' . date('Y年n月j日G時i分', $formdata["due"]) . '</b> までです。お早めにダウンロード願います。</font>';
    echo '<br><font size="2">※<u>このファイルは、一括ダウンロード機能でダウンロードする事が出来ません</u>。ダウンロードが必要な場合は、必ずリンク先からダウンロードして下さい。</font>';
    echo '</td></tr>';
}
?>
<tr>
<th>タイトル</th><td><?php echo htmlspecialchars($formdata["title"]); ?></td>
</tr>
<tr>
<th>提出日時</th><td><?php echo date('Y年n月j日G時i分s秒', $id); ?></td>
</tr>
<tr>
<th>最終更新日時</th><td><?php if (isset($formdata["editdate"])) echo date('Y年n月j日G時i分s秒', $formdata["editdate"]); else echo date('Y年n月j日G時i分s秒', $id); ?></td>
</tr>
<?php
foreach ($formsetting as $key => $array) {
    if ($key === "general") continue;
    echo "<tr>\n";
    echo "<th>" . htmlspecialchars($array["title"]) . "</th>";
    echo "<td>";
    if ($array["type"] == "attach") {
        if (isset($formdata[$array["id"]]) and $formdata[$array["id"]] != array()) {
            echo 'ファイル名をクリックするとそのファイルをダウンロードします。<br>';
            foreach ($formdata[$array["id"]] as $filename => $title)
            echo '<a href="../fnc/filedld.php?author=' . $author . '&genre=submitform&id=' . $id . '&partid=' . $array["id"] . '_' . $filename . '" target="_blank">' . htmlspecialchars($title) . '</a><br>';
        }
    }
    else if ($array["type"] == "check") {
        $dsp = implode("\n", $formdata[$array["id"]]);
        $dsp = htmlspecialchars($dsp);
        echo str_replace("\n", '<br>', $dsp);
    } else if ($array["type"] == "textbox2") {
        echo htmlspecialchars($formdata[$array["id"] . "-1"]);
        echo '<br>';
        echo htmlspecialchars($formdata[$array["id"] . "-2"]);
    } else echo give_br_tag($formdata[$array["id"]]);
    echo '</td>';
    echo "</tr>\n";
}
?>
<tr><th>承認の状態</th><?php
if (isset($formdata["editing"]) and $formdata["editing"] == 1) echo '<td>項目編集の承認待ち<br>※変更後の内容は上記表に反映されていません。</td>';
else switch ($formdata["exam"]) {
    case 0:
        echo '<td>承認待ち</td>';
    break;
    case 1:
        echo '<td class="text-success"><b>承認</b></td>';
    break;
    case 2:
        echo '<td class="text-warning"><b>修正待ち</b></td>';
    break;
    case 3:
        echo '<td class="text-danger"><b>承認見送り</b></td>';
    break;
}
echo "</tr>";
?>
</table>
</div>
<p>削除してもよろしければ、現在のパスワードを入力して「削除する」ボタンを押して下さい。</p>
<form name="form" action="delete_exec.php" method="post" onSubmit="return check()">
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<input type="hidden" name="successfully" value="1">
<input type="hidden" name="author" value="<?php echo $author; ?>">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<div class="form-group">
<label for="password">現在のパスワード</label>
<input type="password" name="password" class="form-control" id="password" onBlur="check_individual()">
<div id="password-errortext" class="invalid-feedback" style="display: block;"></div>
</div>
<br>
<button type="submit" class="btn btn-danger">削除する</button>
</div>
<!-- 接続エラーModal -->
<div class="modal fade" id="neterrormodal" tabindex="-1" role="dialog" aria-labelledby="neterrormodaltitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="neterrormodaltitle">ネットワーク・エラー</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
入力内容の検証中にエラーが発生しました。<br>
お手数ですが、インターネット接続環境をご確認頂き、再度「削除する」を押して下さい。
</div>
<div class="modal-footer">
<button type="button" class="btn btn-primary" data-dismiss="modal" id="dismissbtn">OK</button>
</div>
</div>
</div>
</div>
<!-- 送信確認Modal -->
<div class="modal fade" id="confirmmodal" tabindex="-1" role="dialog" aria-labelledby="confirmmodaltitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="confirmmodaltitle">削除確認</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
作品の削除を行います。これが最後の確認です。<br>
よろしければ「削除する」を押して下さい。<br>
削除を止める場合は「戻る」を押して下さい。
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
<button type="button" class="btn btn-danger" id="submitbtn" onClick="submittohandle();">削除する</button>
</div>
</div>
</div>
</div>
</form>
<script type="text/javascript">
<!--
function check_individual() {
    var valid = 1;
    document.getElementById("password-errortext").innerHTML = "";
    if(document.form.password.value === ""){
        valid = 0;
        document.getElementById("password-errortext").innerHTML = "入力されていません。";
    } else {
        //参考　https://qiita.com/legokichi/items/801e88462eb5c84af97d
        const obj = {password: document.form.password.value};
        const method = "POST";
        const body = Object.keys(obj).map((key)=>key+"="+encodeURIComponent(obj[key])).join("&");
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
        };
        fetch('../fnc/api_verifypw.php', {method, headers, body})
        .then((response) => {
            if(response.ok) {
                return response.json();
            } else {
                throw new Error();
            }
        })
        .then((result) => {
            if (result.result == 0) {
                document.getElementById("password-errortext").innerHTML = "パスワードに誤りがあります。";
                document.form.password.classList.add("is-invalid");
                document.form.password.classList.remove("is-valid");
            } else {
                document.form.password.classList.add("is-valid");
                document.form.password.classList.remove("is-invalid");
            }
        })
    }
    if (valid) {
        document.form.password.classList.add("is-valid");
        document.form.password.classList.remove("is-invalid");
    } else {
        document.form.password.classList.add("is-invalid");
        document.form.password.classList.remove("is-valid");
    }
    return;
}

function check(){
    var valid = 1;

    document.getElementById("password-errortext").innerHTML = "";
    if(document.form.password.value === ""){
        valid = 0;
        document.getElementById("password-errortext").innerHTML = "入力されていません。";
    } else {
        //参考　https://qiita.com/legokichi/items/801e88462eb5c84af97d
        const obj = {password: document.form.password.value};
        const method = "POST";
        const body = Object.keys(obj).map((key)=>key+"="+encodeURIComponent(obj[key])).join("&");
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
        };
        fetch('../fnc/api_verifypw.php', {method, headers, body})
        .then((response) => {
            if(response.ok) {
                return response.json();
            } else {
                $('#neterrormodal').modal();
                $('#neterrormodal').on('shown.bs.modal', function () {
                    document.getElementById("dismissbtn").focus();
                });
                throw new Error();
            }
        })
        .catch((error) => {
            $('#neterrormodal').modal();
            $('#neterrormodal').on('shown.bs.modal', function () {
                document.getElementById("dismissbtn").focus();
            });
            throw new Error();
        })
        .then((result) => {
            if (result.result == 0) {
                document.getElementById("password-errortext").innerHTML = "パスワードに誤りがあります。";
                document.form.password.classList.add("is-invalid");
                document.form.password.classList.remove("is-valid");
            } else {
                document.form.password.classList.add("is-valid");
                document.form.password.classList.remove("is-invalid");
                $('#confirmmodal').modal();
            }
        })
    }
    if (valid) {
        document.form.password.classList.add("is-valid");
        document.form.password.classList.remove("is-invalid");
    } else {
        document.form.password.classList.add("is-invalid");
        document.form.password.classList.remove("is-valid");
    }
    return false;

}

function submittohandle() {
    submitbtn = document.getElementById("submitbtn");
    submitbtn.disabled = "disabled";
    document.form.submit();
}

// -->
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
