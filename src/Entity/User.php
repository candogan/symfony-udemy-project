<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\ResetPasswordAction;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['userget']]
        ),
        new Put(
            security: "is_granted('IS_AUTHENTICATED_FULLY') and object == user"
        ),
        new GetCollection(),
        new Post(security: "is_granted('IS_AUTHENTICATED_FULLY')")
    ]
)]
#[ApiResource(
    security: "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
    controller: ResetPasswordAction::class,
    uriTemplate: '/users/{id}/reset-password',
    operations: [
        new Put(
            denormalizationContext: [
                'groups' => ['put-reset-password']
            ],
            
        )
    ],
)]
#[UniqueEntity(['username'])]
#[UniqueEntity(['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, DataForEmptyBodyValidationInterface
{
    public const ROLE_COMMENTATOR = 'ROLE_COMMENTATOR';
    public const ROLE_WRITER = 'ROLE_WRITER';
    public const ROLE_EDITOR = 'ROLE_EDITOR';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';

    public const DEFAULT_ROLES = [self::ROLE_COMMENTATOR];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['userget', "bpwithauthor"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['post', 'put'])]
    #[Assert\Length(min: 6, max: 255, groups: ['post'])]
    #[Groups(['userget', 'post', "getCommentWithAuthor", "bpwithauthor"])]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['post', 'put'])]
    #[Assert\Regex(
        pattern: "/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
        message: "Password must be seven characters long and contain at least one digit, one upper case letter and one lower case letter",
        groups: ['post']
    )]
    #[Groups(['userget', 'post'])]
    private ?string $password = null;

    #[Assert\NotBlank(groups: ['post', 'put'])]
    #[Assert\Expression(
        "this.getPassword() === this.getRetypedPassword()",
        message: "Passwords do not match",
        groups: ['post']
    )]
    private $retypedPassword;

    #[Assert\NotBlank(groups: ['post', 'put'])]
    #[Assert\Regex(
        pattern: "/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
        message: "Password must be seven characters long and contain at least one digit, one upper case letter and one lower case letter"
    )]
    #[Groups(['put-reset-password'])]
    private $newPassword;

    #[Assert\NotBlank(groups: ['post', 'put'])]
    #[Assert\Expression(
        "this.getNewPassword() === this.getNewRetypedPassword()",
        message: "Passwords do not match"
    )]
    #[Groups(['put-reset-password'])]
    private $newRetypedPassword;

    #[Groups(['put-reset-password'])]
    #[UserPassword(groups: ['put-reset-password'])]
    private $oldPassword;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['post', 'put'])]
    #[Assert\Length(min: 6, max: 255, groups: ['post', 'put'])]
    #[Groups(['userget', 'post', 'put', "getCommentWithAuthor", "bpwithauthor"])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['post', 'put'])]
    #[Assert\Email(groups: ['post', 'put'])]
    #[Assert\Length(min: 6, max: 255, groups: ['post', 'put'])]
    #[Groups(['post', 'put', "getCommentWithAuthor", 'bpwithauthor', 'get-admin', 'get-owner'])]
    private ?string $email = null;

    #[ORM\OneToMany(targetEntity: BlogPost::class, mappedBy: "author")]
    private $posts;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: "author")]
    private $comments;

    #[ORM\Column(type: 'simple_array', length: '255')]
    #[Groups(['get-admin', 'get-owner'])]
    private $roles;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $passwordChangeDate;

    #[ORM\Column(type: 'boolean')]
    private $enabled;

    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    private $confirmationToken;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
        $this->enabled = false;
        $this->confirmationToken = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function setPosts(Collection $posts): self
    {
        $this->posts = $posts;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function setComments(Collection $comments): self
    {
        $this->comments = $comments;

        return $this;
    }


    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return '';
    }

    public function eraseCredentials()
    {
    }

    public function getNewPassword()
    {
        return $this->newPassword;
    }

    public function setNewPassword($newPassword): self
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    public function getNewRetypedPassword(): ?string
    {
        return $this->newRetypedPassword;
    }

    public function setNewRetypedPassword($newRetypedPassword): self
    {
        $this->newRetypedPassword = $newRetypedPassword;
        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword($oldPassword): self
    {
        $this->oldPassword = $oldPassword;
        return $this;
    }

    public function getRetypedPassword(): ?string
    {
        return $this->retypedPassword;
    }

    public
    function setRetypedPassword($retypedPassword): self
    {
        $this->retypedPassword = $retypedPassword;
        return $this;
    }

    public function getPasswordChangeDate()
    {
        return $this->passwordChangeDate;
    }

    public function setPasswordChangeDate($passwordChangeDate): self
    {
        $this->passwordChangeDate = $passwordChangeDate;
        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken($confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    public function getEmptyBodyData(): array
    {
        return [
            'email' => $this->email,
            'username' => $this->username,
            'name' => $this->name
        ];
    }


}
