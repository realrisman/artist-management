<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_EDITOR = 'ROLE_EDITOR';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $role;

    /**
     * @ORM\Column(type="smallint")
     */
    private $active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

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

    public function __construct()
    {
        $this->celebrities = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
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
            self::ROLE_EDITOR,
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

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->login,
            $this->password,
        ) = unserialize($serialized, ['allowed_classes' => false]);
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
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
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
}
