<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use App\Security\TokenGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $fakerGenerator;

    private const USERS = [
        [
            'username' => 'sadmin',
            'email' => 'admin@blog.de',
            'name' => 'Can Dogan',
            'password' => 'Localsecretpassword123',
            'roles' => [User::ROLE_SUPERADMIN],
            'enabled' => true,
        ],
        [
            'username' => 'admin',
            'email' => 'john_doe@blog.de',
            'name' => 'John Doe',
            'password' => 'Localsecretpassword123',
            'roles' => [User::ROLE_ADMIN],
            'enabled' => true,
        ],
        [
            'username' => 'writer1',
            'email' => 'RobStark@blog.de',
            'name' => 'Robb Stark',
            'password' => 'Localsecretpassword123',
            'roles' => [User::ROLE_WRITER],
            'enabled' => true,
        ],
        [
            'username' => 'writer2',
            'email' => 'TywinLennister@blog.de',
            'name' => 'Tywin Lennister',
            'password' => 'Localsecretpassword123',
            'roles' => [User::ROLE_WRITER],
            'enabled' => false,
        ],
        [
            'username' => 'editor',
            'email' => 'EddardStark@blog.de',
            'name' => 'Eddard Stark',
            'password' => 'Localsecretpassword123',
            'roles' => [User::ROLE_EDITOR],
            'enabled' => true,
        ],
        [
            'username' => 'commentator',
            'email' => 'JeimiLennister@blog.de',
            'name' => 'Jeimi Lennister',
            'password' => 'Localsecretpassword123',
            'roles' => [User::ROLE_COMMENTATOR],
            'enabled' => true,
        ],
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TokenGenerator $tokenGenerator
    )
    {
        require_once 'vendor/autoload.php';
        $this->fakerGenerator = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);
    }

    public function loadBlogPosts(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $blogPost = new BlogPost();
            $blogPost->setTitle($this->fakerGenerator->title);
            $blogPost->setPublished($this->fakerGenerator->dateTimeThisYear);
            $blogPost->setContent($this->fakerGenerator->text);

            /** @var User $authorReference */
            $authorReference = $this->getRandomUserReference($blogPost);

            $blogPost->setAuthor($authorReference);
            $blogPost->setSlug($this->fakerGenerator->slug);
            $this->setReference("blog_post_$i", $blogPost);
            $manager->persist($blogPost);
        }

        $manager->flush();
    }

    public function loadComments(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $comment = new Comment();
                $comment->setContent($this->fakerGenerator->realText)
                    ->setPublished($this->fakerGenerator->dateTimeThisYear);

                /** @var User $authorReference */
                $authorReference = $this->getRandomUserReference($comment);

                $comment->setAuthor($authorReference)
                    ->setBlogPost($this->getReference("blog_post_$i"));

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {
        foreach (self::USERS as $userfixture) {
            $user = new User();
            $user->setUsername($userfixture['username'])
                ->setEmail($userfixture['email'])
                ->setName($userfixture['name'])
                ->setPassword($this->passwordHasher->hashPassword(
                    $user,
                    $userfixture['password']
                ))
                ->setRoles($userfixture['roles'])
                ->setEnabled($userfixture['enabled']);

            if ($userfixture['enabled'] === false) {
                $user->setConfirmationToken(
                    $this->tokenGenerator->getRandomSecureToken()
                );
            }

            $this->addReference('user_' . $userfixture['username'], $user);

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getRandomUserReference($entity): User
    {
        $random = self::USERS[rand(0, 5)];

        if ($entity instanceof BlogPost && !count(array_intersect($random['roles'], [
                User::ROLE_SUPERADMIN, User::ROLE_ADMIN, User::ROLE_WRITER
            ]))) {
            return $this->getRandomUserReference($entity);
        }

        if ($entity instanceof Comment && !count(array_intersect($random['roles'], [
                User::ROLE_SUPERADMIN, User::ROLE_ADMIN, User::ROLE_WRITER, User::ROLE_COMMENTATOR
            ]))) {
            return $this->getRandomUserReference($entity);
        }

        /** @var User $user */
        $user = $this->getReference('user_' . $random['username']);

        return $user;
    }
}
