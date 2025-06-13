<?php

namespace App\DataFixtures;

use App\Entity\Maquina;
use App\Entity\Sucursal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class MaquinariaFixture extends Fixture implements FixtureGroupInterface
{

    public function __construct(private EntityManagerInterface $manager) {}

    public function load(ObjectManager $manager): void
    {
        $sucursalSanti1 = $this->manager->getRepository(Sucursal::class)->findOneBy(['direccion' => 'Avenida 60 531']);
        $sucursalSanti2 = $this->manager->getRepository(Sucursal::class)->findOneBy(['direccion' => 'Calle 2 1313']);
        $sucursalMati = $this->manager->getRepository(Sucursal::class)->findOneBy(['direccion' => 'Avenida Alvear 548']);

        $maquinaS760 = new Maquina();
        $maquinaS760->setNombre("Cosechadora S760");
        $maquinaS760->setMarca("John Deere");
        $maquinaS760->setDescripcion("Motor PowerTech 9.0 L con 320 hp de potencia.");
        $maquinaS760->setCostoPorDia(3000);
        $maquinaS760->setAnio(2023);
        $maquinaS760->setMinimoDias(15);
        $maquinaS760->setTipo("Motor gasolero");
        $maquinaS760->setReembolsoNormal(15);
        $maquinaS760->setDiasReembolso(5);
        $maquinaS760->setReembolsoPenalizado(30);
        $maquinaS760->setUbicacion($sucursalSanti1);
        $maquinaS760->addImagen("fixtures/m/S760.png");
        $manager->persist($maquinaS760);

        $maquina5070E = new Maquina();
        $maquina5070E->setNombre("Tractor 5060E");
        $maquina5070E->setMarca("John Deere");
        $maquina5070E->setDescripcion("Motor John Deere de 60 hp, 3 cilindros.");
        $maquina5070E->setCostoPorDia(10);
        $maquina5070E->setAnio(2024);
        $maquina5070E->setMinimoDias(30);
        $maquina5070E->setTipo("Motor gasolero");
        $maquina5070E->setReembolsoNormal(50);
        $maquina5070E->setDiasReembolso(3);
        $maquina5070E->setReembolsoPenalizado(10);
        $maquina5070E->setUbicacion($sucursalMati);
        $maquina5070E->addImagen("fixturxes/m/5060E.png");
        $manager->persist($maquina5070E);

        $maquinaDB50 = new Maquina();
        $maquinaDB50->setNombre("Plantadora Grano Grueso DB50");
        $maquinaDB50->setMarca("John Deere");
        $maquinaDB50->setDescripcion("Dosificadores elétricos con tasa variable, desligue de hileras y pulmones de presión descendente.");
        $maquinaDB50->setCostoPorDia(30000);
        $maquinaDB50->setAnio(2025);
        $maquinaDB50->setMinimoDias(5);
        $maquinaDB50->setTipo("Motor eléctrico");
        $maquinaDB50->setReembolsoNormal(90);
        $maquinaDB50->setDiasReembolso(75);
        $maquinaDB50->setReembolsoPenalizado(10);
        $maquinaDB50->setUbicacion($sucursalSanti2);
        $maquinaDB50->addImagen("fixtures/m/DB50.png");
        $manager->persist($maquinaDB50);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtureMaquinaria'];
    }
}