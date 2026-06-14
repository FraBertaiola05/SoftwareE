<?php
require "Flight.php";
class Runway
{
    public int $id;
    public string $runwayNumber;
    public Flight $flight;

    public function __construct(int $id, string $runwayNumber, Flight $flight){
        $this->id = $id;
        $this->runwayNumber = $runwayNumber;
        $this->flight = $flight;
    }

    public function getId(): int{
        return $this->id;
    }
    public function setId(int $id): void{
        $this->id = $id;
    }
    public function getRunwayNumber(): string{
        return $this->runwayNumber;
    }
    public function setRunwayNumber(string $runwayNumber): void{
        $this->runwayNumber = $runwayNumber;
    }
    public function getFlight(): Flight{
        return $this->flight;
    }
    public function setFlight(Flight $flight): void{
        $this->flight = $flight;
    }
}
?>
