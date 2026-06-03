<?php
require "Classes/User.php";
require "Classes/FlightScheduleSystem.php";
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

$upcomingQuery = $conn->prepare("SELECT f.id, f.plane_id, f.scheduled_time, f.priority, f.validation,
    fs.status AS flight_status, d.code AS dep_code, d.city AS dep_city, d.nation AS dep_nation,
    a.code AS arr_code, a.city AS arr_city, a.nation AS arr_nation, g.gate_number
    FROM flights f
    INNER JOIN airports d ON f.departure_airport_id = d.id
    INNER JOIN airports a ON f.arrival_airport_id = a.id
    INNER JOIN flight_status fs ON f.status_id = fs.id
    LEFT JOIN gates g ON f.id = g.flight_id
    WHERE f.pilot_id = :pilotId AND f.scheduled_time > NOW() AND f.validation IN ('ACCEPTED','CONFIRMED')
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

$notificationQuery = $conn->prepare("SELECT f.id, f.plane_id, f.scheduled_time, f.validation, f.modify_id,
    f2.plane_id AS old_plane, f2.scheduled_time AS old_time
    FROM flights f
    LEFT JOIN flights f2 ON f.modify_id = f2.id
    WHERE f.pilot_id = :pilotId AND f.validation IN ('NOT_ACCEPTED','ACCEPTED','REJECTED')
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
                    if($n["modify_id"] !== null){
                        if($n["validation"] == "NOT_ACCEPTED")
                            $msg = "Modification requested - awaiting TC approval";
                        elseif($n["validation"] == "ACCEPTED")
                            $msg = "Flight modification approved";
                        elseif($n["validation"] == "REJECTED")
                            $msg = "Flight modification was rejected";
                    }else{
                        if($n["validation"] == "NOT_ACCEPTED")
                            $msg = "New flight assigned - awaiting TC approval";
                        elseif($n["validation"] == "ACCEPTED")
                            $msg = "New flight approved";
                        elseif($n["validation"] == "REJECTED")
                            $msg = "New flight was rejected";
                    }
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
