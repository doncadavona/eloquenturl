
# Eloquenturl

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

Eloquenturl automatically adds search and filtering for your Eloquent models using [query strings](https://en.wikipedia.org/wiki/Query_string).

Eloquenturl respects the `$fillable` and `$hidden` attributes of your Eloquent models to make sensible defaults, providing zero-configuration usage. For example:

```php
return \Doncadavona\Eloquenturl\Eloquenturl::eloquenturled(\App\Models\User::class, $request);
```

By just passing the model and the request, the model is made searchable and filterable without you having to manage which attributes are queryable and without writing complex logic and database queries.

_Eloquenturl is fast_ because it is built and executed as a single database query.

Read [Eloquenturl: A Laravel Package to Simplify or Eliminate Query Building for URL Parameters](https://dev.to/doncadavona/eloquenturl-a-laravel-package-to-simplify-or-eliminate-query-building-for-url-parameters-2iem).

## Table of Contents

- [I. Installation](#i-installation)
- [II. Usage](#ii-usage)
- [III. Query Parameters](#iii-query-parameters)
- [IV. Change Log](#iv-change-log)
- [V. Testing](#v-change-log)
- [VI. Contributing](#vi-contributing)
- [VII. Security](#vii-security)
- [VIII. Credits](#viii-credits)
- [IX. License](#ix-license)

## I. Installation

Install using Composer:

``` bash
composer require doncadavona/eloquenturl
```

## II. Usage

**Eloquenturl::eloquenturled()**

Use `Eloquenturl::eloquenturled()` to quickly build and execute the database query based on the request query parameters. It returns the paginated entries of the given Eloquent model, based on the URL parameters and the model's `$fillable` and `$hidden` attributes. Just pass the model and the request:

```php
$users = \Doncadavona\Eloquenturl\Eloquenturl::eloquenturled(User::class, request());
```

**Eloquenturl::eloquenturl()**

Use `Eloquenturl::eloquenturl()` when you need to add additional queries, such as eager-loading, or any other database queries available in [Laravel Query Builder](http://laravel.com/docs/queries).

Here is an example `UsersController` with several examples.

```php
<?php

use App\Models\User;
use App\Models\Article;
use App\Http\Controllers\Controller;
use Doncadavona\Eloquenturl\Eloquenturl;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        // It's this easy.
        $users = Eloquenturl::eloquenturled(User::class, $request);

        // Or, add your own sauce.
        $users = Eloquenturl::eloquenturl(User::class, $request)
            ->with(['roles', 'articles', 'comments'])
            ->paginate();
        
        // Or, with select query and simplePaginate
        $users = Eloquenturl::eloquenturl(User::class, $request)
            ->select('name', 'description')
            ->simplePaginate($request->per_page);

        return $users;

        // Or, use any other Eloquent model.
        $articles = Eloquenturl::eloquenturl(Article::class, $request)
            ->with(['user', 'comments'])
            ->get();

        return $articles;
    }
}
```

Here are example URLs with query parameters:

```http
http://localhost:8000/users?search=apple

http://localhost:8000/users?order_by=created_at&order=desc

http://localhost:8000/users?scopes[role]=admin&scopes[status]=active&company_id=3

http://localhost:8000/users?search=apple&search_by=first_name&order_by=created_at&order=desc&scopes[role]=admin&scopes[status]=active&company_id=3
```

## III. Query Parameters

  - page
  - per_page
  - search
  - search_by
  - order
  - order_by
  - scopes
  - lt _(less than)_
  - gt _(greater than)_
  - lte _(less than or equal)_
  - gte _(greater than or equal)_
  - min _(alias for gte)_
  - max _(alias for lte)_

**page**

```
/users?page=5
```

**per_page**

```
/per_page?page=100
```

**search**

```
/users?search=john
/users?search=john+doe
/users?search=john%20doe
```

**search_by**

```
/users?search=john&search_by=first_name
/users?search=john+doe&search_by=last_name
/users?search=johndoe&search_by=email
```

**order**

```
/users?order=desc
```

**order_by**

```
/users?order_by=age
```

**scopes**

```
/users?scopes[status]=active
/users?scopes[senior]
/users?scopes[admin]
```

**lt _(less than)_**

```
/users?lt[age]=18
```

**gt _(greater than)_**

```
/users?gt[age]=17
```

**lte _(less than or equal)_**

```
/users?lte[age]=18
```

**gte _(greater than or equal)_**

```
/users?gte[age]=60
```

**min _(alias for gte)_**

```
/users?min[age]=18
```

**max _(alias for lte)_**

```
/users?max[age]=18
```

When unknown parameters are in the request, they will be queried with *WHERE Clause* using equality condition. For example:

```http
/users?active=true
/users?status=suspended
/users?country=PH
/users?planet_number=3
/users?company_id=99
```

The equivalent database queries will be:

```sql
SELECT * FROM `users` WHERE `active` = true;
SELECT * FROM `users` WHERE `status` = 'suspended';
SELECT * FROM `users` WHERE `country` = 'PH';
SELECT * FROM `users` WHERE `planet_number` = 3;
SELECT * FROM `users` WHERE `company_id` = 99;
```

## IV. Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## V. Testing

``` bash
$ composer test
```

## VI. Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## VII. Security

If you discover any security related issues, please email dcadavona@gmail.com instead of using the issue tracker.

## VIII. Credits

- [Don Cadavona](https://doncadavona.com)

## IX. License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/doncadavona/eloquenturl.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/doncadavona/eloquenturl.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/doncadavona/eloquenturl/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/doncadavona/eloquenturl
[link-downloads]: https://packagist.org/packages/doncadavona/eloquenturl
[link-travis]: https://travis-ci.org/doncadavona/eloquenturl
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/doncadavona
[link-contributors]: ../../contributors
