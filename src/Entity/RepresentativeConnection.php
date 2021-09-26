<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RepresentativeConnectionRepository")
 */
class RepresentativeConnection implements JsonSerializable
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Representative", inversedBy="representativeConnections")
     * @ORM\JoinColumn(nullable=true)
     * @var Representative
     */
    private $representative;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="representativeConnections")
     * @ORM\JoinColumn(nullable=true)
     * @var Company
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $territory;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Celebrity", inversedBy="representativeConnections")
     * @ORM\JoinColumn(nullable=false)
     */
    private $celebrity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $lastVerified;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $needsVerifyFlag;

    /**
     * @ORM\Column(type="text",length=65000, nullable=true)
     */
    private $verificationLog;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created;

    /**
     * @ORM\Column(type="boolean",options={"default" : false})
     */
    private $isCompany;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * RepresentativeConnection constructor.
     */
    public function __construct()
    {
        $this->setLastVerified(new \DateTime());
        $this->setCreated(new \DateTime());
        $this->setNeedsVerifyFlag(0);
        $this->setIsCompany(false);
    }


    public function getId()
    {
        return $this->id;
    }

    public function getRepresentative(): ?Representative
    {
        return $this->representative;
    }

    public function setRepresentative(?Representative $representative): self
    {
        $this->representative = $representative;

        return $this;
    }

    public function getTerritory(): ?string
    {
        return $this->territory;
    }

    public function setTerritory(?string $territory): self
    {
        $this->territory = $territory;

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

    /**
     * @return Company
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param Company $company
     * @return RepresentativeConnection
     */
    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        $this->isCompany = true;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function jsonSerialize()
    {
        $data =  [
            'territory'      => $this->territory,
            'type'           => $this->type,
            'verify_rank'    => $this->getNeedsVerifyFlag(),
            'verify_date'    => $this->getLastVerified()->format("m/d/Y"),
            'rc_id'          => $this->getId(),
            'created'        => is_null($this->created) ? 'N/A' : $this->getCreated()->format("n/d/y"),
            'is_company'     => $this->isCompany(),
            'position'       => $this->getPosition()
        ];
        if ($this->isCompany()) {
            $data['company'] = $this->company->jsonSerialize();
        } else {
            $data['representative'] = $this->representative->jsonSerialize();
        }

        return $data;
    }

    public function logSerializeForCelebrity()
    {
        $data = [
            'territory'   => $this->territory,
            'type'        => $this->type,
            'verify_rank' => $this->getNeedsVerifyFlag(),
            'verify_date' => $this->getLastVerified()->format("m/d/Y"),
            'created'     => is_null($this->created) ? 'N/A' : $this->getCreated()->format("n/d/y"),
            'is_company'  => $this->isCompany(),
        ];

        if ($this->isCompany()) {
            $data['company'] = $this->company->logSerializeForConnection();
        } else {
            $data['representative'] = $this->representative->logSerializeForConnection();
        }

        return $data;
    }

    public function logSerializeForRepresentative()
    {
        return [
            'territory'      => $this->territory,
            'type'           => $this->type,
            'celebrity'      => $this->getCelebrity()->logSerializeForConnection(),
            'verify_rank'    => $this->getNeedsVerifyFlag(),
            'verify_date'    => $this->getLastVerified()->format("m/d/Y"),
            'created'        => is_null($this->created) ? 'N/A' : $this->getCreated()->format("n/d/y"),
        ];
    }

    public function logSerializeForCompany()
    {
        return [
            'territory'      => $this->territory,
            'type'           => $this->type,
            'celebrity'      => $this->getCelebrity()->logSerializeForConnection(),
            'verify_rank'    => $this->getNeedsVerifyFlag(),
            'verify_date'    => $this->getLastVerified()->format("m/d/Y"),
            'created'        => is_null($this->created) ? 'N/A' : $this->getCreated()->format("n/d/y"),
        ];
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

    public function getNeedsVerifyFlag()
    {
        return $this->needsVerifyFlag;
    }

    public function setNeedsVerifyFlag($needsVerifyFlag): self
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

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(?\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getConnectedName()
    {
        if (!is_null($this->company)) {
            return $this->company->getName();
        }
        if (!is_null($this->representative)) {
            return $this->representative->getName();
        }
    }

    public function getConnectedWpId()
    {
        if (!is_null($this->company)) {
            return $this->company->getWpId();
        }
        if (!is_null($this->representative)) {
            return $this->representative->getWpId();
        }

        return null;
    }
    public function getConnectedCompanyName()
    {
        if (!is_null($this->company)) {
            return $this->company->getName();
        }
        if (!is_null($this->representative)) {
            return $this->representative->getCompanyName();
        }
    }
    public function getConnectedId()
    {
        if (!is_null($this->company)) {
            return $this->company->getId();
        }
        if (!is_null($this->representative)) {
            return $this->representative->getUnid();
        }
    }

    public function getIsCompany(): ?bool
    {
        return $this->isCompany;
    }

    public function isCompany()
    {
        return $this->getIsCompany();
    }

    public function setIsCompany(bool $isCompany): self
    {
        $this->isCompany = $isCompany;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
