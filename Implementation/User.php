<?php

class User
{
    private int $id;
    private string $name;
    private string $email;
    private string $password;
    private RoleEnum $role;

    public function getId(): int;
    public function setId(int $id): void;
    public function getName(): string;
    public function setName(string $name): void;
    public function getEmail(): string;
    public function setEmail(string $email): void;
    public function getPassword(): string;
    public function setPassword(string $password): void;
    public function getRole(): RoleEnum;
    public function setRole(RoleEnum $role): void;
    public function login(): bool;
    public function hasPermission(string $action): bool;
}
