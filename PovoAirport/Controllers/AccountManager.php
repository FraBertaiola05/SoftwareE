<?php

/**
 * @brief Handles user account management operations.
 *
 * This class provides methods for creating, updating, deleting,
 * and managing user accounts and passwords.
 */
class AccountManager
{
    /**
     * @brief Creates a new user account.
     *
     * Validates the provided user information, generates a random password,
     * stores the user in the database, and returns the generated password.
     *
     * @param email User email address.
     * @param name User first name.
     * @param surname User surname.
     * @param role Role id assigned to the user.
     * @param company Company id when required by the selected role.
     *
     * @return string Result message containing either a success message
     *                or an error description.
     */
    public static function createAccount(string $email, string $name, string $surname, int $role, int $company=NULL): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }

        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        if(!is_null($email)&&$email!=""&&preg_match($pattern,$email)&&!is_null($name)&&$name!=""&&!is_null($surname)&&$surname!=""&&!is_null($role)){
            try {
                if(($role==2||$role==6)&&!is_null($company)){
                    $query=$conn->prepare("INSERT INTO users (email, password, name, surname, role_id, company_id) VALUES(:email,:password,:name,:surname,:role,:company)");
                    $query->bindParam(':company',$company);
                }
                else
                    $query=$conn->prepare("INSERT INTO users (email, password, name, surname, role_id) VALUES(:email,:password,:name,:surname,:role)");
                $query->bindParam(':email',$email);
                $query->bindParam(':name',$name);
                $query->bindParam(':surname',$surname);
                $query->bindParam(':role',$role);

                $pass=self::generatePassword();

                $hashedPass=User::hashPassword($pass);
                $query->bindParam(':password',$hashedPass);
                $query->execute();
                return "The user was created with success. Password: ".$pass;
            } catch(PDOException $e){
                return "Query Error. ".$e->getMessage();
            }
        }else{
            return "The inserted data is wrong";
        }
    }

    /**
     * @brief Updates an existing user account.
     *
     * Modifies the information associated with a user account.
     * Optionally generates and assigns a new password.
     *
     * @param id id of the user to update.
     * @param email Updated email address.
     * @param name Updated first name.
     * @param surname Updated surname.
     * @param role Updated role id.
     * @param changePassword Indicates whether a new password should be generated.
     * @param company Company id when required by the selected role.
     *
     * @return string Result message containing either a success message
     *                or an error description.
     */
    public static function modifyAccount(int $id, string $email, string $name, string $surname, int $role, bool $changePassword, int $company=NULL): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }

        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        if(!is_null($email)&&$email!=""&&preg_match($pattern,$email)&&!is_null($name)&&$name!=""&&!is_null($surname)&&$surname!=""&&!is_null($role)){
            try {
                $s="UPDATE users SET email=:email, name=:name, surname=:surname, role_id=:role, company_id=:company";

                if(!is_null($changePassword)&&$changePassword){
                    $newPass=self::generatePassword();
                    $hashedPass=User::hashPassword($newPass);
                    $s=$s.", password=:password, password_reset=1";
                }

                $s=$s." WHERE id=:id";
                $query=$conn->prepare($s);
                $query->bindParam(':email',$email);
                $query->bindParam(':name',$name);
                $query->bindParam(':surname',$surname);
                $query->bindParam(':role',$role);
                $query->bindParam(':id',$id);
                $temp=NULL;

                if(($role==2||$role==6)&&!is_null($company))
                    $query->bindParam(':company',$company);
                else
                    $query->bindParam(':company',$temp);

                if(!is_null($changePassword)&&$changePassword)
                    $query->bindParam(':password',$hashedPass);

                $query->execute();

                if(!is_null($changePassword)&&$changePassword)
                    return "The user was modified with success. New password: ".$newPass;
                else
                    return "The user was modified with success";
            } catch(PDOException $e){
                return "Query Error. ".$e->getMessage();
            }
        }else{
            return "The inserted data is wrong";
        }
    }

    /**
     * @brief Deletes a user account.
     *
     * Removes the user identified by the provided ID from the database.
     *
     * @param id id of the user to delete.
     *
     * @return string Result message containing either a success message
     *                or an error description.
     */
    public static function deleteAccount(int $id): string{
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }

        if(!is_null($id)){
            try {
                $query=$conn->prepare("DELETE FROM users WHERE id=:id");
                $query->bindParam(':id',$id);
                $query->execute();
                return "The user was deleted with success";
            } catch(PDOException $e){
                return "Query Error. ".$e->getMessage();
            }
        }else{
            return "The inserted data is wrong";
        }
    }

    /**
     * @brief Generates a random password.
     *
     * Creates a 12-character password containing at least one lowercase
     * letter, one uppercase letter, one number, and one special character.
     *
     * @return string Generated password.
     */
    public static function generatePassword(): string{
        $lowercase="abcdefghijklmnopqrstuvwxyz";
        $uppercase="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $numbers="0123456789";
        $symbols="#?!@$%^&*-";
        $all=$lowercase.$uppercase.$numbers.$symbols;
        $pass=$lowercase[rand(0, strlen($lowercase)-1)].$uppercase[rand(0, strlen($uppercase)-1)].$numbers[rand(0, strlen($numbers)-1)].$symbols[rand(0, strlen($symbols)-1)];
        for($i=0;$i<8;$i++){
            $pass=$pass.$all[rand(0, strlen($all)-1)];
        }
        return str_shuffle($pass);
    }

    /**
     * @brief Validates a password against the application's requirements.
     *
     * A valid password must contain at least one lowercase letter,
     * one uppercase letter, one number, one special character,
     * and have a length between 12 and 128 characters.
     *
     * @param password Password to validate.
     *
     * @return bool True if the password is valid, false otherwise.
     */
    public static function checkPassword(string $password): bool{
        if(preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.* )(?=.*[^a-zA-Z0-9]).{12,128}$/',$password))
            return true;
        else
            return false;
    }

    /**
     * @brief Updates the password of a user.
     *
     * Verifies that the provided passwords match, validates the
     * password format, hashes the password, and stores it in the database.
     *
     * @param id id of the user whose password will be updated.
     * @param newPass New password chosen by the user.
     * @param newPassBis Confirmation of the new password.
     *
     * @return string Empty string on success or an error message on failure.
     */
    public static function updateUserPassword(int $id, string $newPass, string $newPassBis): string{
        if(isset($newPass)&&!is_null($newPass)&&isset($newPassBis)&&!is_null($newPassBis)&&$newPass==$newPassBis){
            if(AccountManager::checkPassword($newPass)){
                require 'DatabaseInfo.php';
                require_once 'Classes/User.php';
                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch(PDOException $e){
                    return "Could not connect. ".$e->getMessage();
                }
                try {
                    $query=$conn->prepare("UPDATE users SET password=:password, password_reset=0 WHERE id=:id");
                    $hashedPass=User::hashPassword($newPass);
                    $query->bindParam(':password',$hashedPass);
                    $query->bindParam(':id',$id);
                    $query->execute();
                    return "";
                } catch(PDOException $e){
                    return "Query Error. ".$e->getMessage();
                }
            }else{
                return "The password format is wrong";
            }
        }else{
            return "The two passwords inserted are different";
        }
    }
}
?>