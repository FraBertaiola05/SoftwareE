<?php
//Import required files
require "Classes/User.php";
require "Controllers/GroundManagementSystem.php";

//Start the session to handle user login status
session_start();
$user=null;

//Check if the user is logged with the correct role for this page. If not, redirect it to the login page
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::GroundCrew){
    $user=unserialize($_SESSION["user"]);
    //If the user is logged but it still has the same password as when the account was created, redirect it to the page to change the password
    if($user->getChangePass())
        header('Location: ChangePassword.php');
}else{
    header('Location: index.php');
}

//Create an instance of the GroundManagementSystem to interact with plane movements and resources
$gms = new GroundManagementSystem();
$result="";

//Check if an action was submitted through a form
if(isset($_POST["action"])){
    switch($_POST["action"]){
        //Move a plane from the taxiway to a parking spot
        case "toParking":
            $result=$gms->movePlaneToParking($_POST["flight_id"], $_POST["spot_id"]);
            break;
        //Move a plane from parking to a taxiway for take-off preparation
        case "toTaxiway":
            $result=$gms->movePlaneToTaxiway($_POST["flight_id"], $_POST["taxiway_id"]);
            break;
    }
}

//Fetch the data needed to display the current state of planes and available resources
$planesOnGround = $gms->getPlanesOnGround();
$parkingSpots = $gms->getAvailableParkingSpots();
$taxiways = $gms->getAvailableTaxiways();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Ground Crew Panel</title>
    </head>
    <body>
        <h1>Ground Crew Panel</h1>
        <?php
            //Show the result of an operation (move to parking, move to taxiway)
            if($result!="")
                echo "<p>$result</p>";
        ?>

        <!--show the current location of every plane at the airport-->
        <h2>Planes on Ground</h2>
        <?php if(count($planesOnGround)>0){ ?>
            <table border=1>
                <tr><th>Plane</th><th>Model</th><th>Status</th><th>Location</th><th>Scheduled Time</th></tr>
                <?php foreach($planesOnGround as $p){
                    //Determine which resource the plane is currently occupying, prefering most specific (parking → gate → taxiway → runway)
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

        <!--form to move a plane from the taxiway to an available parking spot-->
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

        <!--form to move a plane from parking to an available taxiway for take-off preparation-->
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

        <!--Button to redirect to the logout page-->
        <button onclick="window.location.href='Logout.php'">Logout</button>
    </body>
</html>
