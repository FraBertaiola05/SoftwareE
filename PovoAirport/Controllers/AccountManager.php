<?php
class AccountManager
{
    //Given the data to create a User, create a user in the database
    public static function createAccount(string $email, string $name, string $surname, int $role, int $company=NULL): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        //Regular expression to check if the email is well formatted
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
        //Check if the data that was given to the function is not null
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
                //Generate a random password
                $pass=self::generatePassword();
                //Hash the generated password
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

    //Given the data to modify a User, modify a user in the database
    public static function modifyAccount(int $id, string $email, string $name, string $surname, int $role, bool $changePassword, int $company=NULL): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        //Regular expression to check if the email is well formatted
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
        //Check if the data that was given to the function is not null
        if(!is_null($email)&&$email!=""&&preg_match($pattern,$email)&&!is_null($name)&&$name!=""&&!is_null($surname)&&$surname!=""&&!is_null($role)){
            try {
                $s="UPDATE users SET email=:email, name=:name, surname=:surname, role_id=:role, company_id=:company";
                //If the admin has selected to re-generate the password, generate a new hashed password for the user
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

    //Given the id of the User that needs to be deleted, delete the user from the database
    public static function deleteAccount(int $id): string{
        //Import required file
        require 'DatabaseInfo.php';
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            return "Could not connect. ".$e->getMessage();
        }
        //Check if the data that was given to the function is not null
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

    //Generate a 12 characters password with at least one character that is lowercase, one uppercase, one number and one symbol
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

    //Check if a given password has at least 12 character, one character that is lowercase, one uppercase, one number and one symbol. There is a max value of 128 characters. The check is done through a regular expression
    public static function checkPassword(string $password): bool{
        if(preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.* )(?=.*[^a-zA-Z0-9]).{12,128}$/',$password))
            return true;
        else 
            return false;
    }

    //Given a new password and a user, check if the password is correct, then update the user password
    public static function updateUserPassword(int $id, string $newPass, string $newPassBis): string{
        //Check if the data that was given to the function is not null and the newPass and newPassBis are the same
        if(isset($newPass)&&!is_null($newPass)&&isset($newPassBis)&&!is_null($newPassBis)&&$newPass==$newPassBis){
            if(AccountManager::checkPassword($newPass)){
                //Import required files
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
