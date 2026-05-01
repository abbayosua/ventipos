<?php

namespace App\Core;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    public static function all(): array
    {
        return Database::fetchAll("SELECT * FROM " . static::$table . " WHERE is_active = 1 ORDER BY id DESC");
    }

    public static function find(int $id)
    {
        return Database::fetch(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
    }

    public static function where(string $column, mixed $value): array
    {
        return Database::fetchAll(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ? ORDER BY id DESC",
            [$value]
        );
    }

    public static function whereFirst(string $column, mixed $value)
    {
        return Database::fetch(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ? LIMIT 1",
            [$value]
        );
    }

    public static function companyScope(int $companyId): array
    {
        return Database::fetchAll(
            "SELECT * FROM " . static::$table . " WHERE company_id = ? AND is_active = 1 ORDER BY id DESC",
            [$companyId]
        );
    }

    public static function outletScope(int $outletId): array
    {
        return Database::fetchAll(
            "SELECT * FROM " . static::$table . " WHERE outlet_id = ? AND is_active = 1 ORDER BY id DESC",
            [$outletId]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert(static::$table, $data);
    }

    public static function updateRecord(int $id, array $data): int
    {
        return Database::update(static::$table, $data, static::$primaryKey . " = ?", [$id]);
    }

    public static function deleteRecord(int $id): int
    {
        return Database::update(static::$table, ['is_active' => 0], static::$primaryKey . " = ?", [$id]);
    }

    public static function count(): int
    {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . static::$table . " WHERE is_active = 1");
        return (int) $result->count;
    }

    public static function raw(string $sql, array $params = []): array
    {
        return Database::fetchAll($sql, $params);
    }

    public static function rawFirst(string $sql, array $params = [])
    {
        return Database::fetch($sql, $params);
    }
}
