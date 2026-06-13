<?php
class FlightScheduleSystem
{
    //Given new filght data, create a new flight that will became definitive after the Tower Controller accept the flight
    public static function requestAddFlight(string $datetime, string $plane, int $pilot, int $dAirport, int $aAirport, int $status): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        //Check if the inserted data is not null and correct
        if(!is_null($datetime)&&$datetime!=""&&strtotime($datetime)&&strtotime($datetime)>time()&&!is_null($plane)&&$plane!=""&&!is_null($pilot)&&!is_null($dAirport)&&!is_null($aAirport)&&!is_null($status)&&($dAirport==1||$aAirport==1)&&$dAirport!=$aAirport){
            try {
                $query=$conn->prepare("INSERT INTO flights (scheduled_time, plane_id, pilot_id, departure_airport_id, arrival_airport_id, status_id) VALUES(:datetime,:plane,:pilot,:dAirport,:aAirport,:status)");
                $query->bindParam(':datetime',$datetime);
                $query->bindParam(':plane',$plane);
                $query->bindParam(':pilot',$pilot);
                $query->bindParam(':dAirport',$dAirport);
                $query->bindParam(':aAirport',$aAirport);
                $query->bindParam(':status',$status);
                $query->execute();
                return "The flight was created with success. Check the notifications to see if the flight was accepted by the Tower Controller";
            } catch(PDOException $e){
                return "Query Error. ".$e->getMessage();
            }
        }else{
            return "The inserted data is wrong";
        }
    }

    //Given modified filght data, create a new flight entry that modifies another one when the Tower Controller accept the changes
    public static function requestModifyFlight(string $datetime, string $plane, int $pilot, int $dAirport, int $aAirport, int $status, int $id): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        //Check if the inserted data is not null and correct
        if(!is_null($datetime)&&$datetime!=""&&strtotime($datetime)&&strtotime($datetime)>time()&&!is_null($plane)&&$plane!=""&&!is_null($pilot)&&!is_null($dAirport)&&!is_null($aAirport)&&!is_null($status)&&($dAirport==1||$aAirport==1)&&$dAirport!=$aAirport){
            try {
                $query=$conn->prepare("UPDATE flights SET scheduled_time=:datetime, plane_id=:plane, pilot_id=:pilot, departure_airport_id=:dAirport, arrival_airport_id=:aAirport, status_id=:status, validation='NOT_ACCEPTED' WHERE id=:id");
                $query->bindParam(':datetime',$datetime);
                $query->bindParam(':plane',$plane);
                $query->bindParam(':pilot',$pilot);
                $query->bindParam(':dAirport',$dAirport);
                $query->bindParam(':aAirport',$aAirport);
                $query->bindParam(':status',$status);
                $query->bindParam(':id',$id);
                $query->execute();
                return "The flight was modified with success. The Tower Controller will review the changes";
            } catch(PDOException $e){
                return "Query Error. ".$e->getMessage();
            }
        }else{
            return "The inserted data is wrong";
        }
    }

    //Set a flight validation and status to "deleted"
    public static function deleteFlight(int $id): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        if(!is_null($id)){
            try {
                $query=$conn->prepare("UPDATE flights SET validation='DELETED', status_id=6 WHERE id=:id");
                $query->bindParam(':id',$id);
                $query->execute();
                return "The flight was deleted with success";
            } catch(PDOException $e){
                return "Query Error. ".$e->getMessage();
            }
        }else{
            return "The inserted data is wrong";
        }
    }

    //Update a flight entry that was accepted by the Tower Controller
    public static function updateAccepted(int $id): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        if(!is_null($id)){
            try {
                $query=$conn->prepare("UPDATE flights SET validation='CONFIRMED' WHERE id=:id AND validation='ACCEPTED'");
                $query->bindParam(':id',$id);
                $query->execute();
                return "";
            } catch(PDOException $e){
                return "Query Error. ".$e->getMessage();
            }
        }else{
            return "The inserted data is wrong";
        }
    }

    //Delete a flight entry that was rejected by the Tower Controller
    public static function deleteRejected(int $id): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        if(!is_null($id)){
            try {
                $query=$conn->prepare("DELETE FROM flights WHERE validation='REJECTED' AND id=:id");
                $query->bindParam(':id',$id);
                $query->execute();
                return "";
            } catch(PDOException $e){
                return "Query Error. ".$e->getMessage();
            }
        }else{
            return "The inserted data is wrong";
        }
    }
    
    //Get a table with the flight history. If $b=true, return the departures, while if $b=false, return the arrivals
    //The function also take a starting and a finishing date as input. By default it goes from the moment you call the function onwards
    public static function getFlightHistoryTable($b=true, string $t1="", string $t2="9999-12-31 23:59:59.999"): string{
        $s="";
        if($t1==""){
            $temp=new DateTime();
            $t1=date_format($temp, 'd/m/Y H:i:s');
        }
        try {
            //Import required file
            require 'DatabaseInfo.php';
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e){
                return "Could not connect. ".$e->getMessage();
            }
            //Fetch the departures and creates the table
            if($b){
                $query="SELECT f.plane_id AS 'plane', b.nation AS 'nation', b.city AS 'city', b.name AS 'airportName', f.scheduled_time AS 'depTime', gates.gate_number AS 'gateNumber', fs.status AS 'flightStatus'
                FROM flights AS f INNER JOIN airports AS a ON a.id=f.departure_airport_id
                INNER JOIN airports AS b ON b.id=f.arrival_airport_id
                INNER JOIN flight_status AS fs ON f.status_id=fs.id
                LEFT JOIN gates ON f.id=gates.flight_id
                WHERE f.scheduled_time BETWEEN :t1 AND :t2 AND a.code='POV' AND f.validation IN ('ACCEPTED','CONFIRMED')
                ORDER BY f.scheduled_time";
                $s="<table border=1>
                    <tr>
                        <th>Plane</th>
                        <th>Goes To</th>
                        <th>Airport</th>
                        <th>Departure Time</th>
                        <th>Gate</th>
                        <th>Status</th>
                    </tr>";
            }else{ //Fetch the arrivals and creates the table
                $query="SELECT f.plane_id AS 'plane', a.nation AS 'nation', a.city AS 'city', a.name AS 'airportName', f.scheduled_time AS 'depTime', gates.gate_number AS 'gateNumber', fs.status AS 'flightStatus'
                FROM flights AS f INNER JOIN airports AS a ON a.id=f.departure_airport_id
                INNER JOIN airports AS b ON b.id=f.arrival_airport_id
                INNER JOIN flight_status AS fs ON f.status_id=fs.id
                LEFT JOIN gates ON f.id=gates.flight_id
                WHERE f.scheduled_time BETWEEN :t1 AND :t2 AND b.code='POV' AND f.validation IN ('ACCEPTED','CONFIRMED')
                ORDER BY f.scheduled_time";
                $s="<table border=1>
                        <tr>
                            <th>Plane</th>
                            <th>From</th>
                            <th>Airport</th>
                            <th>Arrival Time</th>
                            <th>Status</th>
                        </tr>";
            }
            $result = $conn->prepare($query);
            $result->bindParam(':t1',$t1);
            $result->bindParam(':t2',$t2);
            $result->execute();
            //Insert the fetched data inside the table
            while($row = $result->fetch()) {
                $s=$s."<tr>";
                $s=$s."<td>" . $row['plane'] . "</td>";
                $s=$s."<td>" . $row['city'] . ", " . $row['nation'] . "</td>";
                $s=$s."<td>" . $row['airportName'] . "</td>";
                $s=$s."<td>" . $row['depTime'] . "</td>";
                if($b){
                    if($row["gateNumber"]!=NULL)
                        $s=$s."<td>" . $row['gateNumber'] . "</td>";
                    else
                        $s=$s."<td> - </td>";
                }
                $s=$s."<td>" . $row['flightStatus'] . "</td>";
                $s=$s."</tr>";
            }
            $s=$s."</table>";
            return $s;
        } catch(PDOException $e) {
            return "Error: " . $e->getMessage();
        }

    }
}
?>
