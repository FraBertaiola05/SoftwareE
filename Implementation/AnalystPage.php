<?php
require "Classes/User.php";
require "Controllers/FinanceSystem.php";
session_start();
$user=null;
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::AirportAnalyst){
    $user=unserialize($_SESSION["user"]);
    if($user->getChangePass())
        header('Location: ChangePassword.php');
}else{
    header('Location: index.php');
}
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
        <form action="AnalystPage.php" method="POST">
            <label for="sDate">From: </label>
            <input type="date" id="sDate" name="sDate" required>
            <label for="fDate" required>to: </label>
            <input type="date" id="fDate" name="fDate" required>
            <input type='submit' value='Calculate'>
        </form>
        <?php
            if(isset($s))
                echo $s;
        ?>
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
