<?php

namespace App\Controller;

use App\Entity\Date;
use App\Entity\User;
use App\Form\CreateDateType;
use App\Form\UpdateDateType;
use App\Repository\DateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/date')]
final class DateController extends AbstractController
{
    public function __construct(
        private readonly string $myDiscordClientId
    )
    {
    }

    #[Route(name: 'app_date_index', methods: ['GET'])]
    public function index(DateRepository $dateRepository): Response
    {
        return $this->render('date/index.html.twig', [
            'dates' => $dateRepository->findBy(["important"=>true]),
        ]);
    }
    #[Route('/all',name: 'app_date_index_all', methods: ['GET'])]
    public function index_everything(DateRepository $dateRepository): Response
    {
        return $this->render('date/index.html.twig', [
            'dates' => $dateRepository->findAll(),
        ]);
    }
    #[Route('/get_dates', name: 'get_dates')]
    public function getDates(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $start= $request->query->get('start');
        $end= $request->query->get('end');

        if (!$start || !$end) {
            return new JsonResponse([],400);
        }
        $dates = $em->getRepository(Date::class)->findDateBetween($start, $end);

        $array = [];
        /** @var Date $date */
        foreach ($dates as $date) {
            $color = "#". $date->getColor();
            $array[] = [
                "id"=> $date->getId(),
                "title"=> $date->getTitle(),
                "start"=> $date->getStartDate()->format("Y-m-d H:i:s"),
                "end"=> $date->getEndDate()->format("Y-m-d H:i:s"),
                "rendering"=> 'background',
                "color"=> $color,
                "backgroundColor"=> $color
            ];
        }
        return new JsonResponse($array);

    }
    #[Route('/new', name: 'app_date_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $date = new Date();
        $form = $this->createForm(CreateDateType::class, $date);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $dbUser = $entityManager->getRepository(User::class)->findOneBy(['email'=>$user->getUserIdentifier()]);
            if ($dbUser && $dbUser->getId() == $this->myDiscordClientId) {
                $date->setIsFromMe(true);
                $date->setColor("4c9294");
            } else {
                $date->setIsFromMe(false);
                $date->setColor("F0A8C6");
            }
            $entityManager->persist($date);
            $entityManager->flush();

            return $this->redirectToRoute('app_date_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('date/new.html.twig', [
            'date' => $date,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_date_show', methods: ['GET'], requirements: ["id"=>"\d+"])]
    public function show(Date $date): Response
    {
        return $this->render('date/show.html.twig', [
            'date' => $date,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_date_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    public function edit(Request $request, Date $date, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UpdateDateType::class, $date);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_date_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('date/edit.html.twig', [
            'date' => $date,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_date_delete', methods: ['DELETE'])]
    #[IsGranted("ROLE_USER")]
    public function delete(Request $request, Date $date, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$date->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($date);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_date_index', [], Response::HTTP_SEE_OTHER);
    }
}
