<?php
// src/Controller/TestEmailController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class TestEmailCOntroller extends AbstractController
{
    #[Route('/test-email', name: 'app_test_email')]
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from($this->getParameter('MAIL_SENDER_EMAIL')) // Doit être l'adresse Gmail utilisée dans le .env
            ->to('turin-ylan@outlook.fr') // L'adresse qui va recevoir le mail
            ->subject('Succès ! Envoi depuis Symfony')
            ->text('Le serveur SMTP de Gmail fonctionne parfaitement avec mon application Symfony.')
            ->html('<p>Le serveur SMTP de <strong>Gmail</strong> fonctionne parfaitement !</p>');

        $mailer->send($email);

        return new Response('L\'e-mail de test a bien été envoyé !');
    }
}
