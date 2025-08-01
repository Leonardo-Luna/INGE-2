<?php

namespace App\Repository;

use App\Entity\Sucursal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sucursal>
 */
class SucursalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sucursal::class);
    }

    public function getSucursalesMasConcurridas(string $estadoAprobada, string $estadoFinalizado): array
    {
        return $this->createQueryBuilder('s')
            ->select('s, COUNT(r.id) AS reservasCount')
            ->leftJoin('s.maquinas', 'm')
            ->leftJoin('m.reservas', 'r')
            ->where('r.estado IN (:estados)')
            ->groupBy('s.id')
            ->orderBy('reservasCount', 'DESC')
            ->setParameter(':estados', [$estadoAprobada, $estadoFinalizado])
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Sucursal[] Returns an array of Sucursal objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sucursal
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
