<?php
session_start();
require "DatabaseInfo.php";
require "Classes/User.php";
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
            header('Location: .php');
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
        <table border=1>
            <tr>
                <th>Plane</th>
                <th>Goes To</th>
                <th>Airport</th>
                <th>Departure Time</th>
                <th>Gate</th>
                <th>Status</th>
            </tr>
            <?php
                try {
                $query="SELECT f.plane_id AS 'plane', b.nation AS 'nation', b.city AS 'city', b.name AS 'airportName', f.scheduled_time AS 'depTime', gates.gate_number AS 'gateNumber', fs.status AS 'flightStatus'
                FROM flights AS f INNER JOIN airports AS a ON a.id=f.departure_airport_id
                INNER JOIN airports AS b ON b.id=f.arrival_airport_id
                INNER JOIN flight_status AS fs ON f.status_id=fs.id
                LEFT JOIN gates ON f.id=gates.flight_id
                WHERE f.scheduled_time > CURDATE() AND a.code='POV'
                ORDER BY f.scheduled_time";
                
                $result = $conn->query($query);

                while($row = $result->fetch()) {
                    echo "<tr>";
                    echo "<td>" . $row['plane'] . "</td>";
                    echo "<td>" . $row['city'] . ", " . $row['nation'] . "</td>";
                    echo "<td>" . $row['airportName'] . "</td>";
                    echo "<td>" . $row['depTime'] . "</td>";
                    if($row["gateNumber"]!=NULL)
                        echo "<td>" . $row['gateNumber'] . "</td>";
                    else
                        echo "<td> - </td>";
                    echo "<td>" . $row['flightStatus'] . "</td>";
                    echo "</tr>";
                }
                } catch(PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
            ?>
        </table>
        <p>Arrivals</p>
        <table border=1>
            <tr>
                <th>Plane</th>
                <th>From</th>
                <th>Airport</th>
                <th>Arrival Time</th>
                <th>Status</th>
            </tr>
            <?php
                try {
                $query="SELECT f.plane_id AS 'plane', a.nation AS 'nation', a.city AS 'city', a.name AS 'airportName', f.scheduled_time AS 'depTime', fs.status AS 'flightStatus'
                FROM flights AS f INNER JOIN airports AS a ON a.id=f.departure_airport_id
                INNER JOIN airports AS b ON b.id=f.arrival_airport_id
                INNER JOIN flight_status AS fs ON f.status_id=fs.id
                WHERE f.scheduled_time > CURDATE() AND b.code='POV'
                ORDER BY f.scheduled_time";
                
                $result = $conn->query($query);

                while($row = $result->fetch()) {
                    echo "<tr>";
                    echo "<td>".$row['plane']."</td>";
                    echo "<td>".$row['city'].", ".$row['nation']."</td>";
                    echo "<td>".$row['airportName']."</td>";
                    echo "<td>".$row['depTime']."</td>";
                    echo "<td>".$row['flightStatus']."</td>";
                    echo "</tr>";
                }
                } catch(PDOException $e) {
                    echo "Error: ".$e->getMessage();
                }
            ?>
        </table>
    </body>
</html>
