<?php
define("DB_HOST", "192.168.50.20");
define("DB_USER", "soccerwars");
define("DB_PASS", "mikas4ever");
define("DB_NAME", "soccerwars");

class DB
{
    private $connection;

    public function __construct()
    {
        try {
            $this->connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->connection = null;
    }

    /**
     * Fetch data from the database either as an object or an array
     * @param string $query
     * @param array $params
     * @param string $object_type
     * @return object|array|int|string
     */
    public function fetch($query, $params = null, $object_type = null)
    {
        try {
            $statement = $this->connection->prepare($query);
            $statement->execute($params);

            // Fetch the results as an object or an associative array
            if ($object_type)
                $data = $statement->fetchAll(PDO::FETCH_CLASS, $object_type);
            else
                $data = $statement->fetchAll(PDO::FETCH_ASSOC);

            // Return a single column or an array depending on the query
            if (count($data) == 1) {
                if (is_array($data[0]) && count($data[0]) == 1)
                    return $data[0][array_keys($data[0])[0]];
            }
            return $data;

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function modify($query, $params = null)
    {
        try {
            $statement = $this->connection->prepare($query);
            $statement->execute($params);

            return $this->lastId();

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function truncate($table)
    {
        try {
            $statement = $this->connection->prepare("TRUNCATE :table");
            $statement->execute(['table' => $table]);

            return true;

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function lastId()
    {
        return $this->connection->lastInsertId();
    }
}