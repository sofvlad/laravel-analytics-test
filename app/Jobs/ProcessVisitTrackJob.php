<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\NominatimOpenstreetmapService;
use App\Services\TwoIpService;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Throwable;
use Illuminate\Contracts\Container\BindingResolutionException;

class ProcessVisitTrackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private string $ip;

    /**
     * @var string
     */
    private string $userAgent;

    /**
     * @var float|null
     */
    private ?float $latitude;

    /**
     * @var float|null
     */
    private ?float $longitude;

    /**
     * @var CarbonInterface
     */
    private CarbonInterface $visitedAt;

    public function __construct(
        string $ip,
        string $userAgent,
        ?float $latitude = null,
        ?float $longitude = null,
        ?CarbonInterface $visitedAt = null,
    ) {
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->visitedAt = $visitedAt ?? Carbon::now();
    }

    /**
     * @param NominatimOpenstreetmapService $nominatimOpenstreetmapService
     * @return void
     */
    public function handle(
        NominatimOpenstreetmapService $nominatimOpenstreetmapService
    ): void {
        try {
            if ($this->latitude !== null && $this->longitude !== null) {
                $visit = $nominatimOpenstreetmapService->store(
                    $this->ip,
                    $this->userAgent,
                    $this->latitude,
                    $this->longitude,
                    $this->visitedAt
                );
            } else {
                try {
                    $twoIpService = app(TwoIpService::class);
                    $visit = $twoIpService->store($this->ip, $this->userAgent, $this->visitedAt);
                } catch (BindingResolutionException) {
                    Log::warning('TwoIpService skipped: token not configured');

                    return;
                }
            }

            Log::debug('Geo data is successfully processed', $visit->toArray());
        } catch (Throwable $e) {
            Log::error('Geo data processing failed', [
                'ip' => $this->ip,
                'userAgent' =>$this->userAgent,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

