<?php

class Flight
{
    private int $flightId;
    private int $priority;
    private DateTime $scheduledTime;
    private FlightStatus $status;

    public function getFlightId(): int;
    public function setFlightId(int $flightId): void;
    public function getPriority(): int;
    public function setPriority(int $priority): void;
    public function getScheduledTime(): DateTime;
    public function setScheduledTime(DateTime $scheduledTime): void;
    public function getStatus(): FlightStatus;
    public function setStatus(FlightStatus $status): void;
}
