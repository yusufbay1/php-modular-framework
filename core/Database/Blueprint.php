<?php

namespace Core\Database;

class Blueprint
{
    private string $table;
    private array $columns = [];
    private array $indexes = [];
    private array $uniques = [];
    private ?string $lastColumnName = null;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(): void
    {
        $this->columns[] = ['name' => 'id', 'definition' => 'id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY'];
        $this->lastColumnName = 'id';
    }

    public function string(string $column, $length = 255): self
    {
        $this->columns[] = ['name' => $column, 'definition' => "$column VARCHAR($length) DEFAULT NULL"];
        $index = "INDEX ($column)";
        if (!in_array($index, $this->indexes))
            $this->indexes[] = $index;

        $this->lastColumnName = $column;

        return $this;
    }

    public function integer(string $column, string $dataType = 'INT', int $length = 11): self
    {
        $this->columns[] = ['name' => $column, 'definition' => "$column $dataType($length) UNSIGNED DEFAULT NULL"];
        $index = "INDEX ($column)";
        if (!in_array($index, $this->indexes))
            $this->indexes[] = $index;

        $this->lastColumnName = $column;
        return $this;
    }

    public function float(string $column): self
    {
        $this->columns[] = ['name' => $column, 'definition' => "$column FLOAT UNSIGNED DEFAULT NULL"];
        $index = "INDEX ($column)";
        if (!in_array($index, $this->indexes))
            $this->indexes[] = $index;

        $this->lastColumnName = $column;
        return $this;
    }

    public function boolean(string $column): self
    {
        $this->columns[] = ['name' => $column, 'definition' => "$column BOOLEAN DEFAULT 0"];
        $index = "INDEX ($column)";
        if (!in_array($index, $this->indexes)) {
            $this->indexes[] = $index;
        }
        $this->lastColumnName = $column;
        return $this;
    }

    public function text(string $column, $type = 'TEXT'): self
    {
        $this->columns[] = ['name' => $column, 'definition' => "$column $type DEFAULT NULL"];
        $index = "FULLTEXT ($column)";
        if (!in_array($index, $this->indexes)) {
            $this->indexes[] = $index;
        }
        $this->lastColumnName = $column;
        return $this;
    }

    public function unique(mixed $column): void
    {
        $columnList = is_array($column) ? implode(', ', $column) : $column;
        $uniqueName = str_replace(',', '_', str_replace(' ', '', $columnList)) . '_unique';

        if (!in_array("CONSTRAINT $uniqueName UNIQUE ($columnList)", $this->uniques)) {
            $this->uniques[] = "CONSTRAINT $uniqueName UNIQUE ($columnList)";
        }
    }

    private function uniqueMultiple(array $columns): void
    {
        $columnsList = implode(', ', $columns);
        $indexName = implode('_', $columns) . '_unique';
        $this->uniques[] = "CONSTRAINT $indexName UNIQUE ($columnsList)";
    }

    public function datetime(string $column): self
    {
        $this->columns[] = ['name' => $column, 'definition' => "$column DATETIME"];
        $index = "INDEX ($column)";
        if (!in_array($index, $this->indexes))
            $this->indexes[] = $index;

        $this->lastColumnName = $column;

        return $this;
    }

    public function timestamps(): self
    {
        $this->columns[] = ['name' => 'created_at', 'definition' => "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"];
        $this->columns[] = ['name' => 'updated_at', 'definition' => "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"];
        $this->indexes[] = "INDEX (created_at)";
        $this->indexes[] = "INDEX (updated_at)";
        $this->lastColumnName = 'updated_at';
        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function getUniques(): array
    {
        return $this->uniques;
    }

    public function after(string $column): void
    {
        $this->columns = array_map(function ($item) use ($column) {
            if ($item['name'] === $this->lastColumnName) {
                $item['definition'] .= " AFTER $column";
            }
            return $item;
        }, $this->columns);

    }

    public function toSql(): string
    {
        $columns = array_column($this->columns, 'definition');
        $indexes = !empty($this->indexes) ? ", " . implode(", ", $this->indexes) : "";
        $uniques = !empty($this->uniques) ? ", " . implode(", ", $this->uniques) : "";
        return "CREATE TABLE {$this->table} (" . implode(", ", $columns) . $indexes . $uniques . ");";
    }
}