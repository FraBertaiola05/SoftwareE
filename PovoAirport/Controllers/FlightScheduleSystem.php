<?php

/**
 * @brief Manages flight scheduling operations.
 *
 * This class provides methods for creating, modifying, deleting,
 * validating, and retrieving flight information used by the airport system.
 */
class FlightScheduleSystem
{
    /**
     * @brief Requests the creation of a new flight.
     *
     * Creates a new flight entry that must be reviewed and accepted
     * by a Tower Controller before becoming active.
     *
     * @param datetime Scheduled date and time of the flight.
     * @param plane id of the assigned aircraft.
     * @param pilot id of the assigned pilot.
     * @param dAirport Departure airport id.
     * @param aAirport Arrival airport id.
     * @param status Initial flight status id.
     *
     * @return string Result message describing the outcome of the request.
     */
    public static function requestAddFlight(string $datetime, string $plane, int $pilot, int $dAirport, int $aAirport, int $status): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }

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

    /**
     * @brief Requests modifications to an existing flight.
     *
     * Updates the selected flight and marks the changes for review
     * by a Tower Controller before they become effective.
     *
     * @param datetime Updated scheduled date and time.
     * @param plane Updated aircraft id.
     * @param pilot Updated pilot id.
     * @param dAirport Updated departure airport id.
     * @param aAirport Updated arrival airport id.
     * @param status Updated flight status id.
     * @param id id of the flight to modify.
     *
     * @return string Result message describing the outcome of the request.
     */
    public static function requestModifyFlight(string $datetime, string $plane, int $pilot, int $dAirport, int $aAirport, int $status, int $id): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }

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

    /**
     * @brief Deletes a flight from the schedule.
     *
     * Removes any gate, runway, and taxiway associations before
     * marking the flight as deleted.
     *
     * @param id id of the flight to delete.
     *
     * @return string Result message describing the outcome of the operation.
     */
    public static function deleteFlight(int $id): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        if(!is_null($id)){
            try {
                $query=$conn->prepare("UPDATE gates SET flight_id=NULL WHERE flight_id=:id");
                $query->bindParam(':id',$id);
                $query->execute();
                $query=$conn->prepare("UPDATE runways SET flight_id=NULL WHERE flight_id=:id");
                $query->bindParam(':id',$id);
                $query->execute();
                $query=$conn->prepare("DELETE FROM taxiway_flight WHERE flight_id=:id");
                $query->bindParam(':id',$id);
                $query->execute();
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

    /**
     * @brief Confirms a flight that has been accepted.
     *
     * Changes the validation state from ACCEPTED to CONFIRMED.
     *
     * @param id id of the accepted flight.
     *
     * @return string Empty string on success or an error message on failure.
     */
    public static function updateAccepted(int $id): string{
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

    /**
     * @brief Removes a rejected flight request.
     *
     * Deletes a flight entry that was rejected during the validation process.
     *
     * @param id id of the rejected flight.
     *
     * @return string Empty string on success or an error message on failure.
     */
    public static function deleteRejected(int $id): string{
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

    /**
     * @brief Generates a flight history table.
     *
     * Retrieves arrival or departure information within the specified
     * time range and returns it as an HTML table.
     *
     * @param b When true, returns departures. When false, returns arrivals.
     * @param t1 Start date and time of the search interval.
     * @param t2 End date and time of the search interval.
     *
     * @return string HTML table containing the requested flight history.
     */
    public static function getFlightHistoryTable($b=true, string $t1="", string $t2="9999-12-31 23:59:59.999"): string{
        $s="";
        if($t1==""){
            $temp=new DateTime();
            $t1=date_format($temp, 'd/m/Y H:i:s');
        }
        try {
            require 'DatabaseInfo.php';
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e){
                return "Could not connect. ".$e->getMessage();
            }

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
            }else{
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

