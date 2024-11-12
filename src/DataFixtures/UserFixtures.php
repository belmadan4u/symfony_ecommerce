<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use App\Enum\UserRoles;

class UserFixtures extends Fixture
{
    // Constantes pour les références d'utilisateurs
    public const USER_ALICE_REFERENCE = 'user-alice';
    public const USER_BOB_REFERENCE = 'user-bob';
    public const USER_CHARLIE_REFERENCE = 'user-charlie';
    public const USER_DAVID_REFERENCE = 'user-david';
    public const USER_EVE_REFERENCE = 'user-eve';

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            [
                'reference' => self::USER_ALICE_REFERENCE,
                'email' => 'alice.dupont@example.com',
                'first_name' => 'Alice',
                'last_name' => 'Dupont',
                'roles' => UserRoles::customer,
                'password' => 'password1',
            ],
            [
                'reference' => self::USER_BOB_REFERENCE,
                'email' => 'bob.martin@example.com',
                'first_name' => 'Bob',
                'last_name' => 'Martin',
                'roles' => UserRoles::admin,
                'password' => 'password2',
            ],
            [
                'reference' => self::USER_CHARLIE_REFERENCE,
                'email' => 'charlie.dubois@example.com',
                'first_name' => 'Charlie',
                'last_name' => 'Dubois',
                'roles' => UserRoles::customer,
                'password' => 'password3',
            ],
            [
                'reference' => self::USER_DAVID_REFERENCE,
                'email' => 'david.moreau@example.com',
                'first_name' => 'David',
                'last_name' => 'Moreau',
                'roles' => UserRoles::customer,
                'password' => 'password4',
            ],
            [
                'reference' => self::USER_EVE_REFERENCE,
                'email' => 'eve.lambert@example.com',
                'first_name' => 'Eve',
                'last_name' => 'Lambert',
                'roles' => UserRoles::customer,
                'password' => 'password5',
            ],
        ];

        foreach ($usersData as $userData) {
            $user = new User();
            $user->setId(Uuid::v4());
            $user->setEmail($userData['email']);
            $user->setFirstName($userData['first_name']);
            $user->setLastName($userData['last_name']);
            $user->setRoles($userData['roles']);

            // Hachage du mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);

            // Ajout de la référence pour cet utilisateur
            $this->addReference($userData['reference'], $user);

            $manager->persist($user);
        }

        // Enregistre tous les utilisateurs en une seule opération
        $manager->flush();
    }
}
