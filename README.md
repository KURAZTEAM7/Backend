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

Create a `database.sqlite` file in the `database` folder

```
touch database/database.sqlite
```

Perfrom database migrations

```
php artisan migrate
```

Add necessary symbolic links for storing files

```
php artisan storage:link
```

Open a [Cloudinary](https://cloudinary.com/) account to enable image uploads.
Go to the [Cloudinary dashboard](https://cloudinary.com/console) to get the
API environment variable, then copy it to you local .env file as follows:

```
CLOUDINARY_URL=cloudinary://xxxxxxxxxxxxxxxxxxx

```

To use the dev server, run the following commands in a persistent terminal window

```
php artisan serve
```

```
npm run dev
```

Read the api [API documentation](https://kurazteam7.github.io/Backend/)
