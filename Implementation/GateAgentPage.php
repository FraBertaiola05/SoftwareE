<?php
require "Classes/User.php";
require "Classes/GroundManagementSystem.php";
session_start();
$user=null;
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::GateAgent){
    $user=unserialize($_SESSION["user"]);
}else{
    header('Location: index.php');
}
$gms=new GroundManagementSystem();
$result="";
if(isset($_POST["flight"])&&is_numeric($_POST["flight"])&&isset($_POST["gate"])&&is_numeric($_POST["gate"]))
    $result=$gms->updateGate($_POST["flight"],$_POST["gate"]);
    

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Gate Agent Page</title>
    </head>
    <body>
        <?php
            echo "<form action='GateAgentPage.php' method='POST'>
            <label for='flight'>Choose a flight: </label>
            <select id='flight' name='flight'>
            <option value=''></option>";
            $data=$gms->getFlightsForGates();
            foreach($data as $d)
                echo "<option value=".$d["id"].">".$d["plane"].": ".$d["dAirport"]." -> ".$d["aAirport"].", ".$d["time"]."</option>";
            echo "</select><label for='gate'>Choose a gate: </label>
            <select id='gate' name='gate'>
            <option value=''></option>";
            $data=$gms->getAvailableGates();
            foreach($data as $d)
                echo "<option value=".$d["id"].">".$d["gate_number"]."</option>";
            echo "</select><input type='submit' value='Assign'></form>";
        ?>
        <?php
            if($result!="")
                echo "<p>$result</p>"
        ?>
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>