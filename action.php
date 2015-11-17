<?php
include('Connection.php');
$c = new Connection();
$func_name = $_REQUEST['f_name'];
if (strcasecmp("Registration", $func_name) == 0) {
    $ur_name = $_REQUEST['username'];
    $ur_email = $_REQUEST['email'];
    $ur_mobile = $_REQUEST['mobile_no'];
    $ur_pass = $_REQUEST['password'];
    $ur_type = $_REQUEST['reg_type'];
    echo call_user_func([$c, $func_name], $ur_name, $ur_email, $ur_mobile, $ur_pass, $ur_type);
} else if (strcasecmp("Getdata", $func_name) == 0) {

    echo call_user_func([$c, $func_name]);
} elseif (strcasecmp("Verification", $func_name) == 0) {
    $ur_em = $_REQUEST['mail'];
    $ur_cod = $_REQUEST['code'];
    echo call_user_func([$c, $func_name], $ur_em, $ur_cod);

}




