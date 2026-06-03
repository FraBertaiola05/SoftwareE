<?php
require "Classes/User.php";
require "Classes/FlightScheduleSystem.php";
require "DatabaseInfo.php";
session_start();
$user=null;
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::AirlineCompanyManager){
    $user=unserialize($_SESSION["user"]);
}else{
    header('Location: index.php');
}

$result="";
if(isset($_POST["id"])&&isset($_POST["type"])&&$_POST["type"]=="Confirm")
    $result=FlightScheduleSystem::updateAccepted($_POST["id"]);
else if(isset($_POST["id"])&&isset($_POST["type"])&&$_POST["type"]=="Delete")
    $result=FlightScheduleSystem::deleteRejected($_POST["id"]);
else if(isset($_POST["id"])&&isset($_POST["datetime"])&&isset($_POST["plane"])&&isset($_POST["pilot"])&&isset($_POST["dAirport"])&&isset($_POST["aAirport"])&&isset($_POST["status"]))
    $result=FlightScheduleSystem::requestModifyFlight($_POST["datetime"],$_POST["plane"],$_POST["pilot"],$_POST["dAirport"],$_POST["aAirport"],$_POST["status"],$_POST["id"]);
else if(isset($_POST["flight"]))
    $result=FlightScheduleSystem::deleteFlight($_POST["flight"]);
else if(isset($_POST["datetime"])&&isset($_POST["plane"])&&isset($_POST["pilot"])&&isset($_POST["dAirport"])&&isset($_POST["aAirport"])&&isset($_POST["status"]))
    $result=FlightScheduleSystem::requestAddFlight($_POST["datetime"],$_POST["plane"],$_POST["pilot"],$_POST["dAirport"],$_POST["aAirport"],$_POST["status"]);

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Airline Page</title>
        <script>
            function loadForm(str){
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("container").innerHTML = this.responseText;
                }
                };
                xmlhttp.open("GET", "AJAX/AirlinePageAJAX.php?type=" + str, true);
                xmlhttp.send();
            }

            function loadModify(){
                var flightId = document.getElementById("flight").value;
                if(!flightId)
                    document.getElementById("container2").innerHTML =""
                else{
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            document.getElementById("container2").innerHTML = this.responseText;
                        }
                    };
                    xmlhttp.open("GET", "AJAX/AirlinePageAJAX.php?type=MODIFY2&id="+flightId, true);
                    xmlhttp.send();
                }
            }
        </script>
    </head>
    <body>
        <button onclick="loadForm('ADD')">Add a Flight</button>
        <button onclick="loadForm('MODIFY')">Modify a Flight</button>
        <button onclick="loadForm('DELETE')">Delete a Flight</button>
        <?php
            if($result!="")
                echo "<p>$result</p>";
        ?>
        <div id="container"></div>
        <p>Notifications</p>
        <?php
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e){
                echo "Could not connect. ".$e->getMessage();
            }
            try {
                $query=$conn->prepare("SELECT f.id AS id, f.plane_id AS plane, d.code AS dAirport, a.code AS aAirport, f.scheduled_time AS time, f.validation AS validation
                FROM flights AS f INNER JOIN airports AS d ON f.departure_airport_id=d.id
                INNER JOIN airports AS a ON f.arrival_airport_id=a.id
                INNER JOIN planes AS p ON f.plane_id=p.plane_number
                WHERE p.company_id=:company AND f.validation IN ('ACCEPTED','REJECTED') AND f.scheduled_time>=CURDATE()");
                $company=$user->getCompany();
                $query->bindParam(':company',$company);
                $query->execute();
            } catch(PDOException $e){
                echo "Query Error. ".$e->getMessage();
            }
            while($row = $query->fetch()){
                echo "<form action=AirlinePage.php method=POST>";
                if($row["validation"]=="ACCEPTED")
                    echo "<label for='id'>The following flight was accepted: ".$row["plane"].": ".$row["dAirport"]." -> ".$row["aAirport"]." - ".$row["time"]."</label>
                    <input type='hidden' name='id' id='id' value=".$row["id"].">
                    <input type='submit' name='type' id='type' value='Confirm'></form>";
                else
                    echo "<label for='id'>The following flight was rejected: ".$row["plane"].": ".$row["dAirport"]." -> ".$row["aAirport"]." - ".$row["time"]."</label>
                    <input type='hidden' name='id' id='id' value=".$row["id"].">
                    <input type='submit' name='type' id='type' value='Delete'></form>";
            }
        ?>
        <p>Departures</p>
        <?php
            echo FlightScheduleSystem::getFlightHistoryTable(true);
        ?>
        <p>Arrivals</p>
        <?php
            echo FlightScheduleSystem::getFlightHistoryTable(false);
        ?>
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
