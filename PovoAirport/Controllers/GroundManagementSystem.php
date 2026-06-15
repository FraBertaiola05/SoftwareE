<?php

class GroundManagementSystem
{
    public function getPlanesOnGround(): array{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->query("SELECT p.plane_number, p.model, fs.status AS plane_status,
                f.id AS flight_id, f.scheduled_time,
                s.spot_number AS parking_spot, r.runway_number, g.gate_number, t.taxiway_number
                FROM planes p
                INNER JOIN flights f ON p.plane_number = f.plane_id AND f.validation IN ('ACCEPTED','CONFIRMED') AND ((f.departure_airport_id = 1 AND f.status_id NOT IN (6, 7)) OR (f.arrival_airport_id = 1 AND f.status_id = 5))
                INNER JOIN flight_status fs ON f.status_id = fs.id
                LEFT JOIN parking_spots s ON p.plane_number = s.plane_id
                LEFT JOIN runways r ON f.id = r.flight_id
                LEFT JOIN gates g ON f.id = g.flight_id
                LEFT JOIN taxiway_flight tf ON f.id = tf.flight_id
                LEFT JOIN taxiways t ON tf.taxiway_id = t.id
                ORDER BY p.plane_number");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    public function getAvailableParkingSpots(): array{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->query("SELECT * FROM parking_spots WHERE plane_id IS NULL");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    public function getAvailableRunways(): array{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->query("SELECT * FROM runways WHERE flight_id IS NULL");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    public function getAvailableTaxiways(): array{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->query("SELECT * FROM taxiways");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    public function movePlaneToParking(int $flightId, int $spotId): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            //Get the plane number for this flight
            $query = $conn->prepare("SELECT plane_id FROM flights WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $plane = $query->fetch(PDO::FETCH_ASSOC);
            if(!$plane){
                $conn->rollBack();
                return "Flight not found";
            }
            $planeId = $plane["plane_id"];
            //Assign the parking spot to the plane
            $query = $conn->prepare("UPDATE parking_spots SET plane_id = :planeId WHERE id = :spotId AND plane_id IS NULL");
            $query->bindParam(':planeId', $planeId);
            $query->bindParam(':spotId', $spotId);
            $query->execute();
            if($query->rowCount() == 0){
                $conn->rollBack();
                return "The parking spot is not available";
            }
            $query = $conn->prepare("DELETE FROM taxiway_flight WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Clear any other parking spot assigned to this plane
            $query = $conn->prepare("UPDATE parking_spots SET plane_id = NULL WHERE plane_id = :planeId AND id != :spotId");
            $query->bindParam(':planeId', $planeId);
            $query->bindParam(':spotId', $spotId);
            $query->execute();
            //Mark the flight as finished once it reaches the parking spot
            $query = $conn->prepare("UPDATE flights SET status_id = 7 WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $conn->commit();
            return "Plane moved to parking spot successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    public function movePlaneToTaxiway(int $flightId, int $taxiwayId): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            //Get the plane number for this flight
            $query = $conn->prepare("SELECT plane_id FROM flights WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $plane = $query->fetch(PDO::FETCH_ASSOC);
            $planeId = $plane ? $plane["plane_id"] : null;
            $query = $conn->prepare("DELETE FROM taxiway_flight WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Clear the parking spot for this plane
            if($planeId){
                $query = $conn->prepare("UPDATE parking_spots SET plane_id = NULL WHERE plane_id = :planeId");
                $query->bindParam(':planeId', $planeId);
                $query->execute();
            }
            $query = $conn->prepare("UPDATE runways SET flight_id = NULL WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Set the flight status to InQueueTakeOff so it appears in the TC queue
            $query = $conn->prepare("UPDATE flights SET status_id = 3 WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Assign the taxiway
            $query = $conn->prepare("INSERT INTO taxiway_flight (flight_id, taxiway_id) VALUES (:flightId, :taxiwayId)");
            $query->bindParam(':flightId', $flightId);
            $query->bindParam(':taxiwayId', $taxiwayId);
            $query->execute();
            $conn->commit();
            return "Plane moved to taxiway successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    //Get flights that are elegible to be assigned to a gates
    public function getFlightsForGates(): array{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->query("SELECT f.id AS id, f.scheduled_time AS time, da.code AS dAirport, aa.code AS aAirport, p.plane_number AS plane
            FROM flights AS f INNER JOIN planes AS p ON f.plane_id=p.plane_number
            INNER JOIN parking_spots AS ps ON f.plane_id=ps.plane_id
            INNER JOIN airports AS da ON da.id=f.departure_airport_id
            INNER JOIN airports AS aa ON aa.id=f.arrival_airport_id
            WHERE f.validation IN ('CONFIRMED','ACCEPTED') AND f.status_id=1 AND (SELECT COUNT(*) FROM gates WHERE f.id=flight_id)=0 AND da.id=1
            ORDER BY f.scheduled_time");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    //Get free gates
    public function getAvailableGates(): array{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->query("SELECT * FROM gates WHERE flight_id IS NULL");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    //Assign a free gate to one flight that is elegible to be assigned to a gate
    public static function updateGate(int $flight, int $gate): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        //Check if inserted data is not null and of the right type
        if(!is_null($flight)&&is_numeric($flight)&&!is_null($gate)&&is_numeric($gate)){
            try {
                $query=$conn->prepare("UPDATE gates SET flight_id=:flight WHERE id=:gate");
                $query->bindParam(':flight',$flight);
                $query->bindParam(':gate',$gate);
                $query->execute();
                return "The gate was assigned with success";
            } catch(PDOException $e){
                return "Query Error. ".$e->getMessage();
            }
        }else{
            return "The inserted data is wrong";
        }
    }
}
