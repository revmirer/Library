<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

class Genre
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    private $genre;

    // add your own fields
}
