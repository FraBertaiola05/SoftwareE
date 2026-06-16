<?php

class TrafficControlSystem
{
    /**
     * @brief Gets all flights currently waiting for take-off clearance.
     *
     * Returns flights with status Boarding or InQueueTakeOff that are
     * confirmed/accepted and don't have a runway assigned yet.
     * Ordered by priority first, then scheduled time.
     *
     * @return array List of queued flights with pilot and route info,
     *               or empty array on failure.
     */
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

    /**
     * @brief Gets all flights currently waiting to land.
     *
     * Returns confirmed/accepted flights with status InQueueLanding,
     * ordered by priority and then scheduled time.
     *
     * @return array List of incoming flights with pilot and route info,
     *               or empty array on failure.
     */
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

    /**
     * @brief Returns all runways that have no flight assigned.
     *
     * Used to populate the runway selection when clearing a flight for
     * take-off or landing.
     *
     * @return array List of free runways, or empty array on failure.
     */
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

    /**
     * @brief Assigns a runway to a flight and clears it for take-off.
     *
     * Checks the flight doesn't already have a runway, assigns the chosen one,
     * removes taxiway/parking/gate assignments, and sets the status to InQueueTakeOff.
     *
     * @param flightId ID of the flight being cleared for take-off.
     * @param runwayId ID of the runway to assign.
     *
     * @return string Success message or error description.
     */
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

    /**
     * @brief Assigns a runway to an incoming flight for landing.
     *
     * Verifies the flight doesn't already have a runway, then assigns
     * the requested one if it's free.
     *
     * @param flightId ID of the flight being cleared to land.
     * @param runwayId ID of the runway to assign.
     *
     * @return string Success message or error description.
     */
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

    /**
     * @brief Confirms that a flight has taken off.
     *
     * Releases the runway and marks the flight as finished,
     * keeping the validation status as CONFIRMED.
     *
     * @param flightId ID of the flight that has taken off.
     *
     * @return string Success message or error description.
     */
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

    /**
     * @brief Confirms that a flight has landed.
     *
     * Releases the runway and sets the flight status to Landed.
     * Priority is cleared since it's no longer needed.
     *
     * @param flightId ID of the flight that has landed.
     *
     * @return string Success message or error description.
     */
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

    /**
     * @brief Returns all taxiways available for post-landing routing.
     *
     * Fetches the full taxiway list so the TC can pick one to guide
     * the plane from the runway toward parking.
     *
     * @return array List of taxiways, or empty array on failure.
     */
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

    /**
     * @brief Assigns a taxiway to a flight after it has landed.
     *
     * Replaces any existing taxiway assignment with the new one,
     * so the ground crew knows where to direct the plane.
     *
     * @param flightId ID of the landed flight.
     * @param taxiwayId ID of the taxiway to assign.
     *
     * @return string Success message or error description.
     */
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

    /**
     * @brief Fetches flights that are still waiting for TC approval.
     *
     * Returns all flights with validation set to NOT_ACCEPTED,
     * ordered by scheduled time so the oldest requests appear first.
     *
     * @return array List of pending flights with pilot and route info,
     *               or empty array on failure.
     */
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

    /**
     * @brief Approves a pending flight request.
     *
     * Sets the validation to ACCEPTED so the flight can proceed
     * through the rest of the workflow.
     *
     * @param flightId ID of the flight to approve.
     *
     * @return string Success message, "Flight not found" if already
     *                processed, or error description.
     */
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

    /**
     * @brief Rejects a pending flight request.
     *
     * Sets the validation to REJECTED. Only works if the flight
     * is still in NOT_ACCEPTED state.
     *
     * @param flightId ID of the flight to reject.
     *
     * @return string Success message, "Flight not found" if already
     *                processed, or error description.
     */
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

    /**
     * @brief Updates the priority of a flight in the queue.
     *
     * Lower numbers mean higher priority. Must be at least 1.
     *
     * @param flightId ID of the flight to update.
     * @param priority New priority value, must be >= 1.
     *
     * @return string Success message or error description.
     */
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