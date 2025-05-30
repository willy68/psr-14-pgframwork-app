<?php

namespace App\Blog\db\Fixtures;

use App\Entity\Category;
use App\Entity\Post;
use DateTime;
use DateTimeImmutable;
use Faker\Factory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostFixtures implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // seeding des categories
        $faker = Factory::create('fr_FR');
        $categories = [];
        for ($i = 0; $i < 5; ++$i) {
            $category = new Category();
            $category->setName($faker->catchPhrase);
            $category->setSlug($faker->slug);
            $manager->persist($category);
            $categories[] = $category;
        }

        // seeding des posts
        for ($i = 0; $i < 100; ++$i) {
            $date = $faker->unixTime('now');
            $post = new Post();
            $post->setName($faker->catchPhrase)
                ->setSlug($faker->slug)
                ->setContent($faker->text(3000))
                ->setCategory($categories[$faker->numberBetween(0, 4)])
                ->setCreatedAt(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', $date)))
                ->setUpdatedAt(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', $date)))
                ->setPublished(1);

            $manager->persist($post);
        }
        $manager->flush();
    }
}
