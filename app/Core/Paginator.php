<?php
declare(strict_types=1);

namespace App\Core;

final class Paginator
{
    public int $total;
    public int $perPage;
    public int $current;
    public int $last;
    public int $offset;

    public function __construct(int $total, int $perPage, int $current)
    {
        $this->total = $total;
        $this->perPage = max(1, $perPage);
        $this->last = max(1, (int) ceil($total / $this->perPage));
        $this->current = max(1, min($current, $this->last));
        $this->offset = ($this->current - 1) * $this->perPage;
    }

    public function link(int $page, array $overrides = []): string
    {
        $query = array_merge($_GET, $overrides, ['page' => $page]);
        unset($query['_csrf']);
        return url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/') . '?' . http_build_query($query);
    }

    public function pages(): array
    {
        $start = max(1, $this->current - 2);
        $end = min($this->last, $start + 4);
        $start = max(1, $end - 4);
        $range = [];
        for ($p = $start; $p <= $end; $p++) {
            $range[] = $p;
        }
        return $range;
    }

    public function summary(): string
    {
        if ($this->total === 0) {
            return '0 results';
        }
        $from = $this->offset + 1;
        $to = min($this->total, $this->offset + $this->perPage);
        return $from . '–' . $to . ' of ' . $this->total;
    }
}
