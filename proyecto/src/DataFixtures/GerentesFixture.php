<?php

namespace App\DataFixtures;

use App\Entity\Rol;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class GerentesFixture extends Fixture implements FixtureGroupInterface
{
    public function __construct(private EntityManagerInterface $manager) { }
    
    public function load(ObjectManager $manager): void
    {
        $rolGerente = $this->manager->getRepository(Rol::class)->find(Rol::GERENTE);
        $rolAutenticado = $this->manager->getRepository(Rol::class)->find(Rol::AUTENTICADO);
        
        $gerenteLuigi = new Usuario();
        $gerenteLuigi->setNombre("Luigi");
        $gerenteLuigi->setApellido("Mario");
        $gerenteLuigi->setDni("12345678");
        $gerenteLuigi->setEmail("luigimario@gmail.com"); // Deberíamos reemplazarlo por un mail real para que lleguen códigos de 2FA...
        $gerenteLuigi->setPassword(hash('sha256', 'LuigiMario_123'));
        $gerenteLuigi->addRole($rolGerente);
        $gerenteLuigi->addRole($rolAutenticado);
        $this->manager->persist($gerenteLuigi);

        $gerenteMario = new Usuario();
        $gerenteMario->setNombre("Mario");
        $gerenteMario->setApellido("Mario");
        $gerenteMario->setDni("12345678");
        $gerenteMario->setEmail("mariomario@gmail.com"); // Deberíamos reemplazarlo por un mail real para que lleguen códigos de 2FA...
        $gerenteMario->setPassword(hash('sha256', 'MarioMario_123'));
        $gerenteMario->addRole($rolGerente);
        $gerenteMario->addRole($rolAutenticado);
        $this->manager->persist($gerenteMario);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtureGerentes'];
    }
}
