<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

class Author 
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    private $name;
    private $gender_id;
    private $birthday;

    // add your own fields
}
