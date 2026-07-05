<?php

namespace App\Http\Controllers;

use App\Traits\ApiTimestampFormatter;

abstract class Controller
{
    use ApiTimestampFormatter;
}
