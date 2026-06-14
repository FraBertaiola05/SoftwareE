<?php
require "Plane.php";
//Class that represents a parking spot assigned to a plane
class ParkingSpot
{
    public int $id;
    public string $spotNumber;
    public Plane $plane;

    public function __construct(int $id, string $spotNumber, Plane $plane){
        $this->id = $id;
        $this->spotNumber = $spotNumber;
        $this->plane = $plane;
    }

    public function getId(): int{
        return $this->id;
    }
    public function setId(int $id): void{
        $this->id = $id;
    }
    public function getSpotNumber(): string{
        return $this->spotNumber;
    }
    public function setSpotNumber(string $spotNumber): void{
        $this->spotNumber = $spotNumber;
    }
    public function getPlane(): Plane{
        return $this->plane;
    }
    public function setPlane(Plane $plane): void{
        $this->plane = $plane;
    }
}
