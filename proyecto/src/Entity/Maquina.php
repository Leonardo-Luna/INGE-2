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
    private ?string $Nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $Marca = null;

    /**
     * @var Collection<int, FechasReserva>
     */
    #[ORM\OneToMany(targetEntity: FechasReserva::class, mappedBy: 'relacionMaquina', orphanRemoval: true)]
    private Collection $fechasOcupadas;

    #[ORM\Column]
    private ?int $costoPorDIa = null;

    #[ORM\Column(length: 255)]
    private ?string $Descripcion = null;

    #[ORM\Column]
    private ?bool $enReparacion = null;

    #[ORM\Column]
    private ?int $Anio = null;

    #[ORM\Column]
    private ?int $minimoDias = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Tipo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rutaImagen = null;

    #[ORM\Column]
    private ?int $reembolsoNormal = null;

    #[ORM\Column]
    private ?DateTimeInterface $diasReembolso = null;

    #[ORM\Column(nullable: true)]
    private ?int $reembolsoPenalizado = null;

    public function __construct()
    {
        $this->fechasOcupadas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->Nombre;
    }

    public function setNombre(string $Nombre): static
    {
        $this->Nombre = $Nombre;

        return $this;
    }

    public function getMarca(): ?string
    {
        return $this->Marca;
    }

    public function setMarca(string $Marca): static
    {
        $this->Marca = $Marca;

        return $this;
    }

    /**
     * @return Collection<int, FechasReserva>
     */
    public function getFechasOcupadas(): Collection
    {
        return $this->fechasOcupadas;
    }

    public function addFechasOcupada(FechasReserva $fechasOcupada): static
    {
        if (!$this->fechasOcupadas->contains($fechasOcupada)) {
            $this->fechasOcupadas->add($fechasOcupada);
            $fechasOcupada->setRelacionMaquina($this);
        }

        return $this;
    }

    public function removeFechasOcupada(FechasReserva $fechasOcupada): static
    {
        if ($this->fechasOcupadas->removeElement($fechasOcupada)) {
            // set the owning side to null (unless already changed)
            if ($fechasOcupada->getRelacionMaquina() === $this) {
                $fechasOcupada->setRelacionMaquina(null);
            }
        }

        return $this;
    }

    public function getCostoPorDIa(): ?int
    {
        return $this->costoPorDIa;
    }

    public function setCostoPorDIa(int $costoPorDIa): static
    {
        $this->costoPorDIa = $costoPorDIa;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->Descripcion;
    }

    public function setDescripcion(string $Descripcion): static
    {
        $this->Descripcion = $Descripcion;

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
        return $this->Anio;
    }

    public function setAnio(int $Anio): static
    {
        $this->Anio = $Anio;

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
        return $this->Tipo;
    }

    public function setTipo(?string $Tipo): static
    {
        $this->Tipo = $Tipo;

        return $this;
    }

    public function getRutaImagen(): ?string
    {
        return $this->rutaImagen;
    }

    public function setRutaImagen(?string $rutaImagen): static
    {
        $this->rutaImagen = $rutaImagen;

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
}
