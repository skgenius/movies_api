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

    public function removeAll(){
        $query = $this->createQueryBuilder('e')
                 ->delete()
                 ->getQuery()
                 ->execute();
        return $query;
    }

    public function storeListFromApi() {         
        $results = $this->webservice->getData('movie/now_playing', array());
        //Ensure to store 20 records
        if($results['results']) $this->removeAll();

        //Store 20 records
        foreach($results['results'] AS $result) {
            $entity = new Movie();
            $entity->setIdApi($result['id']);
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


    public function getAll()
    {
        $em = $this->registry->getManager();
        $cn = $em->getConnection();
        $st = $cn->prepare(
            "SELECT condition.id, condition.titre, condition.reponse_obligatoire, 
            condition.reponse_multiple, condition.question_libelle, condition.is_actif, 
                COALESCE(jsonb_agg(distinct jsonb_build_object(    
                    'id', condition_questions.id,
                    'libelle', condition_questions.reponse_libelle,
                    'reponse', condition_questions.reponse
                    )::jsonb) FILTER (WHERE condition_questions.is_actif = TRUE) ,'[]'::jsonb) AS reponses 
            FROM condition
            left join condition_questions on condition_questions.condition_id = condition.id 
                WHERE condition.is_actif = TRUE
                GROUP BY condition.id"
        );
        $st->execute();
        $result = $st->fetchAll();
        foreach ($result as $key => $field) {
            $result[$key]['reponses'] = json_decode($field['reponses']);
        }
        return $result;
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
