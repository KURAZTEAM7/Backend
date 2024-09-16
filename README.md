# PriceCompare Backend

## Setup

Clone the repo

```
git clone https://github.com/KURAZTEAM7/Backend
```

Install composer dependencies

```
composer install
```

Install npm dependencies

```
npm install
```

Copy the `.env` file, and setup the database there as needed (default is `sqlite`)

```
cp .env.example .env
```

> [!NOTE]
> To enable debug info, go to your .env file set the environment variable `APP_DEBUG` to `true`

> [!WARNING]
> Never set `APP_DEBUG` to `true` in production

Generate app encryption key

```
php artisan key:generate
```

Perfrom database migrations

```
php artisan migrate
```

Add necessary symbolic links for storing files

```
php artisan storage:link
```

To use the dev server, run the following commands in a persistent terminal window

```
php artisan serve
```

```
npm run dev
```

Read the api [API documentation](https://kurazteam7.github.io/Backend/)
