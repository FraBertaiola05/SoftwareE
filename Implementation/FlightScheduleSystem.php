<?php
class FlightScheduleSystem
{
    public function requestAddFlight(User $u, int $flightId, DateTime $time): bool{}
    public function requestModifyFlight(User $u, int $flightId, DateTime $time): bool{}
    public function requestDeleteFlight(User $u, int $flightId): bool{}
    public function getFlightHistory(DateTime $t1, DateTime $t2): array{}
}
?>