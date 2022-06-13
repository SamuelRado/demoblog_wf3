<?php
namespace App\Notification;

use App\Entity\Contact;
use Twig\Environment;

class ContactNotification
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $renderer;

    public function __construct(\Swift_Mailer $mailer, Environment $renderer)
    {
        // hors d'un controller, une injection de dépendance n'est possible que dans le constructeur de la classe
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    public function notify(Contact $contact)
    {
        $message = (new \Swift_Message("Réception d'un message de contact"))    // objet
                ->setFrom($contact->getEmail()) // expéditeur
                ->setTo("mailpro@gmail.com")    // destinataire
                ->setReplyTo($contact->getEmail())  // adresse de réponse
                ->setBody($this->renderer->render('emails/contact.html.twig', [ // corps
                    'contact' => $contact
                ]), 'text/html');
        
        $this->mailer->send($message);
    }
}