<?php
//提出機能・確認機能用関数

//提出期間中かどうか調べる（FALSE:そもそも設定してないor期間外　TRUE:期間中）
function in_term() {
    if (!file_exists(DATAROOT . 'form/submit/general.txt')) return FALSE;
    $generaldata = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);
    if ($generaldata["from"] > time()) return FALSE;
    else if ($generaldata["until"] <= time()) return FALSE;
    else return TRUE;
}

//締め切り前かどうか調べる（FALSE:期間外　TRUE:期間中or未設定）
function before_deadline() {
    if (!file_exists(DATAROOT . 'form/submit/general.txt')) return TRUE;
    $generaldata = json_decode(file_get_contents_repeat(DATAROOT . 'form/submit/general.txt'), true);
    if ($generaldata["until"] <= time()) return FALSE;
    else return TRUE;
}

//ファイル確認メンバー？
function is_exammember($userid, $membermode) {
    $membermode = basename($membermode);
    $memberfile = DATAROOT . 'exammember_' . $membermode . '.txt';
    $submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $submitmem);
    if ($key !== FALSE) {
        $submitmem[$key] = id_promoter();
    }
    if (array_search($userid, $submitmem) === FALSE) return FALSE;
    else return TRUE;
}

//リーダー（設定無しの場合NULL）
function id_leader($membermode) {
    $settingfile = json_unpack(DATAROOT . 'examsetting.txt');
    if ($settingfile[$membermode . "_leader"] == "") return NULL;
    if ($settingfile[$membermode . "_leader"] === "_promoter") {
        $settingfile[$membermode . "_leader"] = id_promoter();
    }
    return $settingfile[$membermode . "_leader"];
}

//匿名モード？
function exam_anonymous() {
    $settingfile = json_unpack(DATAROOT . 'examsetting.txt');
    if ($settingfile["anonymous"] == "1") return TRUE;
    return FALSE;
}

//ファイル確認集計処理ショートカット（新規提出）
//意見の書き込み後、もしくは確認者リストの更新後に呼び出す
//現在の確認者リストに基づき意見を集計、回答が出揃っていれば〆処理
//$subjectは処理するファイル名、「_all」で全部について集計
//$forcecloseがTRUEで強制〆切
//$subjectが「_all」以外の時は、検査結果の数字（未回答者有の場合FALSE）を返す（「_all」の時は一番最後に検査した奴の結果を返すけどあまり意味が無い）
function exam_totalization_new($subject, $forceclose) {
    global $siteurl;
    global $eventname;
    if ($subject === "_all") $subjectarray = glob(DATAROOT . 'exam/*.txt');
    else $subjectarray = array("exam/$subject.txt");
    $formsetting = json_decode(file_get_contents_repeat(DATAROOT . 'examsetting.txt'), true);

    $submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $submitmem);
    if ($key !== FALSE) {
        $submitmem[$key] = id_promoter();
    }

    foreach($subjectarray as $filename) {
        $subject = basename($filename, '.txt');
        $answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam/' . $subject . '.txt'), true);
        list($author, $id) = explode("/", $answerdata["_realid"]);
        if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) continue;

        if ($answerdata["_state"] != 0 and $answerdata["_state"] != 4) continue;
        if ($answerdata["_state"] == 4) $dontnotice = TRUE;
        else $dontnotice = FALSE;

        //メンバーにいない人をファイルから外す
        foreach ($answerdata as $key => $data) {
            if (strpos($key, '_') !== FALSE) continue;
            if (array_search($key, $submitmem) === FALSE) unset($answerdata[$key]);
        }

        //全員の回答終わった？
        $complete = TRUE;
        foreach ($submitmem as $key) {
            if (!isset($answerdata[$key])) {
                $complete = FALSE;
                continue;
            }
            $data = $answerdata[$key];
            if ($data["opinion"] == 0) $complete = FALSE;
        }
        if ($forceclose) $complete = TRUE;

        //回答終わってなければそこでおしまい
        if ($complete == FALSE) {
            if (json_pack(DATAROOT . 'exam/' . $subject . '.txt', $answerdata) === FALSE) die('回答データの書き込みに失敗しました。');
            continue;
        }

        //以下、全員の回答が終わった時の処理

        //意見が一致したのか？（resultが0のままだったら対立してる）
        $result = 0;

        //計測用変数
        $op1 = 0;
        $op2 = 0;
        $op3 = 0;
        $count = 0;
        foreach ($submitmem as $key) {
            if (!isset($answerdata[$key])) continue;
            $data = $answerdata[$key];
            switch ($data["opinion"]){
                case 1:
                    $op1++;
                break;
                case 2:
                    $op2++;
                break;
                case 3:
                    $op3++;
                break;
                default:
                    continue;
            }
            $count++;
        }
        if ($op1 == $count or $count == 0) $result = 1;
        else if ($op2 == $count) $result = 2;
        else if ($op3 == $count) $result = 3;

        //計測結果を保存
        $frame = FALSE;
        if ($result == 0) $answerdata["_state"] = 1;
        else {
            //理由取りまとめの必要不要
            if (id_leader("submit") != NULL and $count >= 2 and $result != 1 and $formsetting["reason"] == "notice") {
                $frame = TRUE;
                $answerdata["_state"] = 4;
            } else $answerdata["_state"] = 3;
            $answerdata["_result"] = ["opinion" => $result, "reason" => ""];
        }

        if (json_pack(DATAROOT . 'exam/' . $subject . '.txt', $answerdata) === FALSE) die('回答データの書き込みに失敗しました。');

        //入力内容を読み込んで書き換え
        $formdata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
        if (!$frame) {
            $formdata["exam"] = $result;
            if (json_pack(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $formdata) === FALSE) die('作品データの書き込みに失敗しました。');
        }

        $authornick = nickname($author);

        if ($result == 0) {
            $pageurl = $siteurl . 'mypage/exam/discuss.php?examname=' . $subject;
            //内部関数で送信
            foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                $nickname = nickname($key);
                if (!$forceclose) $content = "$nickname 様

作品「" . $formdata["title"] . "」について、全てのメンバーが確認を終えました。
メンバー間で意見が分かれたため、この作品の承認可否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                else $content = "$nickname 様

作品「" . $formdata["title"] . "」について、ファイル確認を締め切りました。
メンバー間で意見が分かれたため、この作品の承認可否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                sendmail(email($key), 'ファイル確認の結果（議論の必要あり・' . $formdata["title"] . '）', $content);
            }
        } else {
            switch ($result){
                case 1:
                    $contentpart = '承認しても問題無いという意見で一致したため、この作品を承認しました。
作品の提出者に承認の通知をしました。';
                    $mailsubject = 'ファイル確認の結果（承認・' . $formdata["title"] . '）';
                    $authorsubject = '作品を承認しました（' . $formdata["title"] . '）';
                break;
                case 2:
                    if ($frame) $contentpart = '軽微な修正が必要であるという意見で一致したため、この作品を修正待ち状態にしました。
作品の提出者への通知につきましては、メンバーの意見をファイル確認のリーダーが取りまとめ、修正待ちとなった理由を添えて修正依頼の通知をします。';
                    else $contentpart = '軽微な修正が必要であるという意見で一致したため、この作品を修正待ち状態にしました。
作品の提出者に、修正依頼の通知をしました。';
                    $mailsubject = 'ファイル確認の結果（修正待ち・' . $formdata["title"] . '）';
                    $authorsubject = '作品を修正して下さい（' . $formdata["title"] . '）';
                break;
                case 3:
                    if ($frame) $contentpart = '内容上の問題が多い、もしくは重大な問題があるという意見で一致したため、この作品の承認を見送りました。
作品の提出者への通知につきましては、メンバーの意見をファイル確認のリーダーが取りまとめ、承認見送りとなった理由を添えて通知します。';
                    else $contentpart = '内容上の問題が多い、もしくは重大な問題があるという意見で一致したため、この作品の承認を見送りました。
作品の提出者に承認見送りの通知をしました。';
                    $mailsubject = 'ファイル確認の結果（承認見送り・' . $formdata["title"] . '）';
                    $authorsubject = '作品の承認が見送られました（' . $formdata["title"] . '）';
                break;
            }

            //内部関数で送信
            if (!$dontnotice) foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                if ($author === (string)$key) continue;
                $nickname = nickname($key);
                if ($frame and (string)id_leader("submit") === (string)$key) $ps = "\n\n※ファイル確認のリーダーの方は、ファイル提出者への通知のため、以下のURLから、メンバーの意見を取りまとめる画面に進んで下さい。\n\n　理由入力ページ：" . $siteurl . 'mypage/exam/frame.php?examname=' . $subject;
                else $ps = "";
                if (!$forceclose) $content = "$nickname 様

作品「" . $formdata["title"] . "」について、全てのメンバーが確認を終えました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                else $content = "$nickname 様

作品「" . $formdata["title"] . "」について、ファイル確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                sendmail(email($key), $mailsubject, $content);
            }

            //提出者向け
            $reasons = "";
            if ($formsetting["reason"] == "notice") {
                foreach ($answerdata as $key => $data) {
                    if (strpos($key, '_') !== FALSE) continue;
                    if ($data["reason"] != "") $reasons = $reasons . "◇" . $data["reason"] . "\n\n";
                }
            }
            else if ($formsetting["reason"] == "dont-a") $reasons = "大変お手数ですが、今回の判断の理由につきましては主催者に直接お尋ね願います。\n\n";
            else if ($formsetting["reason"] == "dont-b") $reasons = "大変恐れ入りますが、今回の判断の理由につきましてはお答え致しかねます。\n\n";
            switch ($result){
                case 1:
                    $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、ファイル内容に問題が無いと判断されたため、この作品は承認されました。

$eventname にご参加頂き、ありがとうございます。


【提出内容の修正・削除をしたい場合や、作品を追加提出したい場合】
ファイル提出の締め切りを迎える前であれば、ポータルサイトのマイページから、提出内容の修正・削除や、追加提出を行えます。
提出内容を修正・削除する場合は、マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
追加提出をする場合は、「作品を提出する」をクリックし、改めて作品の提出を行って下さい。
";
                break;
                case 2:
                    $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、ファイルの軽微な修正が必要と判断されました。
お手数ですが、以下をご確認頂き、ファイルの再提出をして頂けますと幸いです。


【修正が必要と判断された理由（ファイル確認者によるコメント）】
$reasons

【再提出をするには（ファイル提出の締め切り前まで）】
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。


【既にファイル提出の締め切りを迎えている場合】
大変お手数ですが、主催者にご相談願います。
主催者が認めた場合は、締め切り後であっても入力内容の編集を行えます。
";
                break;
                case 3:
                    $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、提出されたファイルは、内容などの観点上、本イベントに相応しくないと判断されました。
そのため、大変恐れ入りますが、この作品の承認を見送らせて頂きます。


【相応しくないと判断された理由（ファイル確認者によるコメント）】
$reasons

【再提出をするには】
本イベントに相応しくないとされる内容を修正の上、ファイルを再提出する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。
";
                break;
            }
            if (!$frame) sendmail(email($author), $authorsubject, $content);
        }
    }
    if (isset($result)) return $result;
    else return FALSE;
}

//ファイル確認集計処理ショートカット（既存作品編集・共通情報）
//意見の書き込み後、もしくは確認者リストの更新後に呼び出す
//基本仕様は新規提出用ショートカットと同じ
function exam_totalization_edit($subject, $forceclose) {
    global $siteurl;
    global $eventname;
    if ($subject === "_all") $subjectarray = glob(DATAROOT . 'exam_edit/*.txt');
    else $subjectarray = array("exam_edit/$subject.txt");
    $formsetting = json_decode(file_get_contents_repeat(DATAROOT . 'examsetting.txt'), true);

    $mode1file = DATAROOT . 'exammember_submit.txt';
    $mode1mem = file($mode1file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $mode1mem);
    if ($key !== FALSE) {
        $mode1mem[$key] = id_promoter();
    }
    $mode2file = DATAROOT . 'exammember_edit.txt';
    $mode2mem = file($mode2file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $key = array_search("_promoter", $mode2mem);
    if ($key !== FALSE) {
        $mode2mem[$key] = id_promoter();
    }
    foreach($subjectarray as $filename) {
        $subject = basename($filename, '.txt');
        $answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_edit/' . $subject . '.txt'), true);
        list($author, $id, $editid) = explode("/", $answerdata["_realid"]);
        if ($id !== "common" and !file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) continue;

        if ($id === "common") {
            $submitmem = $mode2mem;
            $membermode = "edit";
        }
        else {
            $membermode = $answerdata["_membermode"];
            if ($answerdata["_membermode"] === "submit") $submitmem = $mode1mem;
            else $submitmem = $mode2mem;
        }

        if ($answerdata["_state"] != 0 and $answerdata["_state"] != 4) continue;
        if ($answerdata["_state"] == 4) $dontnotice = TRUE;
        else $dontnotice = FALSE;

        //メンバーにいない人をファイルから外す
        foreach ($answerdata as $key => $data) {
            if (strpos($key, '_') !== FALSE) continue;
            if (array_search($key, $submitmem) === FALSE) unset($answerdata[$key]);
        }

        //全員の回答終わった？
        $complete = TRUE;
        foreach ($submitmem as $key) {
            if (!isset($answerdata[$key])) {
                $complete = FALSE;
                continue;
            }
            $data = $answerdata[$key];
            if ($data["opinion"] == 0) $complete = FALSE;
        }
        if ($forceclose) $complete = TRUE;

        //回答終わってなければここでおしまい
        if ($complete == FALSE) {
            if (json_pack(DATAROOT . 'exam_edit/' . $subject . '.txt', $answerdata) === FALSE) die('回答データの書き込みに失敗しました。');
            continue;
        }

        //以下、全員の回答が終わった時の処理

        //意見が一致したのか？（resultが0のままだったら対立してる）
        $result = 0;

        //計測用変数
        $op1 = 0;
        $op2 = 0;
        $count = 0;
        foreach ($submitmem as $key) {
            if (!isset($answerdata[$key])) continue;
            $data = $answerdata[$key];
            switch ($data["opinion"]){
                case 1:
                    $op1++;
                break;
                case 2:
                    $op2++;
                break;
                default:
                    continue;
            }
            $count++;
        }
        if ($op1 == $count or $count == 0) $result = 1;
        else if ($op2 == $count) $result = 2;

        //計測結果を保存
        $frame = FALSE;
        if ($result == 0) $answerdata["_state"] = 1;
        else {
            //理由取りまとめの必要不要
            if (id_leader($membermode) != NULL and $count >= 2 and $result != 1 and $formsetting["reason"] == "notice") {
                $frame = TRUE;
                $answerdata["_state"] = 4;
            } else $answerdata["_state"] = 3;
            $answerdata["_result"] = ["opinion" => $result, "reason" => ""];
        }

        if (json_pack(DATAROOT . 'exam_edit/' . $subject . '.txt', $answerdata) === FALSE) die('回答データの書き込みに失敗しました。');

        if ($id !== "common") $formdata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
        else $formdata = json_decode(file_get_contents_repeat(DATAROOT . "users/" . $author . ".txt"), true);

        //議論入りしないなら入力内容を読み込んで書き換え
        if ($result != 0 and $id !== "common" and !$frame) {
            $formdata["editing"] = 0;
            if ($result == 1) {
                $formdata["exam"] = 1;
                $formdata["editdate"] = $editid;
                $changeddata = json_decode(file_get_contents_repeat(DATAROOT . "edit/" . $author . "/" . $id . ".txt"), true);
                foreach($changeddata as $key => $data) {
                    if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
                        $fileto = DATAROOT . 'files/' . $author . '/' . $id . '/';
                        if (!file_exists($fileto)) {
                            if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
                        }
                        $tmp = explode("_", $key);
                        $partid = $tmp[0];
                        if ($partid === "submit") $saveid = "main";
                        else $saveid = $partid;
                        if ($tmp[1] === "add") {
                            foreach ($data as $fileplace => $name) {
                                rename(DATAROOT . 'edit_files/' . $author . '/' . $id . '/' . $saveid . "_$fileplace", DATAROOT . 'files/' . $author . '/' . $id . '/' . $saveid . "_$fileplace");
                            }
                            if (!is_array($formdata[$partid])) $formdata[$partid] = array();
                            $formdata[$partid] = array_merge($formdata[$partid], $data);
                        }
                        if ($tmp[1] === "delete") {
                            foreach ($data as $name) {
                                unlink(DATAROOT . 'files/' . $author . '/' . $id . '/' . $saveid . "_$name");
                                unset($formdata[$partid][$name]);
                            }
                        }
                        continue;
                    }
                    $formdata[$key] = $data;
                }
            } else {
                //総再生時間の処理
                $changeddata = json_decode(file_get_contents_repeat(DATAROOT . "edit/" . $author . "/" . $id . ".txt"), true);
                foreach($changeddata as $key => $data) {
                    if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
                        $tmp = explode("_", $key);
                        $partid = $tmp[0];
                        if ($partid === "submit") $saveid = "main";
                        else continue;
                        $old_length = $formdata["length_sum"];
                        if ($tmp[1] === "add") {
                            foreach ($data as $fileplace => $name) {
                                $formdata["length_sum"] -= get_playtime(DATAROOT . 'edit_files/' . $author . '/' . $id . '/' . $saveid . "_$fileplace");
                            }
                        }
                        if ($tmp[1] === "delete") {
                            foreach ($data as $name) {
                                $formdata["length_sum"] += get_playtime(DATAROOT . 'files/' . $author . '/' . $id . '/' . $saveid . "_$name");
                            }
                        }
                        //合計再生時間
                        $userprofile = new JsonRW(user_file_path());
                        $userprofile->array["length_sum"] += $formdata["length_sum"] - $old_length;
                        $userprofile->write();
                    }
                }
            }
            if (json_pack(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $formdata) === FALSE) die('作品データの書き込みに失敗しました。');
        }

        if ($result != 0 and $id === "common" and !$frame) {
            $formdata["common_editing"] = 0;
            if ($answerdata["_commonmode"] === "new") $formdata["common_acceptance"] = $result;
            else if ($result == 1) {
                $formdata["common_acceptance"] = 1;
                $changeddata = json_decode(file_get_contents_repeat(DATAROOT . "edit/" . $author . "/common.txt"), true);
                foreach($changeddata as $key => $data) {
                    if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
                        $fileto = DATAROOT . 'files/' . $author . '/common/';
                        if (!file_exists($fileto)) {
                            if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
                        }
                        $tmp = explode("_", $key);
                        $partid = $tmp[0];
                        if ($tmp[1] === "add") {
                            foreach ($data as $fileplace => $name) {
                                rename(DATAROOT . 'edit_files/' . $author . '/common/' . $partid . "_$fileplace", DATAROOT . 'files/' . $author . '/common/' . $partid . "_$fileplace");
                            }
                            if (!is_array($formdata[$partid])) $formdata[$partid] = array();
                            $formdata[$partid] = array_merge($formdata[$partid], $data);
                        }
                        if ($tmp[1] === "delete") {
                            foreach ($data as $name) {
                                unlink(DATAROOT . 'files/' . $author . '/common/' . $partid . "_$name");
                                unset($formdata[$partid][$name]);
                            }
                        }
                        continue;
                    }
                    $formdata[$key] = $data;
                }
            }
            if (json_pack(DATAROOT . "users/" . $author . ".txt", $formdata) === FALSE) die('提出データの書き込みに失敗しました。');
        }

        $authornick = nickname($author);

        if ($result == 0) {
            if ($id !== "common") $pageurl = $siteurl . 'mypage/exam/discuss_edit.php?examname=' . $subject;
            else $pageurl = $siteurl . 'mypage/exam/discuss_common.php?examname=' . $subject;
            //内部関数で送信
            foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                $nickname = nickname($key);
                if ($id !== "common") {
                    $mailsubject = 'ファイル確認の結果（議論の必要あり・内容変更・' . $formdata["title"] . '）';
                    if (!$forceclose) $content = "$nickname 様

作品「" . $formdata["title"] . "」の項目変更について、全てのメンバーが確認を終えました。
メンバー間で意見が分かれたため、この変更の承認可否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                    else $content = "$nickname 様

作品「" . $formdata["title"] . "」の項目変更について、ファイル確認を締め切りました。
メンバー間で意見が分かれたため、この変更の承認可否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                } else {
                    $mailsubject = '内容確認の結果（議論の必要あり・共通情報）';
                    if (!$forceclose) $content = "$nickname 様

提出された共通情報について、全てのメンバーが確認を終えました。
メンバー間で意見が分かれたため、この内容の承認可否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                    else $content = "$nickname 様

提出された共通情報について、確認を締め切りました。
メンバー間で意見が分かれたため、この内容の承認可否について議論する必要があります。
以下のURLから、簡易チャット画面に移って下さい。

　簡易チャットページ：$pageurl
";
                }
                sendmail(email($key), $mailsubject, $content);
            }
        } else {
            if ($id !== "common") switch ($result){
                case 1:
                    $contentpart = '承認しても問題無いという意見で一致したため、この変更を承認しました。
作品の提出者に承認の通知をしました。';
                    $mailsubject = 'ファイル確認の結果（承認・内容変更・' . $formdata["title"] . '）';
                    $authorsubject = '内容変更を承認しました（' . $formdata["title"] . '）';
                break;
                case 2:
                    if ($frame) $contentpart = '問題があるという意見で一致したため、この変更の承認を見送りました。
作品の提出者への通知につきましては、メンバーの意見をファイル確認のリーダーが取りまとめ、承認見送りとなった理由を添えて通知します。';
                    else $contentpart = '問題があるという意見で一致したため、この変更の承認を見送りました。
作品の提出者に承認見送りの通知をしました。';
                    $mailsubject = 'ファイル確認の結果（承認見送り・内容変更・' . $formdata["title"] . '）';
                    $authorsubject = '内容変更の承認が見送られました（' . $formdata["title"] . '）';
                break;
            }
            else switch ($result){
                case 1:
                    $contentpart = '承認しても問題無いという意見で一致したため、この内容を承認しました。
情報の提出者に承認の通知をしました。';
                    $mailsubject = '内容確認の結果（承認・共通情報）';
                    $authorsubject = '内容を承認しました（共通情報）';
                break;
                case 2:
                    if ($frame) $contentpart = '問題があるという意見で一致したため、この内容の承認を見送りました。
情報の提出者への通知につきましては、メンバーの意見をファイル確認のリーダーが取りまとめ、承認見送りとなった理由を添えて通知します。';
                    else $contentpart = '問題があるという意見で一致したため、この内容の承認を見送りました。
情報の提出者に承認見送りの通知をしました。';
                    $mailsubject = '内容確認の結果（承認見送り・共通情報）';
                    $authorsubject = '内容の承認が見送られました（共通情報）';
                break;
            }

            //内部関数で送信
            if (!$dontnotice) foreach ($submitmem as $key) {
                $data = $answerdata[$key];
                if ($author == $key) continue;
                $nickname = nickname($key);
                if ($id !== "common") {
                    if ($frame and (string)id_leader($membermode) === (string)$key) $ps = "\n\n※ファイル確認のリーダーの方は、ファイル提出者への通知のため、以下のURLから、メンバーの意見を取りまとめる画面に進んで下さい。\n\n　理由入力ページ：" . $siteurl . 'mypage/exam/frame_edit.php?examname=' . $subject;
                    else $ps = "";
                    if (!$forceclose) $content = "$nickname 様

作品「" . $formdata["title"] . "」の項目変更について、全てのメンバーが確認を終えました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                    else $content = "$nickname 様

作品「" . $formdata["title"] . "」の項目変更について、ファイル確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                } else {
                    if ($frame and (string)id_leader($membermode) === (string)$key) $ps = "\n\n※ファイル確認のリーダーの方は、情報の提出者への通知のため、以下のURLから、メンバーの意見を取りまとめる画面に進んで下さい。\n\n　理由入力ページ：" . $siteurl . 'mypage/exam/frame_common.php?examname=' . $subject;
                    else $ps = "";
                    if (!$forceclose) $content = "$nickname 様

提出された共通情報について、全てのメンバーが確認を終えました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                    else $content = "$nickname 様

提出された共通情報について、確認を締め切りました。
$contentpart

ファイル確認へのご協力、ありがとうございます。$ps
";
                }
                sendmail(email($key), $mailsubject, $content);
            }

            //提出者向け
            $reasons = "";
            if ($formsetting["reason"] == "notice") {
                foreach ($answerdata as $key => $data) {
                    if (strpos($key, '_') !== FALSE) continue;
                    if ($data["reason"] != "") $reasons = $reasons . "◇" . $data["reason"] . "\n\n";
                }
            }
            else if ($formsetting["reason"] == "dont-a") $reasons = "大変お手数ですが、今回の判断の理由につきましては主催者に直接お尋ね願います。\n\n";
            else if ($formsetting["reason"] == "dont-b") $reasons = "大変恐れ入りますが、今回の判断の理由につきましてはお答え致しかねます。\n\n";
            if ($id !== "common") switch ($result){
                case 1:
                    $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」の内容変更について、イベントの運営メンバーが確認しました。
確認の結果、変更内容に問題が無いと判断されたため、この変更は承認されました。

$eventname にご参加頂き、ありがとうございます。


【提出内容の修正・削除をしたい場合や、作品を追加提出したい場合】
ファイル提出の締め切りを迎える前であれば、ポータルサイトのマイページから、提出内容の修正・削除や、追加提出を行えます。
提出内容を修正・削除する場合は、マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
追加提出をする場合は、「作品を提出する」をクリックし、改めて作品の提出を行って下さい。
";
            break;
            case 2:
                $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」の内容変更について、イベントの運営メンバーが確認しました。
確認の結果、変更後の内容に問題があると判断されました。
そのため、大変恐れ入りますが、この変更の承認を見送らせて頂きます。
現在は、変更前の内容を維持したままの状態となっています。


【問題があると判断された理由（ファイル確認者によるコメント）】
$reasons

【再編集をするには】
問題があるとされる内容を修正の上、ファイルを再編集する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。
";
                break;
            }
            else switch ($result){
                case 1:
                    $content = "$authornick 様

あなたの共通情報について、イベントの運営メンバーが確認しました。
確認の結果、内容に問題が無いと判断されたため、この内容は承認されました。

$eventname にご参加頂き、ありがとうございます。


【共通情報を修正したい場合】
ファイル提出の締め切りを迎える前であれば、ポータルサイトのマイページから、共通情報の修正を行えます。
共通情報を修正する場合は、マイページにログイン後、「共通情報の入力・編集」をクリックして下さい。
";
                break;
                case 2:
                    if ($answerdata["_commonmode"] === "new") $changeinfo = "そのため、大変恐れ入りますが、この内容の承認を見送らせて頂きます。";
                    else $changeinfo = "そのため、大変恐れ入りますが、この内容の承認を見送らせて頂きます。\n現在は、変更前の内容を維持したままの状態となっています。";
                    $content = "$authornick 様

あなたの共通情報について、イベントの運営メンバーが確認しました。
確認の結果、その内容に問題があると判断されました。
$changeinfo


【問題があると判断された理由（ファイル確認者によるコメント）】
$reasons

【再編集をするには】
問題があるとされる内容を修正の上、共通情報を再編集する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「共通情報の入力・編集」をクリックして下さい。
";
                break;
            }
            if (!$frame) {
                sendmail(email($author), $authorsubject, $content);
                unlink(DATAROOT . "edit/" . $author . "/" . $id . ".txt");
            }
        }
    }
    if (isset($result)) return $result;
    else return FALSE;
}
