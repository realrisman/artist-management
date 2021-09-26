<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Celebrity", mappedBy="category")
     */
    private $celebrities;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Representative", mappedBy="categories")
     */
    private $representatives;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="categories")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Category", mappedBy="parent")
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Company", mappedBy="categories")
     */
    private $companies;

    public function __construct()
    {
        $this->celebrities     = new ArrayCollection();
        $this->representatives = new ArrayCollection();
        $this->categories      = new ArrayCollection();
        $this->companies = new ArrayCollection();
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
     * @return Collection|Celebrity[]
     */
    public function getCelebrities(): Collection
    {
        return $this->celebrities;
    }

    public function addCelebrity(Celebrity $celebrity): self
    {
        if (!$this->celebrities->contains($celebrity)) {
            $this->celebrities[] = $celebrity;
            $celebrity->addCategory($this);
        }

        return $this;
    }

    public function removeCelebrity(Celebrity $celebrity): self
    {
        if ($this->celebrities->contains($celebrity)) {
            $this->celebrities->removeElement($celebrity);
            $celebrity->removeCategory($this);
        }

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
            $representative->addCategory($this);
        }

        return $this;
    }

    public function removeRepresentative(Representative $representative): self
    {
        if ($this->representatives->contains($representative)) {
            $this->representatives->removeElement($representative);
            $representative->removeCategory($this);
        }

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getChildred(): Collection
    {
        return $this->categories;
    }

    public function addChildCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setParent($this);
        }

        return $this;
    }

    public function removeChildCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getParent() === $this) {
                $category->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|Company[]
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->addCategory($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
            $company->removeCategory($this);
        }

        return $this;
    }
}
