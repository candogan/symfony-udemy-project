<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/users/confirm'
        )
    ]
)]
class UserConfirmation implements DataForEmptyBodyValidationInterface
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 30, max: 30)]
    public $confirmationToken;

    public function getEmptyBodyData(): array
    {
        return [
            'confirmationToken' => $this->confirmationToken
        ];
    }


}