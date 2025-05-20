<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $dni = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $apellido = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * @var Collection<int, Rol>
     */
    #[ORM\ManyToMany(targetEntity: Rol::class, inversedBy: 'usuarios')]
    private Collection $roles;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $token2FA = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiracion2FA = null;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): static
    {
        $this->dni = $dni;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
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

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): static
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection<int, Rol>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Rol $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(Rol $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }

    public function hasRole(Rol $role)
    {
        $roles = $this->roles;

        foreach($roles as $rol) {
            if($rol->getId() == $role->getId()) {
                return true;
            }
        }
        return false;
    }

    public function getToken2FA(): ?string
    {
        return $this->token2FA;
    }

    public function setToken2FA(?string $token2FA): static
    {
        $this->token2FA = $token2FA;

        return $this;
    }

    public function getExpiracion2FA(): ?\DateTimeInterface
    {
        return $this->expiracion2FA;
    }

    public function setExpiracion2FA(?\DateTimeInterface $expiracion2FA): static
    {
        $this->expiracion2FA = $expiracion2FA;

        return $this;
    }

}

