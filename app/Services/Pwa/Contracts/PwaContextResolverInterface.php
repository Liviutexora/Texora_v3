<?php

namespace App\Services\Pwa\Contracts;

use App\Services\Pwa\DTO\PwaContextData;
use Illuminate\Http\Request;

interface PwaContextResolverInterface
{
    public function resolve(?Request $request = null): PwaContextData;
}