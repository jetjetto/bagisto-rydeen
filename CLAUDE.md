# CLAUDE.md

This file provides guidance to Claude Code when working with this repository.

## Project Overview

Rydeen dealer-facing portal built on Bagisto v2.3.16 + B2B Suite. B2B portal for car accessory dealers, not a consumer storefront.

## Prerequisites

- PHP 8.2+, Composer, Node.js 18+, MySQL 8.0+

## Architecture

- **Base:** Bagisto v2.3.16 with `bagisto/b2b-suite` package
- **Custom packages:** `packages/Rydeen/` — Core, Auth, Pricing, Dealer
- **Do NOT modify** `vendor/` or `packages/Webkul/` — use overrides, listeners, view publishing
- **Repository pattern:** Use repositories for data access
- **Events:** Hook into Bagisto events rather than modifying core

## Server / Runtime

- **Use `php artisan serve` for development and current Railway deployment.** Do NOT use Laravel Octane.
- **Why not Octane:** Webkul packages rely heavily on static properties and singleton caches (`Core::$singletonInstances`, `Acl::$aclConfig`, `SystemConfig::$items`, `Price::$typeIndexers`) that persist across requests under Octane, causing stale data and potential cross-dealer data leakage. The `config/octane.php` `flush` array is empty, and fixing this requires modifying `packages/Webkul/` (which we must not do). Stack traces and error logging are also unreliable under Octane/FrankenPHP.
- **Octane dependency:** `laravel/octane` remains in `composer.json` — it's harmless when unused. Do not remove it; Bagisto v2.3 ships with it.
- **Future scaling path:** If traffic outgrows `artisan serve`, migrate to **Nginx + PHP-FPM** (Bagisto's official production recommendation), not Octane.

## Deployment

When the user says "deploy": run `railway up` first, then commit and push to GitHub. Always use Railway CLI, never the web dashboard.

## Common Commands

```bash
php artisan serve              # dev server at localhost:8000
php artisan test packages/Rydeen/  # run Rydeen tests
php artisan optimize:clear     # clear all caches
railway up                     # deploy to Railway
```
