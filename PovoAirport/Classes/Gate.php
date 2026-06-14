<?php
require "Flight.php";
class Gate
{
    public int $id;
    public string $gateNumber;
    public Flight $flight;

    public function __construct(int $id, string $gateNumber, Flight $flight){
        $this->id = $id;
        $this->gateNumber = $gateNumber;
        $this->flight = $flight;
    }

    public function getId(): int{
        return $this->id;
    }
    public function setId(int $id): void{
        $this->id = $id;
    }
    public function getGateNumber(): string{
        return $this->gateNumber;
    }
    public function setGateNumber(string $gateNumber): void{
        $this->gateNumber = $gateNumber;
    }
    public function getFlight(): Flight{
        return $this->flight;
    }
    public function setFlight(Flight $flight): void{
        $this->flight = $flight;
    }
}
?>
