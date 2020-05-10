<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


if (!isset($_GET['number']) or !isset($_GET['do'])) {
    redirect("./index.php");
}

$i = (int) $_GET['number'];

if ($i < 0 or $i > 9) redirect("./orderanddel.php");

if ($_GET['do'] == 'up') {
    if (($i - 1) < 0) redirect("./orderanddel.php");
    $tmp = $_SESSION["submitformdata"][$i - 1];
    $_SESSION["submitformdata"][$i - 1] = $_SESSION["submitformdata"][$i];
    $_SESSION["submitformdata"][$i] = $tmp;
}
if ($_GET['do'] == 'down') {
    if (($i + 1) > 9) redirect("./orderanddel.php");
    $tmp = $_SESSION["submitformdata"][$i + 1];
    $_SESSION["submitformdata"][$i + 1] = $_SESSION["submitformdata"][$i];
    $_SESSION["submitformdata"][$i] = $tmp;
}
if ($_GET['do'] == 'delete') {
    unset($_SESSION["submitformdata"][$i]);
    for(; $i <= 9; $i++){
        if (!isset($_SESSION["submitformdata"][$i + 1])) {
            unset($_SESSION["submitformdata"][$i]);
            break;
        }
        $_SESSION["submitformdata"][$i] = $_SESSION["submitformdata"][$i + 1];
    }
}

redirect("./orderanddel.php");
