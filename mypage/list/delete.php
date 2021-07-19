<?php
require_once('../../set.php');
setup_session();
$titlepart = 'ファイル削除';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p", "c", "g"), TRUE);

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
$formdata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);

//フォーム設定データ
$formsetting = array();
for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/submit/' . "$i" . '.txt')) break;
    $formsetting[$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/' . "$i" . '.txt'), true);
}
$formsetting["general"] = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);

?>

<h1>ファイル削除</h1>
<p>作品 <strong><?php echo hsc($formdata["title"]); ?></strong> を削除します。</p>
<p>入力情報を削除し、サーバーにアップロードしたファイルがあればそれも削除されます。<br>
<strong>この操作を取り消す事は出来ませんのでご注意願います。</strong></p>
<p>削除しようとしている作品を今一度ご確認願います。</p>
<?php
$lists = [];

    $lists[] = ['提出者', hsc(nickname($author))];
    $lists[] = ['提出日時', date('Y年n月j日G時i分s秒', $id)];
    $lists[] = ['最終更新日時', isset($formdata["editdate"]) ? date('Y年n月j日G時i分s秒', $formdata["editdate"]) : date('Y年n月j日G時i分s秒', $id)];
    if (isset($formdata["editing"]) and $formdata["editing"] == 1) $echotext = '項目編集の承認待ち<br>※変更後の内容は上記表に反映されていません。';
    else switch ($formdata["exam"]) {
        case 0:
            $echotext = '承認待ち';
        break;
        case 1:
            $echotext = '<strong class="text-success">承認</strong>';
        break;
        case 2:
            $echotext = '<strong class="text-warning">修正待ち</strong>';
        break;
        case 3:
            $echotext = '<strong class="text-danger">承認見送り</strong>';
        break;
    }
    $lists[] = ['承認の状態', $echotext];

    if (isset($formdata["author_ip"]) and $_SESSION["state"] == 'p') {
        $status = $formdata["author_ip"] . "／";
        $remotesearch = gethostbyaddr($formdata["author_ip"]);
        if ($formdata["author_ip"] !== $remotesearch) $status .= $remotesearch;
        else $status .= '（リモートホスト名の検索に失敗しました）';
        $lists[] = ['最終更新時のIPアドレス／リモートホスト名（主催者にのみ表示されています）', $status];
    }

    if (isset($formdata["submit"]) and $formdata["submit"] != array()) {
        $echotext = 'ファイル名をクリックするとそのファイルをダウンロードします。<br>';
        foreach ($formdata["submit"] as $filename => $title)
        $echotext .= '<a href="../fnc/filedld.php?author=' . $author . '&genre=submitmain&id=' . $id . '&partid=' . $filename . '" target="_blank">' . hsc($title) . '</a><br>';
        $lists[] = ['提出ファイル', $echotext];
    } else {
        $echotext = '<a href="' . hsc($formdata["url"]) . '" target="_blank" rel="noopener">クリックすると新しいウィンドウで開きます</a>';
        if (isset($formdata["dldpw"]) and $formdata["dldpw"] != "") $echotext .= '<br><span class="small">※パスワード等の入力を求められた場合は、次のパスワードを入力して下さい。<code>' . hsc($formdata["dldpw"]) . '</code></span>';
        if (isset($formdata["due"]) and $formdata["due"] != "") $echotext .= '<br><span class="small">※ダウンロードURLの有効期限は <strong>' . date('Y年n月j日G時i分', $formdata["due"]) . '</strong> までです。お早めにダウンロード願います。</span>';
        $echotext .= '<br><span class="small">※<span class="text-decoration-underline">このファイルは、一括ダウンロード機能でダウンロードする事が出来ません</span>。ダウンロードが必要な場合は、必ずリンク先からダウンロードして下さい。</span>';
        $lists[] = ['提出ファイルダウンロード先', $echotext];
    }

    foreach ($formsetting as $key => $array) {
        if ($key === "general") continue;
        if (!isset($formdata[$array["id"]])) {
            $lists[] = [hsc($array["title"]), ''];
            continue;
        }
        if ($array["type"] == "attach") {
            if ($formdata[$array["id"]] != array()) {
                $echotext = 'ファイル名をクリックするとそのファイルをダウンロードします。<br>';
                foreach ($formdata[$array["id"]] as $filename => $title)
                $echotext .= '<a href="../fnc/filedld.php?author=' . $author . '&genre=submitform&id=' . $id . '&partid=' . $array["id"] . '_' . $filename . '" target="_blank">' . hsc($title) . '</a><br>';
            }
        }
        else {
            $echotext = '';
            for ($answer = 0; $answer < count($formdata[$array["id"]]); $answer++) {
                $echotext .= '<div>';
                if (isset($array["prefix"][$answer]) and $array["prefix"][$answer] != "") $echotext .= '<span class="badge badge-secondary">' . hsc($array["prefix"][$answer]) . '</span> ';
                $echotext .= give_br_tag($formdata[$array["id"]][$answer]);
                if (isset($array["suffix"][$answer]) and $array["suffix"][$answer] != "") $echotext .= ' <span class="badge badge-secondary">' . hsc($array["suffix"][$answer]) . '</span> ';
                $echotext .= '</div>';
            }
        }
        $lists[] = [hsc($array["title"]), $echotext];
    }

echo_desc_list($lists);
?>
<p>削除してもよろしければ、現在のパスワードを入力して「削除する」ボタンを押して下さい。</p>
<form name="form" action="delete_exec.php" method="post" onSubmit="return check()">
<div class="border border-primary system-border-spacer">
<?php csrf_prevention_in_form(); ?>
<input type="hidden" name="author" value="<?php echo $author; ?>">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<div class="form-group">
<label for="password">現在のパスワード</label>
<input type="password" name="password" class="form-control" id="password" onChange="check_individual()">
<div id="password-errortext" class="system-form-error"></div>
</div>
<br>
<button type="submit" class="btn btn-danger">削除する</button>
</div>
<?php
echo_modal_alert("認証中にエラーが発生しました。<br>お手数ですが、インターネット接続環境をご確認頂き、再度「削除する」を押して下さい。", "ネットワーク・エラー", null, null, "neterrormodal", "dismissbtn");
echo_modal_confirm("作品の削除を行います。これが最後の確認です。<br>よろしければ「削除する」を押して下さい。<br>削除を止める場合は「戻る」を押して下さい。", "削除確認", null, null, "削除する", "danger");
?>
</form>
<script type="text/javascript">

function check_individual() {
    var valid = 1;
    document.getElementById("password-errortext").innerHTML = "";
    if(document.form.password.value === ""){
        valid = 0;
        document.getElementById("password-errortext").innerHTML = "入力されていません。";
    } else {
        //参考　https://qiita.com/legokichi/items/801e88462eb5c84af97d
        const obj = {password: document.form.password.value, csrf_prevention_token: "<?php echo csrf_prevention_token(); ?>"};
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
                throw new Error("Stopped because of a network error");
            }
        })
        .then((result) => {
            if (result.auth_status == "NG") {
                throw new Error("Stopped because of an API error - response: " + result.error_detail);
            } else if (result.result == 0) {
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
        const obj = {password: document.form.password.value, csrf_prevention_token: "<?php echo csrf_prevention_token(); ?>"};
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
                throw new Error("Stopped because of a network error");
            }
        })
        .catch((error) => {
            $('#neterrormodal').modal();
            $('#neterrormodal').on('shown.bs.modal', function () {
                document.getElementById("dismissbtn").focus();
            });
            throw new Error("Stopped because of a network error");
        })
        .then((result) => {
            if (result.auth_status == "NG") {
                throw new Error("Stopped because of an API error - response: " + result.error_detail);
            } else if (result.result == 0) {
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


</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
