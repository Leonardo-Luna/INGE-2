<?php

namespace App\Repository;

use App\Entity\Maquina;
use App\Entity\Reserva;
use App\Entity\User;
use DateTime;
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
            ->andWhere($qb->expr()->notIn('r.estado', ':estados')) // NOT IN = not in array

            ->setParameter('usuario', $user)
            ->setParameter('estados', ['APROBADO', 'EN CURSO']);

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function buscarReservas() {

        $qb = $this->createQueryBuilder('r');

        $qb->select('r')
            ->andWhere($qb->expr()->notIn('r.estado', ':estados')) // NOT IN = not in array

            ->setParameter('estados', ['APROBADO', 'EN CURSO']);

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function filtrarAlquileresPropios(User $user) {

        $qb = $this->createQueryBuilder('r');

        $qb->select('r')
            ->where('r.usuario = :usuario')
            ->andWhere($qb->expr()->in('r.estado', ':estados')) // IN = in array

            ->setParameter('usuario', $user)
            ->setParameter('estados', ['APROBADO', 'EN CURSO', 'FINALIZADO']);

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

    public function buscarAlquileresEntre(DateTime $fechaUno, DateTime $fechaDos, string $aprobado, string $finalizado, string $enCurso) {
        return $this->createQueryBuilder('r')
            ->where('r.creacion >= :fechaUno')
            ->andWhere('r.creacion <= :fechaDos')
            ->andWhere('r.estado IN (:estados)')
            ->setParameter('fechaUno', $fechaUno)
            ->setParameter('fechaDos', $fechaDos)
            ->setParameter('estados', [$aprobado, $finalizado, $enCurso])
            ->getQuery()
            ->getResult();
    }

}
