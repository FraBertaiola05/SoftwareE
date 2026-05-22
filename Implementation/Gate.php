<?php
require "Location.php";
class Gate extends Location
{
    public int $gateNumber;

    public function assignFlight(Flight $f): void{}
    public function freeGate(): bool{}
}
?>