<?php
class FinanceSystem
{
    public const DAILY_COST=5000;
    public const DAILY_REVENUE=5500;
    public const LANDING_COST=100;
    public const TAKING_OFF_COST=100;
    public const TAKING_OFF_REVENUE=250;

    public static function calculateAirportCosts(String $t1, String $t2): float{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return 0;
        }
        try {
            $query=$conn->prepare("SELECT COUNT(*) AS 'n' FROM flights WHERE scheduled_time BETWEEN :t1 AND :t2 AND departure_airport_id=1");
            $query->bindParam(':t1',$t1);
            $query->bindParam(':t2',$t2);
            $query->execute();
            $result=$query->fetch();
            $tot=$result["n"]*self::TAKING_OFF_COST;
            $query=$conn->prepare("SELECT COUNT(*) AS 'n' FROM flights WHERE scheduled_time BETWEEN :t1 AND :t2 AND arrival_airport_id=1");
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
    public static function calculateAirportRevenue(String $t1, String $t2): float{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return 0;
        }
        try {
            $query=$conn->prepare("SELECT COUNT(*) AS 'n' FROM flights WHERE scheduled_time BETWEEN :t1 AND :t2 AND departure_airport_id=1");
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
    public static function getFinancialOverview(String $t1, String $t2): array{
        if(strtotime($t1)&&strtotime($t1)&&$t1<=$t2){
            $cost=self::calculateAirportCosts($t1,$t2);
            $revenue=self::calculateAirportRevenue($t1,$t2);
            $total=$revenue-$cost;
            return [$cost,$revenue,$total];
        }else
            return[0,0,0];
    }
}
?>
