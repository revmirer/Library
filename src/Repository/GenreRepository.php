<?php

namespace App\Repository;

use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class GenreRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Genre::class);
    }


    public function findAllGenresForBook()
    {
        $qb =
            $this->createQueryBuilder('g')
                ->getQuery()
        ;

        $result =  $qb->getArrayResult();
        $formattedResult = [];
        foreach ($result as $value) {
            $formattedResult[$value['genre']] = $value['id'];
        }
        return $formattedResult;
    }

}
