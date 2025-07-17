<?php

// src/Service/FormErrorLogger.php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;

class FormErrorLogger
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logErrors(FormInterface $form): void
    {
        foreach ($form->getErrors(true) as $error) {
            $this->logger->error(sprintf(
                'Erreur champ "%s": %s',
                $error->getOrigin()->getName(),
                $error->getMessage()
            ));
        }
    }
}