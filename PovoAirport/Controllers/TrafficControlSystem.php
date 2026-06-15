<?php

class TrafficControlSystem
{
    //Fetch all flights waiting in the take-off queue (status_id=3)
    public function getTakeOffQueue(): array{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->prepare("SELECT f.id, f.priority, f.scheduled_time, f.plane_id, p.model,
                u.name AS pilot_name, u.surname AS pilot_surname,
                da.code AS dep_code, da.city AS dep_city,
                aa.code AS arr_code, aa.city AS arr_city
                FROM flights f
                INNER JOIN planes p ON f.plane_id = p.plane_number
                INNER JOIN users u ON f.pilot_id = u.id
                INNER JOIN airports da ON f.departure_airport_id = da.id
                INNER JOIN airports aa ON f.arrival_airport_id = aa.id
                WHERE f.status_id IN (2, 3) AND f.validation IN ('CONFIRMED','ACCEPTED')
                AND NOT EXISTS (SELECT 1 FROM runways WHERE flight_id = f.id)
                ORDER BY f.priority ASC, f.scheduled_time ASC");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    //Fetch all flights waiting in the landing queue (status_id=4)
    public function getLandingQueue(): array{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->prepare("SELECT f.id, f.priority, f.scheduled_time, f.plane_id, p.model,
                u.name AS pilot_name, u.surname AS pilot_surname,
                da.code AS dep_code, da.city AS dep_city,
                aa.code AS arr_code, aa.city AS arr_city
                FROM flights f
                INNER JOIN planes p ON f.plane_id = p.plane_number
                INNER JOIN users u ON f.pilot_id = u.id
                INNER JOIN airports da ON f.departure_airport_id = da.id
                INNER JOIN airports aa ON f.arrival_airport_id = aa.id
                WHERE f.status_id = 4 AND f.validation IN ('CONFIRMED','ACCEPTED')
                ORDER BY f.priority ASC, f.scheduled_time ASC");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    //Return runways that are not currently assigned to any flight
    public function getAvailableRunways(): array{
        //Import required file
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

    //Assign a free runway to a flight waiting for take-off and clean up the taxiway and parking spot
    public function assignRunwayForTakeOff(int $flightId, int $runwayId): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            //Check if this flight already has a runway assigned
            $check = $conn->prepare("SELECT COUNT(*) FROM runways WHERE flight_id = :flightId");
            $check->bindParam(':flightId', $flightId);
            $check->execute();
            if($check->fetchColumn() > 0){
                $conn->rollBack();
                return "Flight already has a runway assigned";
            }
            //Get the plane number for this flight
            $query = $conn->prepare("SELECT plane_id FROM flights WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $plane = $query->fetch(PDO::FETCH_ASSOC);
            $planeId = $plane ? $plane["plane_id"] : null;
            $query = $conn->prepare("UPDATE runways SET flight_id = :flightId WHERE id = :runwayId AND flight_id IS NULL");
            $query->bindParam(':flightId', $flightId);
            $query->bindParam(':runwayId', $runwayId);
            $query->execute();
            //Check if the runway was assigned successfully
            if($query->rowCount() == 0){
                $conn->rollBack();
                return "The runway is not available";
            }
            //Clear the taxiway and parking spot assignments for this flight
            $query = $conn->prepare("DELETE FROM taxiway_flight WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Clear the parking spot for this plane
            if($planeId){
                $query = $conn->prepare("UPDATE parking_spots SET plane_id = NULL WHERE plane_id = :planeId");
                $query->bindParam(':planeId', $planeId);
                $query->execute();
            }
            //Clear the gate assignment for this flight
            $query = $conn->prepare("UPDATE gates SET flight_id = NULL WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Set the flight status to InQueueTakeOff
            $query = $conn->prepare("UPDATE flights SET status_id = 3 WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $conn->commit();
            return "Runway assigned for take off successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    //Assign a free runway to a flight that is ready to land
    public function assignRunwayForLanding(int $flightId, int $runwayId): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            //Check if this flight already has a runway assigned
            $check = $conn->prepare("SELECT COUNT(*) FROM runways WHERE flight_id = :flightId");
            $check->bindParam(':flightId', $flightId);
            $check->execute();
            if($check->fetchColumn() > 0){
                $conn->rollBack();
                return "Flight already has a runway assigned";
            }
            $query = $conn->prepare("UPDATE runways SET flight_id = :flightId WHERE id = :runwayId AND flight_id IS NULL");
            $query->bindParam(':flightId', $flightId);
            $query->bindParam(':runwayId', $runwayId);
            $query->execute();
            //Check if the runway was assigned successfully
            if($query->rowCount() == 0){
                $conn->rollBack();
                return "The runway is not available";
            }
            $conn->commit();
            return "Runway assigned for landing successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    //Confirm a take-off: release the runway and mark the flight as finished
    public function confirmTakeOff(int $flightId): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            //Release the runway assigned to this flight
            $query = $conn->prepare("UPDATE runways SET flight_id = NULL WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Mark the flight as finished and keep the validation as CONFIRMED
            $query = $conn->prepare("UPDATE flights SET priority = NULL, status_id = 7, validation = 'CONFIRMED' WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $conn->commit();
            return "Take off confirmed successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    //Confirm a landing: release the runway and mark the flight as finished
    public function confirmLanding(int $flightId): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            //Release the runway assigned to this flight
            $query = $conn->prepare("UPDATE runways SET flight_id = NULL WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Mark the flight as landed and keep the validation as CONFIRMED
            $query = $conn->prepare("UPDATE flights SET priority = NULL, status_id = 5 WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $conn->commit();
            return "Landing confirmed successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    //Return all taxiways for the pilot to choose after landing
    public function getAvailableTaxiways(): array{
        //Import required file
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

    //Assign a taxiway to a flight after landing
    public function assignTaxiwayAfterLanding(int $flightId, int $taxiwayId): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            //Clear any existing taxiway assignment for this flight
            $query = $conn->prepare("DELETE FROM taxiway_flight WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Assign the new taxiway
            $query = $conn->prepare("INSERT INTO taxiway_flight (flight_id, taxiway_id) VALUES (:flightId, :taxiwayId)");
            $query->bindParam(':flightId', $flightId);
            $query->bindParam(':taxiwayId', $taxiwayId);
            $query->execute();
            $conn->commit();
            return "Taxiway assigned successfully. The ground crew will guide you to the parking spot.";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    //Fetch flights that have not been accepted or rejected yet by the TC
    public function getPendingFlights(): array{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->prepare("SELECT f.id, f.scheduled_time, f.plane_id, p.model,
                u.name AS pilot_name, u.surname AS pilot_surname,
                da.code AS dep_code, da.city AS dep_city,
                aa.code AS arr_code, aa.city AS arr_city
                FROM flights f
                INNER JOIN planes p ON f.plane_id = p.plane_number
                INNER JOIN users u ON f.pilot_id = u.id
                INNER JOIN airports da ON f.departure_airport_id = da.id
                INNER JOIN airports aa ON f.arrival_airport_id = aa.id
                WHERE f.validation = 'NOT_ACCEPTED'
                ORDER BY f.scheduled_time ASC");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    //Approve a pending flight by setting its validation to ACCEPTED
    public function confirmFlight(int $flightId): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $query = $conn->prepare("UPDATE flights SET validation = 'ACCEPTED' WHERE id = :flightId AND validation = 'NOT_ACCEPTED'");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Check if any row was actually updated
            if($query->rowCount() == 0){
                return "Flight not found";
            }
            return "Flight approved successfully";
        } catch(PDOException $e){
            return "Query Error. ".$e->getMessage();
        }
    }

    //Reject a pending flight by setting its validation to REJECTED
    public function rejectFlight(int $flightId): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $query = $conn->prepare("UPDATE flights SET validation = 'REJECTED' WHERE id = :flightId AND validation = 'NOT_ACCEPTED'");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            //Check if any row was actually updated
            if($query->rowCount() == 0){
                return "Flight not found";
            }
            return "Flight rejected successfully";
        } catch(PDOException $e){
            return "Query Error. ".$e->getMessage();
        }
    }

    //Change the priority of a flight in the take-off or landing queue
    public function updatePriority(int $flightId, int $priority): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        //Validate that priority is at least 1
        if($priority < 1){
            return "Priority must be at least 1";
        }
        try {
            $query = $conn->prepare("UPDATE flights SET priority = :priority WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->bindParam(':priority', $priority);
            $query->execute();
            return "Priority updated successfully";
        } catch(PDOException $e){
            return "Query Error. ".$e->getMessage();
        }
    }
}
