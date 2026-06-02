<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TicketController extends AbstractController
{
    #[Route('/ticket/create', name: 'app_ticket_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // asignamos como creador el usuario actualmente logueado
            $ticket->setCreator($this->getUser());
            // $this->getUser() saca del token de seguridad actual el usuario que ha hecho login.

            $entityManager->persist($ticket);
            $entityManager->flush();

            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_ticket_list');
        }

        return $this->render('ticket/create.html.twig', [
            'ticketForm' => $form->createView(),
        ]);

    }

    #[Route('/ticket/list', name: 'app_ticket_list')]
    public function list(TicketRepository $ticketRepo): Response
    {
        // hacemos una consulta por creator y le pasamos el id del usuario logueado
        // así nos muestra solo los tickets del usuario logueado
        $tickets = $ticketRepo->findBy(["creator" => $this->getUser()]);

        return $this->render('ticket/list.html.twig', [
            'tickets' => $tickets
        ]);
    }

    #[Route('/ticket/{id}', name: 'app_ticket_id')]
    public function show(TicketRepository $ticketRepo, int $id): Response
    {
        // Hacemos una consulta por id, pero
        // Los ID de los tickets son GLOBALES
        $ticket = $ticketRepo->find($id);
        
        // Por lo que hay que comprobar si en el ticket de la consulta
        // Coinciden creador y usuario logueado que lo consulta
        if ($ticket->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        // Para que cada user solo pueda acceder a sus tickets

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket
        ]);
    }
}
