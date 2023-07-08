<?php

namespace Doncadavona\Eloquenturl;

use Doncadavona\Eloquenturl\EloquenturlInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class Eloquenturl implements EloquenturlInterface
{
    /**
     * The HTTP request containing the necessary URL query parameters.
     * 
     * @var Illuminate\Http\Request
     */
    private static $request;

    /**
     * The Eloquent model to perform queries on.
     * 
     * @var mixed
     */
    private static $model;

    /**
     * The database query to execute.
     * 
     * @return Illuminate\Database\Eloquent\Builder
     */
    private static $query;

    /**
     * The known URL query parameters.
     * 
     * @var array
     */
    private static $parameters = [
        'page',
        'per_page',
        'search',
        'search_by',
        'order',
        'order_by',
        'scopes',
        'lt',
        'gt',
        'lte',
        'gte',
        'min',
        'max',
    ];

    /**
     * The queryable columns from the Eloquent model.
     * Eg. id, created_at, updated_at.
     * 
     * @var array
     */
    private static $queryable_columns = [];

    /**
     * The database columns to order by.
     * Eg. /users?order_by=name&order=desc
     * 
     * @var array
     */
    private static $order_by_columns = [
        'id',
    ];

    /**
     * The database columns to match.
     * Eg. /users?role_id=3
     * 
     * @var array
     */
    private static $column_matches = [];

    /**
     * The Eloquent scopes to execute.
     * Eg. /users?scopes[active]=true&scopes[role]=admin
     * 
     * @var array
     */
    private static $scopes = [];

    /**
     * The initial function to execute.
     * 
     * @param  mixed  $class   The Eloquent model class
     * @param  Request $request
     * @return void
     */
    public static function boot($class, Request $request)
    {
        self::$request = $request;

        self::$model = new $class;

        // Set the queryable columns as the difference of the model's attributes and $hidden attributes.
        self::$queryable_columns = array_values(array_diff(
            Schema::getColumnListing(self::$model->getTable()),
            self::$model->getHidden()
        ));

        // Set the exact-match columns as the unknown parameters.
        self::$column_matches = $request->only(
            array_diff($request->keys(), self::$parameters)
        );

        // Set the order_by_columns as the the $fillable attribute of the model.
        self::$order_by_columns = self::$model->getFillable();

        // Set the scopes as any parameters that are not known.
        self::$scopes = self::$request->scopes;
    }

    /**
     * Get the database query for the URL query parameters.
     * 
     * @param  mixed  $class
     * @param  Request $request
     * @return Illuminate\Database\Eloquent\Builder
     */
    public static function eloquenturl($class, Request $request)
    {
        self::boot($class, $request);

        return self::buildQuery($request);
    }

    /**
     * Get and execute the database query for the URL query parameters.
     * 
     * @param  mixed  $class
     * @param  Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public static function eloquenturled($class, Request $request)
    {
        self::boot($class, $request);

        return self::buildQuery($request)
            ->paginate(
                self::$request->per_page
                ? (int) self::$request->per_page
                : null
            );
    }

    /**
     * Get and execute the database query for the URL query parameters.
     * 
     * @param  mixed  $class
     * @param  Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public static function eloquenturledWithoutPagination($class, Request $request)
    {
        self::boot($class, $request);

        return self::buildQuery($request)->get();
    }

    /**
     * Build the database query based on query parameters.
     * 
     * For example:
     * $users = Eloquenturl::queryByParameters(User::class)->get();
     * $users = Eloquenturl::queryByParameters(User::class)->paginate();
     * 
     * @param mixed $class
     * @return Illuminate\Database\Eloquent\Builder
     */
    public static function queryByParameters($class)
    {
        return self::eloquenturl($class, request());
    }

    /**
     * Build and execute the database query based on query parameters.
     * 
     * For example:
     * $users = Eloquenturl::getByParameters(User::class);
     * 
     * @param mixed $class
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getByParameters($class)
    {
        return self::eloquenturl($class, request())->get();
    }

    /**
     * Build and execute the paginated database query based on query parameters.
     * 
     * For example:
     * $users = Eloquenturl::paginateByParameters(User::class);
     * 
     * @param mixed $class
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public static function paginateByParameters($class)
    {
        return self::eloquenturled($class, isset($parameters) ? $parameters : request());
    }

    /**
     * Build and execute the simple-paginated database query based on query parameters.
     * 
     * For example:
     * $users = Eloquenturl::simplePaginateByParameters(User::class);
     * 
     * @param mixed $class
     * @return Illuminate\Pagination\Paginator
     */
    public static function simplePaginateByParameters($class)
    {
        self::boot($class, request());

        return self::buildQuery(request())
            ->paginate(
                self::$request->per_page
                ? (int) self::$request->per_page
                : null
            );
    }

    /**
     * Build the database query for the URL query parameters.
     * 
     * @return Illuminate\Database\Eloquent\Builder
     */
    private static function buildQuery()
    {
        // Throw errors
        if (!count(self::$queryable_columns)) {
            throw new \Exception('Eloquenturl requires at least one search column. 0 given.');
        }

        // Initialise query
        self::$query = self::$model;

        // Build column matcher
        self::buildColumnMatcher();

        // Build column less than identifer
        self::buildColumnLessThan();

        // Build column greater than identifer
        self::buildColumnGreaterThan();

        // Build column less than or equal (lte) identifer
        self::buildColumnLessThanOrEqual();

        // Build column greater than or equal (gte) identifer
        self::buildColumnGreaterThanOrEqual();

        // Build column min (alias for gte) identifer
        self::buildColumnMin();

        // Build column max (alias for lte) identifer
        self::buildColumnMax();

        // Build column searcher
        self::buildColumnSearcher();

        // Build column sorter
        self::buildColumnSorter();

        // Build scoper
        self::buildScoper();

        return self::$query;
    }

    /**
     * Build the database query for exact-matching columns without wildcards.
     * 
     * @return void
     */
    private static function buildColumnMatcher(): void
    {
        if (!self::$column_matches) return;

        foreach (self::$column_matches as $key => $value) {
            if (self::$request->filled($key)) {
                if (!self::isParameterValid($key)) {
                    continue;
                }
                if (is_numeric($key)) {
                    self::$query = self::$query->where($key, (int) $value);
                    return;
                } else {
                    self::$query = self::$query->where($key, $value);
                }
            }
        }
    }

    /**
     * Build the database query where columns are less than specified values.
     * 
     * @return void
     */
    private static function buildColumnLessThan(): void
    {
        // Build column less than identifer
        if (self::$request->filled('lt')) {
            foreach (self::$request->lt as $key => $value) {
                if ($value) {
                    self::$query = self::$query->where($key, '<', $value);
                }
            }
        }
    }

    /**
     * Build the database query where columns are greater than specified values.
     * 
     * @return void
     */
    private static function buildColumnGreaterThan(): void 
    {
        // Build column greater than identifer
        if (self::$request->filled('gt')) {
            foreach (self::$request->gt as $key => $value) {
                if ($value) {
                    self::$query = self::$query->where($key, '>', $value);
                }
            }
        }
    }

    /**
     * Build the database query where columns are less than or equal specified values.
     * 
     * @return void
     */
    private static function buildColumnLessThanOrEqual(): void
    {
        // Build column less than or equal (lte) identifer
        if (self::$request->filled('lte')) {
            foreach (self::$request->lte as $key => $value) {
                if ($value) {
                    self::$query = self::$query->where($key, '<=', $value);
                }
            }
        }
    }

    /**
     * Build the database query where columns are greater than or equal specified values.
     * 
     * @return void
     */
    private static function buildColumnGreaterThanOrEqual(): void
    {
        // Build column greater than or equal (lte) identifer
        if (self::$request->filled('gte')) {
            foreach (self::$request->gte as $key => $value) {
                if ($value) {
                    self::$query = self::$query->where($key, '>=', $value);
                }
            }
        }
    }

    /**
     * Alias for buildColumnGreaterThanOrEqual (gte).
     * Build the database query to set the minimum values of columns.
     * 
     * @return void
     */
    private static function buildColumnMin(): void
    {
        // Build column min (alias for gte) identifer
        if (self::$request->filled('min')) {
            foreach (self::$request->min as $key => $value) {
                if ($value) {
                    self::$query = self::$query->where($key, '>=', $value);
                }
            }
        }
    }

    /**
     * Alias for buildColumnLessThanOrEqual (lte).
     * Build the database query to set the maximum values of columns.
     * 
     * @return void
     */
    private static function buildColumnMax(): void
    {
        // Build column less than or equal (lte) identifer
        if (self::$request->filled('max')) {
            foreach (self::$request->max as $key => $value) {
                if ($value) {
                    self::$query = self::$query->where($key, '<=', $value);
                }
            }
        }
    }

    /**
     * Build the database query for searching columns with wildcards.
     * 
     * @return void
     */
    private static function buildColumnSearcher(): void
    {
        // Build the query with search
        if (self::$request->filled('search')) {
            // Search by given field.
            if (self::$request->filled('search_by')) {
                self::$query = self::$query
                    ->where(self::$request->get('search_by'), 'like', '%'.self::$request->search.'%');
            } else {
                // Search by columns.
                self::$query = self::$query->where(
                    self::$queryable_columns[0],
                    'like',
                    '%' . self::$request->search . '%'
                );

                $i = 1;
                do {
                    self::$query = self::$query->orWhere(
                        self::$queryable_columns[$i],
                        'like',
                        '%' . self::$request->search . '%'
                    );

                    $i++;
                } while ($i < count(self::$queryable_columns));
            }
        }
    }

    /**
     * Build the database query for sorting records by column.
     * 
     * @return void
     */
    private static function buildColumnSorter(): void
    {
        if (self::$request->filled('order_by')) {
            $order_by = strtolower(self::$request->order_by);
            $order = self::$request->filled('order') ? strtolower(self::$request->order) : 'asc';
            self::$query = self::$query->orderBy($order_by, $order);
        } else {
            // Set default ordering
            self::$query = self::$query->orderBy('created_at', 'asc');
        }
    }

    /**
     * Build the database query to scope only records.
     * 
     * @return void
     */
    private static function buildScoper(): void
    {
        if (!self::$scopes) return;

        foreach (self::$scopes as $key => $value) {
            if (self::$request->filled('scopes.' . $key)) {
                if (method_exists(self::$model, 'scope'.ucwords($key))) {
                    self::$query = self::$query->$key($value);
                } else if (!config('eloquenturl.ignore_invalid_parameters')) {
                    throw new Exception('The scope "'.$key.'" does not exist in '.self::$model::class.'.');
                }
            }
        }
    }

    /**
     * Check if the 
     */
    private static function isParameterValid(string $parameter): bool
    {
        if (!in_array($parameter, self::$queryable_columns) && config('eloquenturl.ignore_invalid_parameters')) {
            return false;
        }
        if (!in_array($parameter, self::$queryable_columns) && !config('eloquenturl.ignore_invalid_parameters')) {
            throw new Exception('Dubious parameter "'.$parameter.'" received.');
        }

        return true;
    }
}