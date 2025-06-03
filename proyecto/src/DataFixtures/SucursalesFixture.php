<?php

namespace App\DataFixtures;

use App\Entity\Sucursal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use App\Services\MapService;

class SucursalesFixture extends Fixture implements FixtureGroupInterface
{

    public function __construct(private MapService $mapService) { }

    public function load(ObjectManager $manager): void
    {
        $sucursal1 = new Sucursal();
        $sucursal1->setNombre("Avenida 60 entre 5 y 6");
        $sucursal1->setDireccion("Avenida 60 531");
        $sucursal1->setCiudad("La Plata");
        $sucursal1->setHorario("8:00 a 19:00");

        $coords = $this->mapService->calcularCoordenadas($sucursal1->getDireccion());

        $sucursal1->setLatitud($coords['lat']);
        $sucursal1->setLongitud($coords['lon']);
        $manager->persist($sucursal1);

        ###

        $sucursal2 = new Sucursal();
        $sucursal2->setNombre("Calle 2 entre 59 y 60");
        $sucursal2->setDireccion("Calle 2 1313");
        $sucursal2->setCiudad("La Plata");
        $sucursal2->setHorario("8:00 a 20:00");

        $coords = $this->mapService->calcularCoordenadas($sucursal2->getDireccion());

        $sucursal2->setLatitud($coords['lat']);
        $sucursal2->setLongitud($coords['lon']);
        $manager->persist($sucursal2);

        ###

        $sucursal3 = new Sucursal();
        $sucursal3->setNombre("Calle 2 entre 62 y 63");
        $sucursal3->setDireccion("Calle 2 1470");
        $sucursal3->setCiudad("La Plata");
        $sucursal3->setHorario("13:00 a 15:00");

        $coords = $this->mapService->calcularCoordenadas($sucursal3->getDireccion());

        $sucursal3->setLatitud($coords['lat']);
        $sucursal3->setLongitud($coords['lon']);
        $manager->persist($sucursal3);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtureSucursales'];
    }
}