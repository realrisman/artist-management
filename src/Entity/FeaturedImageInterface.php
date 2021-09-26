<?php


namespace App\Entity;


interface FeaturedImageInterface
{

    public function getImage(): ?string;

    public function setImage(?string $image);
}
