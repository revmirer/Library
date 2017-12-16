<?php

namespace App\Repository;

use App\Entity\Gender;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class GenderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Gender::class);
    }


    public function findAllGenders()
    {
        $qb =
            $this->createQueryBuilder('g')
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
        return $this->createQueryBuilder('g')
            ->where('g.something = :value')->setParameter('value', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
