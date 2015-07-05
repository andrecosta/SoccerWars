<?php
define("DB_HOST", "db.drymartini.eu");
define("DB_USER", "soccerwars");
define("DB_PASS", "mikas4ever");
define("DB_NAME", "soccerwars");

class DB
{
    private $connection;

    public function __construct()
    {
        try {
            $this->connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->connection = null;
    }

    public function fetch($query, $params = null, $object_type = null)
    {
        try {
            $statement = $this->connection->prepare($query);
            //$statement->debugDumpParams();
            $statement->execute($params);

            // Fetch the results
            if ($object_type)
                $data = $statement->fetchAll(PDO::FETCH_CLASS, $object_type);
            else
                $data = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (count($data) == 1)
                return $data[0];
            else
                return $data;

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function modify($query, $params = null)
    {
        try {
            $statement = $this->connection->prepare($query);
            //$statement->debugDumpParams();
            $statement->execute($params);

            return $this->lastId();

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function lastId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Escapes a single value or array of values
     *
     * @param $value
     * @return array|string
     */
    public function escape($value)
    {
        if(!is_array($value)) {
            $value = $this->connection->escape_string($value);
        }
        else {
            $value = array_map(array($this, 'escape'), $value);
        }
        return $value;
    }
}