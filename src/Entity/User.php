<?php
/**
 * Created by PhpStorm.
 * User: Никита
 * Date: 16.12.2017
 * Time: 7:01
 */

namespace App\Entity;


use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, \Serializable
{

    private $id;
    private $username;
    private $password;
    private $email;
    private $is_active;
    private $image;

    public function __construct()
    {
        $this->is_active = true;
    }

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
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return boolean
     */
    public function isis_active()
    {
        return $this->is_active;
    }

    /**
     * @param boolean $is_active
     */
    public function setIs_active($is_active)
    {
        $this->is_active = $is_active;
    }


    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            ) = unserialize($serialized);
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function getSalt()
    {

        return null;
    }

    public function eraseCredentials()
    {
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