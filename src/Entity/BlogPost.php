<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id' => 'exact',
        'title' => 'ipartial', // if upper case than only 'partial'. If lower case than use 'ipartial'
        'content' => 'ipartial',
        'author' => 'exact', // Complicated properties will also work
        'author.name' => 'partial'
    ]
)]
// the lower api filter allows: https://udemy.local.bank.c24/api/blog_posts?published[after]=2023-08-01&published[before]=2023-09-01
#[ApiFilter(
    DateFilter::class,
    properties: [
        'published'
    ]
)]
#[ApiFilter(
    RangeFilter::class,
    properties: [
        'id'
    ]
)]
#[ApiFilter(
    OrderFilter::class,
    properties: [
        'id',
        'published',
        'title'
    ],
    arguments: [
        'orderParameterName' => '_order' // way to specify how you would like to let users order your collections
    ]
)]
#[ApiFilter(
    PropertyFilter::class,
    arguments: [
        'parameterName' => 'properties',
        'overrideDefaultProperties' => false,
        'whitelist' => ['id', 'author', 'slug', 'title', 'content']
    ]
)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['bpwithauthor']]),
        new GetCollection(),
        new Put(security: "is_granted('ROLE_EDITOR') or is_granted('ROLE_WRITER') and object == user"),
        new Post(security: "is_granted('ROLE_WRITER')")
    ]
)]
class BlogPost implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "posts")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['bpwithauthor'])]
    private User $author;

    #[ORM\Column(length: 255)]
    #[Groups(['bpwithauthor'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['bpwithauthor'])]
    private ?\DateTimeInterface $published = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['bpwithauthor'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups(['bpwithauthor'])]
    private ?string $slug;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: "blogPost")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getblogpostwithauthor"])]
    private $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function setComments(Collection $comments): self
    {
        $this->comments = $comments;

        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug): self
    {
        $this->slug = $slug;

        return $this;
    }

}
