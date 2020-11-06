<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Repository\MailingListRepository;
use Capco\AppBundle\Traits\UuidTrait;
use Capco\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MailingListRepository::class)
 * @ORM\Table(name="mailing_list")
 */
class MailingList
{
    use UuidTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\ManyToMany(targetEntity="Capco\UserBundle\Entity\User")
     * @ORM\JoinTable(name="mailing_list_user")
     */
    private Collection $users;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=true)
     */
    private ?Project $project;

    /**
     * @ORM\Column(name="is_deletable", type="boolean", options={"default": true})
     */
    private bool $isDeletable = true;

    /**
     * @ORM\OneToMany(targetEntity=EmailingCampaign::class, mappedBy="mailingList", orphanRemoval=true)
     */
    private Collection $emailingCampaigns;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->project = null;
        $this->emailingCampaigns = new ArrayCollection();
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

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function getUsersWithValidEmail(): Collection
    {
        $usersWithValidEmail = new ArrayCollection();
        foreach ($this->users as $user) {
            if ($user->getEmail()) {
                $usersWithValidEmail->add($user);
            }
        }

        return $usersWithValidEmail;
    }

    public function setUsers(array $users = []): self
    {
        $this->users = new ArrayCollection($users);

        return $this;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function isDeletable(): bool
    {
        return $this->isDeletable;
    }

    public function setIsDeletable(bool $isDeletable): self
    {
        $this->isDeletable = $isDeletable;

        return $this;
    }

    public function getEmailingCampaigns(): Collection
    {
        return $this->emailingCampaigns;
    }

    public function addEmailingCampaign(EmailingCampaign $emailingCampaign): self
    {
        if (!$this->emailingCampaigns->contains($emailingCampaign)) {
            $this->emailingCampaigns[] = $emailingCampaign;
            $emailingCampaign->setMailingList($this);
        }

        return $this;
    }

    public function removeEmailingCampaign(EmailingCampaign $emailingCampaign): self
    {
        if ($this->emailingCampaigns->contains($emailingCampaign)) {
            $this->emailingCampaigns->removeElement($emailingCampaign);
            if ($emailingCampaign->getMailingList() === $this) {
                $emailingCampaign->setMailingList(null);
            }
        }

        return $this;
    }
}