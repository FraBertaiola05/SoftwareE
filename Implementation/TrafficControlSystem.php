<?php

class TrafficControlSystem
{
    private array $takeOffQueue;
    private array $landingQueue;

    public function getTakeOffQueue(): array{
        return $this->takeOffQueue;
    }
    public function getLandingQueue(): array{
        return $this->landingQueue;
    }
    public function assignRunwayForTakeOff(Plane $p, Runway $r): void{

    }
    public function assignRunwayForLanding(Plane $p, Runway $r): void{
        
    }
    public function confirmTakeOff(Plane $p): void{
        
    }
    public function confirmLanding(Plane $p): void{
        
    }
    public function getAvailableRunways(): array{
        
    }
}
