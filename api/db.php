<?php
define("DB_HOST", "db.drymartini.eu");
define("DB_USER", "soccerwars");
define("DB_PASS", "mikas4ever");
define("DB_NAME", "soccerwars");

class DB
{
    private $connection = null;

    public function __construct()
    {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $this->set_charset('utf8');
        } catch (Exception $ex) {
            echo '<script>document.location="/maintenance"</script>';
            exit();
            // or die()
        }
    }

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function query($sql)
    {
        $query = $this->connection->query($sql);

        if ($this->connection->error){
            return false;
        } else {
            return true;
        }
    }

    public function select($query, $object = false)
    {
        $row = null;

        $results = $this->connection->query( $query );
        if( $this->connection->error )
        {
            return false;
        }
        else
        {
            $row = array();
            while( $r = ( !$object ) ? $results->fetch_assoc() : $results->fetch_object() )
            {
                $row[] = $r;
            }
            return $row;
        }
    }

    public function select_row($query, $object = false)
    {
        $row = $this->connection->query( $query );
        if( $this->connection->error )
        {
            return false;
        }
        else
        {
            $r = ( !$object ) ? $row->fetch_row() : $row->fetch_object();
            return $r;
        }
    }


    public function lastid()
    {
        return $this->connection->insert_id;
    }

    public function escape($value)
    {
        if( !is_array( $value ) )
        {
            $value = $this->connection->escape_string( $value );
        }
        else
        {
            $value = array_map( array( $this, 'escape' ), $value );
        }

        return $value;
    }
}