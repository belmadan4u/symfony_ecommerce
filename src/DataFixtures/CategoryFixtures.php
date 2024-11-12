<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_FOOTBALL_REFERENCE = 'category_football';
    public const CATEGORY_TENNIS_REFERENCE = 'category_tennis';
    public const CATEGORY_CYCLISME_REFERENCE = 'category_cyclisme';
    public const CATEGORY_YOGA_REFERENCE = 'category_yoga';
    public const CATEGORY_BOXE_REFERENCE = 'category_boxe';

    public function load(ObjectManager $manager): void
    {
        $categories = [
            ['name' => 'Football', 'reference' => self::CATEGORY_FOOTBALL_REFERENCE],
            ['name' => 'Tennis', 'reference' => self::CATEGORY_TENNIS_REFERENCE],
            ['name' => 'Cyclisme', 'reference' => self::CATEGORY_CYCLISME_REFERENCE],
            ['name' => 'Yoga', 'reference' => self::CATEGORY_YOGA_REFERENCE],
            ['name' => 'Boxe', 'reference' => self::CATEGORY_BOXE_REFERENCE],
        ];

        foreach ($categories as $categoryData) {
            $category = new Category();
            $category->setName($categoryData['name']);
            
            $manager->persist($category);

            // Ajoute la référence pour les autres fixtures
            $this->addReference($categoryData['reference'], $category);
        }

        $manager->flush();
    }
}
