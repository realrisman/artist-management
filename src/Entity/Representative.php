<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RepresentativeRepository")
 * @ORM\Table(indexes={@ORM\Index(name="unid_idx", columns={"unid"}), @ORM\Index(name="needsverify_idx", columns={"needs_verify_flag"})})
 */
class Representative implements JsonSerializable, FeaturedImageInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="companyName")
     */
    private $companyName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mailing_address = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $visitor_address = '';


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Email", mappedBy="agent", orphanRemoval = true)
     */
    private $emails;

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
     * @ORM\OneToMany(targetEntity="App\Entity\Phone", mappedBy="agent", orphanRemoval = true)
     */
    private $phones;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="agents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


    /**
     * @ORM\Column(type="integer")
     */
    private $unid;

    /**
     * @ORM\Column(type="smallint")
     */
    private $deleted = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RepresentativeConnection", mappedBy="representative", orphanRemoval=true)
     */
    private $representativeConnections;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\RepresentativeType", inversedBy="representatives")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $status;

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
     * @ORM\Column(type="boolean")
     */
    private $allowsToAddPhone;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="representatives")
     */
    private $location;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Company", inversedBy="representatives")
     */
    private $companies;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $instagram;

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
        $this->phones = new ArrayCollection();
        $this->emails = new ArrayCollection();
        $this->setValidTill(new \DateTime('2099-12-31'));
        $this->representativeConnections = new ArrayCollection();
        $this->type = new ArrayCollection();
        $this->setCreated(new \DateTime());
        $this->setValidFrom(new \DateTime());
        $this->setLastVerified(new \DateTime());
        $this->setLastWeekHits(0);
        $this->setPreviousHitsCount(0);
        $this->setNeedsVerifyFlag(0);
        $this->companies = new ArrayCollection();
        $this->setAllowsToAddPhone(1);
        $this->setUnableToVerify(0);
        $this->setSpotChecked(0);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        $company = $this->companies->first();
        if ($company instanceof Company) {
            return $company->getName();
        }
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = trim($companyName);

        return $this;
    }

    public function getMailingAddress(): ?string
    {
        if (!is_null($this->getLocation())) {
            return $this->getLocation()->getMailingAddress();
        }
        return $this->mailing_address;
    }

    public function setMailingAddress(?string $mailing_address): self
    {
        $this->mailing_address = trim($mailing_address);

        return $this;
    }

    public function getVisitorAddress(): ?string
    {
        if (!is_null($this->getLocation())) {
            return $this->getLocation()->getVisitorAddress();
        }
        return $this->visitor_address;
    }

    public function setVisitorAddress(?string $visitor_address): self
    {
        $this->visitor_address = trim($visitor_address);

        return $this;
    }

    /**
     * @return Collection|Email[]
     */
    public function getEmails(): Collection
    {
        return $this->emails;
    }

    public function addEmail(Email $email): self
    {
        if (!$this->emails->contains($email)) {
            $this->emails[] = $email;
            $email->setAgent($this);
        }

        return $this;
    }

    public function removeEmail(Email $email): self
    {
        if ($this->emails->contains($email)) {
            $this->emails->removeElement($email);
            // set the owning side to null (unless already changed)
            if ($email->getAgent() === $this) {
                $email->setAgent(null);
            }
        }

        return $this;
    }

    public function removeEmails(): self
    {

        foreach ($this->getEmails() as $email) {
            $this->removeEmail($email);
        }

        return $this;
    }

    public function removePhones(): self
    {

        foreach ($this->getPhones() as $phone) {
            $this->removePhone($phone);
        }

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

    /**
     * @return Collection|Phone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->setAgent($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->contains($phone)) {
            $this->phones->removeElement($phone);
            // set the owning side to null (unless already changed)
            if ($phone->getAgent() === $this) {
                $phone->setAgent(null);
            }
        }

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

    public function __clone()
    {

        $this->id = null;
        $this->emails = new ArrayCollection();
        $this->phones = new ArrayCollection();
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
            $representativeConnection->setRepresentative($this);
        }

        return $this;
    }

    public function removeRepresentativeConnection(RepresentativeConnection $representativeConnection): self
    {
        if ($this->representativeConnections->contains($representativeConnection)) {
            $this->representativeConnections->removeElement($representativeConnection);
            // set the owning side to null (unless already changed)
            if ($representativeConnection->getRepresentative() === $this) {
                $representativeConnection->setRepresentative(null);
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

    /**
     * @return Collection|RepresentativeType[]
     */
    public function getType(): Collection
    {
        return $this->type;
    }

    public function addType(RepresentativeType $type): self
    {
        if (!$this->type->contains($type)) {
            $this->type[] = $type;
        }

        return $this;
    }

    public function removeType(RepresentativeType $type): self
    {
        if ($this->type->contains($type)) {
            $this->type->removeElement($type);
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getFirstName(): ?string
    {
        if (is_null($this->name)) {
            return '';
        }
        $parts = explode(" ", $this->name);

        return $parts[0];
    }

    public function getLastName(): ?string
    {
        if (is_null($this->name)) {
            return '';
        }
        $parts = explode(" ", $this->name);
        array_shift($parts);

        return join(" ", $parts);
    }

    public function setName(?string $name): self
    {
        $this->name = trim($name);

        return $this;
    }

    public function jsonSerialize()
    {
        $phones = [];
        $emails = [];
        $categories = [];
        foreach ($this->getPhones() as $phone) {
            $phones[] = $phone->getPhone();
        }
        foreach ($this->getEmails() as $email) {
            $emails[] = $email->getEmail();
        }
        foreach ($this->getCategories() as $category) {
            $categories[] = ['name' => $category->getName(), 'id' => $category->getId()];
        }

        $location = '';
        if (!is_null($this->getLocation())) {
            $location = $this->getLocation()->jsonSerialize();
        }
        $data = [
            'id'               => $this->getUnid(),
            'name'             => $this->getName(),
            'visitor_address'  => $this->getVisitorAddress(),
            'mailing_address'  => $this->getMailingAddress(),
            'wp_id'            => $this->getWpId(),
            'image'            => $this->getImage(),
            'image_title'      => $this->getImageTitle(),
            'image_alt'        => $this->getImageAlt(),
            'status'           => $this->getStatus(),
            'location'         => $location,
            'source'           => $this->getSource(),
            'instagram'        => $this->getInstagram(),
            'phones'           => $phones,
            'emails'           => $emails,
            'type'             => $this->getTypeName(),
            'companies'        => [],
            'categories'       => $categories,
            'verification_log' => json_decode($this->getVerificationLog(), true),
            'remove_reason'    => $this->getRemoveReason(),
            'unable_to_verify' => $this->getUnableToVerify(),
            'spot_checked'     => $this->getSpotChecked(),
            ''
        ];

        if ($this->getPrimaryCategory()) {
            $data['primaryCategory'] = ['id' => $this->getPrimaryCategory()->getId(), 'name' => $this->getPrimaryCategory()->getName()];
        }

        foreach ($this->getCompanies() as $company) {
            $data['companies'][] = $company->jsonSerialize();
        }

        return $data;
    }

    public function getTypeName()
    {
        $type = "";
        if ($this->getType()->get(0)) {
            $type = $this->getType()->get(0)->getName();
        }

        return $type;
    }

    public function logSerialize()
    {
        $data = $this->jsonSerialize();
        $data['celebrities'] = [];
        unset($data['verification_log']);

        foreach ($this->representativeConnections as $rc) {
            /**
             * @var $rc RepresentativeConnection
             */
            $data['celebrities'][] = $rc->logSerializeForRepresentative();
        }

        if ($this->lastVerified instanceof DateTime) {
            $data['last_verify_date'] = $this->lastVerified->format("Y-m-d");
        }
        $data['companies'] = [];
        foreach ($this->getCompanies() as $company) {
            $data['companies'][] = $company->logSerializeForConnection();
        }
        return $data;
    }

    public function logSerializeForConnection()
    {
        $data = [
            'id'   => $this->getUnid(),
            'name' => $this->getName(),
        ];

        return $data;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @return string
     */
    public function getCategoryNames(): string
    {
        return join(", ", array_unique($this->categories->map(function (Category $category) {
            return $category->getName();
        })->getValues()));
    }

    /**
     * @return string
     */
    public function getEmailsAsString(): string
    {
        return join(", ", array_unique($this->getEmails()->map(function (Email $email) {
            return $email->getEmail();
        })->getValues()));
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = trim($status);

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

    public function getPrimaryCategory(): ?Category
    {
        return $this->primaryCategory;
    }

    public function setPrimaryCategory(?Category $primaryCategory): self
    {
        $this->primaryCategory = $primaryCategory;

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

    public function getAllowsToAddPhone(): ?bool
    {
        return $this->allowsToAddPhone;
    }

    public function setAllowsToAddPhone(bool $allowsToAddPhone): self
    {
        $this->allowsToAddPhone = $allowsToAddPhone;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        if (!is_null($location)) {
            $this->setVisitorAddress($location->getVisitorAddress());
            $this->setMailingAddress($location->getPostalAddress());
        }

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
        }

        return $this;
    }

    public function clearCompanies()
    {
        foreach ($this->companies as $company) {
            $this->removeCompany($company);
        }
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
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

    public function getHighestRankedCelebrityName(): string
    {
        return $this->getHighestRankedCelebrityData(function (Celebrity $celebrity) {
            return sprintf("%s", $celebrity->getName());
        });
    }

    protected function getHighestRankedCelebrityData(callable $nameFormatterCallback): string
    {
        $name = "";
        $highestRank = 0;

        foreach ($this->getRepresentativeConnections() as $representativeConnection) {
            if (!is_null($celebrity = $representativeConnection->getCelebrity())) {
                if ($celebrity->getNeedsVerifyFlag() > $highestRank) {
                    $name = $nameFormatterCallback($celebrity);
                    $highestRank = $celebrity->getNeedsVerifyFlag();
                }
            }
        }

        return $name;
    }

    public function getHighestRankedCelebrityNameAndRank(): string
    {
        return $this->getHighestRankedCelebrityData(function (Celebrity $celebrity) {
            return sprintf("%s (%s)", $celebrity->getName(), $celebrity->getNeedsVerifyFlag());
        });
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

    public function setSpotChecked(bool $spotChecked): self
    {
        $this->spotChecked = $spotChecked;
        return $this;
    }

    public function getSpotChecked(): ?bool
    {
        return $this->spotChecked;
    }
}
