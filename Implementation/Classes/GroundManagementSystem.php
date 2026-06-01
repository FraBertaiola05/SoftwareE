<?php

class GroundManagementSystem
{
    public function getAvailableGates(): array{
        foreach ($this->gates as $gate) {
            if (!$gate->isOccupied) {
                $availableGates[] = $gate;
            }
        }
        return $availableGates;
    }
    public function linkPlaneToGate(User $u, Plane $p, Gate $g): void;
    public function updatePlanePosition(User $u, Plane $p, Location $l): void;
    public function markPlaneAsParked(User $u, Plane $p, ParkingSpot $s): void{
        p->setStatus(PlaneStatus::PARKED);
        s->assignPlane($p);
    }
}
