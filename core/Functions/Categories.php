<?php

namespace Core\Functions;

use Core\Database\DB;

class Categories
{
    protected static DB $db;
    protected static string $tableName;
    protected static string $idColumn;
    protected static string $titleColumn;
    protected static string $linkColumn;
    protected static string $parentIdColumn;
    protected static string $statusColumn = 'durum';
    protected static mixed $statusValue = 1;
    protected static string $orderByColumn = 'sira';

    public static function configure(array $config): void
    {
        self::$tableName = $config['table'];
        self::$idColumn = $config['id'];
        self::$titleColumn = $config['title'];
        self::$linkColumn = $config['link'];
        self::$parentIdColumn = $config['parent_id'];
        self::$statusColumn = $config['status_column'] ?? 'durum';
        self::$statusValue = $config['status_value'] ?? 1;
        self::$orderByColumn = $config['order_by'] ?? self::$idColumn;
    }

    public static function setDB($dbInstance): void
    {
        self::$db = $dbInstance;
    }

    public static function category(): array|false
    {
        $columns = implode(',', [
            self::$idColumn,
            self::$titleColumn,
            self::$linkColumn,
            self::$parentIdColumn
        ]);

        return self::$db::table(self::$tableName)
            ->select($columns)
            ->where(self::$statusColumn, self::$statusValue)
            ->orderBy([self::$orderByColumn])
            ->get();
    }

    private static function categoryTree($elements, int|string $parent_id = 0): array
    {
        $branch = [];
        foreach ($elements ?? [] as $element) {
            if ($element->{self::$parentIdColumn} == $parent_id) {
                $children = self::categoryTree($elements, $element->{self::$idColumn});
                $branch[] = [
                    self::$idColumn => $element->{self::$idColumn},
                    self::$titleColumn => $element->{self::$titleColumn},
                    self::$linkColumn => $element->{self::$linkColumn},
                    self::$parentIdColumn => $element->{self::$parentIdColumn},
                    'children' => $children,
                ];
            }
        }
        return $branch;
    }

    private static function categoryDraw(array $items, callable $renderer): string
    {
        $output = '';
        foreach ($items as $item) {
            $output .= $renderer((object)$item, function ($children) use ($renderer) {
                return self::categoryDraw($children, $renderer);
            });
        }
        return $output;
    }

    public static function viewCategories(callable $renderer): string
    {
        $tree = self::categoryTree(self::category());
        return self::categoryDraw($tree, $renderer);
    }

    public static function getTree(bool $asJson = true): string|array
    {
        $tree = self::categoryTree(self::category());
        return $asJson ? json_encode($tree, JSON_UNESCAPED_UNICODE) : $tree;
    }

    public static function flatList(bool $asJson = true): array|string
    {
        $tree = self::categoryTree(self::category());

        $flat = [];

        $flatten = function (array $items, int $level = 0) use (&$flatten, &$flat) {
            foreach ($items as $item) {
                $flat[] = [
                    'id' => $item['id'],
                    'title' => str_repeat('— ', $level) . $item['title'],
                    'link' => $item['link'],
                    'parent_id' => $item['parent_id'],
                    'level' => $level
                ];
                if (!empty($item['children'])) {
                    $flatten($item['children'], $level + 1);
                }
            }
        };

        $flatten($tree);

        return $asJson ? json_encode($flat, JSON_UNESCAPED_UNICODE) : $flat;
    }

    public static function toArray(array $filters = [], bool $asJson = true): array|string
    {
        $query = self::$db::table(self::$tableName)->select('*');

        foreach ($filters as $column => $value) {
            $query->where($column, $value);
        }

        $query->orderBy([self::$orderByColumn]);

        $result = $query->get();

        return $asJson ? json_encode($result, JSON_UNESCAPED_UNICODE) : $result;
    }
}

/*

Category::setDB(new DB());

Category::configure([
    'table'      => 'portal_kategoriler',
    'id'         => 'id',
    'title'      => 'adi',
    'link'       => 'link',
    'parent_id'  => 'ust_id',
    'status_column' => 'durum',
    'status_value'  => 1,
    'order_by'      => 'sira'
]);
 HTML Menü İçin:

Category::viewCategories(function ($item, $childrenRender) {
    $childHTML = !empty($item->children)
        ? '<ul>' . $childrenRender($item->children) . '</ul>'
        : '';
    return "<li><a href='/$item->link'>$item->title</a>$childHTML</li>";
});
JSON Tree:
Category::getTree(); // JSON olarak çıktı verir

PHP'de düz liste olarak:
Category::flatList(false);

Vue / React için::
Category::flatList(); // JSON olarak çıktı verir

Tüm veriyi ham halde al:
Category::toArray([], false);

Sadece ust_id = 0 olanları al:
Category::toArray(['ust_id' => 0], false);

JSON çıktısını frontend'e gönder:
Category::toArray();
*/
