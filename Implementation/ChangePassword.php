<?php
session_start();
require "DatabaseInfo.php";
require "Classes/User.php";
require "Controllers/AccountManager.php";

if(isset($_SESSION["user"])){
    $user=unserialize($_SESSION["user"]);
}else{
    header('Location: index.php');
}



if(isset($_POST["newPass"])&&!is_null($_POST["newPass"])&&isset($_POST["newPassBis"])&&!is_null($_POST["newPassBis"])){
    $error=AccountManager::updateUserPassword($user->getId(),$_POST["newPass"],$_POST["newPassBis"]);
}

if(isset($error)&&$error==""){
    $user->setChangePass(false);
    $_SESSION["user"]=serialize($user);
    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Povo International Airport Login</title>
    </head>
    <body>
        <form action="ChangePassword.php" method="POST">
            <label for="newPass">New Password:</label>
            <input type="text" name="newPass" required><br><br>
            <label for="newPassBis">Repeat New Password:</label>
            <input type="text" name="newPassBis" required><br><br>
            <label>The password need at least 12 characters: at least one uppercase, one lowercase, one symbol, one number</label><br><br>
            <input type="submit" value="Change Password">
        </form>
        <?php
            if(isset($error) && $error!=null)
                echo "<p>".$error."</p>";
        ?>
    </body>
</html>
