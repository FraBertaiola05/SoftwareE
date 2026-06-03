<?php
require "Classes/User.php";
require "Classes/GroundManagementSystem.php";
session_start();
$user=null;
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::GroundCrew){
    $user=unserialize($_SESSION["user"]);
}else{
    header('Location: index.php');
}

$gms = new GroundManagementSystem();
$result="";

if(isset($_POST["action"])){
    switch($_POST["action"]){
        case "toParking":
            $result=$gms->movePlaneToParking($_POST["flight_id"], $_POST["spot_id"]);
            break;
        case "toTaxiway":
            $result=$gms->movePlaneToTaxiway($_POST["flight_id"], $_POST["taxiway_id"]);
            break;
        case "toRunway":
            $result=$gms->movePlaneToRunway($_POST["flight_id"], $_POST["runway_id"]);
            break;
    }
}

$planesOnGround = $gms->getPlanesOnGround();
$parkingSpots = $gms->getAvailableParkingSpots();
$taxiways = $gms->getAvailableTaxiways();
$runways = $gms->getAvailableRunways();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Ground Crew Panel</title>
    </head>
    <body>
        <h1>Ground Crew Panel</h1>
        <?php
            if($result!="")
                echo "<p>$result</p>";
        ?>

        <h2>Planes on Ground</h2>
        <?php if(count($planesOnGround)>0){ ?>
            <table border=1>
                <tr><th>Plane</th><th>Model</th><th>Status</th><th>Location</th><th>Scheduled Time</th></tr>
                <?php foreach($planesOnGround as $p){
                    $location = "-";
                    if($p["parking_spot"]) $location = "Parking ".$p["parking_spot"];
                    else if($p["gate_number"]) $location = "Gate ".$p["gate_number"];
                    else if($p["taxiway_number"]) $location = "Taxiway ".$p["taxiway_number"];
                    else if($p["runway_number"]) $location = "Runway ".$p["runway_number"];
                ?>
                    <tr>
                        <td><?php echo $p["plane_number"]; ?></td>
                        <td><?php echo $p["model"]; ?></td>
                        <td><?php echo $p["plane_status"]; ?></td>
                        <td><?php echo $location; ?></td>
                        <td><?php echo $p["scheduled_time"] ?: "-"; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php }else{ ?>
            <p>No planes on the ground</p>
        <?php } ?>

        <h2>Move Plane to Parking</h2>
        <?php if(count($planesOnGround)>0 && count($parkingSpots)>0){ ?>
            <form method="POST">
                <input type="hidden" name="action" value="toParking">
                <label>Plane:
                    <select name="flight_id" required>
                        <?php foreach($planesOnGround as $p){ ?>
                            <option value="<?php echo $p["flight_id"]; ?>"><?php echo $p["plane_number"]." - ".$p["plane_status"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>Parking Spot:
                    <select name="spot_id" required>
                        <?php foreach($parkingSpots as $s){ ?>
                            <option value="<?php echo $s["id"]; ?>"><?php echo $s["spot_number"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <input type="submit" value="Move to Parking">
            </form>
        <?php }else{ ?>
            <p>No available parking spots or no planes to move</p>
        <?php } ?>

        <h2>Move Plane to Taxiway</h2>
        <?php if(count($planesOnGround)>0 && count($taxiways)>0){ ?>
            <form method="POST">
                <input type="hidden" name="action" value="toTaxiway">
                <label>Plane:
                    <select name="flight_id" required>
                        <?php foreach($planesOnGround as $p){ ?>
                            <option value="<?php echo $p["flight_id"]; ?>"><?php echo $p["plane_number"]." - ".$p["plane_status"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>Taxiway:
                    <select name="taxiway_id" required>
                        <?php foreach($taxiways as $t){ ?>
                            <option value="<?php echo $t["id"]; ?>"><?php echo $t["taxiway_number"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <input type="submit" value="Move to Taxiway">
            </form>
        <?php }else{ ?>
            <p>No available taxiways or no planes to move</p>
        <?php } ?>

        <h2>Move Plane to Runway</h2>
        <?php if(count($planesOnGround)>0 && count($runways)>0){ ?>
            <form method="POST">
                <input type="hidden" name="action" value="toRunway">
                <label>Plane:
                    <select name="flight_id" required>
                        <?php foreach($planesOnGround as $p){ ?>
                            <option value="<?php echo $p["flight_id"]; ?>"><?php echo $p["plane_number"]." - ".$p["plane_status"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>Runway:
                    <select name="runway_id" required>
                        <?php foreach($runways as $r){ ?>
                            <option value="<?php echo $r["id"]; ?>">Runway <?php echo $r["runway_number"]; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <input type="submit" value="Move to Runway">
            </form>
        <?php }else{ ?>
            <p>No available runways or no planes to move</p>
        <?php } ?>

        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
