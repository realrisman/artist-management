<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UniqueLinkCelebrityRepository")
 */
class UniqueLinkCelebrity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Celebrity", inversedBy="uniqueLinkCelebrities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $celebrity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $valid_till;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="uniqueLinkCelebrities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="text",length=16777200, nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCelebrity(): ?Celebrity
    {
        return $this->celebrity;
    }

    public function setCelebrity(?Celebrity $celebrity): self
    {
        $this->celebrity = $celebrity;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getValidTill(): ?\DateTimeInterface
    {
        return $this->valid_till;
    }

    public function setValidTill(?\DateTimeInterface $valid_till): self
    {
        $this->valid_till = $valid_till;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id'         => $this->getId(),
            'celebrity'  => $this->getCelebrity()->getName(),
            'valid_till' => $this->getValidTill()->format("Y-m-d"),
            'user'       => $this->getUser()->getLogin(),
            'token'      => $this->getToken(),
            'data'       => json_decode($this->getData(), true),
            'updated'    => is_null($this->getUpdatedAt()) ? '' : $this->getUpdatedAt()->format("Y-m-d H:i:s")
        ];
    }
}
