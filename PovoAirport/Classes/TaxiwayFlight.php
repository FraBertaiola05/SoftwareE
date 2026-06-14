<?php
require "Flight.php";
require "Taxiway.php";
//Class that represents the assignment of a flight to a taxiway
class TaxiwayFlight
{
    private int $id;
    private Flight $flight;
    private Taxiway $taxiway;

    public function __construct(int $id, Flight $flight, Taxiway $taxiway){
        $this->id = $id;
        $this->flight = $flight;
        $this->taxiway = $taxiway;
    }

    public function getId(): int{
        return $this->id;
    }
    public function setId(int $id): void{
        $this->id = $id;
    }
    public function getFlight(): Flight{
        return $this->flight;
    }
    public function setFlight(Flight $flight): void{
        $this->flight = $flight;
    }
    public function getTaxiway(): Taxiway{
        return $this->taxiway;
    }
    public function setTaxiway(Taxiway $taxiway): void{
        $this->taxiway = $taxiway;
    }
}
