<?php

namespace App\Repository;

use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Video::class);

        $this->paginator = $paginator;
    }

    public function findByChildIds(array $values, int $page, ?string $sort_method)
    {

        if ($sort_method != 'rating') {
            $dbquery = $this->createQueryBuilder('v')
                ->andWhere('v.category IN (:val)')
                ->leftJoin('v.comments', 'c') // we have 23 queries on load without this join
                ->addSelect('c') // we use eager load so query count on load would drop 18 quieries
                ->leftJoin('v.usersThatLike', 'l')
                ->leftJoin('v.usersThatDontLike', 'd')
                ->addSelect('l', 'd') // 8 queries left
                ->setParameter('val', $values)
                ->orderBy('v.title', $sort_method);

        } else {
            $dbquery = $this->createQueryBuilder('v')
                ->addSelect('COUNT(l) AS HIDDEN likes') //hidden hides likes array from dbquery, if its removed, the dump of query would be different so we would need to change twig template
                ->leftJoin('v.usersThatLike', 'l')
                ->andWhere('v.category IN (:val)')
                ->setParameter('val', $values)
                ->groupBy('v')
                ->orderBy('likes', 'DESC');

        }
        $dbquery->getQuery();
        $pagination = $this->paginator->paginate($dbquery, $page, Video::perPage); // last number is default value of paginated items on page
        return $pagination;
    }

    public function findByTitle(string $query, int $page, ?string $sort_method)
    {

        $queryBuilder = $this->createQueryBuilder('v');

        $searchTerms = $this->prepareQuery($query);

        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('v.title LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.trim($term).'%');
        }
        if ($sort_method != 'rating') {
            $dbQuery = $queryBuilder
                ->leftJoin('v.comments', 'c')
                ->leftJoin('v.usersThatLike', 'l')
                ->leftJoin('v.usersThatDontLike', 'd')
                ->addSelect('c', 'l', 'd')
                ->orderBy('v.title', $sort_method);

        } else {
            $dbQuery = $this->createQueryBuilder('v')
                ->addSelect('COUNT(l) AS HIDDEN likes', 'c', 'd')
                ->leftJoin('v.usersThatLike', 'l')
                ->leftJoin('v.comments', 'c')
                ->leftJoin('v.usersThatDontLike', 'd')
                ->groupBy('v', 'c', 'd')
                ->orderBy('likes', 'DESC');
        }
        $dbQuery->getQuery();
        return $this->paginator->paginate($dbQuery, $page, Video::perPage);
    }

    private function prepareQuery(string $query): array
    {   // using this for instance if we search home alone alone, our where clause
        // would be repeated two times so it will slow down our
        // query in this case we gonna use array_unique so we remove repeated string
        $terms =  array_unique(explode(' ', $query));

        return array_filter($terms, function ($term) {
            // check if lenght of search query greater than two in case not its deleted from the array
            // before this query "fa m " returned family and movies, now it returns only family videos
            return 2<= mb_strlen($term);
        });
    }

    public function videoDetails($id)
    {
        //this way we use one query to load all three tables if we remove ->addSelect()
        // we gonna go for lazy load which means it will be load if we request it in twig template but
        // it gonna cost more transactions form DB

        return $this->createQueryBuilder('v')
            ->leftJoin('v.comments', 'c')
            ->leftJoin('c.user', 'u')
            ->addSelect('c', 'u')
            ->where('v.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
