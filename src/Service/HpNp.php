<?php

declare(strict_types=1);

namespace App\Service;

use App\Container\EnvironmentAwareTrait;
use App\Entity\Station;
use GuzzleHttp\Client;

/**
 * Utility class for the High-Performance Now-Playing (HPNP) library.
 */
final class HpNp
{
    use EnvironmentAwareTrait;

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function isSupported(): bool
    {
        return $this->environment->isDocker() && !$this->environment->isTesting();
    }

    public function publishToStation(Station $station, mixed $message): void
    {
        $this->client->post(
            'http://localhost:6055',
            [
                'json' => [
                    'channel' => $station->getShortName(),
                    'payload' => $message,
                ],
            ]
        );
    }

    public function getSseUrl(Station $station): string
    {
        return '/api/live/nowplaying/sse?' . http_build_query(
            [
                    'cf_connect' => json_encode(
                        [
                            'subs' => [
                                $this->getChannelName($station) => [],
                                self::GLOBAL_TIME_CHANNEL => [],
                            ],
                        ],
                        JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT
                    ),
                ]
        );
    }
}