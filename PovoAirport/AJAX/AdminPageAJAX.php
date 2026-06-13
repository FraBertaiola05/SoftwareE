<?php
//Import required file
require "../DatabaseInfo.php";
$s="";
//Check if there is data that was sent in GET
if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["type"])){
    //Generate and return the form to add a user
    if($_GET["type"]=="ADD"){

        $s="<form action='AdminPage.php' method='POST'>".
        "<label for='email'>Email: </label>".
        "<input type='email' id='email' name='email' required><br>".
        "<label for='name'>Name: </label>".
        "<input type='text' id='name' name='name' required><br>".
        "<label for='surname'>Surname: </label>".
        "<input type='text' id='surname' name='surname' required><br>".
        "<label for='role'>Role: </label>".
        "<select id='role' name='role' onchange='updateCompany()' required>".
        "<option value=''></option>";
        //Fetch roles and create the select
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            echo "Could not connect. ".$e->getMessage();
        }
        try {
            $query="SELECT * FROM roles";
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["id"].">".$row["role_name"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<label for='company'>Company: </label>".
        "<select id='company' name='company' disabled>".
        "<option value=''></option>";
        //Fetch companies and create the select
        try {
            $query="SELECT * FROM companies";
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["id"].">".$row["company_name"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br><input type='submit' value='Add'></form>";
        echo $s;
    }

    //Generate and return the select to select a user
    else if($_GET["type"]=="MODIFY"){

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            die("Could not connect. ".$e->getMessage());
        }
        $s="<form action='AdminPage.php' method='POST'>".
        "<select id='user' name='user' onchange='loadModify()' required>".
        "<option value=''></option>";
        //Fetch users and create the select
        try {
            $query="SELECT id, email, name, surname FROM users";
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["id"].">".$row["name"]." ".$row["surname"]." - ".$row["email"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select></form><div id='container2'></div>";
        echo $s;

    }
    //Generate and return the form to modify a user
    else if($_GET["type"]=="MODIFY2"&&isset($_GET["id"])){
        //Fetch the chosen user and insert it into the form
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            die("Could not connect. ".$e->getMessage());
        }
        try {
            $query=$conn->prepare("SELECT * FROM users WHERE id=:id");
            $query->bindParam(':id',$_GET["id"]);
            $query->execute();
            $result=$query->fetch();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s="<form action='AdminPage.php' method='POST'>".
        "<input type='hidden' id='id' name='id' value='".$_GET["id"]."'>".
        "<label for='email'>Email: </label>".
        "<input type='email' id='email' name='email' value='".$result["email"]."' required><br>".
        "<label for='name'>Name: </label>".
        "<input type='text' id='name' name='name' value='".$result["name"]."' required><br>".
        "<label for='surname'>Surname: </label>".
        "<input type='text' id='surname' name='surname' value='".$result["surname"]."' required><br>".
        "<label for='role'>Role: </label>".
        "<select id='role' name='role' onchange='updateCompany()' required>".
        "<option value=''></option>";
        //Fetch roles and create the select
        try {
            $query="SELECT * FROM roles";
            $res = $conn->query($query);
            while($row = $res->fetch()){
                if($row["id"]==$result["role_id"])
                    $s=$s."<option value=".$row["id"]." selected='selected'>".$row["role_name"]."</option><br>";
                else
                    $s=$s."<option value=".$row["id"].">".$row["role_name"]."</option><br>";
            }
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>";
        $s=$s."<label for='company'>Company: </label>".
        "<select id='company' name='company'";
        if($result["role_id"]!=2&&$result["role_id"]!=6)
            $s=$s." disabled";
        $s=$s.">".
        "<option value=''></option>";
        //Fetch companies and create the select
        try {
            $query="SELECT * FROM companies";
            $res = $conn->query($query);
            while($row = $res->fetch())
                if($row["id"]==$result["company_id"])
                    $s=$s."<option value=".$row["id"]." selected='selected'>".$row["company_name"]."</option><br>";
                else
                    $s=$s."<option value=".$row["id"].">".$row["company_name"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br>".
        "<label for='password'>Reset password: </label>".
        "<input type='checkbox' id='password' name='password' value='true'><br>".
        "<input type='submit' value='Modify'></form>";
        echo $s;

    }
    //Generate and return the form to choose a user to delete
    else if($_GET["type"]=="DELETE"){

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e){
            die("Could not connect. ".$e->getMessage());
        }
        $s="<form action='AdminPage.php' method='POST'>".
        "<select id='user' name='user' required>".
        "<option value=''></option>";
        //Fetch users and create the select
         try {
            $query="SELECT id, email, name, surname FROM users";
            $result = $conn->query($query);
            while($row = $result->fetch())
                $s=$s."<option value=".$row["id"].">".$row["name"]." ".$row["surname"]." - ".$row["email"]."</option><br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $s=$s."</select><br><input type='submit' value='Delete'></form>";
        echo $s;
    }
}
?>
