<?php
require "Classes/User.php";
require "Classes/FlightScheduleSystem.php";
require "Classes/TrafficControlSystem.php";
session_start();
$user=null;
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::Pilot){
    $user=unserialize($_SESSION["user"]);
}else{
    header('Location: index.php');
}

require 'DatabaseInfo.php';
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Could not connect. ".$e->getMessage());
}

$pilotId = $user->getId();

$tcs = new TrafficControlSystem();
$landingResult = "";
$takeOffResult = "";
if(isset($_POST["action"])){
    if($_POST["action"] == "confirmLanding"){
        $landingResult = $tcs->confirmLanding($_POST["flight_id"]);
    } elseif($_POST["action"] == "confirmTakeOff"){
        $takeOffResult = $tcs->confirmTakeOff($_POST["flight_id"]);
    }
}

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

$notificationQuery = $conn->prepare("SELECT f.id, f.plane_id, f.scheduled_time, f.validation
    FROM flights f
    WHERE f.pilot_id = :pilotId AND f.validation IN ('NOT_ACCEPTED','REJECTED')
    AND f.scheduled_time > NOW()
    ORDER BY f.id DESC LIMIT 10");
$notificationQuery->bindParam(':pilotId', $pilotId);
$notificationQuery->execute();
$notifications = $notificationQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Pilot Dashboard</title>
    </head>
    <body>
        <h1>Pilot Dashboard</h1>
        <p>Welcome, <?php echo $user->getName()." ".$user->getSurname(); ?></p>

        <h2>Notifications</h2>
        <?php if(count($notifications)>0){ ?>
            <table border=1>
                <tr><th>Flight</th><th>Plane</th><th>Scheduled Time</th><th>Status</th></tr>
                <?php foreach($notifications as $n){
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

        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
