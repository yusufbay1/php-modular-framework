<?php

namespace Router;

class RouteDefinition
{
    public array $methods;
    public string $expression;
    public mixed $function;
    public array $middleware = [];
    public array $where = [];
    public ?string $name = null;

    public function middleware(array|string $middleware): self
    {
        $this->middleware = (array)$middleware;
        return $this;
    }

    public function where(array|string $key, ?string $pattern = null): self
    {
        if (is_array($key)) {
            $this->where = array_merge($this->where, $key);
        } elseif ($pattern !== null) {
            $this->where[$key] = $pattern;
        }
        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function whereNumber(string $key): self
    {
        return $this->where($key, '[0-9]+');
    }

    public function whereAlpha(string $key): self
    {
        return $this->where($key, '[a-zA-Z]+');
    }

    public function whereAlphaNumeric(string $key): self
    {
        return $this->where($key, '[a-zA-Z0-9]+');
    }

    public function whereUuid(string $key): self
    {
        return $this->where($key, '[0-9a-fA-F\-]{36}');
    }

    public function whereIn(string $key, array $values): self
    {
        $pattern = implode('|', array_map('preg_quote', $values));
        return $this->where($key, '(' . $pattern . ')');
    }
}
