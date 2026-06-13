<?php
//Start the session to handle user login status
session_start();

//Import required files
require "DatabaseInfo.php";
require "Classes/User.php";
require "Controllers/FlightScheduleSystem.php";

//Initialize the cookie "timeout" if is not declared yet
if(!isset($_COOKIE["timeout"]))
    setcookie("timeout", 0, time() + 300, "/");

//Initialize database connection
try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
  die("Could not connect. ".$e->getMessage());
}

//If the cookie "timeout" has a value of 5, so after 5 wrong login tries, block the possibility to log for five minutes. Also, set the error message for too many failed login attempts
if(isset($_COOKIE["timeout"])&&$_COOKIE["timeout"]==5){
    $error="Wrong data inserted too many times. Retry in 5 minutes";
}
//Check if there is data entered in the form to login. If this is the case, check if the data is correct, then log the user if the data is correct, else set the error mmessage
else if (isset($_POST["email"]) && isset($_POST["password"])) {
    //Increment the value of the cookie "timeout" by one
    setcookie("timeout", $_COOKIE["timeout"]+1, time() + 300, "/");
    //Fetch the user account associated to the email inserted by the user
    $query=$conn->prepare("SELECT * FROM users WHERE email=:email");
    $query->bindParam(':email',$_POST["email"]);
    $query->execute();
    $r=$query->fetch(PDO::FETCH_ASSOC);
    $error="The data entered is incorrect";
    //If there exist a user connected to the email inserted by the user, check if the password inserted by the user is correct
    if($r!=null){
        $role=null;
        //Translate the role fetched into his Enum correspondent
        switch($r["role_id"]){
            case 1:
                $role=RoleEnum::TowerController;
                break;
            case 2:
                $role=RoleEnum::Pilot;
                break;
            case 3:
                $role=RoleEnum::GroundCrew;
                break;
            case 4:
                $role=RoleEnum::GateAgent;
                break;
            case 5:
                $role=RoleEnum::SystemAdmin;
                break;
            case 6:
                $role=RoleEnum::AirlineCompanyManager;
                break;
            case 7:
                $role=RoleEnum::AirportAnalyst;
                break;
        }
        //Create the User object
        $user=new User($r["id"],$r["name"],$r["surname"],$r["email"],$r["password"],$role,$r["password_reset"]);
        if(isset($r["company_id"])&&!is_null($r["company_id"]))
            $user->setCompany($r["company_id"]);
        //Check if the email and password inserted are correct
        if($user->login($_POST["email"],$_POST["password"])){
            //Insert the User object into the session. We cannot store an object directly in the session so we need to serialize it before storing it in the session
            $_SESSION["user"]=serialize($user);
            $error=null;
            //Delete the "timeout" cookie by setting is time to 1
            setcookie("timeout", 0, 1, "/");
        }
    }
}

//Redirect the user to his designated page based on his role and if he needs to set a new password
if(isset($_SESSION["user"])){
    $user=unserialize($_SESSION["user"]);
    if($user->getChangePass()==1)
        header('Location: ChangePassword.php');
    else{
        switch($user->getRole()){
            case RoleEnum::TowerController:
                header('Location: TowerControllerPage.php');
                break;
            case RoleEnum::Pilot:
                header('Location: PilotPage.php');
                break;
            case RoleEnum::GroundCrew:
                header('Location: GroundCrewPage.php');
                break;
            case RoleEnum::GateAgent:
                header('Location: GateAgentPage.php');
                break;
            case RoleEnum::SystemAdmin:
                header('Location: AdminPage.php');
                break;
            case RoleEnum::AirlineCompanyManager:
                header('Location: AirlinePage.php');
                break;
            case RoleEnum::AirportAnalyst:
                header('Location: AnalystPage.php');
                break;
        }
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Povo International Airport Login</title>
    </head>
    <body>
        <!--Form to insert the email and password to login-->
        <form action="index.php" method="POST">
            <label for="email">Email:</label>
            <input type="text" name="email" required><br><br>
            <label for="password">Password:</label>
            <input type="password" name="password" required><br><br>
            <input type="submit" value="Login">
        </form>
        <?php
            //If there was an error in the login, print here the error
            if(isset($error) && $error!=null)
                echo "<p>".$error."</p>";
        ?>
        <p>Departures</p>
        <?php
            //Print the table with the upcoming departures
            echo FlightScheduleSystem::getFlightHistoryTable(true);
        ?>
        <p>Arrivals</p>
        <?php
            //Print the table with the upcoming arrivals
            echo FlightScheduleSystem::getFlightHistoryTable(false);
        ?>
    </body>
</html>
