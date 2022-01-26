<?php

namespace App\Entity;

use App\Repository\PartnerSalesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PartnerSalesRepository::class)
 */
class PartnerSales
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Partners::class, inversedBy="partnerSales")
     * @ORM\JoinColumn(nullable=false)
     */
    private $partnerId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $itemDateTime;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $clientName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $productName;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="float")
     */
    private $piecePrice;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $deliveryType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $deliveryCity;

    /**
     * @ORM\Column(type="float")
     */
    private $deliveryPrice;

    /**
     * @ORM\Column(type="float")
     */
    private $totalPrice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPartnerId(): ?Partners
    {
        return $this->partnerId;
    }

    public function setPartnerId(?Partners $partnerId): self
    {
        $this->partnerId = $partnerId;

        return $this;
    }

    public function getItemDateTime(): ?\DateTimeInterface
    {
        return $this->itemDateTime;
    }

    public function setItemDateTime(\DateTimeInterface $itemDateTime): self
    {
        $this->itemDateTime = $itemDateTime;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(string $clientName): self
    {
        $this->clientName = $clientName;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPiecePrice(): ?float
    {
        return $this->piecePrice;
    }

    public function setPiecePrice(float $piecePrice): self
    {
        $this->piecePrice = $piecePrice;

        return $this;
    }

    public function getDeliveryType(): ?string
    {
        return $this->deliveryType;
    }

    public function setDeliveryType(string $deliveryType): self
    {
        $this->deliveryType = $deliveryType;

        return $this;
    }

    public function getDeliveryCity(): ?string
    {
        return $this->deliveryCity;
    }

    public function setDeliveryCity(string $deliveryCity): self
    {
        $this->deliveryCity = $deliveryCity;

        return $this;
    }

    public function getDeliveryPrice(): ?float
    {
        return $this->deliveryPrice;
    }

    public function setDeliveryPrice(float $deliveryPrice): self
    {
        $this->deliveryPrice = $deliveryPrice;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }
}
