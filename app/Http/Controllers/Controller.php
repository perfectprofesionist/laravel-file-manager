<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base controller for all application controllers.
 * Provides authorization and validation utilities for child controllers.
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
