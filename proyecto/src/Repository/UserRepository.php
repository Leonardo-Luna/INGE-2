<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.eliminado = :val') 
            ->setParameter('val', false)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getClientesMasReservas(string $estadoAprobado, string $estadoFinalizado): array
    {
        return $this->createQueryBuilder('u')
            ->select('u, COUNT(r.id) AS reservasAprobadas')
            ->leftJoin('u.reservas', 'r')
            ->andWhere('r.estado IN (:estados)')
            ->setParameter('estados', [$estadoAprobado, $estadoFinalizado])
            ->groupBy('u.id')
            ->orderBy('reservasAprobadas', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findOneByDniAndRole(string $dni, string $role): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.dni = :dni')
            ->andWhere('u.roles LIKE :role') 
            ->setParameter('dni', $dni)
            ->setParameter('role', '%' . $role . '%')
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findOneUniqueUser(string $dni, string $role, string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.dni = :dni')
            ->orWhere('u.email = :email')
            ->andWhere('u.roles LIKE :role') 
            ->setParameter('dni', $dni)
            ->setParameter('email', $email)
            ->setParameter('role', '%' . $role . '%')
            ->getQuery()
            ->getOneOrNullResult();
    }
    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
