<?php
require "Classes/User.php";
require "Classes/AccountManager.php";
session_start();
$user=null;
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::SystemAdmin){
    $user=unserialize($_SESSION["user"]);
}else{
    header('Location: index.php');
}

$result="";
if(isset($_POST["id"])&&isset($_POST["email"])&&isset($_POST["name"])&&isset($_POST["surname"])&&isset($_POST["role"])){
    if(isset($_POST["password"]))
        $p=true;
    else
        $p=false;
    if(isset($_POST["company"]))
        $result=AccountManager::modifyAccount($_POST["id"],$_POST["email"],$_POST["name"],$_POST["surname"],$_POST["role"],$p,$_POST["company"]);
    else
        $result=AccountManager::modifyAccount($_POST["id"],$_POST["email"],$_POST["name"],$_POST["surname"],$_POST["role"],$p);
}else if(isset($_POST["email"])&&isset($_POST["name"])&&isset($_POST["surname"])&&isset($_POST["role"])){
    if(isset($_POST["company"]))
        $result=AccountManager::createAccount($_POST["email"],$_POST["name"],$_POST["surname"],$_POST["role"],$_POST["company"]);
    else
        $result=AccountManager::createAccount($_POST["email"],$_POST["name"],$_POST["surname"],$_POST["role"]);
}else if(isset($_POST["user"])){
    $result=AccountManager::deleteAccount($_POST["user"]);
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Admin Page</title>
        <script>
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
        <button onclick="loadForm('ADD')">Add a User</button>
        <button onclick="loadForm('MODIFY')">Modify a User</button>
        <button onclick="loadForm('DELETE')">Delete a User</button>
        <?php
            if($result!="")
                echo "<p>$result</p>"
        ?>
        <div id="container"></div>
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
