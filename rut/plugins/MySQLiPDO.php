<?php
namespace Plugins;
use \PDO;

class MySQLiPDO {

    // MYSQLI METHOD style in PDO
    public function mysqli_connect(string $host, string $db, string $username, string $password){
        try {
            $pdo_param = array(
                PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC
            );
            return new PDO("mysql:host=$host;dbname=$db", "$username", "$password", $pdo_param);
        }
        catch(PDOException $e) {
            return false;
        }
    }
    
    public function mysqli_begin_transaction(PDO $connection){
        return $connection->beginTransaction();
    }
    public function mysqli_rollback(PDO $connection){
        return $connection->rollback();
    }
    public function mysqli_commit(PDO $connection){
        return $connection->commit();
    }

    public function mysqli_insert_id(PDO $connection, string $name = NULL){
        if ($name != NULL){
            return $connection->lastInsertId($name);
        }else{
            return $connection->lastInsertId();
        }

    }

    public function mysqli_query(PDO $connection, string $sql){
        return $connection->query($sql);
    }

    public function escape_string(PDO $connection, string $data){
        return $connection->quote($data);
    }

    public function mysqli_prepare(PDO $connection, string $sql){
        return $connection->prepare($sql);
    }
    
    public function mysqli_exec(PDO $connection, string $sql){
        // executes an SQL statement in a single function call, returning the number of rows affected by the statement.
        return $connection->exec($sql);
    }

    public function mysqli_execute($prepared, $array=array()){
        return $prepared->execute($array);
    }

    // Supported Result from execute or the query itself
    public function mysqli_num_rows($query){
        return $query->rowCount();
    }
    public function mysqli_fetch_rows($query){
        return $query->fetchAll(PDO::FETCH_NUM);
    }
    public function mysqli_fetch_array($query){
        return $query->fetchAll(PDO::FETCH_BOTH);
    }
    public function mysqli_fetch_assoc($query){
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
