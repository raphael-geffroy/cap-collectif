<?php

namespace Capco\ClassificationBundle\Entity;

use Capco\MediaBundle\Entity\Media;
use Cocur\Slugify\Slugify;

class Collection
{
    protected int $id;
    protected ?string $name;
    protected ?string $slug;
    protected bool $enabled;
    protected ?string $description;
    protected ?\DateTimeInterface $createdAt;
    protected ?\DateTimeInterface $updatedAt;
    protected ?Media $media;
    protected ?Context $context;

    public function __toString()
    {
        return $this->getName() ?: 'n/a';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->setSlug($name);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = Slugify::create()->slugify($slug);

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setMedia(?Media $media = null): self
    {
        $this->media = $media;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setContext(Context $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getContext(): ?Context
    {
        return $this->context;
    }
}
