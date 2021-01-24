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

        $dbquery = $this->createQueryBuilder('v')
            ->where('v.category IN (:val)')
            ->setParameter('val', $values)
            ->groupBy('v');


        $dbquery->getQuery();
        $pagination = $this->paginator->paginate($dbquery, $page, Video::perPage); // last number is default value of paginated items on page
        return $pagination;
    }
}
