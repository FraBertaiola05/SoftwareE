<?php
class FinanceSystem
{
    //Daily cost to mantain the airport
    public const DAILY_COST=5000;
    //Daily revenue from the airport
    public const DAILY_REVENUE=5500;
    //Cost for every landed flight
    public const LANDING_COST=100;
    //Cost for every flight that take off
    public const TAKING_OFF_COST=100;
    //Revenue for every flight that take off
    public const TAKING_OFF_REVENUE=250;

    //Given a starting and an ending date, calculates the cost in this timespan
    public static function calculateAirportCosts(String $t1, String $t2): float{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return 0;
        }
        try {
            //Fetch the flight that have taken off in the timespan
            $query=$conn->prepare("SELECT COUNT(*) AS 'n' FROM flights WHERE scheduled_time BETWEEN :t1 AND :t2 AND departure_airport_id=1 AND validation IN ('ACCEPTED','CONFIRMED') AND modify_id IS NULL");
            $query->bindParam(':t1',$t1);
            $query->bindParam(':t2',$t2);
            $query->execute();
            $result=$query->fetch();
            $tot=$result["n"]*self::TAKING_OFF_COST;
            //Fetch the flight that have landed in the timespan
            $query=$conn->prepare("SELECT COUNT(*) AS 'n' FROM flights WHERE scheduled_time BETWEEN :t1 AND :t2 AND arrival_airport_id=1 AND validation IN ('ACCEPTED','CONFIRMED') AND modify_id IS NULL");
            $query->bindParam(':t1',$t1);
            $query->bindParam(':t2',$t2);
            $query->execute();
            $result=$query->fetch();
            $tot=$tot+$result["n"]*self::LANDING_COST;
            $tot=$tot+ (round((strtotime($t2)-strtotime($t1)) / (60 * 60 * 24))+1)*self::DAILY_COST;
            return $tot;
        } catch(PDOException $e) {
            return 0;
        }
    }

    //Given a starting and an ending date, calculates the revenue in this timespan
    public static function calculateAirportRevenue(String $t1, String $t2): float{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return 0;
        }
        try {
            //Fetch the flight that have taken off in the timespan
            $query=$conn->prepare("SELECT COUNT(*) AS 'n' FROM flights WHERE scheduled_time BETWEEN :t1 AND :t2 AND departure_airport_id=1 AND validation IN ('ACCEPTED','CONFIRMED') AND modify_id IS NULL");
            $query->bindParam(':t1',$t1);
            $query->bindParam(':t2',$t2);
            $query->execute();
            $result=$query->fetch();
            $tot=$result["n"]*self::TAKING_OFF_REVENUE;
            $tot=$tot+ (round((strtotime($t2)-strtotime($t1)) / (60 * 60 * 24))+1)*self::DAILY_REVENUE;
            return $tot;
        } catch(PDOException $e) {
            return 0;
        }
    }

    //Given a starting and an ending date, calculates the cost, revenue and total in this timespan
    public static function getFinancialOverview(String $t1, String $t2): array{
        if(strtotime($t1)&&strtotime($t2)&&$t1<=$t2){
            $cost=self::calculateAirportCosts($t1,$t2);
            $revenue=self::calculateAirportRevenue($t1,$t2);
            $total=$revenue-$cost;
            return [$cost,$revenue,$total];
        }else
            return[0,0,0];
    }
}
?>
