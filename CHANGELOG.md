CHANGELOG
---

## 4.3.1
- Fix deprecated alert matcher sort

## 4.3.0
- Use [VSR\\Extend\\Caller](https://github.com/vitorsreis/extend-caller)
- Cache improvements
  - Add permission control for cache prefixes
    - flags: FLAG_ROUTER, FLAG_MATCH, FLAG_EXECUTE, FLAG_OTHERS, FLAG_ALL
    - allowCache(flag)
    - disallowCache(flag)
- Fix property cached in match result
- Fix PSR-12

## 4.2.0
- Added router map builder/store in cache
- Cache improvements
  - Fix get File Cache

## 4.1.1
- Fix variable params
- Fix add filter
- Performance improvements
  - Fix unique added route index in route collection variable tree
  - Fix match indexes tree
  - Fix match middlewares order
- Cache improvements
  - Fix cache get value/default

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
- Fix filter parser delimiter from / to ~
- Add exception NotFoundException and MethodNotAllowedException

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
