<?php
//Import required files
require "Classes/User.php";
require "Controllers/FlightScheduleSystem.php";
require "DatabaseInfo.php";

//Start the session to handle user login status
session_start();
$user=null;

//Check if the user is logged with the correct role for this page. If not, redirect it to the login page
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::AirlineCompanyManager){
    //Obtain the User object of the logged user
    $user=unserialize($_SESSION["user"]);
    //If the user is logged but it still has the same password as when the account was created, redirrect it to the page to change the password
    if($user->getChangePass())
        header('Location: ChangePassword.php');
}else{
    header('Location: index.php');
}

$result="";
//When the user confirms a notification about a confirmed added/modified flight by the tower controller, call the function to set the status of the flight to "Confirmed"
if(isset($_POST["id"])&&isset($_POST["type"])&&$_POST["type"]=="Confirm")
    $result=FlightScheduleSystem::updateAccepted($_POST["id"]);

//When the user confirms a notification about a rejected added/modified flight by the tower controller, call the function that remove the rejected added/modified flight
else if(isset($_POST["id"])&&isset($_POST["type"])&&$_POST["type"]=="Delete")
    $result=FlightScheduleSystem::deleteRejected($_POST["id"]);

//Check if there is data entered in the form to modify a flight. If this is the case, send the data to the function to modify the flight
else if(isset($_POST["id"])&&isset($_POST["datetime"])&&isset($_POST["plane"])&&isset($_POST["pilot"])&&isset($_POST["dAirport"])&&isset($_POST["aAirport"])&&isset($_POST["status"]))
    $result=FlightScheduleSystem::requestModifyFlight($_POST["datetime"],$_POST["plane"],$_POST["pilot"],$_POST["dAirport"],$_POST["aAirport"],$_POST["status"],$_POST["id"]);

//Check if there is data entered in the form to delete a flight. If this is the case, send the data to the function to delete the flight
else if(isset($_POST["flight"]))
    $result=FlightScheduleSystem::deleteFlight($_POST["flight"]);

//Check if there is data entered in the form to add a flight. If this is the case, send the data to the function to add the flight
else if(isset($_POST["datetime"])&&isset($_POST["plane"])&&isset($_POST["pilot"])&&isset($_POST["dAirport"])&&isset($_POST["aAirport"])&&isset($_POST["status"]))
    $result=FlightScheduleSystem::requestAddFlight($_POST["datetime"],$_POST["plane"],$_POST["pilot"],$_POST["dAirport"],$_POST["aAirport"],$_POST["status"]);

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Airline Page</title>
        <script>
            //Load from the AJAX page the form to enter the data
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
            //Load from the AJAX page the form to modify a user with its data
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
        <!--Buttons to call the AJAX functionalities to load the forms for the various operations-->
        <button onclick="loadForm('ADD')">Add a Flight</button>
        <button onclick="loadForm('MODIFY')">Modify a Flight</button>
        <button onclick="loadForm('DELETE')">Delete a Flight</button>
        <?php
            //Show the result of an operation (Add/Modify/Delete)
            if($result!="")
                echo "<p>$result</p>";
        ?>
        <!--Container where the JavaScrip puts the form-->
        <div id="container"></div>
        <!--Button to redirrect to the logout page-->
        <p>Notifications</p>
        <?php
            //Connect to the database and fetch the notifcations
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
            //Format the fetched data into HTML
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
            //Print the table with the upcoming departures
            echo FlightScheduleSystem::getFlightHistoryTable(true);
        ?>
        <p>Arrivals</p>
        <?php
            //Print the table with the upcoming arrivals
            echo FlightScheduleSystem::getFlightHistoryTable(false);
        ?>
        <!--Button to redirect to the logout page-->
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
