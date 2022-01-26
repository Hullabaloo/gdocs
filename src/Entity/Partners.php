<?php

namespace App\Entity;

use App\Repository\PartnersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PartnersRepository::class)
 */
class Partners
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $partnerName;

    /**
     * @ORM\OneToMany(targetEntity=PartnerSales::class, mappedBy="partnerId")
     */
    private $partnerSales;

    public function __construct()
    {
        $this->partnerSales = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPartnerName(): ?string
    {
        return $this->partnerName;
    }

    public function setPartnerName(string $partnerName): self
    {
        $this->partnerName = $partnerName;

        return $this;
    }

    /**
     * @return Collection|PartnerSales[]
     */
    public function getPartnerSales(): Collection
    {
        return $this->partnerSales;
    }

    public function addPartnerSale(PartnerSales $partnerSale): self
    {
        if (!$this->partnerSales->contains($partnerSale)) {
            $this->partnerSales[] = $partnerSale;
            $partnerSale->setPartnerId($this);
        }

        return $this;
    }

    public function removePartnerSale(PartnerSales $partnerSale): self
    {
        if ($this->partnerSales->removeElement($partnerSale)) {
            // set the owning side to null (unless already changed)
            if ($partnerSale->getPartnerId() === $this) {
                $partnerSale->setPartnerId(null);
            }
        }

        return $this;
    }
}
