<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $blogPost = new BlogPost();
        $blogPost->setTitle("AppFixture BlogPost one");
        $blogPost->setPublished(new \DateTime('2023-08-14 12:00:00'));
        $blogPost->setContent('New Content');
        $blogPost->setAuthor('Can Dogan');
        $blogPost->setSlug('SomeSlug-one');
        $manager->persist($blogPost);

        $blogPost = new BlogPost();
        $blogPost->setTitle("AppFixture BlogPost two");
        $blogPost->setPublished(new \DateTime('2023-08-14 12:00:00'));
        $blogPost->setContent('New Content');
        $blogPost->setAuthor('Can Dogan');
        $blogPost->setSlug('SomeSlug-two');
        $manager->persist($blogPost);

        $manager->flush();
    }
}
