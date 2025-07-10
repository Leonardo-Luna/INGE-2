<?php

namespace App\Repository;

use App\Entity\Maquina;
use App\Entity\Sucursal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaquinaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Maquina::class);
    }

    public function findFiltered(?string $search, ?string $disponibilidad, ?string $tipo, ?int $sucursalId): array
    {
        $qb = $this->createQueryBuilder('m');

        // Filtro por búsqueda (nombre, marca, descripción)
        if ($search) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('m.nombre', ':search'),
                    $qb->expr()->like('m.marca', ':search'),
                    $qb->expr()->like('m.descripcion', ':search')
                )
            )
            ->setParameter('search', '%' . $search . '%');
        }

        // Filtro por disponibilidad
        if ($disponibilidad) {
            if ($disponibilidad === 'disponible') {
                $qb->andWhere('m.enReparacion = :enReparacion')->setParameter('enReparacion', false);
            } elseif ($disponibilidad === 'en_reparacion') {
                $qb->andWhere('m.enReparacion = :enReparacion')->setParameter('enReparacion', true);
            }
        }

        // Filtro por tipo
        if ($tipo) {
            $qb->andWhere('m.tipo = :tipo')->setParameter('tipo', $tipo);
        }

        // Filtro por sucursal
        if ($sucursalId) {
            $qb->andWhere('m.ubicacion = :sucursalId')->setParameter('sucursalId', $sucursalId);
        }

        $qb->orderBy('m.nombre', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function filtrarDisponibles() {
        $qb = $this->createQueryBuilder('m');

        $qb->select('m')
            ->where('m.enReparacion = false');

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function getMasAlquiladas(string $estadoAprobada, string $estadoFinalizada): array
    {
        return $this->createQueryBuilder('m')
            ->select('m, COUNT(r.id) AS reservasCount')
            ->leftJoin('m.reservas', 'r')
            ->andWhere('r.estado IN (:estados)')
            ->setParameter('estados', [$estadoAprobada, $estadoFinalizada])
            ->groupBy('m.id')
            ->orderBy('reservasCount', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
    public function tieneAlquileres(Maquina $maquina, string $estadoAprobada, string $estadoEnCurso)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.reservas', 'r')
            ->andWhere('m.id = :id')
            ->andWhere('r.estado IN :estados')
            ->setParameter('id', $maquina->getId())
            ->setParameter('estados', [$estadoAprobada, $estadoEnCurso])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Cuenta el número de máquinas asociadas a una sucursal específica.
     *
     * @param Sucursal $sucursal La entidad Sucursal a buscar.
     * @return int El número de máquinas asociadas.
     */
    public function countBySucursal(Sucursal $sucursal): int
    {
        return $this->createQueryBuilder('m') // 'm' es el alias para la entidad Maquina
            ->select('COUNT(m.id)')           
            ->andWhere('m.ubicacion = :sucursal') 
            ->setParameter('sucursal', $sucursal) 
            ->getQuery()                      
            ->getSingleScalarResult();        // Ejecuta la consulta y devuelve un único resultado escalar (el conteo)
    }
}
