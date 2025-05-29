<?php

namespace App\Entity;

use App\Repository\ReservaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservaRepository::class)]
class Reserva
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Maquina $maquina = null;

    #[ORM\Column]
    private ?int $costoTotal = null;

    #[ORM\Column]
    private ?int $montoReembolso = null;

    #[ORM\Column]
    private ?DateTimeInterface $fechaReembolsoPenalizado = null;

    #[ORM\Column(nullable: true)]
    private ?int $reembolsoPenalizado = null;

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaquina(): ?Maquina
    {
        return $this->maquina;
    }

    public function setMaquina(?Maquina $Maquina): static
    {
        $this->maquina = $Maquina;

        return $this;
    }

    public function getFechas(): ?FechasReserva
    {
        return $this->fechas;
    }

    public function setFechas(FechasReserva $Fechas): static
    {
        $this->Fechas = $Fechas;

        return $this;
    }

    public function setId(int $Id): static
    {
        $this->Id = $Id;

        return $this;
    }

    public function getCostoTotal(): ?int
    {
        return $this->CostoTotal;
    }

    public function setCostoTotal(int $CostoTotal): static
    {
        $this->CostoTotal = $CostoTotal;

        return $this;
    }

    public function getMontoReembolso(): ?int
    {
        return $this->MontoReembolso;
    }

    public function setMontoReembolso(int $MontoReembolso): static
    {
        $this->MontoReembolso = $MontoReembolso;

        return $this;
    }
}
