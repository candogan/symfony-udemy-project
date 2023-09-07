<?php
declare(strict_types=1);

namespace App\Entity;

interface PublishedDateEntityInterface
{
    public function setPublished(\DateTimeInterface $published): PublishedDateEntityInterface;
}