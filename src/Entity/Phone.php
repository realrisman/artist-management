<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PhoneRepository")
 */
class Phone implements \JsonSerializable
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $phone;

    /**
     * @ORM\Column(type="smallint")
     */
    private $deleted = 0;

    public function __construct()
    {
        $this->agents = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAgent(): ?Representative
    {
        return $this->agent;
    }

    public function setAgent(?Representative $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(int $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'phone' => $this->getPhone(),
        ];
    }

    public function __toString()
    {
        return $this->getPhone();
    }
}
