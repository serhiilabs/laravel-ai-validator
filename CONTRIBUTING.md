# Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Make your changes
4. Run the full check suite: `composer check`
5. Commit your changes
6. Push to your branch and open a pull request

## Development Setup

```bash
git clone https://github.com/serhiilabs/laravel-ai-validator.git
cd laravel-ai-validator
composer install
```

## Commands

```bash
composer test       # Run tests
composer analyse    # Run PHPStan (level max)
composer format     # Fix code style (Laravel Pint)
composer check      # Run all checks (lint + analyse + test)
```

## Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) with the `laravel` preset. Run `composer format` before committing.

## Static Analysis

PHPStan runs at `level: max`. All code must pass without errors.

## Testing

All new features and bug fixes must include tests. The project uses [Pest](https://pestphp.com/).

## Pull Request Guidelines

- Keep PRs focused on a single change
- Include tests for new functionality
- Ensure `composer check` passes
- Follow existing code patterns and conventions
