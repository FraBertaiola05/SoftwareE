<?php

enum PlaneStatus: string
{
    case OnGround = 'on_ground';
    case InFlight = 'in_flight';
    case Parked = 'parked';
    case Taxiing = 'taxiing';
    case Landing = 'landing';
    case TakingOff = 'taking_off';
}
