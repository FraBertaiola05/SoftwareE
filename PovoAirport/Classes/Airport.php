<?php
//Class that represents an airport with its identifying information
class Airport
{
    private int $airportId;
    private string $name;
    private string $code;
    private string $nation;
    private string $city;

    public function __construct(int $airportId, string $name, string $code, string $nation, string $city){
        $this->airportId = $airportId;
        $this->name = $name;
        $this->code = $code;
        $this->nation = $nation;
        $this->city = $city;
    }

    public function getAirportId(): int{
        return $this->airportId;
    }
    public function setAirportId(int $airportId): void{
        $this->airportId = $airportId;
    }
    public function getName(): string{
        return $this->name;
    }
    public function setName(string $name): void{
        $this->name = $name;
    }
    public function getCode(): string{
        return $this->code;
    }
    public function setCode(string $code): void{
        $this->code = $code;
    }
    public function getNation(): string{
        return $this->nation;
    }
    public function setNation(string $nation): void{
        $this->nation = $nation;
    }
    public function getCity(): string{
        return $this->city;
    }
    public function setCity(string $city): void{
        $this->city = $city;
    }
}
