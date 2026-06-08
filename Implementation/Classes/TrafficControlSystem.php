<?php

class TrafficControlSystem
{
    public function getTakeOffQueue(): array{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->prepare("SELECT f.id, f.priority, f.scheduled_time, f.plane_id, p.model,
                u.name AS pilot_name, u.surname AS pilot_surname
                FROM flights f
                INNER JOIN planes p ON f.plane_id = p.plane_number
                INNER JOIN users u ON f.pilot_id = u.id
                WHERE f.status_id = 3 AND f.validation IN ('CONFIRMED','ACCEPTED')
                ORDER BY f.priority ASC, f.scheduled_time ASC");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    public function getLandingQueue(): array{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->prepare("SELECT f.id, f.priority, f.scheduled_time, f.plane_id, p.model,
                u.name AS pilot_name, u.surname AS pilot_surname
                FROM flights f
                INNER JOIN planes p ON f.plane_id = p.plane_number
                INNER JOIN users u ON f.pilot_id = u.id
                WHERE f.status_id = 4 AND f.validation IN ('CONFIRMED','ACCEPTED')
                ORDER BY f.priority ASC, f.scheduled_time ASC");
            $query->execute();
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

    public function assignRunwayForTakeOff(int $flightId, int $runwayId): string{
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
            $conn->commit();
            return "Runway assigned for take off successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    public function assignRunwayForLanding(int $flightId, int $runwayId): string{
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
            $conn->commit();
            return "Runway assigned for landing successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    public function confirmTakeOff(int $flightId): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            $query = $conn->prepare("SELECT plane_id FROM flights WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $flight = $query->fetch(PDO::FETCH_ASSOC);
            $query = $conn->prepare("UPDATE planes SET status_id = 4 WHERE plane_number = :planeId");
            $query->bindParam(':planeId', $flight['plane_id']);
            $query->execute();
            $query = $conn->prepare("UPDATE runways SET flight_id = NULL WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $query = $conn->prepare("UPDATE flights SET priority = NULL, validation = 'DELETED' WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $conn->commit();
            return "Take off confirmed successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    public function confirmLanding(int $flightId): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            $query = $conn->prepare("SELECT plane_id FROM flights WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $flight = $query->fetch(PDO::FETCH_ASSOC);
            $query = $conn->prepare("UPDATE planes SET status_id = 1 WHERE plane_number = :planeId");
            $query->bindParam(':planeId', $flight['plane_id']);
            $query->execute();
            $query = $conn->prepare("UPDATE runways SET flight_id = NULL WHERE flight_id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $query = $conn->prepare("UPDATE flights SET priority = NULL, status_id = 5, validation = 'DELETED' WHERE id = :flightId");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $conn->commit();
            return "Landing confirmed successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    public function getPendingFlights(): array{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return [];
        }
        try {
            $query = $conn->prepare("SELECT f.id, f.scheduled_time, f.plane_id, p.model,
                u.name AS pilot_name, u.surname AS pilot_surname, f.modify_id
                FROM flights f
                INNER JOIN planes p ON f.plane_id = p.plane_number
                INNER JOIN users u ON f.pilot_id = u.id
                WHERE f.validation = 'NOT_ACCEPTED'
                ORDER BY f.scheduled_time ASC");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e){
            return [];
        }
    }

    public function confirmFlight(int $flightId): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            $query = $conn->prepare("SELECT scheduled_time, plane_id, pilot_id, departure_airport_id, arrival_airport_id, status_id, modify_id FROM flights WHERE id = :flightId AND validation = 'NOT_ACCEPTED'");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $flight = $query->fetch(PDO::FETCH_ASSOC);
            if(!$flight){
                $conn->rollBack();
                return "Flight not found";
            }

            if($flight['modify_id'] !== null){
                $query = $conn->prepare("UPDATE flights SET scheduled_time = :time, plane_id = :plane, pilot_id = :pilot, departure_airport_id = :dep, arrival_airport_id = :arr, status_id = :status, validation = 'ACCEPTED' WHERE id = :oldId");
                $query->bindParam(':time', $flight['scheduled_time']);
                $query->bindParam(':plane', $flight['plane_id']);
                $query->bindParam(':pilot', $flight['pilot_id']);
                $query->bindParam(':dep', $flight['departure_airport_id']);
                $query->bindParam(':arr', $flight['arrival_airport_id']);
                $query->bindParam(':status', $flight['status_id']);
                $query->bindParam(':oldId', $flight['modify_id']);
                $query->execute();
                $query = $conn->prepare("DELETE FROM flights WHERE id = :flightId");
                $query->bindParam(':flightId', $flightId);
                $query->execute();
            }else{
                $query = $conn->prepare("UPDATE flights SET validation = 'ACCEPTED' WHERE id = :flightId AND validation = 'NOT_ACCEPTED'");
                $query->bindParam(':flightId', $flightId);
                $query->execute();
            }

            $conn->commit();
            return "Flight approved successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    public function rejectFlight(int $flightId): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        try {
            $conn->beginTransaction();
            $query = $conn->prepare("SELECT modify_id FROM flights WHERE id = :flightId AND validation = 'NOT_ACCEPTED'");
            $query->bindParam(':flightId', $flightId);
            $query->execute();
            $flight = $query->fetch(PDO::FETCH_ASSOC);
            if(!$flight){
                $conn->rollBack();
                return "Flight not found";
            }

            if($flight['modify_id'] !== null){
                $query = $conn->prepare("DELETE FROM flights WHERE id = :flightId");
                $query->bindParam(':flightId', $flightId);
                $query->execute();
            }else{
                $query = $conn->prepare("UPDATE flights SET validation = 'REJECTED' WHERE id = :flightId AND validation = 'NOT_ACCEPTED'");
                $query->bindParam(':flightId', $flightId);
                $query->execute();
            }

            $conn->commit();
            return "Flight rejected successfully";
        } catch(PDOException $e){
            $conn->rollBack();
            return "Query Error. ".$e->getMessage();
        }
    }

    public function updatePriority(int $flightId, int $priority): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
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
