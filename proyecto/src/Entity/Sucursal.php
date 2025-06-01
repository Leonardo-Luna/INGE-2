<?php

namespace App\Entity;

use App\Repository\SucursalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SucursalRepository::class)]
class Sucursal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $direccion = null;

    #[ORM\Column(length: 255)]
    private ?string $ciudad = null;

    #[ORM\Column(length: 255)]
    private ?string $horario = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitud = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitud = null;

    /**
     * @var Collection<int, Maquina>
     */
    #[ORM\OneToMany(targetEntity: Maquina::class, mappedBy: 'ubicacion')]
    private Collection $maquinas;

    public function __construct()
    {
        $this->maquinas = new ArrayCollection();
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

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(string $ciudad): static
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getHorario(): ?string
    {
        return $this->horario;
    }

    public function setHorario(string $horario): static
    {
        $this->horario = $horario;

        return $this;
    }

    public function getLatitud(): ?float
    {
        return $this->Latitud;
    }

    public function setLatitud(?float $latitud): static
    {
        $this->latitud = $latitud;

        return $this;
    }

    public function getLongitud(): ?float
    {
        return $this->longitud;
    }

    public function setLongitud(?float $longitud): static
    {
        $this->longitud = $longitud;

        return $this;
    }

    /**
     * @return Collection<int, Maquina>
     */
    public function getMaquinas(): Collection
    {
        return $this->maquinas;
    }

    public function addMaquina(Maquina $maquina): static
    {
        if (!$this->maquinas->contains($maquina)) {
            $this->maquinas->add($maquina);
            $maquina->setUbicacion($this);
        }

        return $this;
    }

    public function removeMaquina(Maquina $maquina): static
    {
        if ($this->maquinas->removeElement($maquina)) {
            // set the owning side to null (unless already changed)
            if ($maquina->getUbicacion() === $this) {
                $maquina->setUbicacion(null);
            }
        }

        return $this;
    }
}
