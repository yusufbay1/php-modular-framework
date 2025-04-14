<?php

namespace Core\Database;

class Schema
{
    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $existingColumns = self::getExistingColumns($table);

        if (empty($existingColumns)) {
            $sql = $blueprint->toSql();
            try {
                DB::rawQueryFirst($sql);
                echo "Tablo oluşturuldu ve tüm kolonlar eklendi.\n";
            } catch (\Exception $e) {
                echo "Tablo oluşturulamadı: " . $e->getMessage() . "\n";
            }
        } else {
            // Tablo varsa eksik kolonları ve indeksleri ekle
            self::updateTable($table, $blueprint, $existingColumns);
        }
    }

    private static function getExistingColumns(string $table): array
    {
        try {
            $result = DB::rawQuery("SHOW COLUMNS FROM {$table}");
            return array_column($result, 'Field');
        } catch (\Exception $e) {
            return [];
        }
    }

    private static function updateTable(string $table, Blueprint $blueprint, array $existingColumns): void
    {
        $queries = [];
        $existingIndexes = self::getExistingIndexes($table);
        $getColumns = $blueprint->getColumns();

        foreach ($getColumns as $column) {
            if (!in_array($column['name'], $existingColumns)) {
                $queries[] = "ADD COLUMN {$column['definition']}";
            }
        }

        foreach ($blueprint->getUniques() as $unique) {
            preg_match('/\((.*?)\)/', $unique, $matches);
            $columnNames = explode(',', $matches[1]) ?? null;

            $indexNames = $columnNames[0] . '_' . $columnNames[1] . '_unique';

            if ($columnNames) {
                $checkIndex = DB::rawQuery("SELECT COUNT(1) as index_exists FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = '{$table}' AND index_name = '{$indexNames}'");
                if (!empty($checkIndex) && $checkIndex[0]->index_exists > 0) {
                    DB::rawQuery("ALTER TABLE {$table} DROP INDEX {$indexNames}");
                }
                $unique = "CONSTRAINT {$indexNames} UNIQUE $matches[0]";
                $queries[] = "ADD {$unique}"; // Yeni UNIQUE index'i ekle
            }
        }

        foreach ($blueprint->getIndexes() as $index) {
            preg_match('/\((.*?)\)/', $index, $matches);
            $columnName = $matches[1] ?? null;

            if ($columnName && !isset($existingIndexes[$columnName])) {
                $queries[] = "ADD {$index}";
            }
        }

        if (!empty($queries)) {
            $alterSql = "ALTER TABLE {$table} " . implode(", ", $queries) . ";";
            try {
                DB::rawQueryFirst($alterSql);
                echo "Tablo güncellendi ve eksik kolonlar ve indeksler eklendi.\n";
            } catch (\Exception $e) {
                echo "Tablo güncellenemedi: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Tablo zaten güncel.\n";
        }
    }

    private static function getExistingUniques(string $table): array
    {
        try {
            $result = DB::rawQuery("SHOW INDEX FROM {$table} WHERE Non_unique = 0");

            $uniques = [];
            foreach ($result as $row) {
                $row = (array)$row;
                $uniques[$row['Key_name']] = $row['Column_name']; // UNIQUE key'leri sakla
            }

            return $uniques;
        } catch (\Exception $e) {
            return [];
        }
    }

    private static function generateUniqueKeyName(string $columnNames): string
    {
        $columns = explode(',', str_replace(' ', '', $columnNames));
        [$one, $two] = $columns;
        $limited = $one . '_' . $two . '_unique';
        return $limited;
    }

    private static function getExistingIndexes(string $table): array
    {
        try {
            $result = DB::rawQuery("SHOW INDEX FROM {$table}");

            $indexes = [];
            foreach ($result as $row) {
                $row = (array)$row; // stdClass nesnesini diziye çevir
                $indexes[$row['Column_name']] = $row['Key_name']; // Kolon adına göre indeksleri sakla
            }

            return $indexes;
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function drop(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS `{$table}`;";
        try {
            DB::rawQueryFirst($sql);
            echo "Tablo silindi\n";
        } catch (\Exception $e) {
            echo "Tablo silinemedi\n" . $e->getMessage() . "\n";
        }
    }
}