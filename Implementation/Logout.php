<?php
    //Open and then destroy the session. After that, redirect to the login page
    session_start();
    session_destroy();
    header('Location: index.php');
?>
