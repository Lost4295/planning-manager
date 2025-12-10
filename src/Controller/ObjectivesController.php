<?php

namespace App\Controller;

use App\Entity\Objectif;
use App\Entity\User;
use App\Form\CreateObjectifType;
use App\Form\UpdateObjectifType;
use App\Repository\ObjectifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/objectives')]
final class ObjectivesController extends AbstractController
{
    public function __construct(
    )
    {
    }

    #[Route(name: 'objectives', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function index(ObjectifRepository $objectifRepository): Response
    {
        return $this->render('objectif/index.html.twig', [
            'objectifs' => $objectifRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_objectif_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $objectif = new Objectif();
        $form = $this->createForm(CreateObjectifType::class, $objectif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $dbUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getUserIdentifier()]);
            $objectif->setCompleted(false);
            $objectif->setCreator($dbUser);
            $entityManager->persist($objectif);
            $entityManager->flush();
            return $this->redirectToRoute('objectives', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('objectif/new.html.twig', [
            'objectif' => $objectif,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_objectif_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    public function edit(Request $request, Objectif $objectif, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UpdateObjectifType::class, $objectif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('objectives', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('objectif/edit.html.twig', [
            'objectif' => $objectif,
            'form' => $form,
        ]);
    }


    #[Route('/complete/{id}', name: 'objectif_complete', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function complete(Request $request, Objectif $objectif, EntityManagerInterface $entityManager): Response
    {
        $objectif
            ->setCompleted(true)
            ->setCompletionDate(new \DateTime());
        $entityManager->persist($objectif);
        $entityManager->flush();
        return $this->redirectToRoute('objectives', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_objectif_delete', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function delete(Request $request, Objectif $objectif, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $objectif->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($objectif);
            $entityManager->flush();
        }

        return $this->redirectToRoute('objectives', [], Response::HTTP_SEE_OTHER);
    }
}
