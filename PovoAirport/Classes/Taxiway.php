<?php

class Taxiway
{
    public int $id;
    public string $taxiwayNumber;

    public function __construct(int $id, string $taxiwayNumber){
        $this->id = $id;
        $this->taxiwayNumber = $taxiwayNumber;
    }

    public function getId(): int{
        return $this->id;
    }
    public function setId(int $id): void{
        $this->id = $id;
    }
    public function getTaxiwayNumber(): string{
        return $this->taxiwayNumber;
    }
    public function setTaxiwayNumber(string $taxiwayNumber): void{
        $this->taxiwayNumber = $taxiwayNumber;
    }
}
?>
