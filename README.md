# MataKo MVP

MataKo is a simple digital eye strain (DES) self-assessment prototype for students and working professionals. It includes a Laravel JSON API secured with Sanctum and a Flutter client.

## Project layout

```text
backend/                 Laravel application source to copy into a Laravel 10+ project
  app/Models/
  app/Http/Controllers/Api/
  database/migrations/
  routes/api.php
mobile/                  Flutter application
  lib/models/
  lib/screens/
  lib/services/
```

## Backend setup

The `backend/` folder contains the Laravel application files for this MVP, not Laravel's generated framework boilerplate. Create a Laravel 11+ project in a separate directory, then copy the supplied `backend/app`, `backend/database/migrations`, and `backend/routes/api.php` files into it. From that Laravel project directory, install Sanctum and configure MySQL in `.env`:

```sh
composer require laravel/sanctum
php artisan install:api
php artisan migrate
php artisan serve
```

If using an existing Laravel install, ensure Sanctum's `personal_access_tokens` migration is present. The supplied user migration adds `age` and `role` to Laravel's default `users` table. If your project has a custom users migration, add those columns there and omit the supplied `add_age_and_role_to_users` migration.

Endpoints:

| Method | Path | Auth | Purpose |
| --- | --- | --- | --- |
| POST | `/api/register` | No | Create an account and return a token |
| POST | `/api/login` | No | Sign in and return a token |
| GET | `/api/user` | Bearer token | Current user |
| POST | `/api/assessment` | Bearer token | Save answers, score and recommendations |
| GET | `/api/assessments` | Bearer token | Assessment history |
| GET | `/api/assessment/{id}` | Bearer token | One assessment and its answers |

Each answer is an integer from 0 (None) to 3 (Always). The five values are summed: 0–5 LOW, 6–10 MEDIUM, and 11–15 HIGH. Recommendations are returned with saved assessment data.

## Flutter setup

```sh
cd mobile
flutter create .
flutter pub get
flutter run
```

Run `flutter create .` once to generate the standard Android/iOS runner folders around the supplied `lib/` app code.

The default API address is `http://10.0.2.2:8000/api`, which reaches the host machine from an Android emulator. Change `ApiService.baseUrl` in `mobile/lib/services/api_service.dart` for an iOS simulator, physical device, or deployed backend. Use HTTPS for a deployed service.

The dashboard's 20-minute reminder is a foreground timer and displays an in-app message while the app is open. Native background notifications require platform notification configuration and are outside this small prototype.

## Notes

This is an educational self-assessment, not a diagnosis or medical device. A high result suggests seeking advice from an eye care professional. Passwords are hashed by Laravel; the mobile app stores the Sanctum token locally with `shared_preferences` for this MVP.
