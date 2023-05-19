<?php

namespace Doncadavona\Eloquenturl;

use Illuminate\Http\Request;

interface EloquenturlInterface
{
    /**
     * The initial function to execute.
     * 
     * @param  mixed  $class   The Eloquent model class
     * @param  Request $request
     * @return void
     */
    public static function boot($class, Request $request);

    /**
     * Get the database query for the URL query parameters.
     * 
     * @param  mixed  $class
     * @param  Request $request
     * @return Illuminate\Database\Eloquent\Builder
     */
    public static function eloquenturl($class, Request $request);

    /**
     * Get and execute the database query for the URL query parameters
     * with pagination.
     * 
     * @param  mixed  $class
     * @param  Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public static function eloquenturled($class, Request $request);

    /**
     * Get and execute the database query for the URL query parameters
     * without pagination.
     * 
     * @param  mixed  $class
     * @param  Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public static function eloquenturledWithoutPagination($class, Request $request);

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
    public static function queryByParameters($class);

    /**
     * Build and execute the database query based on query parameters.
     * 
     * For example:
     * $users = Eloquenturl::getByParameters(User::class);
     * 
     * @param mixed $class
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getByParameters($class);

    /**
     * Build and execute the paginated database query based on query parameters.
     * 
     * For example:
     * $users = Eloquenturl::paginateByParameters(User::class);
     * 
     * @param mixed $class
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public static function paginateByParameters($class);

    /**
     * Build and execute the simple-paginated database query based on query parameters.
     * 
     * For example:
     * $users = Eloquenturl::simplePaginateByParameters(User::class);
     * 
     * @param mixed $class
     * @return Illuminate\Pagination\Paginator
     */
    public static function simplePaginateByParameters($class);
}