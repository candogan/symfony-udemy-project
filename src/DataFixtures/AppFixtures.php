<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $fakerGenerator;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
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
        /** @var User $user */
        $user = $this->getReference('user_admin');

        for ($i = 0; $i < 100; $i++) {
            $blogPost = new BlogPost();
            $blogPost->setTitle($this->fakerGenerator->title);
            $blogPost->setPublished($this->fakerGenerator->dateTimeThisYear);
            $blogPost->setContent($this->fakerGenerator->text);
            $blogPost->setAuthor($user);
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
                    ->setPublished($this->fakerGenerator->dateTimeThisYear)
                    ->setAuthor($this->getReference('user_admin'))
                    ->setBlogPost($this->getReference("blog_post_$i"));

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('admin')
            ->setEmail('admin@blog.com')
            ->setName('Can Dogan')
            ->setPassword($this->passwordHasher->hashPassword(
                $user,
                'localsecretpassword'
            ));

        $this->addReference('user_admin', $user);

        $manager->persist($user);
        $manager->flush();
    }
}
