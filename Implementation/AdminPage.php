<?php
//Import required files
require "Classes/User.php";
require "Controllers/AccountManager.php";

//Start the session to handle user login status
session_start();
$user=null;

//Check if the user is logged with the correct role for this page. If not, redirrect it to the login page
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::SystemAdmin){
    //Obtain the User object of the logged user
    $user=unserialize($_SESSION["user"]);
    //If the user is logged but it still has the same password as when the account was created, redirect it to the page to change the password
    if($user->getChangePass())
        header('Location: ChangePassword.php');
}else{
    header('Location: index.php');
}

$result="";
//Check if there is data entered in the form to modify an account. If this is the case, send the data to the function to modify the account
if(isset($_POST["id"])&&isset($_POST["email"])&&isset($_POST["name"])&&isset($_POST["surname"])&&isset($_POST["role"])){
    if(isset($_POST["password"]))
        $p=true;
    else
        $p=false;
    if(isset($_POST["company"]))
        $result=AccountManager::modifyAccount($_POST["id"],$_POST["email"],$_POST["name"],$_POST["surname"],$_POST["role"],$p,$_POST["company"]);
    else
        $result=AccountManager::modifyAccount($_POST["id"],$_POST["email"],$_POST["name"],$_POST["surname"],$_POST["role"],$p);
}
//Check if there is data entered in the form to add an account. If this is the case, send the data to the function to add the account
else if(isset($_POST["email"])&&isset($_POST["name"])&&isset($_POST["surname"])&&isset($_POST["role"])){
    if(isset($_POST["company"]))
        $result=AccountManager::createAccount($_POST["email"],$_POST["name"],$_POST["surname"],$_POST["role"],$_POST["company"]);
    else
        $result=AccountManager::createAccount($_POST["email"],$_POST["name"],$_POST["surname"],$_POST["role"]);
}
//Check if there is data entered in the form to delete an account. If this is the case, send the data to the function to delete the account
else if(isset($_POST["user"])){
    $result=AccountManager::deleteAccount($_POST["user"]);
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Admin Page</title>
        <script>
            //Load from the AJAX page the form to enter the data
            function loadForm(str){
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("container").innerHTML = this.responseText;
                }
                };
                xmlhttp.open("GET", "AJAX/AdminPageAJAX.php?type=" + str, true);
                xmlhttp.send();
            }
            //Load from the AJAX page the form to modify a user with its data
            function loadModify(){
                var userId = document.getElementById("user").value;
                if(!userId)
                    document.getElementById("container2").innerHTML =""
                else{
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            document.getElementById("container2").innerHTML = this.responseText;
                        }
                    };
                    xmlhttp.open("GET", "AJAX/AdminPageAJAX.php?type=MODIFY2&id="+userId, true);
                    xmlhttp.send();
                }
            }
            //Enable/disable company field if the role allow/don't allow a company
            function updateCompany(){
                var roleValue = document.getElementById("role").value;
                if(roleValue==2||roleValue==6)
                    document.getElementById("company").removeAttribute("disabled");
                else
                    document.getElementById("company").setAttribute("disabled", "disabled");
            }
        </script>
    </head>
    <body>
        <!--Buttons to call the AJAX functionalities to load the forms for the various operations-->
        <button onclick="loadForm('ADD')">Add a User</button>
        <button onclick="loadForm('MODIFY')">Modify a User</button>
        <button onclick="loadForm('DELETE')">Delete a User</button>
        <?php
            //Show the result of an operation (Add/Modify/Delete)
            if($result!="")
                echo "<p>$result</p>"
        ?>
        <!--Container where the JavaScrip puts the form-->
        <div id="container"></div>
        <!--Button to redirect to the logout page-->
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
