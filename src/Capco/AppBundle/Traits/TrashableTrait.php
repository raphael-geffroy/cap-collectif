<?php
namespace Capco\AppBundle\Traits;

use Capco\AppBundle\Entity\Interfaces\TrashableInterface;
use Doctrine\ORM\Mapping as ORM;

trait TrashableTrait
{
    /**
     * @ORM\Column(name="trashed_status", type="string", nullable=true, columnDefinition="ENUM('visible', 'invisible')")
     */
    private $trashedStatus;

    /**
     * @ORM\Column(name="trashed_at", type="datetime", nullable=true)
     */
    private $trashedAt = null;

    /**
     * @ORM\Column(name="trashed_reason", type="text", nullable=true)
     */
    private $trashedReason = null;

    public function isTrashed(): bool
    {
        return $this->trashedStatus !== null;
    }

    public function getTrashedStatus(): ?string
    {
        return $this->trashedStatus;
    }

    public function setTrashedStatus(?string $status)
    {
        if (!$status) {
            $this->trashedStatus = null;
            $this->trashedReason = null;
            $this->trashedAt = null;

            return $this;
        }

        if (
            !in_array($status, array(
                TrashableInterface::STATUS_VISIBLE,
                TrashableInterface::STATUS_INVISIBLE,
            ))
        ) {
            throw new \InvalidArgumentException("Invalid status");
        }

        $this->trashedStatus = $status;
        $this->trashedAt = new \DateTime();

        return $this;
    }

    public function getTrashedAt(): ?\DateTime
    {
        return $this->trashedAt;
    }

    public function getTrashedReason(): ?string
    {
        return $this->trashedReason;
    }

    public function setTrashedReason(string $trashedReason = null)
    {
        $this->trashedReason = $trashedReason;

        return $this;
    }
}
