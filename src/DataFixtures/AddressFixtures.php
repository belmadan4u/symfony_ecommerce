<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Address;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AddressFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Liste des adresses à créer
        $addressesData = [
            [
                'street' => '123 Rue de Paris',
                'postal_code' => 75001,
                'city' => 'Paris',
                'country' => 'France',
                'userReference' => UserFixtures::USER_ALICE_REFERENCE,
            ],
            [
                'street' => '456 Avenue des Champs',
                'postal_code' => 75008,
                'city' => 'Paris',
                'country' => 'France',
                'userReference' => UserFixtures::USER_BOB_REFERENCE,
            ],
            [
                'street' => '789 Boulevard Saint-Germain',
                'postal_code' => 75006,
                'city' => 'Paris',
                'country' => 'France',
                'userReference' => UserFixtures::USER_CHARLIE_REFERENCE,
            ],
            [
                'street' => '101 Rue de la Liberté',
                'postal_code' => 69001,
                'city' => 'Lyon',
                'country' => 'France',
                'userReference' => UserFixtures::USER_DAVID_REFERENCE,
            ],
            [
                'street' => '202 Rue de la République',
                'postal_code' => 13001,
                'city' => 'Marseille',
                'country' => 'France',
                'userReference' => UserFixtures::USER_EVE_REFERENCE,
            ],
        ];

        // Boucle pour créer chaque adresse
        foreach ($addressesData as $addressData) {
            $address = new Address();
            $address->setStreet($addressData['street']);
            $address->setPostalCode($addressData['postal_code']);
            $address->setCity($addressData['city']);
            $address->setCountry($addressData['country']);
            $address->setOfUser($this->getReference($addressData['userReference'])); // Associe l'adresse à l'utilisateur

            $manager->persist($address);
        }

        // Enregistre toutes les adresses en une seule opération
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
