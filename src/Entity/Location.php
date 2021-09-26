<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 */
class Location implements JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $postal_address;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $visitor_address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="locations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Company;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Representative", mappedBy="location")
     */
    private $representatives;

    public function __construct()
    {
        $this->representatives = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPostalAddress(): ?string
    {
        return $this->postal_address;
    }

    public function getMailingAddress(): ?string
    {
        return $this->postal_address;
    }

    public function setPostalAddress(?string $postal_address): self
    {
        $this->postal_address = strip_tags($postal_address);

        return $this;
    }

    public function setMailingAddress(?string $postal_address): self
    {
        return $this->setPostalAddress($postal_address);
    }

    public function getVisitorAddress(): ?string
    {
        return $this->visitor_address;
    }

    public function setVisitorAddress(?string $visitor_address): self
    {
        $this->visitor_address = strip_tags($visitor_address);

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->Company;
    }

    public function setCompany(?Company $Company): self
    {
        $this->Company = $Company;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'id'              => $this->getId(),
            'name'            => $this->getName(),
            'email'           => $this->getEmail(),
            'phone'           => $this->getPhone(),
            'postal_address'  => $this->getPostalAddress(),
            'visitor_address' => $this->getVisitorAddress()
        ];
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
            $representative->setLocation($this);
        }

        return $this;
    }

    public function removeRepresentative(Representative $representative): self
    {
        if ($this->representatives->contains($representative)) {
            $this->representatives->removeElement($representative);
            // set the owning side to null (unless already changed)
            if ($representative->getLocation() === $this) {
                $representative->setLocation(null);
            }
        }

        return $this;
    }
}
