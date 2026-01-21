<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Controller\InvoiceIncrementionController;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Post(),
        new Post(
            uriTemplate: '/invoices/{id}/increment',
            controller: InvoiceIncrementionController::class,
            openapi: new Model\Operation(
                summary: 'Incrémente une facture',
                description: "Incrémente le chrono d'une facture donnée"
            ),
            name: 'Increment'
        ),
        new GetCollection(),
        new Put(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['invoices_read']],
    denormalizationContext:[
        "disable_type_enforcement" => true
    ],
    order: ['amount' => 'ASC'],
    paginationEnabled: false,
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
    #[Assert\NotBlank(message: "Le montant est obligatoire")]
    #[Assert\Type(type: "numeric", message: "Le montant de la facture doit être au format numérique")]
    private $amount = null;

    #[ORM\Column]
    #[Groups(['invoices_read','customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: "La date de la facture est obligatoire")]
    #[Assert\Regex(
        pattern: "/^\d{4}-\d{2}-\d{2}$/",
        message: "La date doit être au format YYYY-MM-DD"
    )]
    private $sentAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['invoices_read','customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: "Le statut de la facture est obligatoire")]
    #[Assert\Choice(choices:["SENT","PAID","CANCELED"], message:"Le statut doit être soit SENT, PAID ou CANCELED")]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invoices_read'])]
    #[Assert\NotBlank(message: "Le client de la factoire doit être renseigné")]
    private ?Customer $customer = null;

    #[ORM\Column]
    #[Groups(['invoices_read','customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: "Le chrono de la facture est obligatoire")]
    #[Assert\Type(type: "integer", message: "Le chrono de la facture doit être au format numérique")]
    private $chrono = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt()
    {
        return $this->sentAt;
    }

    public function setSentAt($sentAt): static
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

    public function getChrono()
    {
        return $this->chrono;
    }

    public function setChrono($chrono): static
    {
        $this->chrono = $chrono;

        return $this;
    }
}
