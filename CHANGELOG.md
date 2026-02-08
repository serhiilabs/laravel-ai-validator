# Changelog

All notable changes to `laravel-ai-validator` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
