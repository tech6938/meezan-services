<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\ApiTimestampFormatter;

abstract class Controller
{
    use AuthorizesRequests;
    use ApiTimestampFormatter;
}
