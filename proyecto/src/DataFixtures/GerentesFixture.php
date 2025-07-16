<?php

namespace App\DataFixtures;

use App\Entity\Rol;
use App\Entity\User;
use DateTime;
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
        $rolEmpleado = $manager->getRepository(Rol::class)->find(Rol::EMPLEADO)->getNombre();
        
        $gerenteLuigi = new User();
        $gerenteLuigi->setNombre("Luigi");
        $gerenteLuigi->setApellido("Mario");
        $gerenteLuigi->setDni("20345678");
        $gerenteLuigi->setEmail("santiago0105@hotmail.com.ar"); // Deberíamos reemplazarlo por un mail real para que lleguen códigos de 2FA...
        $gerenteLuigi->setPassword($this->passwordHasher->hashPassword($gerenteLuigi, 'abc123'));
        $gerenteLuigi->addRole($rolAutenticado);
        $gerenteLuigi->addRole($rolGerente);
        $gerenteLuigi->setFechaNacimiento(new DateTime('01/01/2000'));
        $manager->persist($gerenteLuigi);

        $gerenteMario = new User();
        $gerenteMario->setNombre("Mario");
        $gerenteMario->setApellido("Mario");
        $gerenteMario->setDni("20345679");
        $gerenteMario->setEmail("ivtech.unlp@gmail.com"); // Deberíamos reemplazarlo por un mail real para que lleguen códigos de 2FA...
        $gerenteMario->setPassword($this->passwordHasher->hashPassword($gerenteMario, 'abc123'));
        $gerenteMario->addRole($rolAutenticado);
        $gerenteMario->addRole($rolGerente);
        $gerenteMario->setFechaNacimiento(new DateTime('01/01/2000'));
        $manager->persist($gerenteMario);

        //Empleado para testear
        //Podria estar en otro fixture
        $testingLeo = new User();
        $testingLeo->setNombre("Leo");
        $testingLeo->setApellido("Luna");
        $testingLeo->setDni("20345680");
        $testingLeo->setEmail("lunaleonardo031@gmail.com");
        $testingLeo->setPassword($this->passwordHasher->hashPassword($testingLeo, "abc123"));
        $testingLeo->addRole($rolAutenticado);
        $testingLeo->addRole($rolEmpleado);
        $testingLeo->setFechaNacimiento(new DateTime('01/01/2000'));
        $manager->persist($testingLeo);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtureGerentes'];
    }
}