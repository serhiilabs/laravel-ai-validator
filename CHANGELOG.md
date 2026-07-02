# Changelog

All notable changes to `laravel-ai-validator` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Laravel 13 support (`orchestra/testbench ^11.0`, `pestphp/pest ^4.0`).

### Removed

- **BREAKING:** Dropped Laravel 11 support. Laravel 11 reached end of security support on 2026-03-12, and all `11.x` releases are now blocked by Composer's security-advisory policy. The package now requires Laravel 12 or newer.

### Changed

- Raised framework constraints to `illuminate/* ^12.0 || ^13.0` and `orchestra/testbench ^10.0 || ^11.0`.
- Widened dev tooling to `pestphp/pest ^3.0 || ^4.0` (with Pest plugins) for Laravel 13 compatibility.
- CI matrix now tests Laravel 12 (PHP 8.2-8.5) and Laravel 13 (PHP 8.3-8.5).

## [1.1.0] - 2026-02-24

### Added

- Config-based preset registry via `AiRule::preset('name')`
- `presets` configuration option for reusable validation descriptions

## [1.0.0] - 2026-02-08

### Added

- `AiRule` validation rule with natural language descriptions
- Built-in `PrismDriver` supporting OpenAI, Anthropic, Gemini, Ollama, and 12+ providers
- Middleware pipeline: `using()`, `timeout()`, `cacheTtl()`, `withoutCache()`, `withoutRateLimit()`, `errorMessage()`
- Custom middleware support via `RuleOptionInterface`
- Result caching with configurable TTL and store
- Global rate limiting with configurable attempts and decay
- Input length limiting and XML-tag prompt injection protection
- Custom driver support via `DriverInterface`
- `AiValidatorFake` for testing with per-description expectations and assertions
- PHPStan level max, Pest tests, Pint code style
- GitHub Actions CI with PHP 8.2-8.5 and Laravel 11-12 matrix
