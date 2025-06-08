<?php

namespace App\Entity;

use App\Repository\MaquinaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaquinaRepository::class)]
class Maquina
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $marca = null;

    #[ORM\Column]
    private ?int $costoPorDia = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;

    #[ORM\Column]
    private ?bool $enReparacion = false;

    #[ORM\Column]
    private ?int $anio = null;

    #[ORM\Column]
    private ?int $minimoDias = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tipo = null;

    #[ORM\Column]
    private ?int $reembolsoNormal = null;

    #[ORM\Column]
    private ?int $diasReembolso = null;

    #[ORM\Column(nullable: true)]
    private ?int $reembolsoPenalizado = null;

    /**
     * @var Collection<int, Reserva>
     */
    #[ORM\OneToMany(targetEntity: Reserva::class, mappedBy: 'maquina', orphanRemoval: true)] // Corregido 'Maquina' a 'maquina' en mappedBy
    private Collection $reservas;

    #[ORM\Column(nullable: true)]
    private array $imagenes = [];

    #[ORM\ManyToOne(inversedBy: 'maquinas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sucursal $ubicacion = null; // Relación con Sucursal

    public function __construct()
    {
        $this->reservas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getMarca(): ?string
    {
        return $this->marca;
    }

    public function setMarca(string $marca): static
    {
        $this->marca = $marca;

        return $this;
    }

    public function getCostoPorDia(): ?int
    {
        return $this->costoPorDia;
    }

    public function setCostoPorDia(int $costoPorDia): static
    {
        $this->costoPorDia = $costoPorDia;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function isEnReparacion(): ?bool
    {
        return $this->enReparacion;
    }

    public function setEnReparacion(bool $enReparacion): static
    {
        $this->enReparacion = $enReparacion;

        return $this;
    }

    public function getAnio(): ?int
    {
        return $this->anio;
    }

    public function setAnio(int $anio): static
    {
        $this->anio = $anio;

        return $this;
    }

    public function getMinimoDias(): ?int
    {
        return $this->minimoDias;
    }

    public function setMinimoDias(int $minimoDias): static
    {
        $this->minimoDias = $minimoDias;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getReembolsoNormal(): ?int
    {
        return $this->reembolsoNormal;
    }

    public function setReembolsoNormal(int $reembolsoNormal): static
    {
        $this->reembolsoNormal = $reembolsoNormal;

        return $this;
    }

    public function getDiasReembolso(): ?int
    {
        return $this->diasReembolso;
    }

    public function setDiasReembolso(int $diasReembolso): static
    {
        $this->diasReembolso = $diasReembolso;

        return $this;
    }

    public function getReembolsoPenalizado(): ?int
    {
        return $this->reembolsoPenalizado;
    }

    public function setReembolsoPenalizado(?int $reembolsoPenalizado): static
    {
        $this->reembolsoPenalizado = $reembolsoPenalizado;

        return $this;
    }

    /**
     * @return Collection<int, Reserva>
     */
    public function getReservas(): Collection
    {
        return $this->reservas;
    }

    public function addReserva(Reserva $reserva): static
    {
        if (!$this->reservas->contains($reserva)) {
            $this->reservas->add($reserva);
            $reserva->setMaquina($this);
        }

        return $this;
    }

    public function removeReserva(Reserva $reserva): static
    {
        if ($this->reservas->removeElement($reserva)) {
            if ($reserva->getMaquina() === $this) {
                $reserva->setMaquina(null);
            }
        }

        return $this;
    }

    public function getImagenes(): array
    {
        return $this->imagenes;
    }

    public function setImagenes(array $imagenes): static
    {
        $this->imagenes = $imagenes;

        return $this;
    }

    public function addImagen($imagen): static
    {
        $this->imagenes[] = $imagen;

        return $this;
    }

    public function getUbicacion(): ?Sucursal
    {
        return $this->ubicacion;
    }

    public function setUbicacion(?Sucursal $ubicacion): static
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }
}
