<?php

namespace App\Controller;

use App\Entity\MailRecipient;
use App\Form\MailRecipientType;
use App\Repository\MailRecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mail')]
#[IsGranted('ROLE_USER')]
final class MailRecipientController extends AbstractController
{
    #[Route(name: 'app_mail_index', methods: ['GET'])]
    public function index(MailRecipientRepository $repository): Response
    {
        return $this->render('mail_recipient/index.html.twig', ['recipients' => $repository->findAll()]);
    }

    #[Route('/new', name: 'app_mail_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        return $this->handleForm($request, $em, new MailRecipient(), 'Ajouter un destinataire');
    }

    #[Route('/{id}/edit', name: 'app_mail_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, MailRecipient $recipient, EntityManagerInterface $em): Response
    {
        return $this->handleForm($request, $em, $recipient, 'Modifier le destinataire');
    }

    #[Route('/{id}/delete', name: 'app_mail_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, MailRecipient $recipient, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_mail_'.$recipient->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($recipient);
            $em->flush();
        }

        return $this->redirectToRoute('app_mail_index', [], Response::HTTP_SEE_OTHER);
    }

    private function handleForm(Request $request, EntityManagerInterface $em, MailRecipient $recipient, string $title): Response
    {
        $form = $this->createForm(MailRecipientType::class, $recipient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($recipient);
            try {
                $em->flush();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException) {
                $form->get('email')->addError(new \Symfony\Component\Form\FormError('Cette adresse existe déjà.'));

                return $this->render('mail_recipient/form.html.twig', ['form' => $form, 'title' => $title]);
            }

            return $this->redirectToRoute('app_mail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mail_recipient/form.html.twig', ['form' => $form, 'title' => $title]);
    }
}