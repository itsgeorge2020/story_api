<?php

namespace App\Contracts;

interface TextRequestServiceInterface
{
    public function send(string $prompt): string;
}
