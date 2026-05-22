<?php
class AccountManager
{
    public function createAccount(User $admin, int $id, string $email, string $name, string $password, Role $r): bool{}
    public function modifyAccount(User $admin, int $id, string $email, string $name, string $password, Role $r): bool{}
    public function deleteAccount(User $admin, int $id): bool{}
}
?>