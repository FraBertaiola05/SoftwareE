<?php
require "FlightStatus.php";
require "Plane.php";
require "Airport.php";

//Enum representing the possible validation states of a flight
enum Validation: string
{
    case NotAccepted = 'NOT_ACCEPTED';
    case Accepted = 'ACCEPTED';
    case Rejected = 'REJECTED';
    case Confirmed = 'CONFIRMED';
    case Deleted = 'DELETED';
}

//Class that represents a flight with its schedule, route, plane and validation status
class Flight
{
    private int $flightId;
    private int $priority;
    private DateTime $scheduledTime;
    private FlightStatus $status;
    private Validation $validation;
    private Plane $plane;
    private Airport $depAirport;
    private Airport $arrAirport;

    public function __construct(int $flightId, int $priority, DateTime $scheduledTime, FlightStatus $status, Validation $validation, Plane $plane, Airport $depAirport, Airport $arrAirport){
        $this->flightId = $flightId;
        $this->priority = $priority;
        $this->scheduledTime = $scheduledTime;
        $this->status = $status;
        $this->validation = $validation;
        $this->plane = $plane;
        $this->depAirport = $depAirport;
        $this->arrAirport = $arrAirport;
    }

    public function getFlightId(): int{
        return $this->flightId;
    }
    public function setFlightId(int $flightId): void{
        $this->flightId = $flightId;
    }
    public function getPriority(): int{
        return $this->priority;
    }
    public function setPriority(int $priority): void{
        $this->priority = $priority;
    }
    public function getScheduledTime(): DateTime{
        return $this->scheduledTime;
    }
    public function setScheduledTime(DateTime $scheduledTime): void{
        $this->scheduledTime = $scheduledTime;
    }
    public function getStatus(): FlightStatus{
        return $this->status;
    }
    public function setStatus(FlightStatus $status): void{
        $this->status = $status;
    }
    public function getValidation(): Validation{
        return $this->validation;
    }
    public function setValidation(Validation $validation): void{
        $this->validation = $validation;
    }
    public function getPlane(): Plane{
        return $this->plane;
    }
    public function setPlane(Plane $plane): void{
        $this->plane = $plane;
    }
    public function getDepAirport(): Airport{
        return $this->depAirport;
    }
    public function setDepAirport(Airport $depAirport): void{
        $this->depAirport = $depAirport;
    }
    public function getArrAirport(): Airport{
        return $this->arrAirport;
    }
    public function setArrAirport(Airport $arrAirport): void{
        $this->arrAirport = $arrAirport;
    }
}
