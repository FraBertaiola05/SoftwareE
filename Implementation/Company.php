<?php

class Company
{
    private int $companyId;
    private string $name;

    public function getCompanyId(): int{
        return $this->companyId;
    }
    public function setCompanyId(int $companyId): void{
        $this->companyId = $companyId;
    }
    public function getName(): string{
        return $this->name;
    }
    public function setName(string $name): void{
        $this->name = $name;
    }
}
