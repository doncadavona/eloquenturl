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
}