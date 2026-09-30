<?php

namespace App\Contracts;

use App\DTO\GeneratedTextDTO;

interface TextRequestServiceInterface
{
    public function send(string $prompt): GeneratedTextDTO;
}
