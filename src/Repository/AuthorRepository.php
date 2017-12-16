<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Author::class);
    }

    public function findAllAuthorsForBook()
    {
        $qb =
            $this->createQueryBuilder('a')
                ->getQuery()
        ;

        $result =  $qb->getArrayResult();
        $formattedResult = [];
        foreach ($result as $value) {
            $formattedResult[$value['name']] = $value['id'];
        }
        return $formattedResult;
    }
    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('a')
            ->where('a.something = :value')->setParameter('value', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}