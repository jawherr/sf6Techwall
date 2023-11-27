<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

trait TimeStampTrait
{

    #[ORM\Column(nullable: true)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;


    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
    #[ORM\PrePersist()]
    public function onPrePersist() {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
    #[ORM\PreUpdate()]
    public function onPreUpdate() {
        $this->updatedAt = new \DateTime();
    }

}