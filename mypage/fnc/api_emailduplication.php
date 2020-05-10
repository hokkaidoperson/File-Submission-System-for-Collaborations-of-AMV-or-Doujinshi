<?php
//メルアド重複チェックAPI
require_once('../../set.php');
setup_session();
if (!session_validation(FALSE, TRUE)) die();
if (!csrf_prevention_validate(TRUE)) {
    $returnarray = array("auth_status" => "NG", "error_detail" => "Likely fraudulent access such as CSRF");
    die(json_encode($returnarray));
}

$returnarray = array("auth_status" => "OK", "error_detail" => "OK");

$invalid = FALSE;

if($_POST["email"] == "") $invalid = TRUE;
else if(!preg_match('/.+@.+\..+/', $_POST["email"])) $invalid = TRUE;

if ($invalid) $returnarray["emailresult"] = 0;
else {
    $email = $_POST["email"];

    $conflict = FALSE;

    //登録済みの中から探す
    foreach (glob(DATAROOT . 'users/*.txt') as $filename) {
        if (basename($filename, ".txt") == $_SESSION["userid"] and $_POST["skipmyself"]) continue;
        $filedata = json_decode(file_get_contents($filename), true);
        if ($filedata["email"] == $email) {
            $conflict = TRUE;
            break;
        }
    }

    if ($conflict) $returnarray["emailresult"] = 0;
    else $returnarray["emailresult"] = 1;
}

echo json_encode($returnarray);
