CHANGELOG
---

## 4.1.0
- Add router group
- Cache improvements
  - Add execution hash ($context->header->hash)
  - Add clear() method
  - Add match result to cache
  - Add execution result to cache
  - Add APCU cache
  - Add File cache
  - Add Redis cache
  - Add Memcache cache
  - Add Memcached cache

## 4.0.0
- Downgrade for PHP 5.6+ usage
- PSR-12 code style
- Add throw reserved name "context" from router
- Add callback on context execute
- Fix caller optional params
- Fix cursor position

## 3.0.2
- Performance improvements
- Fix benchmark

## 3.0.1
- Change filter syntax: **var|filter** to **var\[filter]**
- Use loose filter in routes **\[filter]**
- Fix params mapping
- Fix static router indexer

## 3.0.0
- Full restructuring
