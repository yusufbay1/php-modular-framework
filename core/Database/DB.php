<?php

namespace Core\Database;

class DB
{
    public string $host;
    public string $username;
    public string $password;
    public string $databaseName;
    protected static ?\PDO $database = null;

    protected static string $table;
    protected static string $select = "*";
    protected static mixed $whereRawKey;
    protected static mixed $whereRawVal;
    protected static mixed $whereKey;
    protected static mixed $whereVal = array();
    protected static mixed $orderBy = null;
    protected static mixed $groupBy = null;
    protected static mixed $limit = null;
    protected static string $join = "";
    protected static string $raw = "";

    public function __construct()
    {
        $this->host = Env::get("DB_HOST");
        $this->username = Env::get("DB_USERNAME");
        $this->password = Env::get("DB_PASSWORD");
        $this->databaseName = Env::get("DB_DATABASE");
        $this->__connect();
    }

    protected function __connect(): void
    {
        try {
            self::$database = new \PDO(
                "mysql:host={$this->host};dbname={$this->databaseName};charset=UTF8",
                $this->username,
                $this->password
            );
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }

    public static function table($tableName): DB
    {
        self::$table = $tableName;
        self::$select = "*";
        self::$whereRawKey = null;
        self::$whereRawVal = null;
        self::$whereKey = null;
        self::$whereVal = array();
        self::$orderBy = null;
        self::$limit = null;
        self::$join = "";
        self::$groupBy = null;
        self::$raw = "";
        return new self;
    }

    public static function select($columns): DB
    {
        self::$select = $columns;
        return new self;
    }

    public static function whereRaw($whereRaw, $whereRawVal = []): DB
    {
        self::$whereRawKey = "(" . $whereRaw . ")";
        self::$whereRawVal = $whereRawVal;
        return new self;
    }

    public static function whereIn($column, $values): DB
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        self::$whereKey = $column . " IN (" . $placeholders . ")";
        self::$whereVal = array_merge(self::$whereVal, $values);
        return new self;
    }

    public static function where($columns, $columnsTwo = null, $columnsTheree = null): DB
    {
        if (is_array($columns)) {
            $keyList = array_keys($columns);
            self::$whereVal = array_values($columns);
            self::$whereKey = implode(" = ? AND ", $keyList) . " =? ";
        } elseif ($columnsTwo !== null && $columnsTheree === null) {
            self::$whereVal[] = $columnsTwo;
            self::$whereKey = "$columns =? ";
        } elseif ($columnsTheree !== null) {
            self::$whereVal[] = $columnsTheree;
            self::$whereKey = "$columns$columnsTwo ? ";
        }
        return new self;
    }

    public static function when($condition, $callback): DB
    {
        if ($condition) {
            $callback(new self);
        }
        return new self;
    }

    public static function orderBy($parameter): DB
    {
        self::$orderBy = $parameter[0] . " " . ((!empty($parameter[1])) ? $parameter[1] : "ASC");
        return new self;
    }

    public static function groupBy($parameter): DB
    {
        self::$groupBy = $parameter[0] . " " . ((!empty($parameter[1]))) ? $parameter[1] : " ";
        return new self;
    }

    public static function limit($start, $end = null): DB
    {
        self::$limit = $start . (($end != null) ? "," . $end : "");
        return new self;
    }

    private static function joins($tableName, $first, $operator, $second = null, $type = ''): void
    {
        if (is_null($second)) {
            $second = $operator;
            $operator = '=';
        }
        self::$join .= " $type JOIN $tableName ON $first $operator $second ";
    }

    public static function join($tableName, $first, $operator, $second = null, $type = ''): DB
    {
        self::joins($tableName, $first, $operator, $second, $type);
        return new self;
    }

    public static function raw(string $raw): DB
    {
        self::$raw = $raw;
        return new self;
    }

    public static function leftJoin($tableName, $first, $operator, $second = null): DB
    {
        self::joins($tableName, $first, $operator, $second, 'LEFT');
        return new self;
    }

    public static function get(): false|array
    {
        $Entity = self::entity();
        $Result = $Entity->fetchAll(\PDO::FETCH_OBJ);
        return $Result ?: false;
    }

    public static function first(): object|false
    {
        $Entity = self::entity();
        $Result = $Entity->fetch(\PDO::FETCH_OBJ);
        return $Result ?? false;
    }

    public static function entity(): \PDOStatement|false
    {
        $SQL = "SELECT " . self::$select . " FROM " . self::$table . " ";
        $SQL .= (!empty(self::$join)) ? self::$join : " ";
        $SQL .= (!empty(self::$raw)) ? self::$raw : " ";
        $where = null;
        $buildWhereSql = self::buildWhereSql($where);
        $SQL .= $buildWhereSql['sql'];
        $where = $buildWhereSql['where'];
        $SQL .= (!empty(self::$groupBy)) ? " GROUP BY " . self::$groupBy . " " : " ";
        $SQL .= (!empty(self::$orderBy)) ? " ORDER BY " . self::$orderBy . " " : " ";
        $SQL .= (!empty(self::$limit)) ? " LIMIT " . self::$limit . " " : "";
        $Entity = self::$database->prepare(trim($SQL));
        $Sync = ($where != null) ? $Entity->execute($where) : $Entity->execute();
        return $Entity;
    }

    public static function create($arrayColumns): bool|int
    {
        $columns = array_keys($arrayColumns);
        $columnsValue = array_values($arrayColumns);
        $SQL = "INSERT INTO " . self::$table . " SET " . implode("=?,", $columns) . "=? ";
        $Entity = self::$database->prepare($SQL);
        $Sync = $Entity->execute($columnsValue);
        return ($Sync !== false);
    }

    public static function update($arrayColumns): bool|int
    {
        $columns = array_keys($arrayColumns);
        $columnsValue = array_values($arrayColumns);
        $SQL = "UPDATE " . self::$table . " SET " . implode("=?,", $columns) . "=? ";
        $where = null;

        $buildWhereSql = self::buildWhereSql($where, $SQL);
        $SQL .= $buildWhereSql['sql'];
        $where = $buildWhereSql['where'];
        if ($where != null)
            $arrayColumns = array_merge($columnsValue, $where);

        $Entity = self::$database->prepare($SQL);
        $Sync = $Entity->execute($arrayColumns);
        return ($Sync !== false);
    }

    public static function delete(): bool|int
    {
        $SQL = 'DELETE FROM ' . self::$table . ' ';
        $where = null;
        $buildWhereSql = self::buildWhereSql($where, $SQL);
        $SQL .= $buildWhereSql['sql'];
        $where = $buildWhereSql['where'];
        $Entity = self::$database->prepare($SQL);
        $Sync = $Entity->execute($where);
        return ($Sync !== false);
    }

    public static function onDuplicateBatch($data, $updateColumns): bool
    {
        if (empty($data))
            return false;

        $columns = array_keys($data[0]);
        $placeholders = [];
        $values = [];
        foreach ($data as $row) {
            $placeholders[] = '(' . implode(',', array_fill(0, count($row), '?')) . ')';
            $values = array_merge($values, array_values($row));
        }
        $updateParts = [];
        foreach ($updateColumns as $column) {
            $updateParts[] = "{$column} = VALUES({$column})";
        }

        $SQL = "INSERT INTO " . self::$table . " (" . implode(", ", $columns) . ") ";
        $SQL .= "VALUES " . implode(", ", $placeholders) . " ";
        $SQL .= "ON DUPLICATE KEY UPDATE " . implode(", ", $updateParts);
        $Entity = self::$database->prepare($SQL);
        $Sync = $Entity->execute($values);
        return ($Sync !== false);
        /*DB::table('users')->onDuplicateBatch(
            [
                ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
                ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
                ['id' => 3, 'name' => 'Alice Brown', 'email' => 'alice@example.com']
            ],
            ['name', 'email'] // Güncellenecek sütunlar
        );*/
    }

    public static function primaryKey($tableName): int
    {
        $SQL = "SHOW TABLE STATUS FROM " . (new static)->databasename . " WHERE Name = '" . $tableName . "'";
        $Entity = self::$database->prepare($SQL);
        $Sync = $Entity->execute();
        $Result = $Entity->fetchAll(\PDO::FETCH_OBJ);
        return ($Result[0]->Auto_increment);
    }

    public static function rawQuery($sql, $params = []): array|object|false
    {
        return self::Query($sql, 'fetchAll', $params);
    }

    public static function rawQueryFirst($sql, $params = []): object|false
    {
        return self::Query($sql, 'fetch', $params);
    }

    public static function pluck($collection, $key): array
    {
        return array_column($collection, $key);
    }

    private static function buildWhereSql($where): array
    {
        $SQL = "";
        if (!empty(self::$whereKey) && !empty(self::$whereRawKey)) {
            $SQL .= " WHERE " . self::$whereKey . " AND " . self::$whereRawKey . " ";
            $where = array_merge(self::$whereVal, self::$whereRawVal);
        } else {
            if (!empty(self::$whereKey)) {
                $SQL .= " WHERE " . self::$whereKey . " ";
                $where = self::$whereVal;
            }
            if (!empty(self::$whereRawKey)) {
                $SQL .= " WHERE " . self::$whereRawKey . " ";
                $where = self::$whereRawVal;
            }
        }


        return ['sql' => $SQL, 'where' => $where];
    }

    private static function Query($sql, $fetchQuery, $params = []): array|object|false
    {
        if (is_null(self::$database))
            (new static)->__connect();

        $Entity = self::$database->prepare($sql);
        $Sync = $Entity->execute($params);
        $Result = $Entity->$fetchQuery(\PDO::FETCH_OBJ);
        return $Result ?? false;
    }

    public static function beginTransaction(): void
    {
        if (is_null(self::$database)) {
            (new static)->__connect();
        }

        if (!self::$database->inTransaction()) {
            self::$database->beginTransaction();
        }
    }

    public static function commit(): void
    {
        if (is_null(self::$database)) {
            (new static)->__connect();
        }
        self::$database->commit();
        if (self::$database->inTransaction()) {
            echo 'Transaction is still active';
        }
    }

    public static function rollback(): void
    {
        if (is_null(self::$database)) {
            (new static)->__connect();
        }
        self::$database->rollBack();
    }
}