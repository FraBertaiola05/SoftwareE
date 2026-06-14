<?php
//Import required files
require "Classes/User.php";
require "Controllers/FlightScheduleSystem.php";
require "Controllers/TrafficControlSystem.php";

//Start the session to handle user login status
session_start();
$user=null;

//Check if the user is logged with the correct role for this page. If not, redirect it to the login page
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::Pilot){
    //Obtain the User object of the logged user
    $user=unserialize($_SESSION["user"]);
    //If the user is logged but it still has the same password as when the account was created, redirect it to the page to change the password
    if($user->getChangePass())
        header('Location: ChangePassword.php');
}else{
    header('Location: index.php');
}

//Import database connection file and establish connection
require 'DatabaseInfo.php';
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Could not connect. ".$e->getMessage());
}

//Get the logged pilot's ID for filtering queries
$pilotId = $user->getId();

//Create an instance of the TrafficControlSystem to confirm landing and take-off
$tcs = new TrafficControlSystem();
$landingResult = "";
$takeOffResult = "";
$assignResult = "";

//Check if the pilot confirmed a landing or take-off action
if(isset($_POST["action"])){
    if($_POST["action"] == "confirmLanding"){
        $landingResult = $tcs->confirmLanding($_POST["flight_id"]);
    } elseif($_POST["action"] == "confirmTakeOff"){
        $takeOffResult = $tcs->confirmTakeOff($_POST["flight_id"]);
    } elseif($_POST["action"] == "assignTaxiway"){
        $assignResult = $tcs->assignTaxiwayAfterLanding($_POST["flight_id"], $_POST["taxiway_id"]);
    }
}

//Fetch upcoming flights where the pilot is assigned
$upcomingQuery = $conn->prepare("SELECT f.id, f.plane_id, f.scheduled_time, f.priority, f.validation,
    fs.status AS flight_status, d.code AS dep_code, d.city AS dep_city, d.nation AS dep_nation,
    a.code AS arr_code, a.city AS arr_city, a.nation AS arr_nation, g.gate_number
    FROM flights f
    INNER JOIN airports d ON f.departure_airport_id = d.id
    INNER JOIN airports a ON f.arrival_airport_id = a.id
    INNER JOIN flight_status fs ON f.status_id = fs.id
    LEFT JOIN gates g ON f.id = g.flight_id
    WHERE f.pilot_id = :pilotId AND f.scheduled_time > NOW() AND f.validation = 'CONFIRMED'
    ORDER BY f.scheduled_time ASC");
$upcomingQuery->bindParam(':pilotId', $pilotId);
$upcomingQuery->execute();
$upcomingFlights = $upcomingQuery->fetchAll(PDO::FETCH_ASSOC);

//Fetch past flights for the pilot's history view
$historyQuery = $conn->prepare("SELECT f.id, f.plane_id, f.scheduled_time, f.priority, f.validation,
    fs.status AS flight_status, d.code AS dep_code, d.city AS dep_city,
    a.code AS arr_code, a.city AS arr_city, g.gate_number
    FROM flights f
    INNER JOIN airports d ON f.departure_airport_id = d.id
    INNER JOIN airports a ON f.arrival_airport_id = a.id
    INNER JOIN flight_status fs ON f.status_id = fs.id
    LEFT JOIN gates g ON f.id = g.flight_id
    WHERE f.pilot_id = :pilotId AND (f.scheduled_time <= NOW() OR f.validation = 'DELETED')
    ORDER BY f.scheduled_time DESC LIMIT 20");
$historyQuery->bindParam(':pilotId', $pilotId);
$historyQuery->execute();
$historyFlights = $historyQuery->fetchAll(PDO::FETCH_ASSOC);

//Fetch notifications for flights awaiting TC approval or that were rejected
$notificationQuery = $conn->prepare("SELECT f.id, f.plane_id, f.scheduled_time, f.validation
    FROM flights f
    WHERE f.pilot_id = :pilotId AND f.validation IN ('NOT_ACCEPTED','REJECTED')
    AND f.scheduled_time > NOW()
    ORDER BY f.id DESC LIMIT 10");
$notificationQuery->bindParam(':pilotId', $pilotId);
$notificationQuery->execute();
$notifications = $notificationQuery->fetchAll(PDO::FETCH_ASSOC);

//Fetch landed flights that do not have a taxiway assigned yet
$taxiwayQuery = $conn->prepare("SELECT f.id, f.plane_id, f.scheduled_time,
    d.code AS dep_code, d.city AS dep_city,
    a.code AS arr_code, a.city AS arr_city
    FROM flights f
    INNER JOIN airports d ON f.departure_airport_id = d.id
    INNER JOIN airports a ON f.arrival_airport_id = a.id
    WHERE f.pilot_id = :pilotId AND f.status_id = 5 AND f.validation IN ('CONFIRMED','ACCEPTED')
    AND NOT EXISTS (SELECT 1 FROM taxiway_flight tf WHERE tf.flight_id = f.id)
    ORDER BY f.scheduled_time ASC");
$taxiwayQuery->bindParam(':pilotId', $pilotId);
$taxiwayQuery->execute();
$landedNoTaxiway = $taxiwayQuery->fetchAll(PDO::FETCH_ASSOC);

//Fetch available taxiways
$availableTaxiways = $tcs->getAvailableTaxiways();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Pilot Dashboard</title>
    </head>
    <body>
        <h1>Pilot Dashboard</h1>
        <p>Welcome, <?php echo $user->getName()." ".$user->getSurname(); ?></p>

        <!--show pending notifications for the pilot-->
        <h2>Notifications</h2>
        <?php if(count($notifications)>0){ ?>
            <table border=1>
                <tr><th>Flight</th><th>Plane</th><th>Scheduled Time</th><th>Status</th></tr>
                <?php foreach($notifications as $n){
                    //Determine the message to display based on the validation status
                    $msg = "";
                    if($n["validation"] == "NOT_ACCEPTED")
                        $msg = "Awaiting TC approval";
                    elseif($n["validation"] == "REJECTED")
                        $msg = "Flight was rejected";
                ?>
                    <tr>
                        <td><?php echo $n["id"]; ?></td>
                        <td><?php echo $n["plane_id"]; ?></td>
                        <td><?php echo $n["scheduled_time"]; ?></td>
                        <td><?php echo $msg; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php }else{ ?>
            <p>No new notifications</p>
        <?php } ?>

        <!--show flights waiting to land so the pilot can confirm landing-->
        <h2>Ready to Land</h2>
        <?php
        $landingQuery = $conn->prepare("SELECT f.id, f.plane_id, f.scheduled_time, f.priority,
            d.code AS dep_code, d.city AS dep_city,
            a.code AS arr_code, a.city AS arr_city,
            r.runway_number
            FROM flights f
            INNER JOIN airports d ON f.departure_airport_id = d.id
            INNER JOIN airports a ON f.arrival_airport_id = a.id
            INNER JOIN runways r ON r.flight_id = f.id
            WHERE f.pilot_id = :pilotId AND f.status_id = 4 AND f.validation IN ('CONFIRMED','ACCEPTED')
            ORDER BY f.priority ASC, f.scheduled_time ASC");
        $landingQuery->bindParam(':pilotId', $pilotId);
        $landingQuery->execute();
        $readyToLand = $landingQuery->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if(count($readyToLand) > 0){ ?>
            <table border=1>
                <tr><th>Plane</th><th>From</th><th>To</th><th>Scheduled Time</th><th>Runway</th><th>Action</th></tr>
                <?php foreach($readyToLand as $f){ ?>
                    <tr>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["dep_code"]." (".$f["dep_city"].")"; ?></td>
                        <td><?php echo $f["arr_code"]." (".$f["arr_city"].")"; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                        <td><?php echo $f["runway_number"]; ?></td>
                        <td>
                            <!--Form to confirm landing for a specific flight-->
                            <form method="POST">
                                <input type="hidden" name="action" value="confirmLanding">
                                <input type="hidden" name="flight_id" value="<?php echo $f["id"]; ?>">
                                <input type="submit" value="Confirm Landing">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php }else{ ?>
            <p>No flights ready to land</p>
        <?php } ?>

        <?php if($landingResult != "") echo "<p>$landingResult</p>"; ?>

        <!--show flights that have landed and need a taxiway assigned-->
        <h2>Assign Taxiway</h2>
        <?php if(count($landedNoTaxiway) > 0 && count($availableTaxiways) > 0){ ?>
            <table border=1>
                <tr><th>Plane</th><th>Route</th><th>Scheduled Time</th></tr>
                <?php foreach($landedNoTaxiway as $f){ ?>
                    <tr>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["dep_code"]." (".$f["dep_city"].") -> ".$f["arr_code"]." (".$f["arr_city"].")"; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <form method="POST">
                <input type="hidden" name="action" value="assignTaxiway">
                <label>Flight:
                    <select name="flight_id" required>
                        <?php foreach($landedNoTaxiway as $f){ ?>
                            <option value="<?php echo $f["id"]; ?>"><?php echo $f["plane_id"]." - ".$f["scheduled_time"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>Taxiway:
                    <select name="taxiway_id" required>
                        <?php foreach($availableTaxiways as $t){ ?>
                            <option value="<?php echo $t["id"]; ?>"><?php echo $t["taxiway_number"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <input type="submit" value="Assign Taxiway">
            </form>
        <?php }else{ ?>
            <p>No landed flights waiting for taxiway assignment</p>
        <?php } ?>

        <?php if($assignResult != "") echo "<p>$assignResult</p>"; ?>

        <!--show flights ready for take-off so the pilot can confirm take-off-->
        <h2>Ready for Take Off</h2>
        <?php
        $takeOffQuery = $conn->prepare("SELECT f.id, f.plane_id, f.scheduled_time, f.priority,
            d.code AS dep_code, d.city AS dep_city,
            a.code AS arr_code, a.city AS arr_city,
            r.runway_number
            FROM flights f
            INNER JOIN airports d ON f.departure_airport_id = d.id
            INNER JOIN airports a ON f.arrival_airport_id = a.id
            INNER JOIN runways r ON r.flight_id = f.id
            WHERE f.pilot_id = :pilotId AND f.status_id = 3 AND f.validation IN ('CONFIRMED','ACCEPTED')
            ORDER BY f.priority ASC, f.scheduled_time ASC");
        $takeOffQuery->bindParam(':pilotId', $pilotId);
        $takeOffQuery->execute();
        $readyForTakeOff = $takeOffQuery->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if(count($readyForTakeOff) > 0){ ?>
            <table border=1>
                <tr><th>Plane</th><th>From</th><th>To</th><th>Scheduled Time</th><th>Runway</th><th>Action</th></tr>
                <?php foreach($readyForTakeOff as $f){ ?>
                    <tr>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["dep_code"]." (".$f["dep_city"].")"; ?></td>
                        <td><?php echo $f["arr_code"]." (".$f["arr_city"].")"; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                        <td><?php echo $f["runway_number"]; ?></td>
                        <td>
                            <!--Form to confirm take-off for a specific flight-->
                            <form method="POST">
                                <input type="hidden" name="action" value="confirmTakeOff">
                                <input type="hidden" name="flight_id" value="<?php echo $f["id"]; ?>">
                                <input type="submit" value="Confirm Take Off">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php }else{ ?>
            <p>No flights ready for take off</p>
        <?php } ?>

        <?php if($takeOffResult != "") echo "<p>$takeOffResult</p>"; ?>

        <!--show the pilot's confirmed upcoming flights-->
        <h2>Upcoming Flights</h2>
        <?php if(count($upcomingFlights)>0){ ?>
            <table border=1>
                <tr>
                    <th>Plane</th><th>Route</th><th>Scheduled Time</th>
                    <th>Priority</th><th>Gate</th><th>Status</th>
                </tr>
                <?php foreach($upcomingFlights as $f){ ?>
                    <tr>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["dep_code"]." (".$f["dep_city"].", ".$f["dep_nation"].") -> ".$f["arr_code"]." (".$f["arr_city"].", ".$f["arr_nation"].")"; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                        <td><?php echo $f["priority"]; ?></td>
                        <td><?php echo $f["gate_number"] ?: "Not assigned"; ?></td>
                        <td><?php echo $f["flight_status"]; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php }else{ ?>
            <p>No upcoming flights</p>
        <?php } ?>

        <!--show the pilot's flight history-->
        <h2>Flight History</h2>
        <?php if(count($historyFlights)>0){ ?>
            <table border=1>
                <tr>
                    <th>Plane</th><th>Route</th><th>Scheduled Time</th>
                    <th>Priority</th><th>Gate</th><th>Status</th>
                </tr>
                <?php foreach($historyFlights as $f){ ?>
                    <tr>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["dep_code"]." -> ".$f["arr_code"]; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                        <td><?php echo $f["priority"]; ?></td>
                        <td><?php echo $f["gate_number"] ?: "-"; ?></td>
                        <td><?php echo $f["flight_status"]." (".$f["validation"].")"; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php }else{ ?>
            <p>No flight history</p>
        <?php } ?>

        <!--Button to redirect to the logout page-->
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
