# Eloquenturl

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

Query Laravel Eloquent models with URL query parameters. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require doncadavona/eloquenturl
```

## Usage

Here are example URLs with query parameters that we can parse into database queries:

```
http://app.test/users?search=apple

http://app.test/users?order_by=created_at&order=desc

http://app.test/users?scopes[role]=admin&scopes[status]=active&company_id=3

http://app.test/users?search=apple&search_by=first_name&order_by=created_at&order=desc&scopes[role]=admin&scopes[status]=active&company_id=3
```

Supported URL Query Parameters:

  - page
  - per_page
  - search
  - search_by
  - order
  - order_by
  - scopes

**Eloquenturl::eloquenturled()**

This the simplest way let your app query your Eloquent models using the URL parameters. It returns the paginated entries of the model based on the URL parameters and your Eloquent models' `$fillable` and `$hidden` attributes. Just pass the model and the request:

```php
$users = Doncadavona\Eloquenturl\Eloquenturl::eloquenturled(User::class, request());
```

**Eloquenturl::eloquenturl()**

Use `Eloquenturl::eloquenturl()` when you need to add additional queries, such as eager-loading, or any other database queries available in Laravel's Query Builder.

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

        return $users;

        // Or, use any other Eloquent model.
        $articles = Eloquenturl::eloquenturl(Article::class, $request)
            ->with(['user', 'comments'])
            ->get();

        return $articles;
    }
}
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email dcadavona@gmail.com instead of using the issue tracker.

## Credits

- [Don Cadavona][link-author]
- [All Contributors][link-contributors]

## License

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
