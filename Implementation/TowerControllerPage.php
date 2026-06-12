<?php
require "Classes/User.php";
require "Controllers/TrafficControlSystem.php";
session_start();
$user=null;
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::TowerController){
    $user=unserialize($_SESSION["user"]);
    if($user->getChangePass())
        header('Location: ChangePassword.php');
}else{
    header('Location: index.php');
}

$tcs = new TrafficControlSystem();
$result="";

if(isset($_POST["action"])){
    switch($_POST["action"]){
        case "assignTakeOff":
            $result=$tcs->assignRunwayForTakeOff($_POST["flight_id"], $_POST["runway_id"]);
            break;
        case "assignLanding":
            $result=$tcs->assignRunwayForLanding($_POST["flight_id"], $_POST["runway_id"]);
            break;
        case "approveFlight":
            $result=$tcs->confirmFlight($_POST["flight_id"]);
            break;
        case "rejectFlight":
            $result=$tcs->rejectFlight($_POST["flight_id"]);
            break;
        case "updatePriority":
            $result=$tcs->updatePriority($_POST["flight_id"], $_POST["priority"]);
            break;
    }
}

$takeOffQueue = $tcs->getTakeOffQueue();
$landingQueue = $tcs->getLandingQueue();
$availableRunways = $tcs->getAvailableRunways();
$pendingFlights = $tcs->getPendingFlights();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Tower Controller Panel</title>
    </head>
    <body>
        <h1>Tower Controller Panel</h1>
        <?php
            if($result!="")
                echo "<p>$result</p>";
        ?>

        <h2>Take Off Queue</h2>
        <?php if(count($takeOffQueue)>0){ ?>
            <table border=1>
                <tr><th>ID</th><th>Plane</th><th>Model</th><th>Priority</th><th>Scheduled Time</th><th>Pilot</th></tr>
                <?php foreach($takeOffQueue as $f){ ?>
                    <tr>
                        <td><?php echo $f["id"]; ?></td>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["model"]; ?></td>
                        <td><?php echo $f["priority"]; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                        <td><?php echo $f["pilot_name"]." ".$f["pilot_surname"]; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <form method="POST">
                <input type="hidden" name="action" value="assignTakeOff">
                <label>Flight:
                    <select name="flight_id" required>
                        <?php foreach($takeOffQueue as $f){ ?>
                            <option value="<?php echo $f["id"]; ?>"><?php echo $f["plane_id"]." - Priority: ".$f["priority"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>Runway:
                    <select name="runway_id" required>
                        <?php foreach($availableRunways as $r){ ?>
                            <option value="<?php echo $r["id"]; ?>">Runway <?php echo $r["runway_number"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <input type="submit" value="Assign Runway">
            </form>
        <?php }else{ ?>
            <p>No planes in take off queue</p>
        <?php } ?>

        <h2>Landing Queue</h2>
        <?php if(count($landingQueue)>0){ ?>
            <table border=1>
                <tr><th>ID</th><th>Plane</th><th>Model</th><th>Priority</th><th>Scheduled Time</th><th>Pilot</th></tr>
                <?php foreach($landingQueue as $f){ ?>
                    <tr>
                        <td><?php echo $f["id"]; ?></td>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["model"]; ?></td>
                        <td><?php echo $f["priority"]; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                        <td><?php echo $f["pilot_name"]." ".$f["pilot_surname"]; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <form method="POST">
                <input type="hidden" name="action" value="assignLanding">
                <label>Flight:
                    <select name="flight_id" required>
                        <?php foreach($landingQueue as $f){ ?>
                            <option value="<?php echo $f["id"]; ?>"><?php echo $f["plane_id"]." - Priority: ".$f["priority"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>Runway:
                    <select name="runway_id" required>
                        <?php foreach($availableRunways as $r){ ?>
                            <option value="<?php echo $r["id"]; ?>">Runway <?php echo $r["runway_number"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <input type="submit" value="Assign Runway">
            </form>
        <?php }else{ ?>
            <p>No planes in landing queue</p>
        <?php } ?>

        <h2>Pending Flights</h2>
        <?php if(count($pendingFlights)>0){ ?>
            <table border=1>
                <tr><th>ID</th><th>Plane</th><th>Model</th><th>Scheduled Time</th><th>Pilot</th></tr>
                <?php foreach($pendingFlights as $f){ ?>
                    <tr>
                        <td><?php echo $f["id"]; ?></td>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["model"]; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                        <td><?php echo $f["pilot_name"]." ".$f["pilot_surname"]; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="approveFlight">
                <label>Flight:
                    <select name="flight_id" required>
                        <?php foreach($pendingFlights as $f){ ?>
                            <option value="<?php echo $f["id"]; ?>"><?php echo $f["plane_id"]." - ".$f["scheduled_time"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <input type="submit" value="Approve">
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="rejectFlight">
                <label>Flight:
                    <select name="flight_id" required>
                        <?php foreach($pendingFlights as $f){ ?>
                            <option value="<?php echo $f["id"]; ?>"><?php echo $f["plane_id"]." - ".$f["scheduled_time"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <input type="submit" value="Reject">
            </form>
        <?php }else{ ?>
            <p>No pending flights</p>
        <?php } ?>

        <h2>Manage Priority</h2>
        <form method="POST">
            <input type="hidden" name="action" value="updatePriority">
            <label>Flight:
                <select name="flight_id" required>
                    <?php
                    $allFlights = array_merge($takeOffQueue, $landingQueue);
                    foreach($allFlights as $f){ ?>
                        <option value="<?php echo $f["id"]; ?>"><?php echo $f["plane_id"]." - Current Priority: ".$f["priority"]; ?></option>
                    <?php } ?>
                </select>
            </label>
            <label>New Priority:
                <input type="number" name="priority" min="1" required>
            </label>
            <input type="submit" value="Update Priority">
        </form>

        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
