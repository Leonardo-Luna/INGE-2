<?php

namespace App\Entity;

use App\Repository\FechasReservaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FechasReservaRepository::class)]
class FechasReserva
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fechaInicio = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fechaFin = null;

    #[ORM\ManyToOne(inversedBy: 'fechasOcupadas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Maquina $relacionMaquina = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTimeInterface $fechaInicio): static
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fechaFin;
    }

    public function setFechaFin(\DateTimeInterface $fechaFin): static
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getRelacionMaquina(): ?Maquina
    {
        return $this->relacionMaquina;
    }

    public function setRelacionMaquina(?Maquina $relacionMaquina): static
    {
        $this->relacionMaquina = $relacionMaquina;

        return $this;
    }
}
