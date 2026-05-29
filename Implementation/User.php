<?php

class User
{
    private int $id;
    private string $name;
    private string $email;
    private string $password;
    private RoleEnum $role;
    public User(int $id, string $name, string $email, string $password, RoleEnum $role){
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
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
    public function getPassword(): string{
        return $this->password;
    }
    public function setPassword(string $password): void{
        $this->password = hashPassword($password);
    }
    public function getRole(): RoleEnum{
        return $this->role;
    }
    public function setRole(RoleEnum $role): void{
        $this->role = $role;
    }
    public function login(string $email, string $password): bool{
        // Implementation for user login
    }
    public function hasPermission(string $action): bool{
        // Implementation for checking user permissions
    }
    private function hashPassword(string $password): string{
        // Implementation for hashing passwords
    }
}
