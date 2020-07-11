<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
    * @return Article[] Returns an array of Article objects
    */
    public function findSevenLatest() : array
    {
        // Return pour retourner ce que la requête DQL aura trouvé en base de données
        return $this->createQueryBuilder('a')   // a = alias de la table "article"
        ->orderBy('a.publicationDate', 'DESC')   // order by publication_date
        ->setMaxResults(7)
        ->getQuery()    // Execution de la requête
        ->getResult()   // Récupération du résultat de la requête
        ;
    }

}
