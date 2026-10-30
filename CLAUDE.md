# Campus Resource Hub

A Laravel 13 REST API where students upload study resources, tag them, vote, and comment.
This is a 4th-year university project. I must be able to explain every line to my supervisor.

## Stack
- Laravel 13, PHP 8.5
- SQLite for now, MySQL later (keep the data layer engine-agnostic)
- Laravel Sanctum for token authentication
- Tested with Apidog

## Rules for Claude
- Explain before you code. Keep it simple and idiomatic Laravel — no clever tricks.
- Prefer built-in Laravel features (Form Requests, API Resources, Policies) over custom code.
- No raw SQL. Use migrations and Eloquent only.
- No extra packages unless I approve them first.
- Use `php artisan make:` commands rather than hand-writing boilerplate files.
- After each change, list the files you touched and one sentence on why.
