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
        $usuarioPepe = $manager->getRepository(User::class)->findOneBy(['email' => 'lunaleonardo031@gmail.com']);
        $usuarioPipo = $manager->getRepository(User::class)->findOneBy(['email' => 'matiterrone@gmail.com']);
        $tractor = $manager->getRepository(Maquina::class)->findOneBy(['nombre' => 'Tractor 5060E']);
        $cosechadora = $manager->getRepository(Maquina::class)->findOneBy(['nombre' => 'Cosechadora S760']);
        $plantadora = $manager->getRepository(Maquina::class)->findOneBy(['nombre' => 'Plantadora Grano Grueso DB50']);
        $estadoAprobada = $manager->getRepository(EstadoReserva::class)->find(EstadoReserva::APROBADA);
        $estadoCancelada = $manager->getRepository(EstadoReserva::class)->find(EstadoReserva::CANCELADA);
        $estadoEnCurso = $manager->getRepository(EstadoReserva::class)->find(EstadoReserva::EN_CURSO);

        $reserva = new Reserva();
        $reserva->setMaquina($tractor);
        $reserva->setUsuario($usuarioPepe);
        $reserva->setCostoTotal(60000);
        $reserva->setMontoReembolso(300);
        $reserva->setFechaReembolsoPenalizado(new DateTime('2025-06-05'));
        $reserva->setEstado($estadoAprobada->getEstado());
        $reserva->setFechaInicio(new DateTime('2025-06-10'));
        $reserva->setFechaFin(new DateTime('2025-06-30'));
        $reserva->setCreacion(new DateTime('2025-06-5'));
        $manager->persist($reserva);

        $reserva2 = new Reserva();
        $reserva2->setMaquina($tractor);
        $reserva2->setUsuario($usuarioPepe);
        $reserva2->setCostoTotal(60000);
        $reserva2->setMontoReembolso(300);
        $reserva2->setFechaReembolsoPenalizado(new DateTime('2025-06-05'));
        $reserva2->setEstado($estadoAprobada->getEstado());
        $reserva2->setFechaInicio(new DateTime('2025-07-10'));
        $reserva2->setFechaFin(new DateTime('2025-07-30'));
        $reserva2->setCreacion(new DateTime('2025-06-5'));
        $manager->persist($reserva2);

        $reserva3 = new Reserva();
        $reserva3->setMaquina($tractor);
        $reserva3->setUsuario($usuarioPipo);
        $reserva3->setCostoTotal(60000);
        $reserva3->setMontoReembolso(300);
        $reserva3->setFechaReembolsoPenalizado(new DateTime('2025-06-05'));
        $reserva3->setEstado($estadoAprobada->getEstado());
        $reserva3->setFechaInicio(new DateTime('2025-08-10'));
        $reserva3->setFechaFin(new DateTime('2025-08-30'));
        $reserva3->setCreacion(new DateTime('2025-06-5'));
        $manager->persist($reserva3);

        $reservaCancelada = new Reserva();
        $reservaCancelada->setMaquina($plantadora);
        $reservaCancelada->setUsuario($usuarioPepe);
        $reservaCancelada->setCostoTotal(60000);
        $reservaCancelada->setMontoReembolso(300);
        $reservaCancelada->setFechaReembolsoPenalizado(new DateTime('2025-06-05'));
        $reservaCancelada->setEstado($estadoCancelada->getEstado());
        $reservaCancelada->setFechaInicio(new DateTime('2025-06-10'));
        $reservaCancelada->setFechaFin(new DateTime('2025-06-30'));
        $reservaCancelada->setCreacion(new DateTime('2025-06-5'));
        $manager->persist($reservaCancelada);
        

        $reservaEnCurso = new Reserva();
        $reservaEnCurso->setMaquina($plantadora);
        $reservaEnCurso->setUsuario($usuarioPepe);
        $reservaEnCurso->setCostoTotal(60000);
        $reservaEnCurso->setMontoReembolso(300);
        $reservaEnCurso->setFechaReembolsoPenalizado(new DateTime('2025-06-05'));
        $reservaEnCurso->setEstado($estadoEnCurso->getEstado());
        $reservaEnCurso->setFechaInicio(new DateTime('2025-06-10'));
        $reservaEnCurso->setFechaFin(new DateTime('2025-07-10'));
        $reservaEnCurso->setCreacion(new DateTime('2025-06-5'));
        $manager->persist($reservaEnCurso);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtureReservas'];
    }
}