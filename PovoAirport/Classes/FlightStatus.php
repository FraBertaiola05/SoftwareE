<?php

//Enum representing the possible statuses of a flight
enum FlightStatus: string
{
    case Scheduled = 'scheduled';
    case Boarding = 'boarding';
    case Departed = 'departed';
    case Arrived = 'arrived';
    case Cancelled = 'cancelled';
    case Delayed = 'delayed';
    case Finished = 'finished';
}
