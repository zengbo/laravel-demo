<?php

namespace App\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheController extends Controller
{
    public function create(Request $request): void
    {
        $key = $request->get("key");
        Cache::tags(['test_tag'])->remember($key, 86400, fn (): string => $key);
    }

    public function delete(): void
    {
        Cache::tags(['test_tag'])->flush();
    }

    public function check(): Response
    {
        /** @var \Illuminate\Cache\RedisTaggedCache $redis_tagged_cache */
        $redis_tagged_cache = Cache::tags(['test_tag']);
        /** @var \Illuminate\Cache\RedisTagSet redis_tag_set */
        $redis_tag_set = $redis_tagged_cache->getTags();

        $keys_in_tags = $redis_tag_set->entries();
        $cache_tagged_item_key_prefix = sha1($redis_tag_set->getNamespace());

        $cache_tagged = self::getTaggedCacheByPrefix($cache_tagged_item_key_prefix);

        if (count($cache_tagged) != $keys_in_tags->count()) {
            return response()->json(['result' => 'inconsistent']);
        } else {
            return response()->json(['result' => 'consistent']);
        }
    }

    private static function getTaggedCacheByPrefix(string $prefix): array
    {
        /** @var \Illuminate\Cache\RedisStore $redis_store */
        $redis_store = Cache::store('redis');
        $cursor = $defaultCursorValue = '0';
        $tagged_caches = [];
        do {
            [$cursor, $entries] = $redis_store->connection()->scan(
                $cursor,
                ['match' => '*' . $prefix . '*', 'count' => 1000]
            );

            if (! is_array($entries)) {
                break;
            }

            $entries = array_unique(array_keys($entries));

            if (count($entries) === 0) {
                continue;
            }

            foreach ($entries as $entry) {
                $tagged_caches[] = $entry;
            }
        } while (((string) $cursor) !== $defaultCursorValue);

        return $tagged_caches;
    }
}
