<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyLogRepository")
 */
class CompanyLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $unid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User",inversedBy="companyLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="text",length=16777200)
     */
    private $old;

    /**
     * @ORM\Column(type="text",length=16777200)
     */
    private $new;

    public function getId()
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getOld(): ?string
    {
        return $this->old;
    }

    public function setOld(string $old): self
    {
        $this->old = $old;

        return $this;
    }

    public function getNew(): ?string
    {
        return $this->new;
    }

    public function setNew(string $new): self
    {
        $this->new = $new;

        return $this;
    }
}
