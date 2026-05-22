<?php
class FinanceSystem
{
    public float $landingCost;
    public float $parkingCost;
    public float $scheduleFlightPrice;

    public function calculateAirportCosts(DateTime $t1, DateTime $t2): float{}
    public function calculateAirportRevenue(DateTime $t1, DateTime $t2): float{}
    public function getFinancialOverview(User $u, DateTime $t1, DateTime $t2): array{}
}
?>