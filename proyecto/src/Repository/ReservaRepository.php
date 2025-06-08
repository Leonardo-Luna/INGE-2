<?php

namespace App\Repository;

use App\Entity\Maquina;
use App\Entity\Reserva;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reserva>
 */
class ReservaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reserva::class);
    }

    //    /**
    //     * @return Reserva[] Returns an array of Reserva objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reserva
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    
    public function filtrarReservasPropias(User $user) {
    
        $qb = $this->createQueryBuilder('r');

        $qb->select('r')
            ->where('r.usuario = :usuario')
            ->andWhere($qb->expr()->neq('r.estado', ':estadoAprobada')) # NEQ = not equals

            ->setParameter('usuario', $user)
            ->setParameter('estadoAprobada', 'APROBADA');

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function filtrarAlquileresPropios(User $user) {
    
        $qb = $this->createQueryBuilder('r');

        $qb->select('r')
            ->where('r.usuario = :usuario')
            ->andWhere($qb->expr()->eq('r.estado', ':estadoAprobada')) # EQ = equals
            
            ->setParameter('usuario', $user)
            ->setParameter('estadoAprobada', 'APROBADA');

        $query = $qb->getQuery();
        return $query->getResult();
    }
    
    public function filtrarPorMaquinaYEstado(Maquina $maquina, string $estado) {
        $qb = $this->createQueryBuilder('r');

        $qb->select('r')
            ->where('r.maquina = :maquina')
            ->andWhere('r.estado = :estado')
            
            ->setParameter('maquina', $maquina)
            ->setParameter('estado', $estado);

        $query = $qb->getQuery();
        return $query->getResult();
    }



}
