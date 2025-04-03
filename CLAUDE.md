# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build Commands
- Install dependencies: `composer install && npm install`
- Development: `php artisan serve` and `npm run dev`
- Build assets: `npm run build`
- Run all tests: `php artisan test`
- Run single test: `php artisan test --filter=TestName`
- Code linting: `./vendor/bin/pint`

## Code Style
- PSR-4 autoloading for PHP classes
- Follow Laravel coding style guidelines (PSR-2/12 based)
- Use camelCase for methods and variables, PascalCase for classes
- Model relationships: hasMany(), belongsTo(), etc. as defined by Laravel conventions
- Type-hint parameters and return types in PHP 8.1+ style
- Handle exceptions using Laravel's built-in exception handling
- Filament UI components follow the TALL stack conventions (Tailwind, Alpine, Laravel, Livewire)
- Use dependency injection and Laravel's service container for dependencies