<?php

class GroundManagementSystem
{
    public function getAvailableGates(): array;
    public function linkPlaneToGate(User $u, Plane $p, Gate $g): void;
    public function updatePlanePosition(User $u, Plane $p, Location $l): void;
    public function markPlaneAsParked(User $u, Plane $p, ParkingSpot $s): void;
}
