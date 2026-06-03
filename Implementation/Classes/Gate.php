<?php
require "Location.php";
class Gate extends Location
{
    public int $gateNumber;
    public bool $isOccupied;

    public function assignFlight(Flight $f): void{}
    public function freeGate(): bool{return $this->isOccupied;}
}
?>
