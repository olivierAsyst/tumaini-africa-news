<?php

namespace App\Repository;

use App\Entity\Audio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Audio>
 */
class AudioRepository extends ServiceEntityRepository
{
    private $paginator;
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Audio::class);
        $this->paginator = $paginator;
    }

    /**
     * 
     * @param int $page
     * @param int $limit
     */
    public function findAllPaginated(int $page = 1, int $limit = 20)
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC') // Exemple de tri par date de création
            ->getQuery();
            
        return $this->paginator->paginate(
            $query,    // Requête Doctrine
            $page,      // Numéro de page
            $limit      // Limite par page
        );
    }
    //    /**
    //     * @return Audio[] Returns an array of Audio objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Audio
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
