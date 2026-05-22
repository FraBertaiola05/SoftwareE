<?php
abstract class Location
{
    public int $id;
    public bool $isFree;

    public function getIsFree(): bool{}
    public function setIsFree(bool $status): void{}
}
?>