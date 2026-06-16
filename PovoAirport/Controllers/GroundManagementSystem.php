<?php

class GroundManagementSystem
{
    /**
     * @brief Gets all planes currently on the ground.
     *
     * Fetches planes involved in active flights, along with their
     * current status and assigned infrastructure (spot, runway, gate, taxiway).
     *
     * @return array List of planes with flight and ground assignment details,
     *               or an empty array on failure.
     */
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

    /**
     * @brief Returns all parking spots that are currently free.
     *
     * Queries the database for spots with no plane assigned.
     *
     * @return array List of available parking spots, or empty array on failure.
     */
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

    /**
     * @brief Returns all runways that are currently free.
     *
     * Queries the database for runways with no flight assigned.
     *
     * @return array List of available runways, or empty array on failure.
     */
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

    /**
     * @brief Returns all taxiways.
     *
     * Fetches the full list of taxiways from the database.
     *
     * @return array List of taxiways, or empty array on failure.
     */
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

    /**
     * @brief Moves a plane to a parking spot after landing.
     *
     * Assigns the given spot to the plane, clears any previous spot or taxiway
     * assignments, and marks the flight as finished.
     *
     * @param flightId ID of the flight whose plane needs to be parked.
     * @param spotId ID of the parking spot to assign.
     *
     * @return string Success message or error description.
     */
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

    /**
     * @brief Moves a plane to a taxiway to prepare for departure.
     *
     * Clears existing taxiway, parking, and runway assignments for the flight,
     * sets the status to Boarding so it shows up in the TC queue,
     * then assigns the requested taxiway.
     *
     * @param flightId ID of the flight to move.
     * @param taxiwayId ID of the taxiway to assign.
     *
     * @return string Success message or error description.
     */
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
            //Set the flight status to Boarding so it appears in the TC queue
            $query = $conn->prepare("UPDATE flights SET status_id = 2 WHERE id = :flightId");
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

    /**
     * @brief Gets flights that are ready to be assigned a gate.
     *
     * Only returns departing flights that are parked, confirmed/accepted,
     * not yet assigned a gate, and scheduled from this airport.
     *
     * @return array List of eligible flights with basic info, or empty array on failure.
     */
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

    /**
     * @brief Returns all gates that have no flight assigned.
     *
     * Simple query to find free gates available for assignment.
     *
     * @return array List of free gates, or empty array on failure.
     */
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

    /**
     * @brief Assigns a gate to a flight.
     *
     * Updates the gate record with the given flight ID.
     * Both parameters must be valid numeric values.
     *
     * @param flight ID of the flight to assign.
     * @param gate ID of the gate to update.
     *
     * @return string Success message or error description.
     */
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