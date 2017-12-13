<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @return Book[]
     */
    public function findAllBooks()
    {
        $qb =
            $this->createQueryBuilder('b')
                ->select('b.id as id, b.title as title, b.added_on as added_on, b.published_on as published_on, b.raiting as raiting, a.id as author_id, a.name as author_name, g.genre as genre, g.id as genre_id')
                ->leftJoin('App\Entity\Author', 'a', Join::WITH, 'b.author_id = a.id')
                ->leftJoin('App\Entity\Genre', 'g', Join::WITH, 'b.genre_id = g.id')
                ->getQuery()
        ;

        return $qb->getArrayResult();
    }
}