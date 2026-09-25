<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface UserInterface
{
    public function stories(): MorphMany;
}
