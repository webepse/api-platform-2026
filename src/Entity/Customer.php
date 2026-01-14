<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['customers_read']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'firstName' => 'partial',
    'lastName',
    'company'])]
#[ApiFilter(OrderFilter::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['customers_read','users_read','invoices_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customers_read','users_read','invoices_read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customers_read','users_read','invoices_read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customers_read','users_read','invoices_read'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['customers_read','users_read','invoices_read'])]
    private ?string $company = null;

    /**
     * @var Collection<int, Invoice>
     */
    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'customer')]
    #[Groups(['customers_read','users_read'])]
    private Collection $invoices;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['customers_read'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    /**
     * permet de récupérer le montant total des factures d'un client
     * @return float
     */
    #[Groups(['customers_read'])]
    public function getTotalAmount(): float
    {
        return round(array_reduce($this->invoices->toArray(), function($total, $invoice) {return $total + $invoice->getAmount();}, 0), 2);
        // round(1.615, 2) => 1.62
        // array_reduce(array, function callback, initial_value)
        // array_reduce(array, function ($carry, $item) { return $carry + $item['price']; }, 0)
        // array_reduce($this->invoices->toArray(), function($total, $invoice) {return $total + $invoice->getAmount();}, 0)
    }

    /**
     * Permet de récupérer le montant total non payé des factures d'un client
     * @return float
     */
    #[Groups(['customers_read'])]
    public function getUnpaidAmount(): float
    {
        return round(array_reduce($this->invoices->toArray(),
            function($total, $invoice) {
                return $total + ($invoice->getStatus() === "PAID" || $invoice->getStatus() === "CANCELED" ? 0 : $invoice->getAmount());
            }, 0),
        2);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setCustomer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getCustomer() === $this) {
                $invoice->setCustomer(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}


