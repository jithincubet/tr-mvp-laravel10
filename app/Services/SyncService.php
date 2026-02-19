<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Sync Service
 * Handles bidirectional synchronization with external MySQL databases
 */
class SyncService
{
    protected string $externalApiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->externalApiUrl = config('tr2.external_api_url');
        $this->apiKey = config('tr2.external_api_key');
    }

    /**
     * Sync all data from external source
     */
    public function syncAll(int $clientId): array
    {
        $results = [
            'started_at' => now()->toIso8601String(),
            'entities' => [],
            'errors' => [],
        ];

        $entities = [
            'users',
            'events',
            'sessions',
            'endorsements',
            'currencies',
            'forms',
            'blocks',
        ];

        foreach ($entities as $entity) {
            try {
                $result = $this->syncEntity($clientId, $entity);
                $results['entities'][$entity] = $result;
            } catch (\Exception $e) {
                $results['errors'][$entity] = $e->getMessage();
                Log::error("Sync error for {$entity}", [
                    'client_id' => $clientId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $results['completed_at'] = now()->toIso8601String();
        return $results;
    }

    /**
     * Sync a specific entity type
     */
    public function syncEntity(int $clientId, string $entity): array
    {
        $lastSyncAt = $this->getLastSyncTime($clientId, $entity);
        
        // Fetch updates from external source
        $response = $this->fetchFromExternal($entity, $clientId, $lastSyncAt);
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch {$entity}: " . $response->body());
        }

        $data = $response->json('data', []);
        
        $created = 0;
        $updated = 0;
        
        foreach ($data as $record) {
            $result = $this->upsertRecord($entity, $record);
            if ($result === 'created') {
                $created++;
            } elseif ($result === 'updated') {
                $updated++;
            }
        }

        // Update sync timestamp
        $this->updateLastSyncTime($clientId, $entity);

        return [
            'fetched' => count($data),
            'created' => $created,
            'updated' => $updated,
        ];
    }

    /**
     * Push local changes to external source
     */
    public function pushToExternal(int $clientId, string $entity): array
    {
        $lastPushAt = $this->getLastPushTime($clientId, $entity);
        
        // Get local records modified since last push
        $model = $this->getModelForEntity($entity);
        $records = $model::where('client_id', $clientId)
            ->where('updated_at', '>', $lastPushAt ?? '1970-01-01')
            ->get();

        if ($records->isEmpty()) {
            return ['pushed' => 0];
        }

        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->externalApiUrl}/sync/{$entity}", [
                'client_id' => $clientId,
                'data' => $records->toArray(),
            ]);

        if (!$response->successful()) {
            throw new \Exception("Failed to push {$entity}: " . $response->body());
        }

        // Update push timestamp
        $this->updateLastPushTime($clientId, $entity);

        // Mark records as synced
        $model::whereIn('id', $records->pluck('id'))
            ->update(['synced_at' => now()]);

        return [
            'pushed' => $records->count(),
        ];
    }

    /**
     * Fetch data from external API
     */
    protected function fetchFromExternal(string $entity, int $clientId, ?Carbon $since = null)
    {
        $params = [
            'client_id' => $clientId,
        ];

        if ($since) {
            $params['since'] = $since->toIso8601String();
        }

        return Http::withHeaders($this->getHeaders())
            ->get("{$this->externalApiUrl}/sync/{$entity}", $params);
    }

    /**
     * Upsert a record into local database
     */
    protected function upsertRecord(string $entity, array $data): string
    {
        $model = $this->getModelForEntity($entity);
        $legacyId = $data['legacy_id'] ?? $data['id'];
        
        $existing = $model::where('legacy_id', $legacyId)->first();
        
        if ($existing) {
            $existing->update($data);
            return 'updated';
        } else {
            $data['legacy_id'] = $legacyId;
            $model::create($data);
            return 'created';
        }
    }

    /**
     * Get Eloquent model class for entity
     */
    protected function getModelForEntity(string $entity): string
    {
        $models = [
            'users' => \App\Models\User::class,
            'events' => \App\Models\Event::class,
            'sessions' => \App\Models\EventSession::class,
            'endorsements' => \App\Models\Endorsement::class,
            'currencies' => \App\Models\Currency::class,
            'forms' => \App\Models\Form::class,
            'blocks' => \App\Models\Block::class,
        ];

        if (!isset($models[$entity])) {
            throw new \InvalidArgumentException("Unknown entity: {$entity}");
        }

        return $models[$entity];
    }

    /**
     * Get HTTP headers for external API
     */
    protected function getHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Get last sync time for entity
     */
    protected function getLastSyncTime(int $clientId, string $entity): ?Carbon
    {
        $config = \App\Models\SystemConfig::where('key', "sync_{$entity}_{$clientId}_last_sync")
            ->first();
        
        return $config ? Carbon::parse($config->value) : null;
    }

    /**
     * Update last sync time for entity
     */
    protected function updateLastSyncTime(int $clientId, string $entity): void
    {
        \App\Models\SystemConfig::updateOrCreate(
            ['key' => "sync_{$entity}_{$clientId}_last_sync"],
            ['value' => now()->toIso8601String()]
        );
    }

    /**
     * Get last push time for entity
     */
    protected function getLastPushTime(int $clientId, string $entity): ?Carbon
    {
        $config = \App\Models\SystemConfig::where('key', "sync_{$entity}_{$clientId}_last_push")
            ->first();
        
        return $config ? Carbon::parse($config->value) : null;
    }

    /**
     * Update last push time for entity
     */
    protected function updateLastPushTime(int $clientId, string $entity): void
    {
        \App\Models\SystemConfig::updateOrCreate(
            ['key' => "sync_{$entity}_{$clientId}_last_push"],
            ['value' => now()->toIso8601String()]
        );
    }

    /**
     * Get sync status for a client
     */
    public function getSyncStatus(int $clientId): array
    {
        $entities = ['users', 'events', 'sessions', 'endorsements', 'currencies', 'forms', 'blocks'];
        
        $status = [];
        foreach ($entities as $entity) {
            $status[$entity] = [
                'last_sync' => $this->getLastSyncTime($clientId, $entity)?->toIso8601String(),
                'last_push' => $this->getLastPushTime($clientId, $entity)?->toIso8601String(),
            ];
        }

        return [
            'client_id' => $clientId,
            'entities' => $status,
        ];
    }
}
