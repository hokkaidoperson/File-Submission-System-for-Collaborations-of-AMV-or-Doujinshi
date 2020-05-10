<?php
require_once('../../../set.php');
setup_session();
session_validation();

if (no_access_right(array("p"))) redirect("./index.php");


if (!isset($_GET['number']) or !isset($_GET['type'])) {
    redirect("./index.php");
}

$number = hsc(basename($_GET['number']));
$type = basename($_GET['type']);

switch ($type) {
    case "textbox": break;
    case "textbox2": break;
    case "textarea": break;
    case "radio": break;
    case "check": break;
    case "dropdown": break;
    case "attach": break;
    default: die();
}

$_SESSION["userformdata"][$number] = array(
    "id" => time(),
    "type" => $type,
);

redirect("./$type.php?number=$number");
