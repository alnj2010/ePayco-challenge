<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="clients")
 */
class Client
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $document;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $phone;

    /**
     * @ORM\Column(type="float")
     */
    protected $balance;


    /**
     * @ORM\Column(type="string")
     */
    protected $name;


    /**
     * @param $document
     * @param $email
     * @param $phone
     * @param $name
     */
    public function __construct(
        $document,
        $email,
        $phone,
        $name
    ) {
        $this->document = $document;
        $this->email  = $email;
        $this->phone  = $phone;
        $this->name  = $name;

        $this->balance  = 0.0;
    }

    /**
     * @param $amount
     */
    function addBalance($amount)
    {
        $this->balance += $amount;
    }

    function getBalance()
    {
        return $this->balance;
    }

    function getEmail()
    {
        return $this->email;
    }

    function getName()
    {
        return $this->name;
    }

    function discountProduct($price)
    {
        $this->balance -= $price;
    }
}
