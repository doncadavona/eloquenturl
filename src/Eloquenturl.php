<?php

namespace Doncadavona\Eloquenturl;

use Doncadavona\Eloquenturl\EloquenturlInterface;
use Illuminate\Http\Request;

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
    ];

    /**
     * The database columns to search.
     * Eg. /users?search=apple&search_by=first_name
     * 
     * @var array
     */
    private static $search_columns = [
        'id',
    ];

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

        // Set the exact-match columns as the unknown parameters.
        self::$column_matches = $request->only(
            array_diff($request->keys(), self::$parameters)
        );

        // Set the search columns as the difference of the $fillable and $hidden attribute of the model.
        self::$search_columns = array_merge(
            self::$search_columns,
            array_diff(self::$model->getFillable(), self::$model->getHidden())
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
     * Build the database query for the URL query parameters.
     * 
     * @return Illuminate\Database\Eloquent\Builder
     */
    private static function buildQuery()
    {
        // Throw errors
        if (!count(self::$search_columns)) {
            throw new \Exception('Eloquenturl requires at least one search column. 0 given.');
        }

        // Initialise query
        self::$query = self::$model;

        // Build column matcher
        self::buildColumnMatcher();

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
                    self::$search_columns[0],
                    'like',
                    '%' . self::$request->search . '%'
                );

                $i = 1;
                do {
                    self::$query = self::$query->orWhere(
                        self::$search_columns[$i],
                        'like',
                        '%' . self::$request->search . '%'
                    );

                    $i++;
                } while ($i < count(self::$search_columns));
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
                self::$query = self::$query->$key($value);
            }
        }
    }
}