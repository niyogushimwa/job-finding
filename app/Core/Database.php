<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

/** Thin, prepared-statement-only database layer. */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $cfg = config('database');
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']);
            try {
                self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                ]);
            } catch (PDOException $e) {
                $hint = 'Please create the database and import database/schema.sql (see docs/INSTALLATION.md).';
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage() . ' ' . $hint);
            }
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $statement = self::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $param = is_int($key) ? $key + 1 : (str_starts_with($key, ':') ? $key : ':' . $key);
            if ($value === null) {
                $statement->bindValue($param, null, PDO::PARAM_NULL);
            } elseif (is_bool($value)) {
                $statement->bindValue($param, $value, PDO::PARAM_BOOL);
            } elseif (is_int($value)) {
                $statement->bindValue($param, $value, PDO::PARAM_INT);
            } else {
                $statement->bindValue($param, (string) $value, PDO::PARAM_STR);
            }
        }
        $statement->execute();
        return $statement;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function scalar(string $sql, array $params = []): mixed
    {
        $value = self::query($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }

    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`, `', $columns),
            implode(', ', array_map(static fn($c) => ':' . $c, $columns))
        );
        self::query($sql, $data);
        return (int) self::connection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $params = []): int
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "`$column` = :set_$column";
        }
        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, implode(', ', $sets), $where);
        $bound = [];
        foreach ($data as $key => $value) {
            $bound['set_' . $key] = $value;
        }
        foreach ($params as $key => $value) {
            $bound[$key] = $value;
        }
        return self::query($sql, $bound)->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        return self::query(sprintf('DELETE FROM `%s` WHERE %s', $table, $where), $params)->rowCount();
    }

    public static function count(string $table, string $where = '1=1', array $params = []): int
    {
        return (int) self::scalar("SELECT COUNT(*) FROM `$table` WHERE $where", $params);
    }

    public static function begin(): void { self::connection()->beginTransaction(); }
    public static function commit(): void { self::connection()->commit(); }
    public static function rollBack(): void
    {
        if (self::connection()->inTransaction()) {
            self::connection()->rollBack();
        }
    }

    /** Safe dynamic IN clause helper. */
    public static function in(array $values): array
    {
        $placeholders = [];
        $params = [];
        foreach (array_values($values) as $i => $value) {
            $key = 'in' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $value;
        }
        return [implode(',', $placeholders), $params];
    }
}
