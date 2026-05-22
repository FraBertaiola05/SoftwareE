<?php

class Plane
{
    private int $planeId;
    private PlaneStatus $status;

    public function getPlaneId(): int;
    public function setPlaneId(int $planeId): void;
    public function getStatus(): PlaneStatus;
    public function setStatus(PlaneStatus $status): void;
    public function updatePosition(Location $newLocation): void;
}
