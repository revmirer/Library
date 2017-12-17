<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
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

    public function findBooksById($id)
    {
        $qb =
            $this->createQueryBuilder('b')
                ->select('b.id as id, b.title as title, b.added_on as added_on, b.published_on as published_on, b.raiting as raiting, a.id as author_id, a.name as author_name, g.genre as genre, g.id as genre_id, b.image as image')
                ->leftJoin('App\Entity\Author', 'a', Join::WITH, 'b.author_id = a.id')
                ->leftJoin('App\Entity\Genre', 'g', Join::WITH, 'b.genre_id = g.id')
                ->where('b.id = ' . $id)
                ->getQuery()
        ;

        return $qb->getArrayResult();
    }

    public function findBooksByAuthorFilter($filterId)
    {
        $qb =
            $this->createQueryBuilder('b')
                ->select('b.id as id, b.title as title, b.added_on as added_on, b.published_on as published_on, b.raiting as raiting, a.id as author_id, a.name as author_name, g.genre as genre, g.id as genre_id')
                ->leftJoin('App\Entity\Author', 'a', Join::WITH, 'b.author_id = a.id')
                ->leftJoin('App\Entity\Genre', 'g', Join::WITH, 'b.genre_id = g.id')
                ->where('a.id = ' .$filterId)
                ->getQuery()
        ;

        return $qb->getArrayResult();
    }

    public function findBooksByGenreFilter($filterId)
    {
        $qb =
            $this->createQueryBuilder('b')
                ->select('b.id as id, b.title as title, b.added_on as added_on, b.published_on as published_on, b.raiting as raiting, a.id as author_id, a.name as author_name, g.genre as genre, g.id as genre_id')
                ->leftJoin('App\Entity\Author', 'a', Join::WITH, 'b.author_id = a.id')
                ->leftJoin('App\Entity\Genre', 'g', Join::WITH, 'b.genre_id = g.id')
                ->where('g.id = ' .$filterId)
                ->getQuery()
        ;

        return $qb->getArrayResult();
    }

    public function findFavoriteBooksOfUser($userId)
    {
        $qb =
            $this->createQueryBuilder('b')
                ->select('b.id as id, b.title as title, b.added_on as added_on, b.published_on as published_on, b.raiting as raiting, a.id as author_id, a.name as author_name, g.genre as genre, g.id as genre_id')
                ->leftJoin('App\Entity\Author', 'a', Join::WITH, 'b.author_id = a.id')
                ->leftJoin('App\Entity\Genre', 'g', Join::WITH, 'b.genre_id = g.id')
                ->leftJoin('App\Entity\Favorite', 'f', Join::LEFT_JOIN, 'b.id = f.book_id')
                ->where('f.user_id ='. $userId)
                ->where('f.active = 1')
                ->getQuery()
        ;

        return $qb->getArrayResult();
    }

    public function findSimilarBooks($book)
    {
        $bookLimit = 5;
        $results = [];

        if ($bookLimit > 0) {
            $conn = $this->getEntityManager()->getConnection();

            $sql = '
                SELECT b0_.id AS id, b0_.title AS title, b0_.added_on AS added_on, b0_.published_on AS published_on, b0_.raiting AS raiting, a1_.id AS author_id, a1_.name AS author_name, g2_.genre AS genre, g2_.id AS genre_id
                FROM book b0_
                LEFT JOIN author a1_ ON (b0_.author_id = a1_.id)
                LEFT JOIN genre g2_ ON (b0_.genre_id = g2_.id)
                WHERE b0_.genre_id = :genre_id
                AND b0_.id != :id
                ORDER BY RAND()
                LIMIT ' . $bookLimit
            ;
            $stmt = $conn->prepare($sql);
            $stmt->execute(['genre_id' => $book[0]['genre_id'], 'id' => $book[0]['id']]);

            $tempResults = $stmt->fetchAll();
            foreach ($tempResults as $key => $row) {
                $tempResults[$key]['added_on'] = new \DateTime($row['added_on']);
                $tempResults[$key]['published_on'] = new \DateTime($row['published_on']);
            }

            $results = array_merge($results, $tempResults);
            $bookLimit -= count($tempResults);
        }

        if ($bookLimit > 0) {
            $conn = $this->getEntityManager()->getConnection();

            $sql = '
                SELECT b0_.id AS id, b0_.title AS title, b0_.added_on AS added_on, b0_.published_on AS published_on, b0_.raiting AS raiting, a1_.id AS author_id, a1_.name AS author_name, g2_.genre AS genre, g2_.id AS genre_id
                FROM book b0_
                LEFT JOIN author a1_ ON (b0_.author_id = a1_.id)
                LEFT JOIN genre g2_ ON (b0_.genre_id = g2_.id)
                WHERE b0_.genre_id != :genre_id
                AND  b0_.author_id = :author_id
                AND b0_.id != :id
                ORDER BY RAND()
                LIMIT ' . $bookLimit
            ;
            $stmt = $conn->prepare($sql);
            $stmt->execute(['genre_id' => $book[0]['genre_id'], 'id' => $book[0]['id'], 'author_id' => $book[0]['author_id']]);

            $tempResults = $stmt->fetchAll();
            foreach ($tempResults as $key => $row) {
                $tempResults[$key]['added_on'] = new \DateTime($row['added_on']);
                $tempResults[$key]['published_on'] = new \DateTime($row['published_on']);
            }

            $results = array_merge($results, $tempResults);
            $bookLimit -= count($tempResults);
        }

        if ($bookLimit > 0) {
            $conn = $this->getEntityManager()->getConnection();

            $sql = '
                SELECT b0_.id AS id, b0_.title AS title, b0_.added_on AS added_on, b0_.published_on AS published_on, b0_.raiting AS raiting, a1_.id AS author_id, a1_.name AS author_name, g2_.genre AS genre, g2_.id AS genre_id
                FROM book b0_
                LEFT JOIN author a1_ ON (b0_.author_id = a1_.id)
                LEFT JOIN genre g2_ ON (b0_.genre_id = g2_.id)
                WHERE b0_.genre_id != :genre_id
                AND b0_.author_id != :author_id
                AND b0_.id != :id
                ORDER BY RAND()
                LIMIT ' . $bookLimit
            ;
            $stmt = $conn->prepare($sql);
            $stmt->execute(['genre_id' => $book[0]['genre_id'], 'author_id' => $book[0]['author_id'], 'id' => $book[0]['id']] );

            $tempResults = $stmt->fetchAll();
            foreach ($tempResults as $key => $row) {
                $tempResults[$key]['added_on'] = new \DateTime($row['added_on']);
                $tempResults[$key]['published_on'] = new \DateTime($row['published_on']);
            }

            $results = array_merge($results, $tempResults);
        }

        return $results;
    }
}