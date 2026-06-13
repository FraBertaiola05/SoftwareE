<?php
//Start the session to handle user login status
session_start();

//Import required files
require "DatabaseInfo.php";
require "Classes/User.php";
require "Controllers/AccountManager.php";

//Check if the user is logged with the correct role for this page. If not, redirrect it to the login page
if(isset($_SESSION["user"])){
    //Obtain the User object of the logged user
    $user=unserialize($_SESSION["user"]);
}else{
    header('Location: index.php');
}


//Check if there is the user entered the new password. If he has inserted a new password, pass the data to the function that updates the password
if(isset($_POST["newPass"])&&!is_null($_POST["newPass"])&&isset($_POST["newPassBis"])&&!is_null($_POST["newPassBis"])){
    $error=AccountManager::updateUserPassword($user->getId(),$_POST["newPass"],$_POST["newPassBis"]);
}

//If the user changed his password without any error, update the User object in the session and redirrect him to his dedicated page
if(isset($error)&&$error==""){
    $user->setChangePass(false);
    $_SESSION["user"]=serialize($user);
    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Change Password</title>
    </head>
    <body>
        <!--Form to insert the new password two times-->
        <form action="ChangePassword.php" method="POST">
            <label for="newPass">New Password:</label>
            <input type="text" name="newPass" required><br><br>
            <label for="newPassBis">Repeat New Password:</label>
            <input type="text" name="newPassBis" required><br><br>
            <label>The password need at least 12 characters: at least one uppercase, one lowercase, one symbol, one number</label><br><br>
            <input type="submit" value="Change Password">
        </form>
        <?php
            //If there was an error in the password update, print the error
            if(isset($error) && $error!=null)
                echo "<p>".$error."</p>";
        ?>
    </body>
</html>
