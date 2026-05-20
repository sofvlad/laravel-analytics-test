<?php

namespace App\Repositories;

use App\Models\Visit;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

interface VisitRepositoryInterface
{
    /**
     * Сохранить посещение
     *
     * @param array $data
     * @return Visit
     */
    public function save(array $data): Visit;

    /**
     * Получить почасовую статистику за последние N дней
     *
     * @param int $days
     * @return Collection
     */
    public function getHourlyStats(int $days): Collection;

    /**
     * Получить статистику по городам за последние N дней
     *
     * @param int $days
     * @return Collection
     */
    public function getCityStats(int $days): Collection;
}
