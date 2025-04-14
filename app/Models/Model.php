<?php

namespace App\Models;

use Core\Database\DB;
use Core\Http\{Request,Response};

class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    protected static function filterAttributes($attributes, $passwordKey = null)
    {
        $filtered = array_filter($attributes, function ($key) {
            return !str_ends_with($key, '_confirm');
        }, ARRAY_FILTER_USE_KEY);

        if ($passwordKey && array_key_exists($passwordKey, $filtered)) {
            $filtered[$passwordKey] = password_hash($filtered[$passwordKey], PASSWORD_DEFAULT);
        }

        return $filtered;
    }

    public static function all($select = '*', string $table = '', $orderBy = false, $limit = false): object|false|array|string
    {
        $all = DB::table($table != '' ? $table : (new static)->table)->select($select)
            ->when($orderBy, fn($query) => $query->orderBy($orderBy))
            ->when($limit, fn($query) => $query->limit($limit))->get();
        return $all ?: Response::notfound();
    }

    public static function find($where, $select = '*', $table = false, $orderBy = []): object|false
    {
        return DB::table($table ?: (new static)->table)->select($select)->where($where)
            ->when($orderBy, fn($query) => $query->orderBy($orderBy))
            ->first();
    }

    public static function where($where, string $select = '*', string $table = '', $orderBy = false, $limit = false): object|false|array
    {
        return DB::table($table ?: (new static)->table)->select($select)->where($where)
            ->when($orderBy, fn($query) => $query->orderBy($orderBy))
            ->when($limit, fn($query) => $query->limit($limit))->get();
    }

    public static function onDuplicate($table, $data, $updateColumns): bool
    {
        return DB::table($table)->onDuplicateBatch($data, $updateColumns);
    }

    public static function create($attributes, $table = false, $uniqueCheck = [], $passwordKey = null): false|string
    {
        $attributes = static::filterAttributes($attributes, $passwordKey);
        if (!empty($uniqueCheck)) {
            if (DB::table($table ?: (new static)->table)->where($uniqueCheck)->first()) {
                return Response::conflict();
            }
        }
        return DB::table($table ?: (new static)->table)->create($attributes) ? Response::created() : Response::error();
    }

    public static function update($id, $attributes, $table = false, $passwordKey = null): false|string
    {
        $attributes = static::filterAttributes($attributes, $passwordKey);
        return DB::table($table ?: (new static)->table)->where(is_array($id) ? $id : [static::getPrimaryKey() => $id])->update($attributes) ? Response::ok() : Response::error();
    }

    public static function delete($id, $table = false): false|string
    {
        return DB::table($table ?: (new static)->table)->where(is_array($id) ? $id : [static::getPrimaryKey() => $id])->delete() ? Response::ok() : Response::error();
    }

    public static function duplicate(array $data, array $updateColumns, $table = false): bool
    {
        return DB::table($table ?: (new static)->table)->onDuplicateBatch($data, $updateColumns);
    }

    public
    static function findDelete($id, $table, $primaryKey): false|string
    {
        return DB::table($table)->where($primaryKey, $id)->delete() ? Response::ok() : Response::error();
    }

    public static function getPrimaryKey(): string
    {
        return (new static)->primaryKey;
    }

    public static function validateFillable($data): bool
    {
        $fillable = (new static)->getFillable();
        $invalidFields = array_diff(array_keys($data), $fillable);
        return empty($invalidFields);
    }

    public static function getFillable(): array
    {
        return (new static)->fillable;
    }

    public static function pluck($key): \Closure
    {
        return function ($collection) use ($key) {
            return DB::pluck($collection, $key);
        };
    }

    public static function datatable(Request $request, array $columnMapping = [], string $joins = '', string $select = '*', string $default = '1=1'): array
    {
        $table = (new static)->table;
        $filterData = $request->request('filterData') ?? [];
        $andOr = !empty($filterData) ? " and " : " or ";

        $where = [];
        $order = ['id', 'ASC'];
        $orderPost = $request->request('order');
        $columns = $request->request('columns');
        if (isset($orderPost)) {
            $column = $orderPost[0]['column'];
            $column_name = $columns[$column]['data'];
            $column_order = $orderPost[0]['dir'];
        }

        if (!empty($column_name) && !empty($column_order)) {
            $order[0] = $column_name;
            $order[1] = $column_order;
        }

        $search = $request->request('search')['value'];
        $params = [];
        if (!empty($search)) {
            $searchData = array_fill_keys(array_column($columns, 'data'), $search);
            self::addFilters($where, $params, $searchData, $columnMapping);
        }

        if (!empty($filterData))
            self::addFilters($where, $params, $filterData, $columnMapping);

        $whereSql = !empty($where) ? '(' . implode($andOr, $where) . ')' : $default;

        $start = $request->request('start');
        $length = $request->request('length');
        $limit = $length != -1 ? "LIMIT $start, $length" : '';

        $sql = "SELECT $select FROM $table $joins WHERE $whereSql ORDER BY $order[0] $order[1] $limit";
        $data = DB::rawQuery($sql, $params);
        $total = DB::rawQueryFirst("SELECT COUNT(*) as total FROM $table $joins WHERE $whereSql", $params);

        $response = [];
        $response["recordsTotal"] = $total->total;
        $response["recordsFiltered"] = $total->total;
        $response['data'] = $data;
        return $response;
    }

    private static function addFilters(&$where, &$params, $data, $columnMapping): void
    {
        foreach ($data as $key => $value) {
            if (isset($columnMapping[$key])) {
                $columnData = $columnMapping[$key];
                $where[] = "$columnData LIKE ?";
                $params[] = '%' . $value . '%';
            }
        }
    }
}
