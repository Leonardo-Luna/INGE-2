<?php

namespace App\DataFixtures;

use App\Entity\Rol;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientesFixture extends Fixture implements FixtureGroupInterface
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher) { }
   
    public function load(ObjectManager $manager): void
    {
        $rolAutenticado = $manager->getRepository(Rol::class)->find(Rol::AUTENTICADO)->getNombre();
        $rolCilente = $manager->getRepository(Rol::class)->find(Rol::CLIENTE)->getNombre();

        $clientePepe = new User();
        $clientePepe->setNombre("Pepe");
        $clientePepe->setApellido("Pérez");
        $clientePepe->setDni("12345678");
        $clientePepe->setEmail("pepe@gmail.com");
        $clientePepe->setPassword($this->passwordHasher->hashPassword($clientePepe, "abc123"));
        $clientePepe->addRole($rolAutenticado);
        $clientePepe->addRole($rolCilente);
        $clientePepe->setValoracionTotal(5);
        $clientePepe->setFechaNacimiento(new DateTime('01/01/2000'));
        $manager->persist($clientePepe);

        $clientePedro = new User();
        $clientePedro->setNombre("Pedro");
        $clientePedro->setApellido("Pérez");
        $clientePedro->setDni("12345679");
        $clientePedro->setEmail("pedro@gmail.com");
        $clientePedro->setPassword($this->passwordHasher->hashPassword($clientePedro, "abc123"));
        $clientePedro->addRole($rolAutenticado);
        $clientePedro->addRole($rolCilente);
        $clientePedro->setValoracionTotal(3);
        $clientePedro->setFechaNacimiento(new DateTime('04/01/2000'));
        $manager->persist($clientePedro);

        $clientePipo = new User();
        $clientePipo->setNombre("Pipo");
        $clientePipo->setApellido("Pérez");
        $clientePipo->setDni("12345680");
        $clientePipo->setEmail("santi2frames@gmail.com");
        $clientePipo->setPassword($this->passwordHasher->hashPassword($clientePipo, "abc123"));
        $clientePipo->addRole($rolAutenticado);
        $clientePipo->addRole($rolCilente);
        $clientePipo->setFechaNacimiento(new DateTime('02/01/2000'));
        $clientePipo->setValoracionTotal(2);
        $manager->persist($clientePipo);

        $clientePato = new User();
        $clientePato->setNombre("Pato");
        $clientePato->setApellido("Pérez");
        $clientePato->setDni("12345681");
        $clientePato->setEmail("chico.comp0@gmail.com");
        $clientePato->setPassword($this->passwordHasher->hashPassword($clientePato, "abc123"));
        $clientePato->addRole($rolAutenticado);
        $clientePato->addRole($rolCilente);
        $clientePato->setValoracionTotal(1);
        $clientePato->setFechaNacimiento(new DateTime('03/01/2000'));
        $manager->persist($clientePato);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtureClientes'];
    }
}