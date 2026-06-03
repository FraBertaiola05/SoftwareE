<?php
require "../Classes/User.php";
require "../DatabaseInfo.php";
$s="";
session_start();
$user=null;
if(isset($_SESSION["user"])&&unserialize($_SESSION["user"])->getRole()==RoleEnum::AirlineCompanyManager){
    $user=unserialize($_SESSION["user"]);
}else{
    header('Location: index.php');
}
if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["type"])){

    if($_GET["type"]=="ADD"){

        $s="<form action='AirlinePage.php' method='POST'>".
        "<label for='datetime'>Time: </label>".
        "<input type='datetime-local' id='datetime' name='datetime' required><br>".
        "<label for='plane'>Plane: </label>".
        "<select id='plane' name='plane' required>".
        "<option value=''></option>";
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            echo "Could not connect. ".$e->getMessage();
        }
        try {
            $query="SELECT * FROM planes WHERE company_id=".$user->getCompany();
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["plane_number"].">".$row["plane_number"]." - ".$row["model"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<label for='pilot'>Pilot: </label>".
        "<select id='pilot' name='pilot' required>".
        "<option value=''></option>";
        try {
            $query="SELECT * FROM users WHERE role_id=2 AND company_id=".$user->getCompany();
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["id"].">".$row["name"]." ".$row["surname"]." - ".$row["email"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<label for='dAirport'>Departure Airport: </label>".
        "<select id='dAirport' name='dAirport' required>".
        "<option value=''></option>";
        try {
            $query="SELECT * FROM airports";
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["id"].">".$row["code"]." - ".$row["city"].", ".$row["nation"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<label for='aAirport'>Arrival Airport: </label>".
        "<select id='aAirport' name='aAirport' required>".
        "<option value=''></option>";
        try {
            $query="SELECT * FROM airports";
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["id"].">".$row["code"]." - ".$row["city"].", ".$row["nation"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<label for='status'>Status: </label>".
        "<select id='status' name='status' required>".
        "<option value=''></option>";
        try {
            $query="SELECT * FROM flight_status";
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["id"].">".$row["status"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<input type='submit' value='Add'></form>";
        echo $s;

    }else if($_GET["type"]=="MODIFY"){

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            die("Could not connect. ".$e->getMessage());
        }
        $s="<form action='AirlinePage.php' method='POST'>".
        "<select id='flight' name='flight' onchange='loadModify()' required>".
        "<option value=''></option>";
        try {
            $dateNow=date('Y-m-d H:i:s');
            $query="SELECT f.id AS id, f.plane_id AS plane, d.code AS dAirport, a.code AS aAirport, f.scheduled_time AS time
            FROM flights AS f INNER JOIN airports AS d ON f.departure_airport_id=d.id
            INNER JOIN airports AS a ON f.arrival_airport_id=a.id
            INNER JOIN planes AS p ON f.plane_id=p.plane_number
            WHERE f.scheduled_time > '$dateNow' AND f.validation IN ('ACCEPTED','CONFIRMED') AND f.modify_id IS NULL AND p.company_id=".$user->getCompany()." ORDER BY f.scheduled_time";
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["id"].">".$row["plane"].": ".$row["dAirport"]." -> ".$row["aAirport"]." - ".$row["time"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select></form><div id='container2'></div>";
        echo $s;

    }else if($_GET["type"]=="MODIFY2"&&isset($_GET["id"])){

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            echo "Could not connect. ".$e->getMessage();
        }
        try {
            $query=$conn->prepare("SELECT * FROM flights WHERE id=:id");
            $query->bindParam(':id',$_GET["id"]);
            $query->execute();
            $flight=$query->fetch();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s="<form action='AirlinePage.php' method='POST'>".
        "<input type='hidden' id='id' name='id' value='".$_GET["id"]."'>".
        "<label for='datetime'>Time: </label>".
        "<input type='datetime-local' id='datetime' name='datetime' value='".$flight["scheduled_time"]."' required><br>".
        "<label for='plane'>Plane: </label>".
        "<select id='plane' name='plane' required>".
        "<option value=''></option>";
        try {
            $query="SELECT * FROM planes WHERE company_id=".$user->getCompany();
            $result = $conn->query($query);
            while($row = $result->fetch())
                if($row["plane_number"]==$flight["plane_id"])
                    $s=$s."<option value=".$row["plane_number"]." selected='selected'>".$row["plane_number"]." - ".$row["model"]."</option><br>";
                else
                    $s=$s."<option value=".$row["plane_number"].">".$row["plane_number"]." - ".$row["model"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<label for='pilot'>Pilot: </label>".
        "<select id='pilot' name='pilot' required>".
        "<option value=''></option>";
        try {
            $query="SELECT * FROM users WHERE role_id=2 AND company_id=".$user->getCompany();
            $result = $conn->query($query);
            while($row = $result->fetch())
                if($row["id"]==$flight["pilot_id"])
                    $s=$s."<option value=".$row["id"]." selected='selected'>".$row["name"]." ".$row["surname"]." - ".$row["email"]."</option><br>";
                else
                    $s=$s."<option value=".$row["id"].">".$row["name"]." ".$row["surname"]." - ".$row["email"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<label for='dAirport'>Departure Airport: </label>".
        "<select id='dAirport' name='dAirport' required>".
        "<option value=''></option>";
        try {
            $query="SELECT * FROM airports";
            $result = $conn->query($query);
            while($row = $result->fetch())
                if($row["id"]==$flight["departure_airport_id"])
                    $s=$s."<option value=".$row["id"]." selected='selected'>".$row["code"]." - ".$row["city"].", ".$row["nation"]."</option><br>";
                else
                    $s=$s."<option value=".$row["id"].">".$row["code"]." - ".$row["city"].", ".$row["nation"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<label for='aAirport'>Arrival Airport: </label>".
        "<select id='aAirport' name='aAirport' required>".
        "<option value=''></option>";
        try {
            $query="SELECT * FROM airports";
            $result = $conn->query($query);
            while($row = $result->fetch())
                if($row["id"]==$flight["arrival_airport_id"])
                    $s=$s."<option value=".$row["id"]." selected='selected'>".$row["code"]." - ".$row["city"].", ".$row["nation"]."</option><br>";
                else
                    $s=$s."<option value=".$row["id"].">".$row["code"]." - ".$row["city"].", ".$row["nation"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<label for='status'>Status: </label>".
        "<select id='status' name='status' required>".
        "<option value=''></option>";
        try {
            $query="SELECT * FROM flight_status";
            $result = $conn->query($query);
            while($row = $result->fetch())
                if($row["id"]==$flight["status_id"])
                   $s=$s."<option value=".$row["id"]." selected='selected'>".$row["status"]."</option><br>";
                else
                    $s=$s."<option value=".$row["id"].">".$row["status"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<input type='submit' value='Modify'></form>";
        echo $s;

    }else if($_GET["type"]=="DELETE"){

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            die("Could not connect. ".$e->getMessage());
        }
        $s="<form action='AirlinePage.php' method='POST'>".
        "<select id='flight' name='flight' required>".
        "<option value=''></option>";
        try {
            $dateNow=date('Y-m-d H:i:s');
            $query="SELECT f.id AS id, f.plane_id AS plane, d.code AS dAirport, a.code AS aAirport, f.scheduled_time AS time
            FROM flights AS f INNER JOIN airports AS d ON f.departure_airport_id=d.id
            INNER JOIN airports AS a ON f.arrival_airport_id=a.id
            INNER JOIN planes AS p ON f.plane_id=p.plane_number
            WHERE f.scheduled_time > '$dateNow' AND f.validation IN ('ACCEPTED','CONFIRMED') AND f.modify_id IS NULL AND p.company_id=".$user->getCompany()." ORDER BY f.scheduled_time";
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["id"].">".$row["plane"].": ".$row["dAirport"]." -> ".$row["aAirport"]." - ".$row["time"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br><input type='submit' value='Delete'></form>";
        echo $s;
    }
}
?>
