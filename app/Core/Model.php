<?php
declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function table(): string { return static::$table; }

    public static function find(int|string $id): ?array
    {
        return Database::one('SELECT * FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = :id', ['id' => $id]);
    }

    public static function all(string $order = 'id DESC', int $limit = 1000): array
    {
        return Database::all('SELECT * FROM `' . static::$table . '` ORDER BY ' . $order . ' LIMIT ' . $limit);
    }

    public static function where(string $column, mixed $value, string $order = 'id DESC'): array
    {
        return Database::all("SELECT * FROM `" . static::$table . "` WHERE `$column` = :v ORDER BY $order", ['v' => $value]);
    }

    public static function create(array $data): int
    {
        return Database::insert(static::$table, $data);
    }

    public static function update(int $id, array $data): int
    {
        return Database::update(static::$table, $data, static::$primaryKey . ' = :id', ['id' => $id]);
    }

    public static function destroy(int $id): int
    {
        return Database::delete(static::$table, static::$primaryKey . ' = :id', ['id' => $id]);
    }

    public static function countWhere(string $where = '1=1', array $params = []): int
    {
        return Database::count(static::$table, $where, $params);
    }
}
