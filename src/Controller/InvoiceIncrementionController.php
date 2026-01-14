<?php
namespace App\Controller;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class InvoiceIncrementionController
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(Invoice $data): JsonResponse
    {
        $data->setChrono($data->getChrono() + 1);
        $this->manager->persist($data);
        $this->manager->flush();
        // retourner les données modifiées en JSON
        return new JsonResponse([
            'id'=> $data->getId(),
            'chrono'=> $data->getChrono(),
            'message' => 'Invoice chrono incremented successfully'
        ]);
    }
}
