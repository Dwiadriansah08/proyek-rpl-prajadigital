<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

abstract class Controller
{
    /**
     * Authorize a given action against a set of arguments.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        return Gate::authorize($ability, $arguments);
    }
}
