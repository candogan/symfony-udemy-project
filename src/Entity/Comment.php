<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Put(security: "is_granted('ROLE_EDITOR') or is_granted('ROLE_COMMENTATOR') and object == user"),
        new Post(security: "is_granted('ROLE_COMMENTATOR')"),
    ]
)]
// Die zweite ApiResource Definition sorgt dafür das die Comments Entitäten richtig geladen und die Informationen
// nach außen hin preisgegeben werden
#[ApiResource(
    uriTemplate: '/blog_posts/{id}/comment',
    uriVariables: [
        'id' => new Link(
            fromClass: BlogPost::class,
            fromProperty: 'comments'
        )
    ],
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => ['getCommentWithAuthor']
            ],
        )
    ],
    order: ['published' => 'DESC'],
//    paginationEnabled: false,
//    paginationClientEnabled: true,
//    paginationMaximumItemsPerPage: 4
)]
class Comment implements AuthoredEntityInterface, PublishedDateEntityInterface, DataForEmptyBodyValidationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCommentWithAuthor'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['getCommentWithAuthor'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['getCommentWithAuthor'])]
    private ?\DateTimeInterface $published = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "comments")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getCommentWithAuthor'])]
    private User $author;

    #[ORM\ManyToOne(targetEntity: BlogPost::class, inversedBy: "comments")]
    #[ORM\JoinColumn(nullable: false)]
    private BlogPost $blogPost;

    public function __construct()
    {
    }

    public function getBlogPost(): BlogPost
    {
        return $this->blogPost;
    }

    public function setBlogPost(BlogPost $blogPost): self
    {
        $this->blogPost = $blogPost;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): static
    {
        $this->published = $published;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(UserInterface $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getEmptyBodyData(): array
    {
        return [
            'content' => $this->content,
        ];
    }


}
