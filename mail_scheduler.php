<?php
//メールの自動配信制御用
//スケジューラーやCronジョブを使う時はこのファイルを呼び出す

//絶対パスで読み込む
require_once(realpath(dirname(__FILE__).'/set.php'));

if (file_exists(DATAROOT . 'form/submit/done.txt')) {
    //日時の計算
    $general = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);

    $date = array();

    //開始日時
    $date['from_just'] = (int)$general["from"];

    //開始日時の3，2，1日前
    $date['from_1'] = $date['from_just'] - 24 * 60 * 60;
    $date['from_2'] = $date['from_1'] - 24 * 60 * 60;
    $date['from_3'] = $date['from_2'] - 24 * 60 * 60;

    //締切日時
    $date['until_just'] = (int)$general["until"];

    //開始日時の3，2，1日前
    $date['until_1'] = $date['until_just'] - 24 * 60 * 60;
    $date['until_2'] = $date['until_1'] - 24 * 60 * 60;
    $date['until_3'] = $date['until_2'] - 24 * 60 * 60;

    //ループ処理用
    $roop = array('from_3', 'from_2', 'from_1', 'from_just', 'until_3', 'until_2', 'until_1', 'until_just');
    $current = time();

    foreach ($roop as $value) {
        if (file_exists(DATAROOT . 'mail_schedule/' . $value . '.txt') and $date[$value] <= $current) {
            $dsp = date('Y年n月j日G時i分s秒', $date['from_just']);
            $dspu = date('Y年n月j日G時i分s秒', $date['until_just']);
            foreach (users_array() as $array) {
                if ($array["state"] == "o") continue;
                $nickname = $array["nickname"];
                switch ($value) {
                    case 'from_3':
                        $subject = "提出受付開始3日前のお知らせ";
                        $content = <<<EOT
$nickname 様

$eventname のファイル提出受付が、3日後（ $dsp ）より始まりますので、お知らせ致します。


【ファイルの提出方法のご案内】
$dsp 以降、ポータルサイトよりファイルの提出を行えます。
ポータルサイトにログイン後、マイページトップの「作品を提出する」を選択し、
画面の指示に従って下さい。

【提出した作品の確認・編集について】
提出した作品は、マイページトップの「提出済み作品の一覧・編集」（主催者の場合は
「参加者・作品の一覧・編集」）から確認を行えます。一覧から作品を選択すると、
詳細画面に移ります。
自分の作品を編集する場合は、詳細画面下部の「ファイル操作」から、作品の編集および
削除を行えます。

【アカウント情報（ニックネームなど）の編集について】
ニックネームなど、ポータルサイト登録時に入力した情報を変更する場合は、マイページ
トップの「アカウント情報編集」を選択して下さい（画面右上の「ログイン中」というボタンを
押すと出てくるメニューから「アカウント情報編集」を選択しても構いません）。
以降は、画面の指示に従って下さい。

EOT;
                    break;
                    case 'from_2':
                        $subject = "提出受付開始2日前のお知らせ";
                        $content = <<<EOT
$nickname 様

$eventname のファイル提出受付が、2日後（ $dsp ）より始まりますので、お知らせ致します。


【ファイルの提出方法のご案内】
$dsp 以降、ポータルサイトよりファイルの提出を行えます。
ポータルサイトにログイン後、マイページトップの「作品を提出する」を選択し、
画面の指示に従って下さい。

【提出した作品の確認・編集について】
提出した作品は、マイページトップの「提出済み作品の一覧・編集」（主催者の場合は
「参加者・作品の一覧・編集」）から確認を行えます。一覧から作品を選択すると、
詳細画面に移ります。
自分の作品を編集する場合は、詳細画面下部の「ファイル操作」から、作品の編集および
削除を行えます。

【アカウント情報（ニックネームなど）の編集について】
ニックネームなど、ポータルサイト登録時に入力した情報を変更する場合は、マイページ
トップの「アカウント情報編集」を選択して下さい（画面右上の「ログイン中」というボタンを
押すと出てくるメニューから「アカウント情報編集」を選択しても構いません）。
以降は、画面の指示に従って下さい。

EOT;
                    break;
                    case 'from_1':
                        $subject = "提出受付開始前日のお知らせ";
                        $content = <<<EOT
$nickname 様

$eventname のファイル提出受付開始日（ $dsp ）の前日になりましたので、お知らせ致します。


【ファイルの提出方法のご案内】
$dsp 以降、ポータルサイトよりファイルの提出を行えます。
ポータルサイトにログイン後、マイページトップの「作品を提出する」を選択し、
画面の指示に従って下さい。

【提出した作品の確認・編集について】
提出した作品は、マイページトップの「提出済み作品の一覧・編集」（主催者の場合は
「参加者・作品の一覧・編集」）から確認を行えます。一覧から作品を選択すると、
詳細画面に移ります。
自分の作品を編集する場合は、詳細画面下部の「ファイル操作」から、作品の編集および
削除を行えます。

【アカウント情報（ニックネームなど）の編集について】
ニックネームなど、ポータルサイト登録時に入力した情報を変更する場合は、マイページ
トップの「アカウント情報編集」を選択して下さい（画面右上の「ログイン中」というボタンを
押すと出てくるメニューから「アカウント情報編集」を選択しても構いません）。
以降は、画面の指示に従って下さい。

EOT;
                    break;
                    case 'from_just':
                        $subject = "提出受付を開始しました";
                        $content = <<<EOT
$nickname 様

$eventname のファイル提出受付を、$dsp より開始しました。


【ファイルの提出方法のご案内】
ポータルサイトよりファイルの提出を行えるようになりました。
ポータルサイトにログイン後、マイページトップの「作品を提出する」を選択し、
画面の指示に従って下さい。

【提出した作品の確認・編集について】
提出した作品は、マイページトップの「提出済み作品の一覧・編集」（主催者の場合は
「参加者・作品の一覧・編集」）から確認を行えます。一覧から作品を選択すると、
詳細画面に移ります。
自分の作品を編集する場合は、詳細画面下部の「ファイル操作」から、作品の編集および
削除を行えます。

【アカウント情報（ニックネームなど）の編集について】
ニックネームなど、ポータルサイト登録時に入力した情報を変更する場合は、マイページ
トップの「アカウント情報編集」を選択して下さい（画面右上の「ログイン中」というボタンを
押すと出てくるメニューから「アカウント情報編集」を選択しても構いません）。
以降は、画面の指示に従って下さい。

EOT;
                    break;
                    case 'until_3':
                        $subject = "提出受付締切3日前のお知らせ";
                        $content = <<<EOT
$nickname 様

$eventname のファイル提出受付は、3日後（ $dspu ）に締め切られますので、お知らせ致します。


【提出締め切り後について】
$dspu にファイル提出の締め切りを迎え、ポータルサイトは以下のような状態となります。
・ファイルの新規提出機能にロックが掛かります。
・提出した作品の確認は行えますが、編集・削除機能にロックが掛かります。
・アカウント情報編集機能に制限が掛かり、メールアドレス・パスワード以外は
　編集出来なくなります。
・その他の機能（メッセージ機能など）はご利用になれます。

【締め切り後に提出・情報編集をする場合は】
締め切り後でも、主催者が認めた場合には作品の新規提出・情報編集を行えます。
締め切り後に作品提出・情報編集が必要になった場合は、主催者にご連絡下さい。
※主催者の方は、マイページトップから「権限コントロール」→「提出期間外のファイル提出・
　情報編集権限を操作」の順に選択すると、締め切り後の提出・編集を許可する画面に移れます。

【ファイルの提出方法のご案内】
$dspu まで、ポータルサイトよりファイルの提出を行えます。
ポータルサイトにログイン後、マイページトップの「作品を提出する」を選択し、
画面の指示に従って下さい。

【提出した作品の確認・編集について】
提出した作品は、マイページトップの「提出済み作品の一覧・編集」（主催者の場合は
「参加者・作品の一覧・編集」）から確認を行えます。一覧から作品を選択すると、
詳細画面に移ります。
自分の作品を編集する場合は、詳細画面下部の「ファイル操作」から、作品の編集および
削除を行えます。

【アカウント情報（ニックネームなど）の編集について】
ニックネームなど、ポータルサイト登録時に入力した情報を変更する場合は、マイページ
トップの「アカウント情報編集」を選択して下さい（画面右上の「ログイン中」というボタンを
押すと出てくるメニューから「アカウント情報編集」を選択しても構いません）。
以降は、画面の指示に従って下さい。

EOT;
                    break;
                    case 'until_2':
                        $subject = "提出受付締切2日前のお知らせ";
                        $content = <<<EOT
$nickname 様

$eventname のファイル提出受付は、2日後（ $dspu ）に締め切られますので、お知らせ致します。


【提出締め切り後について】
$dspu にファイル提出の締め切りを迎え、ポータルサイトは以下のような状態となります。
・ファイルの新規提出機能にロックが掛かります。
・提出した作品の確認は行えますが、編集・削除機能にロックが掛かります。
・アカウント情報編集機能に制限が掛かり、メールアドレス・パスワード以外は
　編集出来なくなります。
・その他の機能（メッセージ機能など）はご利用になれます。

【締め切り後に提出・情報編集をする場合は】
締め切り後でも、主催者が認めた場合には作品の新規提出・情報編集を行えます。
締め切り後に作品提出・情報編集が必要になった場合は、主催者にご連絡下さい。
※主催者の方は、マイページトップから「権限コントロール」→「提出期間外のファイル提出・
　情報編集権限を操作」の順に選択すると、締め切り後の提出・編集を許可する画面に移れます。

【ファイルの提出方法のご案内】
$dspu まで、ポータルサイトよりファイルの提出を行えます。
ポータルサイトにログイン後、マイページトップの「作品を提出する」を選択し、
画面の指示に従って下さい。

【提出した作品の確認・編集について】
提出した作品は、マイページトップの「提出済み作品の一覧・編集」（主催者の場合は
「参加者・作品の一覧・編集」）から確認を行えます。一覧から作品を選択すると、
詳細画面に移ります。
自分の作品を編集する場合は、詳細画面下部の「ファイル操作」から、作品の編集および
削除を行えます。

【アカウント情報（ニックネームなど）の編集について】
ニックネームなど、ポータルサイト登録時に入力した情報を変更する場合は、マイページ
トップの「アカウント情報編集」を選択して下さい（画面右上の「ログイン中」というボタンを
押すと出てくるメニューから「アカウント情報編集」を選択しても構いません）。
以降は、画面の指示に従って下さい。

EOT;
                    break;
                    case 'until_1':
                        $subject = "提出受付締切前日のお知らせ";
                        $content = <<<EOT
$nickname 様

$eventname のファイル提出締切日（ $dspu ）の前日になりましたので、お知らせ致します。


【提出締め切り後について】
$dspu にファイル提出の締め切りを迎え、ポータルサイトは以下のような状態となります。
・ファイルの新規提出機能にロックが掛かります。
・提出した作品の確認は行えますが、編集・削除機能にロックが掛かります。
・アカウント情報編集機能に制限が掛かり、メールアドレス・パスワード以外は
　編集出来なくなります。
・その他の機能（メッセージ機能など）はご利用になれます。

【締め切り後に提出・情報編集をする場合は】
締め切り後でも、主催者が認めた場合には作品の新規提出・情報編集を行えます。
締め切り後に作品提出・情報編集が必要になった場合は、主催者にご連絡下さい。
※主催者の方は、マイページトップから「権限コントロール」→「提出期間外のファイル提出・
　情報編集権限を操作」の順に選択すると、締め切り後の提出・編集を許可する画面に移れます。

【ファイルの提出方法のご案内】
$dspu まで、ポータルサイトよりファイルの提出を行えます。
ポータルサイトにログイン後、マイページトップの「作品を提出する」を選択し、
画面の指示に従って下さい。

【提出した作品の確認・編集について】
提出した作品は、マイページトップの「提出済み作品の一覧・編集」（主催者の場合は
「参加者・作品の一覧・編集」）から確認を行えます。一覧から作品を選択すると、
詳細画面に移ります。
自分の作品を編集する場合は、詳細画面下部の「ファイル操作」から、作品の編集および
削除を行えます。

【アカウント情報（ニックネームなど）の編集について】
ニックネームなど、ポータルサイト登録時に入力した情報を変更する場合は、マイページ
トップの「アカウント情報編集」を選択して下さい（画面右上の「ログイン中」というボタンを
押すと出てくるメニューから「アカウント情報編集」を選択しても構いません）。
以降は、画面の指示に従って下さい。

EOT;
                    break;
                    case 'until_just':
                        $subject = "提出受付を締め切りました";
                        $content = <<<EOT
$nickname 様

$eventname のファイル提出は、 $dspu に締め切られました。


【ポータルサイトの機能について】
締め切りを迎えたため、現在、ポータルサイトは以下のような状態となっています。
・ファイルの新規提出機能がロックされています。
・提出した作品の確認は行えますが、編集・削除機能がロックされています。
・アカウント情報編集機能に制限が掛かり、メールアドレス・パスワード以外は
　編集出来なくなっています。
・その他の機能（メッセージ機能など）はご利用になれます。

【締め切り後に提出・情報編集をする場合は】
締め切り後でも、主催者が認めた場合には作品の新規提出・情報編集を行えます。
これ以降、作品提出・情報編集が必要になった場合は、主催者にご連絡下さい。
提出・編集を許可された方は、締め切り前と同じ操作方法で作品提出・情報編集を行って下さい。
※主催者の方は、マイページトップから「権限コントロール」→「提出期間外のファイル提出・
　情報編集権限を操作」の順に選択すると、締め切り後の提出・編集を許可する画面に移れます。

EOT;
                    break;
                }
                //内部関数で送信
                sendmail($array["email"], $subject, $content);
            }
            unlink(DATAROOT . 'mail_schedule/' . $value . '.txt');
        }
    }
}
