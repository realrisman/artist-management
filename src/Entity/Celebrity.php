<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CelebrityRepository")
 * @ORM\Table(indexes={@ORM\Index(name="unid_idx", columns={"unid"}),@ORM\Index(name="wpid_idx", columns={"wp_id"}),@ORM\Index(name="needsverify_idx", columns={"needs_verify_flag"})})
 */
class Celebrity implements JsonSerializable, FeaturedImageInterface
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
    private $bio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $profession;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthdate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $youtube;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     */
    private $valid_from;

    /**
     * @ORM\Column(type="datetime")
     */
    private $valid_till;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="celebrities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Link", mappedBy="celebrity", orphanRemoval = true)
     */
    private $links;

    /**
     * @ORM\Column(type="integer")
     */
    private $unid;


    /**
     * @ORM\Column(type="smallint")
     */
    private $deleted = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RepresentativeConnection", mappedBy="celebrity", orphanRemoval = true)
     */
    private $representativeConnections;

    /**
     * @ORM\Column(type="integer")
     */
    private $wp_id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="celebrities")
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $directAddress;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $source;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     */
    private $primaryCategory;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deceased;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hiatus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $previousHitsCount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lastWeekHits;

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
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $removeReason;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $selfManaged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $instagram;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UniqueLinkCelebrity", mappedBy="celebrity", orphanRemoval=true)
     */
    private $uniqueLinkCelebrities;

    /**
     * @ORM\Column(type="boolean")
     */
    private $unableToVerify;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $spotChecked;

    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->representativeConnections = new ArrayCollection();
        $this->category = new ArrayCollection();

        $this->setValidTill(new \DateTime('2099-12-31'));
        $this->setValidFrom(new DateTime());
        $this->setCreated(new DateTime());
        $this->setWpId(0);
        $this->setDeceased(false);
        $this->setHiatus(false);
        $this->setLastVerified(new DateTime());
        $this->setLastWeekHits(0);
        $this->setPreviousHitsCount(0);
        $this->setNeedsVerifyFlag(0);
        $this->uniqueLinkCelebrities = new ArrayCollection();
        $this->setUnableToVerify(0);
        $this->setSpotChecked(0);
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
        $this->name = trim($name);

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = trim($bio);

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): self
    {
        $this->profession = trim($profession);

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = trim($city);

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = trim($state);

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = trim($country);

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = trim($price);

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = trim($status);

        return $this;
    }

    public function getYoutube(): ?string
    {
        return $this->youtube;
    }

    public function setYoutube(?string $youtube): self
    {
        $this->youtube = trim($youtube);

        return $this;
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

    public function getValidFrom(): ?\DateTimeInterface
    {
        return $this->valid_from;
    }

    public function setValidFrom(\DateTimeInterface $valid_from): self
    {
        $this->valid_from = $valid_from;

        return $this;
    }

    public function getValidTill(): ?\DateTimeInterface
    {
        return $this->valid_till;
    }

    public function setValidTill(\DateTimeInterface $valid_till): self
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

    /**
     * @return Collection|Link[]
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(Link $link): self
    {
        if (!$this->links->contains($link)) {
            $this->links[] = $link;
            $link->setCelebrity($this);
        }

        return $this;
    }

    public function removeLink(Link $link): self
    {
        if ($this->links->contains($link)) {
            $this->links->removeElement($link);
            // set the owning side to null (unless already changed)
            if ($link->getCelebrity() === $this) {
                $link->setCelebrity(null);
            }
        }

        return $this;
    }

    public function removeLinks(): self
    {
        foreach ($this->links as $link) {
            /**
             * @var $link Link
             */
            $this->removeLink($link);
        }

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

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(int $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection|RepresentativeConnection[]
     * @throws \Exception
     */
    public function getRepresentativeConnections(): Collection
    {
        $iterator = $this->representativeConnections->getIterator();
        $iterator->uasort(function (RepresentativeConnection $a, RepresentativeConnection $b) {
            return ($a->getPosition() < $b->getPosition()) ? -1 : 1;
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }

    public function addRepresentativeConnection(RepresentativeConnection $representativeConnection): self
    {
        if (!$this->representativeConnections->contains($representativeConnection)) {
            $this->representativeConnections[] = $representativeConnection;
            $representativeConnection->setCelebrity($this);
        }

        return $this;
    }

    public function removeRepresentativeConnection(RepresentativeConnection $representativeConnection): self
    {
        if ($this->representativeConnections->contains($representativeConnection)) {
            $this->representativeConnections->removeElement($representativeConnection);
            // set the owning side to null (unless already changed)
            if ($representativeConnection->getCelebrity() === $this) {
                $representativeConnection->setCelebrity(null);
            }
        }

        return $this;
    }

    public function getWpId(): ?int
    {
        return $this->wp_id;
    }

    public function setWpId(int $wp_id): self
    {
        $this->wp_id = $wp_id;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->category->contains($category)) {
            $this->category[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->category->contains($category)) {
            $this->category->removeElement($category);
        }

        return $this;
    }

    public function jsonSerialize()
    {
        $birthday = $this->birthdate;
        if ($this->birthdate instanceof DateTime) {
            $birthday = $this->birthdate->format("Y-m-d");
        }
        $lastVerifyDate = $this->lastVerified;
        if ($this->lastVerified instanceof DateTime) {
            $lastVerifyDate = $this->lastVerified->format("Y-m-d");
        }
        $data = [
            'id'                => $this->unid,
            'name'              => $this->name,
            'bio'               => $this->bio,
            'profession'        => $this->profession,
            'price'             => $this->price,
            'birthdate'         => $birthday,
            'city'              => $this->city,
            'image'             => $this->getImage(),
            'image_title'       => $this->getImageTitle(),
            'image_alt'         => $this->getImageAlt(),
            'source'            => $this->source,
            'state'             => $this->state,
            'country'           => $this->country,
            'status'            => $this->status,
            'youtube'           => $this->youtube,
            'deceased'          => $this->deceased,
            'hiatus'            => $this->getHiatus(),
            'instagram'         => $this->getInstagram(),
            'directAddress'     => $this->getDirectAddress(),
            'needs_update_flag' => $this->getNeedsVerifyFlag(),
            'last_verify_date'  => $lastVerifyDate,
            'links'             => [],
            'representatives'   => [],
            'categories'        => [],
            'verification_log'  => json_decode($this->getVerificationLog(), true),
            'remove_reason'     => $this->getRemoveReason(),
            'selfManaged'       => $this->getSelfManaged(),
            'unable_to_verify'  => $this->getUnableToVerify(),
            'spot_checked'      => $this->getSpotChecked(),
        ];
        if ($this->getPrimaryCategory()) {
            $data['primaryCategory'] = ['id' => $this->getPrimaryCategory()->getId(), 'name' => $this->getPrimaryCategory()->getName()];
        }
        foreach ($this->getCategory() as $category) {
            $data['categories'][] = ['id' => $category->getId(), 'name' => $category->getName()];
        }
        foreach ($this->links as $link) {
            $data['links'][] = $link->jsonSerialize();
        }
        foreach ($this->representativeConnections as $rc) {
            /**
             * @var $rc RepresentativeConnection
             */
            $data['representatives'][] = $rc->jsonSerialize();
        }

        return $data;
    }

    public function logSerialize()
    {
        $birthday = $this->birthdate;
        if ($this->birthdate instanceof DateTime) {
            $birthday = $this->birthdate->format("Y-m-d");
        }
        $lastVerifyDate = $this->lastVerified;
        if ($this->lastVerified instanceof DateTime) {
            $lastVerifyDate = $this->lastVerified->format("Y-m-d");
        }
        $data = [
            'id'                => $this->unid,
            'name'              => $this->name,
            'bio'               => $this->bio,
            'profession'        => $this->profession,
            'price'             => $this->price,
            'birthdate'         => $birthday,
            'city'              => $this->city,
            'image'             => $this->getImage(),
            'image_title'       => $this->getImageTitle(),
            'image_alt'         => $this->getImageAlt(),
            'source'            => $this->source,
            'state'             => $this->state,
            'country'           => $this->country,
            'status'            => $this->status,
            'youtube'           => $this->youtube,
            'deceased'          => $this->deceased,
            'hiatus'            => $this->getHiatus(),
            'instagram'         => $this->getInstagram(),
            'directAddress'     => $this->getDirectAddress(),
            'needs_update_flag' => $this->getNeedsVerifyFlag(),
            'last_verify_date'  => $lastVerifyDate,
            'links'             => [],
            'representatives'   => [],
            'categories'        => [],
            'remove_reason'     => $this->getRemoveReason(),
            'selfManaged'       => $this->getSelfManaged(),
            'unable_to_verify'  => $this->getUnableToVerify(),
            'spot_checked'      => $this->getSpotChecked(),
        ];
        if ($this->getPrimaryCategory()) {
            $data['primaryCategory'] = ['id' => $this->getPrimaryCategory()->getId(), 'name' => $this->getPrimaryCategory()->getName()];
        }
        foreach ($this->getCategory() as $category) {
            $data['categories'][] = ['id' => $category->getId(), 'name' => $category->getName()];
        }
        foreach ($this->links as $link) {
            $data['links'][] = $link->jsonSerialize();
        }
        foreach ($this->representativeConnections as $rc) {
            /**
             * @var $rc RepresentativeConnection
             */
            $data['representatives'][] = $rc->logSerializeForCelebrity();
        }

        return $data;
    }

    public function logSerializeForConnection()
    {

        return [
            'id'   => $this->getUnid(),
            'name' => $this->getName(),
        ];
    }

    public function getDirectAddress(): ?string
    {
        return $this->directAddress;
    }

    public function setDirectAddress(?string $directAddress): self
    {
        $this->directAddress = trim($directAddress);

        return $this;
    }

    public function __clone()
    {

        $this->id = null;
        $this->links = new ArrayCollection();
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = trim($image);

        return $this;
    }

    public function getImageTitle(): ?string
    {
        return $this->imageTitle;
    }

    public function setImageTitle(?string $imageTitle): self
    {
        $this->imageTitle = trim($imageTitle);

        return $this;
    }

    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    public function setImageAlt(?string $imageAlt): self
    {
        $this->imageAlt = trim($imageAlt);

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

    public function getDeceased(): ?bool
    {
        return $this->deceased;
    }

    public function setDeceased(bool $deceased): self
    {
        $this->deceased = $deceased;

        return $this;
    }

    public function getHiatus(): ?bool
    {
        return $this->hiatus;
    }

    public function setHiatus(bool $hiatus): self
    {
        $this->hiatus = $hiatus;

        return $this;
    }

    public function getPreviousHitsCount(): ?int
    {
        return $this->previousHitsCount;
    }

    public function setPreviousHitsCount(?int $previousHitsCount): self
    {
        $this->previousHitsCount = $previousHitsCount;

        return $this;
    }

    public function getLastWeekHits(): ?int
    {
        return $this->lastWeekHits;
    }

    public function setLastWeekHits(?int $lastWeekHits): self
    {
        $this->lastWeekHits = $lastWeekHits;

        return $this;
    }

    public function getLastVerified(): ?\DateTimeInterface
    {
        return $this->lastVerified;
    }

    public function setLastVerified(?\DateTimeInterface $lastVerified): self
    {
        $this->lastVerified = $lastVerified;

        return $this;
    }

    public function getNeedsVerifyFlag(): ?float
    {
        return $this->needsVerifyFlag;
    }

    public function setNeedsVerifyFlag(?float $needsVerifyFlag): self
    {
        $this->needsVerifyFlag = $needsVerifyFlag;

        return $this;
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

    public function getRemoveReason(): ?string
    {
        return $this->removeReason;
    }

    public function setRemoveReason(?string $removeReason): self
    {
        $this->removeReason = $removeReason;

        return $this;
    }

    public function getSelfManaged(): ?bool
    {
        return $this->selfManaged;
    }

    public function setSelfManaged(?bool $selfManaged): self
    {
        $this->selfManaged = $selfManaged;

        return $this;
    }

    /**
     * @return Collection|UniqueLinkCelebrity[]
     */
    public function getUniqueLinkCelebrities(): Collection
    {
        return $this->uniqueLinkCelebrities;
    }

    public function addUniqueLinkCelebrity(UniqueLinkCelebrity $uniqueLinkCelebrity): self
    {
        if (!$this->uniqueLinkCelebrities->contains($uniqueLinkCelebrity)) {
            $this->uniqueLinkCelebrities[] = $uniqueLinkCelebrity;
            $uniqueLinkCelebrity->setCelebrity($this);
        }

        return $this;
    }

    public function removeUniqueLinkCelebrity(UniqueLinkCelebrity $uniqueLinkCelebrity): self
    {
        if ($this->uniqueLinkCelebrities->contains($uniqueLinkCelebrity)) {
            $this->uniqueLinkCelebrities->removeElement($uniqueLinkCelebrity);
            // set the owning side to null (unless already changed)
            if ($uniqueLinkCelebrity->getCelebrity() === $this) {
                $uniqueLinkCelebrity->setCelebrity(null);
            }
        }

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

    public function getUnableToVerify(): ?bool
    {
        return $this->unableToVerify;
    }

    public function setUnableToVerify(bool $unableToVerify): self
    {
        $this->unableToVerify = $unableToVerify;

        return $this;
    }

    /**
     * @param bool $spotChecked
     * @return $this
     */
    public function setSpotChecked(bool $spotChecked): self
    {
        $this->spotChecked = $spotChecked;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSpotChecked(): bool
    {
        return $this->spotChecked;
    }
}
