<?php

namespace App\Entity;

use App\Repository\ReservaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservaRepository::class)]
class Reserva
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Maquina $maquina = null;

    #[ORM\Column]
    private ?int $costoTotal = null;

    #[ORM\Column]
    private ?int $montoReembolso = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fechaReembolsoPenalizado = null;

    #[ORM\Column(nullable: true)]
    private ?int $reembolsoPenalizado = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fechaInicio = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fechaFin = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $usuario = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaquina(): ?Maquina
    {
        return $this->maquina;
    }

    public function setMaquina(?Maquina $maquina): self
    {
        $this->maquina = $maquina;
        return $this;
    }

    public function getCostoTotal(): ?int
    {
        return $this->costoTotal;
    }

    public function setCostoTotal(int $costoTotal): self
    {
        $this->costoTotal = $costoTotal;
        return $this;
    }

    public function getMontoReembolso(): ?int
    {
        return $this->montoReembolso;
    }

    public function setMontoReembolso(int $montoReembolso): self
    {
        $this->montoReembolso = $montoReembolso;
        return $this;
    }

    public function getFechaReembolsoPenalizado(): ?\DateTimeInterface
    {
        return $this->fechaReembolsoPenalizado;
    }

    public function setFechaReembolsoPenalizado(\DateTimeInterface $fecha): self
    {
        $this->fechaReembolsoPenalizado = $fecha;
        return $this;
    }

    public function getReembolsoPenalizado(): ?int
    {
        return $this->reembolsoPenalizado;
    }

    public function setReembolsoPenalizado(?int $monto): self
    {
        $this->reembolsoPenalizado = $monto;
        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;
        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTimeInterface $fechaInicio): self
    {
        $this->fechaInicio = $fechaInicio;
        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fechaFin;
    }

    public function setFechaFin(\DateTimeInterface $fechaFin): self
    {
        $this->fechaFin = $fechaFin;
        return $this;
    }

    public function getUsuario(): ?user
    {
        return $this->usuario;
    }

    public function setUsuario(?user $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }
}
