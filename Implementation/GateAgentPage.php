<?php
//Import required files
require "Classes/User.php";
require "Controllers/GroundManagementSystem.php";

//Start the session to handle user login status
session_start();
$user=null;
//Check if the user is logged with the correct role for this page. If not, redirrect it to the login page
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::GateAgent){
    //Obtain the User object of the logged user
    $user=unserialize($_SESSION["user"]);
    //If the user is logged but it still has the same password as when the account was created, redirect it to the page to change the password
    if($user->getChangePass())
        header('Location: ChangePassword.php');
}else{
    header('Location: index.php');
}
$gms=new GroundManagementSystem();
$result="";

//Check if there is data entered in the form to assign a gate to a flight. If this is the case, send the data to the function to update the gate status
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
            //Print the form to assign a gate to a flight
            echo "<form action='GateAgentPage.php' method='POST'>
            <label for='flight'>Choose a flight: </label>
            <select id='flight' name='flight'>
            <option value=''></option>";
            //Fetch the flights that can be linked to a gate
            $data=$gms->getFlightsForGates();
            foreach($data as $d)
                echo "<option value=".$d["id"].">".$d["plane"].": ".$d["dAirport"]." -> ".$d["aAirport"].", ".$d["time"]."</option>";
            echo "</select><label for='gate'>Choose a gate: </label>
            <select id='gate' name='gate'>
            <option value=''></option>";
            //Fetch the empty gates
            $data=$gms->getAvailableGates();
            foreach($data as $d)
                echo "<option value=".$d["id"].">".$d["gate_number"]."</option>";
            echo "</select><input type='submit' value='Assign'></form>";
        ?>
        <?php
            //Post the result of the gate assignemnt
            if($result!="")
                echo "<p>$result</p>";
        ?>
        <!--Button to redirect to the logout page-->
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
