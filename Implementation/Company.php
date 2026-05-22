<?php

class Company
{
    private int $companyId;
    private string $name;

    public function getCompanyId(): int;
    public function setCompanyId(int $companyId): void;
    public function getName(): string;
    public function setName(string $name): void;
}
