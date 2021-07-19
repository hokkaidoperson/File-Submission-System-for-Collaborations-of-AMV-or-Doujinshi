<?php
require_once('../../../set.php');
setup_session();
session_validation();

csrf_prevention_validate();

if (no_access_right(array("p"))) redirect("./index.php");


unset($_SESSION["submitformdata"]);

register_alert("ファイル提出に関する設定を中止しました。", "warning");

redirect("../../index.php");
