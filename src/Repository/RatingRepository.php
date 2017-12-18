<?php
/**
 * Created by PhpStorm.
 * User: Никита
 * Date: 17.12.2017
 * Time: 16:09
 */

namespace App\Repository;


use App\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RatingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    public function findBooksRatingsOfUser($userId, $books = [])
    {
        $booksIds = [];
        foreach ($books as $book)
        {
            $booksIds[] = $book['id'];
        }

        $qb =
            $this->createQueryBuilder('r')
                ->andWhere('r.user_id = ' . $userId)
                ->andWhere('r.book_id in (' . implode(',', $booksIds) .')')
                ->andWhere('r.active = 1')
                ->getQuery()
        ;

        $result = [];
        foreach ($qb->getArrayResult() as $item) {
            $result[$item['book_id']] = $item['score'];
        }

        return $result;

    }
}