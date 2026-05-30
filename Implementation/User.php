<?php

class User
{
    private int $id;
    private string $name;
    private string $email;
    private string $password;
    private RoleEnum $role;
    public function __construct(int $id, string $name, string $email, string $password, RoleEnum $role){
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $this->hashPassword($password);
        $this->role = $role;
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
    public function getEmail(): string{
        return $this->email;
    }
    public function setEmail(string $email): void{
        $this->email = $email;
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
    public function login(string $email, string $password): bool{
        int try = 0;
        while(try < 3){
             if($this->email === $email && $this->password === $this->hashPassword($password)){
                return true;
            }
            try++;
        }
        return false;
    }
    public function hasPermission(string $action): bool{
        // Implementation for checking user permissions
    }
    private function hashPassword(string $password): string{
        return hash('sha512', $password);
    }
}
