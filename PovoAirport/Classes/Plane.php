<?php

class Plane
{
    private int $planeId;
    private PlaneStatus $status;

    public function getPlaneId(): int{
        return $this->planeId;
    }
    public function setPlaneId(int $planeId): void{
        $this->planeId = $planeId;
    }
    public function getStatus(): PlaneStatus{
        return $this->status;
    }
    public function setStatus(PlaneStatus $status): void{
        $this->status = $status;
    }
    public function updatePosition(Location $newLocation): void{
        // Implementation for updating plane position
    }
}
