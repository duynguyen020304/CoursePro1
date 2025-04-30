<?php
    require_once("database.php");
    class User extends Database{
        public function create_1_user( $username, $password, $firstname, $lastname, $role )
        {
            $sql = "INSERT INTO user (firstname, lastname, username, password)
                    VALUES ('{$firstname}', '{$lastname}', '{$username}', '{$password}')";
            $this->execute($sql);
            $this->close();
        }
    }

?>
