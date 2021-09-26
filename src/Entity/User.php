<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{

    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_EDITOR = 'ROLE_EDITOR';
    const ROLE_SPECTATOR = 'ROLE_SPECTATOR';
    const ROLE_SPOT_CHECKER = 'ROLE_SPOT_CHECKER';
    const ROLE_WRITER = 'ROLE_WRITER';
    const ROLE_TRAINER = 'ROLE_TRAINER';
    const ROLE_IMAGE_UPLOADER = 'ROLE_IMAGE_UPLOADER';
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity="Representative", mappedBy="user")
     */
    private $agents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Celebrity", mappedBy="user")
     */
    private $celebrities;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Company", mappedBy="user")
     */
    private $companies;

    /**
     * @ORM\Column(type="smallint")
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $login;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $last_name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RepresentativeLog", mappedBy="user")
     */
    private $representativeLogs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CelebrityLog", mappedBy="user")
     */
    private $celebrityLogs;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CompanyLog", mappedBy="user")
     */
    private $companyLogs;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $monthlyLimit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limitUsed;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UniqueLink", mappedBy="user")
     */
    private $uniqueLinks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UniqueLinkCelebrity", mappedBy="user", orphanRemoval=true)
     */
    private $uniqueLinkCelebrities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UniqueLinkCompany", mappedBy="user", orphanRemoval=true)
     */
    private $uniqueLinkCompanies;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email_sync;

    public function __construct()
    {
        $this->agents             = new ArrayCollection();
        $this->celebrities        = new ArrayCollection();
        $this->representativeLogs = new ArrayCollection();
        $this->celebrityLogs      = new ArrayCollection();
        $this->uniqueLinks = new ArrayCollection();
        $this->uniqueLinkCelebrities = new ArrayCollection();
        $this->uniqueLinkCompanies = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        if (!in_array($role, self::getAvailableRoles())) {
            throw new \Exception('Unknown user role');
        }
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection|Representative[]
     */
    public function getAgents(): Collection
    {
        return $this->agents;
    }

    public function addAgent(Representative $agent): self
    {
        if (!$this->agents->contains($agent)) {
            $this->agents[] = $agent;
            $agent->setUser($this);
        }

        return $this;
    }

    public function removeAgent(Representative $agent): self
    {
        if ($this->agents->contains($agent)) {
            $this->agents->removeElement($agent);
            // set the owning side to null (unless already changed)
            if ($agent->getUser() === $this) {
                $agent->setUser(null);
            }
        }

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
            $celebrity->setUser($this);
        }

        return $this;
    }

    public function removeCelebrity(Celebrity $celebrity): self
    {
        if ($this->celebrities->contains($celebrity)) {
            $this->celebrities->removeElement($celebrity);
            // set the owning side to null (unless already changed)
            if ($celebrity->getUser() === $this) {
                $celebrity->setUser(null);
            }
        }

        return $this;
    }

    public function getActive(): ?int
    {
        return $this->active;
    }

    public function setActive(int $active): self
    {
        $this->active = $active;

        return $this;
    }

    public static function getAvailableRoles()
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_SPOT_CHECKER,
            self::ROLE_EDITOR,
            self::ROLE_SPECTATOR,
            self::ROLE_TRAINER,
            self::ROLE_WRITER,
            self::ROLE_IMAGE_UPLOADER
        ];
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return [$this->role];
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->login;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->login,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->login,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection|RepresentativeLog[]
     */
    public function getRepresentativeLogs(): Collection
    {
        return $this->representativeLogs;
    }

    public function addRepresentativeLog(RepresentativeLog $representativeLog): self
    {
        if (!$this->representativeLogs->contains($representativeLog)) {
            $this->representativeLogs[] = $representativeLog;
            $representativeLog->setUser($this);
        }

        return $this;
    }

    public function removeRepresentativeLog(RepresentativeLog $representativeLog): self
    {
        if ($this->representativeLogs->contains($representativeLog)) {
            $this->representativeLogs->removeElement($representativeLog);
            // set the owning side to null (unless already changed)
            if ($representativeLog->getUser() === $this) {
                $representativeLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CelebrityLog[]
     */
    public function getCelebrityLogs(): Collection
    {
        return $this->celebrityLogs;
    }

    public function addCelebrityLog(CelebrityLog $celebrityLog): self
    {
        if (!$this->celebrityLogs->contains($celebrityLog)) {
            $this->celebrityLogs[] = $celebrityLog;
            $celebrityLog->setUser($this);
        }

        return $this;
    }

    public function removeCelebrityLog(CelebrityLog $celebrityLog): self
    {
        if ($this->celebrityLogs->contains($celebrityLog)) {
            $this->celebrityLogs->removeElement($celebrityLog);
            // set the owning side to null (unless already changed)
            if ($celebrityLog->getUser() === $this) {
                $celebrityLog->setUser(null);
            }
        }

        return $this;
    }

    public function getMonthlyLimit(): ?int
    {
        return $this->monthlyLimit;
    }

    public function setMonthlyLimit(?int $monthlyLimit): self
    {
        $this->monthlyLimit = $monthlyLimit;

        return $this;
    }

    public function getLimitUsed(): ?int
    {
        return $this->limitUsed;
    }

    public function setLimitUsed(?int $limitUsed): self
    {
        $this->limitUsed = $limitUsed;

        return $this;
    }

    public function __toString()
    {
        return $this->getLogin();
    }

    /**
     * @return mixed
     */
    public function getCompanies()
    {
        return $this->companies;
    }

    /**
     * @param mixed $companies
     */
    public function setCompanies($companies): void
    {
        $this->companies = $companies;
    }

    /**
     * @return mixed
     */
    public function getCompanyLogs()
    {
        return $this->companyLogs;
    }

    /**
     * @param mixed $companyLogs
     */
    public function setCompanyLogs($companyLogs): void
    {
        $this->companyLogs = $companyLogs;
    }

    /**
     * @return Collection|UniqueLink[]
     */
    public function getUniqueLinks(): Collection
    {
        return $this->uniqueLinks;
    }

    public function addUniqueLink(UniqueLink $uniqueLink): self
    {
        if (!$this->uniqueLinks->contains($uniqueLink)) {
            $this->uniqueLinks[] = $uniqueLink;
            $uniqueLink->setUser($this);
        }

        return $this;
    }

    public function removeUniqueLink(UniqueLink $uniqueLink): self
    {
        if ($this->uniqueLinks->contains($uniqueLink)) {
            $this->uniqueLinks->removeElement($uniqueLink);
            // set the owning side to null (unless already changed)
            if ($uniqueLink->getUser() === $this) {
                $uniqueLink->setUser(null);
            }
        }

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
            $uniqueLinkCelebrity->setUser($this);
        }

        return $this;
    }

    public function removeUniqueLinkCelebrity(UniqueLinkCelebrity $uniqueLinkCelebrity): self
    {
        if ($this->uniqueLinkCelebrities->contains($uniqueLinkCelebrity)) {
            $this->uniqueLinkCelebrities->removeElement($uniqueLinkCelebrity);
            // set the owning side to null (unless already changed)
            if ($uniqueLinkCelebrity->getUser() === $this) {
                $uniqueLinkCelebrity->setUser(null);
            }
        }

        return $this;
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
            $uniqueLinkCompany->setUser($this);
        }

        return $this;
    }

    public function removeUniqueLinkCompany(UniqueLinkCompany $uniqueLinkCompany): self
    {
        if ($this->uniqueLinkCompanies->contains($uniqueLinkCompany)) {
            $this->uniqueLinkCompanies->removeElement($uniqueLinkCompany);
            // set the owning side to null (unless already changed)
            if ($uniqueLinkCompany->getUser() === $this) {
                $uniqueLinkCompany->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmailSync()
    {
        return $this->email_sync;
    }

    /**
     * @param mixed $email_sync
     */
    public function setEmailSync($email_sync): void
    {
        $this->email_sync = $email_sync;
    }
}
