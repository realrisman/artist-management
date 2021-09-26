<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LinkRepository")
 */
class Link implements JsonSerializable
{

    const FACEBOOK = 'facebook';
    const YOUTUBE = 'youtube';
    const INSTAGRAM = 'instagram';
    const TWITTER = 'twitter';
    const GOOGLEPLUS = 'google_plus';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $text;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Celebrity", inversedBy="links")
     * @ORM\JoinColumn(nullable=false)
     */
    private $celebrity;

    /**
     * @ORM\Column(type="smallint")
     */
    private $deleted = 0;

    /**
     * Link constructor.
     */
    public function __construct()
    {
        $this->setType('');
    }


    public function getId()
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
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
            'id'   => $this->id,
            'url'  => $this->url,
            'type' => $this->type,
            'text' => $this->text
        ];
    }
}
