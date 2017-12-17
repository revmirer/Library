<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    private $title;
    private $added_on;
    private $published_on;
    private $raiting;
    private $genre_id;
    private $author_id;
    private $image;
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getAddedOn()
    {
        return $this->added_on;
    }

    /**
     * @param mixed $added_on
     */
    public function setAddedOn($added_on)
    {
        $this->added_on = $added_on;
    }

    /**
     * @return mixed
     */
    public function getPublishedOn()
    {
        return $this->published_on;
    }

    /**
     * @param mixed $published_on
     */
    public function setPublishedOn($published_on)
    {
        $this->published_on = $published_on;
    }

    /**
     * @return mixed
     */
    public function getRaiting()
    {
        return $this->raiting;
    }

    /**
     * @param mixed $raiting
     */
    public function setRaiting($raiting)
    {
        $this->raiting = $raiting;
    }

    /**
     * @return mixed
     */
    public function getGenreId()
    {
        return $this->genre_id;
    }

    /**
     * @param mixed $genre_id
     */
    public function setGenreId($genre_id)
    {
        $this->genre_id = $genre_id;
    }

    /**
     * @return mixed
     */
    public function getAuthorId()
    {
        return $this->author_id;
    }

    /**
     * @param mixed $author_id
     */
    public function setAuthorId($author_id)
    {
        $this->author_id = $author_id;
    }
    
    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    
}
