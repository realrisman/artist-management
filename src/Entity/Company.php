<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 */
class Company implements JsonSerializable
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="companies")
     */
    private $categories;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     */
    private $primaryCategory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $wp_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $instagram;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Location", mappedBy="Company")
     */
    private $locations;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="companies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Representative", mappedBy="companies")
     */
    private $representatives;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $lastVerified;

    /**
     * @ORM\Column(type="decimal", nullable=true,precision=10, scale=2)
     */
    private $needsVerifyFlag;

    /**
     * @ORM\Column(type="text",length=65000, nullable=true)
     */
    private $verificationLog;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastUpdatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RepresentativeConnection", mappedBy="company", orphanRemoval=true)
     */
    private $representativeConnections;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageAlt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UniqueLinkCompany", mappedBy="company", orphanRemoval=true)
     */
    private $uniqueLinkCompanies;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $source;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->representatives = new ArrayCollection();
        $this->representativeConnections = new ArrayCollection();
        $this->uniqueLinkCompanies = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    public function getPrimaryCategory(): ?Category
    {
        return $this->primaryCategory;
    }

    public function setPrimaryCategory(?Category $primaryCategory): self
    {
        $this->primaryCategory = $primaryCategory;

        return $this;
    }

    public function getWpId(): ?int
    {
        return $this->wp_id;
    }

    public function setWpId(?int $wp_id): self
    {
        $this->wp_id = $wp_id;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): self
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return Collection|Location[]
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations[] = $location;
            $location->setCompany($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
            // set the owning side to null (unless already changed)
            if ($location->getCompany() === $this) {
                $location->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created): void
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }


    public function jsonSerialize()
    {
        $created = $this->created;
        if ($this->created instanceof DateTime) {
            $created = $this->created->format("Y-m-d");
        }

        $lastVerifyDate = $this->lastVerified;
        if ($this->lastVerified instanceof DateTime) {
            $lastVerifyDate = $this->lastVerified->format("Y-m-d");
        }
        $data = [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'status'            => $this->getStatus(),
            'wp_id'             => $this->getWpId(),
            'website'           => $this->getWebsite(),
            'instagram'         => $this->getInstagram(),
            'description'       => $this->getDescription(),
            'image'             => $this->getImage(),
            'image_title'       => $this->getImageTitle(),
            'image_alt'         => $this->getImageAlt(),
            'created'           => $created,
            'primaryCategory'   => '',
            'categories'        => [],
            'locations'         => [],
            'needs_update_flag' => $this->getNeedsVerifyFlag(),
            'last_verify_date'  => $lastVerifyDate,
            'verification_log'  => json_decode($this->getVerificationLog(), true),
            'source'            => $this->getSource(),
        ];

        if ($this->getPrimaryCategory()) {
            $data['primaryCategory'] = ['id' => $this->getPrimaryCategory()->getId(), 'name' => $this->getPrimaryCategory()->getName()];
        }
        foreach ($this->getCategories() as $category) {
            $data['categories'][] = ['id' => $category->getId(), 'name' => $category->getName()];
        }
        foreach ($this->getLocations() as $location) {
            $data['locations'][] = $location->jsonSerialize();
        }

        return $data;
    }

    public function logSerialize()
    {
        $data = $this->jsonSerialize();
        foreach ($this->representativeConnections as $rc) {
            /**
             * @var $rc RepresentativeConnection
             */
            $data['celebrities'][] = $rc->logSerializeForRepresentative();
        }
        return $data;
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
            $representative->addCompany($this);
        }

        return $this;
    }

    public function removeRepresentative(Representative $representative): self
    {
        if ($this->representatives->contains($representative)) {
            $this->representatives->removeElement($representative);
            $representative->removeCompany($this);
        }

        return $this;
    }

    public function removeRepresentatives(): self
    {
        foreach ($this->getRepresentatives() as $representative) {
            $this->removeRepresentative($representative);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastVerified()
    {
        return $this->lastVerified;
    }

    /**
     * @param mixed $lastVerified
     */
    public function setLastVerified($lastVerified): void
    {
        $this->lastVerified = $lastVerified;
    }

    /**
     * @return mixed
     */
    public function getNeedsVerifyFlag()
    {
        return $this->needsVerifyFlag;
    }

    /**
     * @param mixed $needsVerifyFlag
     */
    public function setNeedsVerifyFlag($needsVerifyFlag): void
    {
        $this->needsVerifyFlag = $needsVerifyFlag;
    }

    /**
     * @return mixed
     */
    public function getVerificationLog()
    {
        return empty($this->verificationLog) ? "[]" : $this->verificationLog;
    }

    /**
     * @param mixed $verificationLog
     */
    public function setVerificationLog($verificationLog): void
    {
        $this->verificationLog = $verificationLog;
    }

    public function addVerificationLog($login)
    {

        $logs = json_decode($this->verificationLog, true);
        if (is_null($logs)) {
            $logs = [];
        }
        $today = new DateTime();
        array_unshift($logs, ['login' => $login, 'date' => $today->format("m/d/Y")]);
        //limit array to 100 entries to prevent truncation due to database field length
        array_slice($logs, 0, 100);
        $this->verificationLog = json_encode($logs);
    }

    public function getLastUpdatedAt(): ?\DateTimeInterface
    {
        return $this->lastUpdatedAt;
    }

    public function setLastUpdatedAt(?\DateTimeInterface $lastUpdatedAt): self
    {
        $this->lastUpdatedAt = $lastUpdatedAt;

        return $this;
    }

    /**
     * @return Collection|RepresentativeConnection[]
     */
    public function getRepresentativeConnections(): Collection
    {
        return $this->representativeConnections;
    }


    public function addRepresentativeConnection(RepresentativeConnection $representativeConnection): self
    {
        if (!$this->representativeConnections->contains($representativeConnection)) {
            $this->representativeConnections[] = $representativeConnection;
            $representativeConnection->setCompany($this);
        }

        return $this;
    }

    public function removeRepresentativeConnection(RepresentativeConnection $representativeConnection): self
    {
        if ($this->representativeConnections->contains($representativeConnection)) {
            $this->representativeConnections->removeElement($representativeConnection);
            // set the owning side to null (unless already changed)
            if ($representativeConnection->getCompany() === $this) {
                $representativeConnection->setCompany(null);
            }
        }

        return $this;
    }

    public function removeRepresentativeConnections()
    {
        foreach ($this->getRepresentativeConnections() as $representativeConnection) {
            $this->removeRepresentativeConnection($representativeConnection);
        }
    }

    public function logSerializeForConnection()
    {
        $data = [
            'id'   => $this->getId(),
            'name' => $this->getName(),
        ];

        return $data;
    }

    public function getUnid()
    {
        return $this->getId();
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getImageTitle()
    {
        return $this->imageTitle;
    }

    /**
     * @param mixed $imageTitle
     */
    public function setImageTitle($imageTitle): void
    {
        $this->imageTitle = $imageTitle;
    }

    /**
     * @return mixed
     */
    public function getImageAlt()
    {
        return $this->imageAlt;
    }

    /**
     * @param mixed $imageAlt
     */
    public function setImageAlt($imageAlt): void
    {
        $this->imageAlt = $imageAlt;
    }

    /**
     * @return Collection|UniqueLinkCompany[]
     */
    public function getUniqueLinkCompanies(): Collection
    {
        return $this->uniqueLinkCompanies;
    }

    public function addUniqueLinkCompany(UniqueLinkCompany $uniqueLinkCompany): self
    {
        if (!$this->uniqueLinkCompanies->contains($uniqueLinkCompany)) {
            $this->uniqueLinkCompanies[] = $uniqueLinkCompany;
            $uniqueLinkCompany->setCompany($this);
        }

        return $this;
    }

    public function removeUniqueLinkCompany(UniqueLinkCompany $uniqueLinkCompany): self
    {
        if ($this->uniqueLinkCompanies->contains($uniqueLinkCompany)) {
            $this->uniqueLinkCompanies->removeElement($uniqueLinkCompany);
            // set the owning side to null (unless already changed)
            if ($uniqueLinkCompany->getCompany() === $this) {
                $uniqueLinkCompany->setCompany(null);
            }
        }

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = trim($source);

        return $this;
    }
}
