<?php
abstract class Location
{
    public int $id;
    public bool $isFree;

    public function getIsFree(): bool{return $this->isFree;}
    public function setIsFree(bool $status): void{$this->isFree = $status;}
}
?>
