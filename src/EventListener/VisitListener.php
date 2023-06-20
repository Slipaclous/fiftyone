<?php
// src/EventListener/VisitListener.php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Entity\Visit;

class VisitListener
{
    private $entityManager;
    private $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    public function onKernelRequest(RequestEvent $event)
{
    $request = $event->getRequest();

    // Retrieve visitor information from the request, such as the IP address
    $visitorIp = $request->getClientIp();

    // Create a new Visit entity and persist it to the database
    $visit = new Visit();
    $visit->setVisitorIp($visitorIp);
    $visit->setVisitDate(new \DateTime());

    $entityManager = $this->entityManager;
    
    if ($entityManager->isOpen()) {
        $entityManager->persist($visit);
        
        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            // Handle the exception appropriately
        }
    } else {
        // Handle the case when the EntityManager is closed
    }
}
}
