<?php
require "Company.php";
//Class that represents an airplane with its model and company
class Plane
{
    private string $planeNumber;
    private string $model;
    private Company $company;

    public function __construct(string $planeNumber, string $model, Company $company){
        $this->planeNumber = $planeNumber;
        $this->model = $model;
        $this->company = $company;
    }

    public function getPlaneNumber(): string{
        return $this->planeNumber;
    }
    public function setPlaneNumber(string $planeNumber): void{
        $this->planeNumber = $planeNumber;
    }
    public function getModel(): string{
        return $this->model;
    }
    public function setModel(string $model): void{
        $this->model = $model;
    }
    public function getCompany(): Company{
        return $this->company;
    }
    public function setCompany(Company $company): void{
        $this->company = $company;
    }
}
