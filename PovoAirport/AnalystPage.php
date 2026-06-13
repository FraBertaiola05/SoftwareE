<?php
//Import required files
require "Classes/User.php";
require "Controllers/FinanceSystem.php";

//Start the session to handle user login status
session_start();
$user=null;

//Check if the user is logged with the correct role for this page. If not, redirect it to the login page
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::AirportAnalyst){
    //Obtain the User object of the logged user
    $user=unserialize($_SESSION["user"]);
    //If the user is logged but it still has the same password as when the account was created, redirrect it to the page to change the password
    if($user->getChangePass())
        header('Location: ChangePassword.php');
}else{
    header('Location: index.php');
}

//Check if the user entered two dates and if the interval is valid. If it is, calculate cost and revenue of the timespan and print it to the user screen
if(isset($_POST["sDate"])&&!is_null($_POST["sDate"])&&isset($_POST["fDate"])&&!is_null($_POST["fDate"])&&$_POST["fDate"]>=$_POST["sDate"]){
    [$cost, $revenue, $total]=FinanceSystem::getFinancialOverview($_POST["sDate"],$_POST["fDate"]);
    $s="<table border=1><tr><th>Start Date</th><th>End Date</th><th>Cost</th><th>Revenue</th><th>Total</th></tr>".
    "<tr><td>".$_POST["sDate"]."</td><td>".$_POST["fDate"]."</td><td>$cost</td><td>$revenue</td><td>$total</td></tr></table>";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
    </head>
    <body>
        <!--Form to select the starting and finishing date-->
        <form action="AnalystPage.php" method="POST">
            <label for="sDate">From: </label>
            <input type="date" id="sDate" name="sDate" required>
            <label for="fDate" required>to: </label>
            <input type="date" id="fDate" name="fDate" required>
            <input type='submit' value='Calculate'>
        </form>
        <?php
            //Print the table with the cost and revenue of the selected timestamp
            if(isset($s))
                echo $s;
        ?>
        <!--Button to redirect to the logout page-->
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
