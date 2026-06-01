<?php
require "TransactionType.php";
class FinancialLog{
    public int $logId;
    public float $amount;
    public DateTime $timestamp;
    public TransactionType $type;
    public string $description;
}

?>
