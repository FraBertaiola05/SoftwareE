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
            $query = $conn->query("SELECT p.plane_number, p.model, ps.status AS plane_status,
                f.id AS flight_id, f.scheduled_time,
                s.spot_number AS parking_spot, r.runway_number, g.gate_number, t.taxiway_number
                FROM planes p
                INNER JOIN plane_status ps ON p.status_id = ps.id
                LEFT JOIN flights f ON p.plane_number = f.plane_id AND f.validation IN ('ACCEPTED','CONFIRMED')
                LEFT JOIN parking_spots s ON f.id = s.flight_id
                LEFT JOIN runways r ON f.id = r.flight_id
                LEFT JOIN gates g ON f.id = g.flight_id
                LEFT JOIN taxiway_flight tf ON f.id = tf.flight_id
                LEFT JOIN taxiways t ON tf.taxiway_id = t.id
                WHERE p.status_id IN (1, 2)
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
            $query = $conn->query("SELECT * FROM parking_spots WHERE flight_id IS NULL");
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
            $query = $conn->prepare("UPDATE parking_spots SET flight_id = :flightId WHERE id = :spotId AND flight_id IS NULL");
            $query->bindParam(':flightId', $flightId);
            $query->bindParam(':spotId', $spotId);
            $query->execute();
            if($query->rowCount() == 0){
                $conn->rollBack();
                return "The parking spot is not available";
            }
            $query = $conn->prepare("DELETE FROM taxiway_flight WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $query = $conn->prepare("UPDATE parking_spots SET flight_id = NULL WHERE flight_id = :flightId AND id != :spotId");
            $query->bindParam(':flightId', $flightId);
            $query->bindParam(':spotId', $spotId);
            $query->execute();
            $query = $conn->prepare("SELECT plane_id FROM flights WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $flight = $query->fetch(PDO::FETCH_ASSOC);
            if($flight){
                $query = $conn->prepare("UPDATE planes SET status_id = 1 WHERE plane_number = :planeId");
                $query->bindParam(':planeId', $flight['plane_id']);
                $query->execute();
            }
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
            $query = $conn->prepare("DELETE FROM taxiway_flight WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $query = $conn->prepare("UPDATE parking_spots SET flight_id = NULL WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $query = $conn->prepare("UPDATE runways SET flight_id = NULL WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $query = $conn->prepare("INSERT INTO taxiway_flight (flight_id, taxiway_id) VALUES (:flightId, :taxiwayId)");
            $query->bindParam(':flightId', $flightId);
            $query->bindParam(':taxiwayId', $taxiwayId);
            $query->execute();
            $query = $conn->prepare("SELECT plane_id FROM flights WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $flight = $query->fetch(PDO::FETCH_ASSOC);
            if($flight){
                $query = $conn->prepare("UPDATE planes SET status_id = 2 WHERE plane_number = :planeId");
                $query->bindParam(':planeId', $flight['plane_id']);
                $query->execute();
            }
            $conn->commit();
            return "Plane moved to taxiway successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    public function movePlaneToRunway(int $flightId, int $runwayId): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            $query = $conn->prepare("UPDATE runways SET flight_id = :flightId WHERE id = :runwayId AND flight_id IS NULL");
            $query->bindParam(':flightId', $flightId);
            $query->bindParam(':runwayId', $runwayId);
            $query->execute();
            if($query->rowCount() == 0){
                $conn->rollBack();
                return "The runway is not available";
            }
            $query = $conn->prepare("UPDATE runways SET flight_id = NULL WHERE flight_id = :flightId AND id != :runwayId");
            $query->bindParam(':flightId', $flightId);
            $query->bindParam(':runwayId', $runwayId);
            $query->execute();
            $query = $conn->prepare("UPDATE parking_spots SET flight_id = NULL WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $query = $conn->prepare("DELETE FROM taxiway_flight WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $query = $conn->prepare("SELECT plane_id FROM flights WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $flight = $query->fetch(PDO::FETCH_ASSOC);
            if($flight){
                $query = $conn->prepare("UPDATE planes SET status_id = 3 WHERE plane_number = :planeId");
                $query->bindParam(':planeId', $flight['plane_id']);
                $query->execute();
            }
            $conn->commit();
            return "Plane moved to runway successfully";
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
            INNER JOIN parking_spots AS ps ON f.id=ps.flight_id
            INNER JOIN airports AS da ON da.id=f.departure_airport_id
            INNER JOIN airports AS aa ON aa.id=f.arrival_airport_id
            WHERE f.validation IN ('CONFIRMED','ACCEPTED') AND f.status_id=1 AND (SELECT COUNT(*) FROM gates WHERE f.id=flight_id)=0 AND (SELECT COUNT(*) FROM gates INNER JOIN flights ON gates.flight_id=flights.id WHERE flights.plane_id=p.plane_number)=0 AND da.id=1
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
