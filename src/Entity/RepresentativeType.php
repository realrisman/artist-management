<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RepresentativeTypeRepository")
 */
class RepresentativeType implements \JsonSerializable
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
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Representative", mappedBy="type")
     */
    private $representatives;

    public function __construct($name = '')
    {
        if ($name) {
            $this->name = $name;
        }
        $this->representatives = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Representative[]
     */
    public function getRepresentatives(): Collection
    {
        return $this->representatives;
    }

    public function addRepresentative(Representative $representative): self
    {
        if (!$this->representatives->contains($representative)) {
            $this->representatives[] = $representative;
            $representative->addType($this);
        }

        return $this;
    }

    public function removeRepresentative(Representative $representative): self
    {
        if ($this->representatives->contains($representative)) {
            $this->representatives->removeElement($representative);
            $representative->removeType($this);
        }

        return $this;
    }

    public function getApiName()
    {
        return ucfirst($this->getName()) . "s";
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->getName()
        ];
    }

    public function __toString()
    {
        return $this->getName();
    }
}
