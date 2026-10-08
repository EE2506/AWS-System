<?php

namespace App\Services\Analytics;

use Illuminate\Database\Connection;

class DateBucket
{
    public function __construct(private readonly Connection $connection) {}

    public function expression(string $column, string $granularity): string
    {
        $wrapped = $this->connection->getQueryGrammar()->wrap($column);

        return match ($this->connection->getDriverName()) {
            'sqlite' => match ($granularity) {
                'month' => "strftime('%Y-%m', {$wrapped})",
                'week' => "strftime('%Y-%W', {$wrapped})",
                default => "strftime('%Y-%m-%d', {$wrapped})",
            },
            'mysql' => match ($granularity) {
                'month' => "DATE_FORMAT({$wrapped}, '%Y-%m')",
                'week' => "DATE_FORMAT({$wrapped}, '%x-%v')",
                default => "DATE_FORMAT({$wrapped}, '%Y-%m-%d')",
            },
            default => "CAST({$wrapped} AS DATE)",
        };
    }

    public function weekday(string $column): string
    {
        $wrapped = $this->connection->getQueryGrammar()->wrap($column);

        return $this->connection->getDriverName() === 'sqlite'
            ? "strftime('%w', {$wrapped})"
            : "DAYOFWEEK({$wrapped}) - 1";
    }

    public function hour(string $column): string
    {
        $wrapped = $this->connection->getQueryGrammar()->wrap($column);

        return $this->connection->getDriverName() === 'sqlite'
            ? "strftime('%H', {$wrapped})"
            : "HOUR({$wrapped})";
    }
}
