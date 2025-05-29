<?php

namespace App\DataFixtures;

use App\Entity\Rol;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GerentesFixture extends Fixture implements FixtureGroupInterface
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher) { }
   
    public function load(ObjectManager $manager): void
    {
        $rolGerente = $manager->getRepository(Rol::class)->find(Rol::GERENTE)->getNombre();
        $rolAutenticado = $manager->getRepository(Rol::class)->find(Rol::AUTENTICADO)->getNombre();
        
        $gerenteLuigi = new User();
        $gerenteLuigi->setNombre("Luigi");
        $gerenteLuigi->setApellido("Mario");
        $gerenteLuigi->setDni("12345678");
        $gerenteLuigi->setEmail("aritzblesa@gmail.com"); // Deberíamos reemplazarlo por un mail real para que lleguen códigos de 2FA...
        $gerenteLuigi->setPassword($this->passwordHasher->hashPassword($gerenteLuigi, 'LuigiMario_123'));
        $gerenteLuigi->addRole($rolAutenticado);
        $gerenteLuigi->addRole($rolGerente);
        $manager->persist($gerenteLuigi);

        $gerenteMario = new User();
        $gerenteMario->setNombre("Mario");
        $gerenteMario->setApellido("Mario");
        $gerenteMario->setDni("12345678");
        $gerenteMario->setEmail("oscar.stanchi@gmail.com"); // Deberíamos reemplazarlo por un mail real para que lleguen códigos de 2FA...
        $gerenteMario->setPassword($this->passwordHasher->hashPassword($gerenteMario, 'MarioMario_123'));
        $gerenteMario->addRole($rolAutenticado);
        $gerenteMario->addRole($rolGerente);
        $manager->persist($gerenteMario);

        $testingLeo = new User();
        $testingLeo->setNombre("Leo");
        $testingLeo->setApellido("Luna");
        $testingLeo->setDni("12345678");
        $testingLeo->setEmail("lunaleonardo031@gmail.com");
        $testingLeo->setPassword($this->passwordHasher->hashPassword($testingLeo, "abc123"));
        $testingLeo->addRole($rolAutenticado);
        $manager->persist($testingLeo);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtureGerentes'];
    }
}
