<?php

namespace App\EventSubscriber;

use App\Event\AddPersonneEvent;
use App\Service\MailerService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PersonneEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerService $mailerService,
        private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AddPersonneEvent::ADD_PERSONNE_EVENT => ['onAddPersonneEvent', 3000]
        ];
    }
    public function onAddPersonneEvent(AddPersonneEvent $event){
        $personne = $event->getPersonne();
        //$mailMessage = $personne->getFirstname().' '.$personne->getName()." a été ajouté avec succés";
        $this->logger->info("Envoi d'email pour ".$personne->getFirstname().' '.$personne->getName());
        //$this->mailerService->sendEmail(content: $mailMessage, subject: 'Mail sent from EventSubscriber');
    }
}