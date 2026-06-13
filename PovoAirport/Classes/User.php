<?php
require "RoleEnum.php";
class User
{
    private int $id;
    private string $name;
    private string $surname;
    private string $email;
    private string $password;
    private RoleEnum $role;
    private int $company;
    private bool $changePass;
    public function __construct(int $id, string $name, string $surname, string $email, string $password, RoleEnum $role, bool $changePass){
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->changePass = $changePass;
    }
    public function getId(): int{
        return $this->id;
    }
    public function setId(int $id): void{
        $this->id = $id;
    }
    public function getName(): string{
        return $this->name;
    }
    public function setName(string $name): void{
        $this->name = $name;
    }
    public function getSurname(): string{
        return $this->surname;
    }
    public function setSurname(string $surname): void{
        $this->name = $surname;
    }
    public function getEmail(): string{
        return $this->email;
    }
    public function setEmail(string $email): void{
        $this->email = $email;
    }
    public function getPassword(): string{
        return $this->password;
    }
    public function setPassword(string $password): void{
        $this->password = $this->hashPassword($password);
    }
    public function getRole(): RoleEnum{
        return $this->role;
    }
    public function setRole(RoleEnum $role): void{
        $this->role = $role;
    }
    public function getCompany(): int{
        return $this->company;
    }
    public function setCompany(int $company): void{
        $this->company = $company;
    }
    public function getChangePass(): bool{
        return $this->changePass;
    }
    public function setChangePass(int $changePass): void{
        $this->changePass = $changePass;
    }
    public function login(string $email, string $password): bool{
        if($this->email === $email && $this->password === $this->hashPassword($password)){
            return true;
        }
        return false;
    }
    /*public function hasPermission(string $action): bool{
        // Implementation for checking user permissions
    }*/
    public static function hashPassword(string $password): string{
        return hash('sha512', $password);
    }
}
