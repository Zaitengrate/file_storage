<?php
declare(strict_types=1);
class DB
{
    private static function connection():object
    {
        try
        {
            $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET.'', DB_USER, DB_PASSWORD, array( PDO::ATTR_PERSISTENT => false));
        }
        catch (PDOException $e)
        {
            die($e->getMessage());
        }
        return $pdo;
    }

    public static function query(string $query, array $params = array()) :array
    {
        //$start_time = microtime(TRUE);

        $statement = self::connection()->prepare($query);
        $statement->execute($params);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        /*$end_time = microtime(TRUE);
        $ex_time = $end_time - $start_time;
        $ex_time_string = "Execution time: ".$ex_time." seconds;";
        $query_text = "Text of the query: ".$query.";";
        $param_string = "Params: ";
        foreach ($params as $param) {
            $param_string .= $param." ";
        }
        $log = fopen('log.txt', 'a+');
        fwrite($log, $ex_time_string.' '.$query_text.' '.$param_string.PHP_EOL);
        fclose($log);*/

        return $data;

    }
}