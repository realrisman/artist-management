<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContactChangeRepository")
 */
class ContactChange
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="integer")
     */
    private $unid;

    /**
     * @ORM\Column(type="integer")
     */
    private $celebrities = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $author;

    /**
     * ContactChange constructor.
     */
    public function __construct()
    {
        $this->setCreated(new \DateTime());
        $this->setCelebrities(0);
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUnid(): ?int
    {
        return $this->unid;
    }

    public function setUnid(int $unid): self
    {
        $this->unid = $unid;

        return $this;
    }

    public function getCelebrities(): ?int
    {
        return $this->celebrities;
    }

    public function setCelebrities(int $celebrities): self
    {
        $this->celebrities = $celebrities;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
