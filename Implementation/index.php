<?php
session_start();
require "DatabaseInfo.php";
require "Classes/User.php";
require "Classes/FlightScheduleSystem.php";
if(!isset($_COOKIE["timeout"]))
    setcookie("timeout", 0, time() + 300, "/");
try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
  die("Could not connect. ".$e->getMessage());
}
if(isset($_COOKIE["timeout"])&&$_COOKIE["timeout"]==5){
    $error="Wrong data inserted too many times. Retry in 5 minutes";
}else if (isset($_POST["email"]) && isset($_POST["password"])) {
    setcookie("timeout", $_COOKIE["timeout"]+1, time() + 300, "/");
    $query=$conn->prepare("SELECT * FROM users WHERE email=:email");
    $query->bindParam(':email',$_POST["email"]);
    $query->execute();
    $r=$query->fetch(PDO::FETCH_ASSOC);
    $error="The data entered is incorrect";
    if($r!=null){
        $role=null;
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
        $user=new User($r["id"],$r["name"],$r["surname"],$r["email"],$r["password"],$role);
        if(isset($r["company_id"])&&!is_null($r["company_id"]))
            $user->setCompany($r["company_id"]);
        if($user->login($_POST["email"],$_POST["password"])){
            $_SESSION["user"]=serialize($user);
            $error=null;
            setcookie("timeout", 0, 1, "/");
        }
    }
}

if(isset($_SESSION["user"])){
    $user=unserialize($_SESSION["user"]);
    switch($user->getRole()){
        case RoleEnum::TowerController:
            header('Location: .php');
            break;
        case RoleEnum::Pilot:
            header('Location: .php');
            break;
        case RoleEnum::GroundCrew:
            header('Location: .php');
            break;
        case RoleEnum::GateAgent:
            header('Location: .php');
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

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Povo International Airport Login</title>
    </head>
    <body>
        <form action="index.php" method="POST">
            <label for="email">Email:</label>
            <input type="text" name="email" required><br><br>
            <label for="password">Password:</label>
            <input type="password" name="password" required><br><br>
            <input type="submit" value="Login">
        </form>
        <?php
            if(isset($error) && $error!=null)
                echo "<p>".$error."</p>";
        ?>
        <p>Departures</p>
        <?php
            echo FlightScheduleSystem::getFlightHistoryTable(true);
        ?>
        <p>Arrivals</p>
        <?php
            echo FlightScheduleSystem::getFlightHistoryTable(false);
        ?>
    </body>
</html>
