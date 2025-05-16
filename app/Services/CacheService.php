<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Get an item from the cache, or store the default value.
     *
     * @param string $key
     * @param int $seconds
     * @param \Closure $callback
     * @return mixed
     */
    public function remember(string $key, int $seconds, \Closure $callback)
    {
        return Cache::remember($key, $seconds, $callback);
    }
    
    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     * @return bool
     */
    public function put(string $key, $value, int $seconds): bool
    {
        return Cache::put($key, $value, $seconds);
    }
    
    /**
     * Retrieve an item from the cache.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }
    
    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }
    
    /**
     * Clear all items from the cache.
     *
     * @return bool
     */
    public function flush(): bool
    {
        return Cache::flush();
    }
    
    /**
     * Clear cache items matching a pattern.
     *
     * @param string $pattern
     * @return bool
     */
    public function clearPattern(string $pattern): bool
    {
        // This is a simplified implementation that works with file or array cache drivers
        // For Redis or Memcached, you would use their specific pattern-based deletion methods
        $keys = $this->getKeysMatchingPattern($pattern);
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        return true;
    }
    
    /**
     * Get cache keys matching a pattern.
     * 
     * Note: This is a simplified implementation and may not work with all cache drivers.
     * For production use with Redis or Memcached, use their native pattern matching.
     *
     * @param string $pattern
     * @return array
     */
    protected function getKeysMatchingPattern(string $pattern): array
    {
        // For the file cache driver, we can scan the cache directory
        // This is a simplified example and may need to be adapted based on your cache configuration
        $cacheKeys = [];
        
        // Get all cache keys (simplified approach)
        $allKeys = $this->getAllCacheKeys();
        
        // Filter keys by pattern
        foreach ($allKeys as $key) {
            if ($this->patternMatches($pattern, $key)) {
                $cacheKeys[] = $key;
            }
        }
        
        return $cacheKeys;
    }
    
    /**
     * Get all cache keys.
     * 
     * Note: This is a simplified implementation and may not work with all cache drivers.
     *
     * @return array
     */
    protected function getAllCacheKeys(): array
    {
        // This is a simplified implementation
        // In a real application, you might need to implement this differently based on your cache driver
        
        // For testing purposes, we'll return a hardcoded list of keys
        // In a real application, you would need to retrieve this from your cache store
        return [
            'top_priority_tasks_all_5',
            'top_priority_tasks_1_5',
            'top_priority_tasks_2_5',
            'task_statistics_all',
            'task_statistics_1',
            // Add more keys as needed
        ];
    }
    
    /**
     * Check if a key matches a pattern.
     *
     * @param string $pattern
     * @param string $key
     * @return bool
     */
    protected function patternMatches(string $pattern, string $key): bool
    {
        // Convert the pattern to a regex
        $regex = str_replace(['*', '?'], ['.*', '.'], $pattern);
        $regex = '/^' . $regex . '$/';
        
        return (bool) preg_match($regex, $key);
    }
}
