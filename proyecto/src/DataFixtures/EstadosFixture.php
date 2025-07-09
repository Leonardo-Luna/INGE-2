<?php

namespace App\DataFixtures;

use App\Entity\EstadoReserva;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class EstadosFixture extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager): void
    {
        $estadoAprobada = new EstadoReserva();
        $estadoAprobada->setEstado("APROBADO");
        $estadoAprobada->setDescripcion("Estado para los alquileres que hayan sido pagados (el único tipo que se tiene en cuenta en el calendario).");
        $manager->persist($estadoAprobada);

        $estadoFaltaDePago = new EstadoReserva();
        $estadoFaltaDePago->setEstado("FALTA DE PAGO");
        $estadoFaltaDePago->setDescripcion("Estado para las reservas que hayan sido generadas pero nunca pagadas.");
        $manager->persist($estadoFaltaDePago);

        $estadoCancelada = new EstadoReserva();
        $estadoCancelada->setEstado("CANCELADA");
        $estadoCancelada->setDescripcion("Estado para las reservas que hayan sido canceladas manualmente.");
        $manager->persist($estadoCancelada);

        $estadoFinalizada = new EstadoReserva();
        $estadoFinalizada->setEstado("FINALIZADO");
        $estadoFinalizada->setDescripcion("Estado para los alquileres que hayan finalizado y la máquina haya sido devuelta.");
        $manager->persist($estadoFinalizada);

        $estadoEnCurso = new EstadoReserva();
        $estadoEnCurso->setEstado("EN_CURSO");
        $estadoEnCurso->setDescripcion("Estado para los alquileres que no hayan finalizado.");
        $manager->persist($estadoEnCurso);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtureEstados'];
    }
}