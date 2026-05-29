<?php
class AccountManager
{
    public function createAccount(User $admin, int $id, string $email,
    string $name, string $password, Role $r): bool{
        new User( $id, $name, $email, $password, $r);
    }
    public function modifyAccount(User $admin, int $id, string $email, string $name, 
    string $password, Role $r): bool{
        
    }
    public function deleteAccount(User $admin, int $id): bool{}
}
?>