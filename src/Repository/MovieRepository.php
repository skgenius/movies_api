<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry; 
use App\Service\WebService;

/**
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{ 

    private $webservice;

    public function __construct(
        ManagerRegistry $registry,
        WebService $webservice 
        )
    {
        parent::__construct($registry, Movie::class); 
        $this->webservice = $webservice;  
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Movie $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Movie $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function storeListFromApi() {         
        $results = $this->webservice->getData('movie/now_playing', array());
        foreach($results['results'] AS $result) {
            $entity = new Movie();
            $entity->setOriginalLanguage($result['original_language']);
            $entity->setOriginalTitle($result['original_title']);
            $entity->setOverview($result['overview']);
            $entity->setPopularity($result['popularity']);
            $entity->setPosterPath($result['poster_path']);
            $entity->setReleaseDate(new \DateTime($result['release_date']));
            $entity->setTitle($result['title']);
            $entity->setVideo($result['video']);
            $entity->setVoteAverage($result['vote_average']);
            $entity->setVoteCount($result['vote_count']);
            $entity->setShareEmailCount(0);
            $this->add($entity); 
        } 
    }

    // /**
    //  * @return Movie[] Returns an array of Movie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Movie
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
