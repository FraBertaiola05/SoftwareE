<?php

class Flight
{
    private int $flightId;
    private int $priority;
    private DateTime $scheduledTime;
    private FlightStatus $status;

    public function getFlightId(): int{
        return $this->flightId;
    }
    public function setFlightId(int $flightId): void{
        $this->flightId = $flightId;
    }
    public function getPriority(): int{
        return $this->priority;
    }
    public function setPriority(int $priority): void{
        $this->priority = $priority;
    }
    public function getScheduledTime(): DateTime{
        return $this->scheduledTime;
    }
    public function setScheduledTime(DateTime $scheduledTime): void{
        $this->scheduledTime = $scheduledTime;
    }
    public function getStatus(): FlightStatus{
        return $this->status;
    }
    public function setStatus(FlightStatus $status): void{
        $this->status = $status;
    }
}
