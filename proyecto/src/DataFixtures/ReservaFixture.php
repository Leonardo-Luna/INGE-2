<?php

namespace App\DataFixtures;

use App\Entity\EstadoReserva;
use App\Entity\Maquina;
use App\Entity\Reserva;
use App\Entity\Rol;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ReservaFixture extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager): void
    {
        $usuarioPepe = $manager->getRepository(User::class)->findOneBy(['email' => 'pepe@gmail.com']);
        $tractor = $manager->getRepository(Maquina::class)->findOneBy(['nombre' => 'Tractor 5060E']);
        $estadoAprobada = $manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA);
        $estadoCancelada = $manager->getRepository(EstadoReserva::class)->find(EstadoReserva::CANCELADA);

        $reserva = new Reserva();
        $reserva->setMaquina($tractor);
        $reserva->setUsuario($usuarioPepe);
        $reserva->setCostoTotal(60000);
        $reserva->setMontoReembolso(300);
        $reserva->setFechaReembolsoPenalizado(new DateTime('2025-06-05'));
        $reserva->setEstado($estadoAprobada->getEstado());
        $reserva->setFechaInicio(new DateTime('2025-06-10'));
        $reserva->setFechaFin(new DateTime('2025-06-30'));
        $manager->persist($reserva);

        $reservaCancelada = new Reserva();
        $reservaCancelada->setMaquina($tractor);
        $reservaCancelada->setUsuario($usuarioPepe);
        $reservaCancelada->setCostoTotal(60000);
        $reservaCancelada->setMontoReembolso(300);
        $reservaCancelada->setFechaReembolsoPenalizado(new DateTime('2025-06-05'));
        $reservaCancelada->setEstado($estadoCancelada->getEstado());
        $reservaCancelada->setFechaInicio(new DateTime('2025-06-10'));
        $reservaCancelada->setFechaFin(new DateTime('2025-06-30'));
        $manager->persist($reservaCancelada);
        
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtureReservas'];
    }
}