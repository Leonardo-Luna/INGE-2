<?php

namespace App\DataFixtures;

use App\Entity\Rol;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class RolFixture extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager): void
    {
        $rolAutenticado = new Rol();
        $rolAutenticado->setNombre("ROLE_AUTENTICADO");
        $rolAutenticado->setDescripcion("Rol para cualquier persona con una cuenta");
        $manager->persist($rolAutenticado);

        $rolCliente = new Rol();
        $rolCliente->setNombre("ROLE_CLIENTE");
        $rolCliente->setDescripcion("Rol para los clientes (pura y exclusivamente)");
        $manager->persist($rolCliente);

        $rolEmpleado = new Rol();
        $rolEmpleado->setNombre("ROLE_EMPLEADO");
        $rolEmpleado->setDescripcion("Rol para los empleados (no gerentes)");
        $manager->persist($rolEmpleado);
        
        $rolGerente = new Rol();
        $rolGerente->setNombre("ROLE_GERENTE");
        $rolGerente->setDescripcion("Rol para los gerentes");
        $manager->persist($rolGerente);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtureRoles'];
    }
}