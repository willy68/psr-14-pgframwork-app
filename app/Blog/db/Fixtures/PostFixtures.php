<?php

namespace App\Blog\db\Fixtures;

use App\Entity\Category;
use App\Entity\Post;
use DateTime;
use Faker\Factory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserDataLoader implements FixtureInterface
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
        $data = [];
        for ($i = 0; $i < 100; ++$i) {
            $date = $faker->unixTime('now');
            $data[] = [
                'name' => $faker->catchPhrase,
                'slug' => $faker->slug,
                'content' => $faker->text(3000),
                'category_id' => $faker->numberBetween(1, 5),
                'created_at' => date('Y-m-d H:i:s', $date),
                'updated_at' => date('Y-m-d H:i:s', $date),
                'published' => 1
            ];
            $post = new Post();
            $post->setName($faker->catchPhrase)
                ->setSlug($faker->slug)
                ->setContent($faker->text(3000))
                ->setCategory($categories[$faker->numberBetween(1, 5)])
                ->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', $date)))
                ->setUpdatedAt(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', $date)))
                ->setPublished(1);
        }
    }
}
