<?php
/**
 * Created by PhpStorm.
 * User: Никита
 * Date: 16.12.2017
 * Time: 16:24
 */

namespace App\Repository;


use App\Entity\Favorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    public function addBookToFavorites($userId, $bookId)
    {
        $em = $this->getDoctrine()->getManager();
        $qb =
            $this->createQueryBuilder('f')
                ->where('f.user_id = '. $userId)
                ->where('f.book_id = '. $bookId)
                ->getQuery()
        ;

        $results = $qb->execute();
        var_dump($results);
        if (count($results)) {
            var_dump($results);
        } else {
            $favorite = new Favorite();
//            $f
        }
    }

    public function removeBookFromFavorites($userId, $bookId)
    {

    }
}