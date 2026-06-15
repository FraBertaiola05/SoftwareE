<?php
//Import required files
require "Classes/User.php";
require "Controllers/TrafficControlSystem.php";

//Start the session to handle user login status
session_start();
$user=null;

//Check if the user is logged with the correct role for this page. If not, redirect it to the login page
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::TowerController){
    //Obtain the User object of the logged user
    $user=unserialize($_SESSION["user"]);
    //If the user is logged but it still has the same password as when the account was created, redirect it to the page to change the password
    if($user->getChangePass())
        header('Location: ChangePassword.php');
}else{
    header('Location: index.php');
}

//Create an instance of the TrafficControlSystem to interact with the flight queues and runways
$tcs = new TrafficControlSystem();
$result="";

//Check if an action was submitted through a form
if(isset($_POST["action"])){
    switch($_POST["action"]){
        //Assign a runway to a flight that is waiting for take-off
        case "assignTakeOff":
            $result=$tcs->assignRunwayForTakeOff($_POST["flight_id"], $_POST["runway_id"]);
            break;
        //Assign a runway to a flight that is waiting to land
        case "assignLanding":
            $result=$tcs->assignRunwayForLanding($_POST["flight_id"], $_POST["runway_id"]);
            break;
        //Approve a pending flight created by an airline manager
        case "approveFlight":
            $result=$tcs->confirmFlight($_POST["flight_id"]);
            break;
        //Reject a pending flight created by an airline manager
        case "rejectFlight":
            $result=$tcs->rejectFlight($_POST["flight_id"]);
            break;
        //Update the priority of a queued flight
        case "updatePriority":
            $result=$tcs->updatePriority($_POST["flight_id"], $_POST["priority"]);
            break;
    }
}

//Fetch the data needed to display the queues and available runways
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
            //Show the result of an operation (assign runway, approve/reject flight, update priority)
            if($result!="")
                echo "<p>$result</p>";
        ?>

        <!--show flights waiting for take-off and allow runway assignment-->
        <h2>Take Off Queue</h2>
        <?php if(count($takeOffQueue)>0){ ?>
            <table border=1>
                <tr><th>ID</th><th>Plane</th><th>Model</th><th>Route</th><th>Priority</th><th>Scheduled Time</th><th>Pilot</th></tr>
                <?php foreach($takeOffQueue as $f){ ?>
                    <tr>
                        <td><?php echo $f["id"]; ?></td>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["model"]; ?></td>
                        <td><?php echo $f["dep_code"]." (".$f["dep_city"].") -> ".$f["arr_code"]." (".$f["arr_city"].")"; ?></td>
                        <td><?php echo $f["priority"]; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                        <td><?php echo $f["pilot_name"]." ".$f["pilot_surname"]; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <!--Form to assign a runway to a flight in the take-off queue-->
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

        <!--show flights waiting to land and allow runway assignment-->
        <h2>Landing Queue</h2>
        <?php if(count($landingQueue)>0){ ?>
            <table border=1>
                <tr><th>ID</th><th>Plane</th><th>Model</th><th>Route</th><th>Priority</th><th>Scheduled Time</th><th>Pilot</th></tr>
                <?php foreach($landingQueue as $f){ ?>
                    <tr>
                        <td><?php echo $f["id"]; ?></td>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["model"]; ?></td>
                        <td><?php echo $f["dep_code"]." (".$f["dep_city"].") -> ".$f["arr_code"]." (".$f["arr_city"].")"; ?></td>
                        <td><?php echo $f["priority"]; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                        <td><?php echo $f["pilot_name"]." ".$f["pilot_surname"]; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <!--Form to assign a runway to a flight in the landing queue-->
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

        <!--show flights created by airlines awaiting TC approval-->
        <h2>Pending Flights</h2>
        <?php if(count($pendingFlights)>0){ ?>
            <table border=1>
                <tr><th>ID</th><th>Plane</th><th>Model</th><th>Route</th><th>Scheduled Time</th><th>Pilot</th></tr>
                <?php foreach($pendingFlights as $f){ ?>
                    <tr>
                        <td><?php echo $f["id"]; ?></td>
                        <td><?php echo $f["plane_id"]; ?></td>
                        <td><?php echo $f["model"]; ?></td>
                        <td><?php echo $f["dep_code"]." (".$f["dep_city"].") -> ".$f["arr_code"]." (".$f["arr_city"].")"; ?></td>
                        <td><?php echo $f["scheduled_time"]; ?></td>
                        <td><?php echo $f["pilot_name"]." ".$f["pilot_surname"]; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <!--Form to approve a pending flight-->
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
            <!--Form to reject a pending flight-->
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

        <!--allows the TC to change the priority of queued flights-->
        <h2>Manage Priority</h2>
        <form method="POST">
            <input type="hidden" name="action" value="updatePriority">
            <label>Flight:
                <select name="flight_id" required>
                    <?php
                    //Combine take-off and landing queues so the TC can update priority for any queued flight
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

        <!--Button to redirect to the logout page-->
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
