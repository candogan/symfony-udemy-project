<?php
declare(strict_types=1);

namespace App\Entity;

interface DataForEmptyBodyValidationInterface
{
    public function getEmptyBodyData(): array;
}