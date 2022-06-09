<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        
        for($i = 0; $i < 3; $i++)   // 3 catégories
        {
            $category = new Category;
            $category->setTitle($faker->sentence(3));
            $manager->persist($category);

            for($j = 0; $j < mt_rand(4, 6); $j++)   // entre 4 & 6 articles par catégorie
            {
                $article = new Article;

                $content = '<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>';

                $article->setTitle($faker->sentence(3))
                        ->setContent($content)
                        ->setImage($faker->imageUrl)
                        ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                        ->setCategory($category);

                $manager->persist($article);

                for($k = 0; $k < mt_rand(4, 10); $k++)  // entre 4 & 10 commentaires par article
                {
                    $comment = new Comment;

                    $content = '<p>' . join('</p><p>', $faker->paragraphs(2)) . '</p>';

                    // $now = new \DateTime();
                    // $interval = $now->diff($article->getCreatedAt());
                    // $days = $interval->days;
                    $days = (new \DateTime())->diff($article->getCreatedAt())->days;

                    $comment->setAuthor($faker->name)
                            ->setContent($content)
                            ->setCreatedAt($faker->dateTimeBetween('-' . $days . ' days'))
                            ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }
        $manager->flush();
        // flush() exécute la requête SQL préparée
    }
}
