<?php
require("../model/bll.php");
require('../model/dto.php');
session_start();
if (isset($_POST)) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $role = 1;

    $new_user = new UserDTO($username, $password, $firstname, $lastname, $role);
    $user_bll = new UserBLL();
    $user_bll->create_user($new_user);
    header("Location: ../signup.php?success=1");
    exit();
}
