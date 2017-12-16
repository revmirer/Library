<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

class Gender
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    private $name;

    // add your own fields
}
