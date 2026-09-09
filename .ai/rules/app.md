---
paths:
  - 'app/**'
---

# App

## Cache plain arrays only, never objects
`config/cache.php` sets `serializable_classes => false`, so every cache store refuses to unserialize objects and returns an `__PHP_Incomplete_Class` instead. Caching an object therefore works on the request that warms it and fails on every request after, which is how it slips through review.

SettingsRepository hit exactly this: it cached an `Illuminate\Support\Collection` and every later request died on the method's `: Collection` return type. Cache `->all()` and rebuild the Collection on read.

The test suite cannot catch it on its own. `phpunit.xml` sets `CACHE_STORE=array` and the array store keeps live objects in memory without serializing. A test that needs the production behaviour must set `cache.stores.array.serialize => true` plus `cache.serializable_classes => false` and then call `Cache::purge('array')` so the store is rebuilt.
