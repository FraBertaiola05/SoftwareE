<?php
require "Classes/User.php";
require "Classes/FlightScheduleSystem.php";
session_start();
$user=null;
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::AirlineCompanyManager){
    $user=unserialize($_SESSION["user"]);
}else{
    header('Location: index.php');
}

$result="";
if(isset($_POST["id"])&&isset($_POST["datetime"])&&isset($_POST["plane"])&&isset($_POST["pilot"])&&isset($_POST["dAirport"])&&isset($_POST["aAirport"])&&isset($_POST["status"]))
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
        <button onclick="loadForm('ADD')">Add a Flight</button>
        <button onclick="loadForm('MODIFY')">Modify a Flight</button>
        <button onclick="loadForm('DELETE')">Delete a Flight</button>
        <?php
            if($result!="")
                echo "<p>$result</p>";
        ?>
        <div id="container"></div>
        <p>Notifications</p>
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
