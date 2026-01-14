<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['invoices_read']],
    order: ['amount' => 'ASC'],
    paginationEnabled: true,
    paginationItemsPerPage: 10,
)]
#[ApiResource(
    uriTemplate: '/customers/{id}/invoices',
    operations: [ new GetCollection() ],
    uriVariables: ['id' => new Link(fromProperty: 'invoices', fromClass: Customer::class)],
    normalizationContext: [
        'groups' => ['invoices_subresource']
    ]
)]
#[ApiFilter(OrderFilter::class, properties: ['amount', 'sentAt'])]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoices_read','customers_read', 'invoices_subresource'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['invoices_read','customers_read', 'invoices_subresource'])]
    private ?float $amount = null;

    #[ORM\Column]
    #[Groups(['invoices_read','customers_read', 'invoices_subresource'])]
    private ?\DateTime $sentAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['invoices_read','customers_read', 'invoices_subresource'])]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invoices_read'])]
    private ?Customer $customer = null;

    #[ORM\Column]
    #[Groups(['invoices_read','customers_read', 'invoices_subresource'])]
    private ?int $chrono = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTime $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getChrono(): ?int
    {
        return $this->chrono;
    }

    public function setChrono(int $chrono): static
    {
        $this->chrono = $chrono;

        return $this;
    }
}
