<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Ticket;
use App\Enum\TicketStatus;
use App\Form\CommentType;
use App\Form\TicketType;
use App\Repository\CommentRepository;
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
            try {
                // asignamos como creador el usuario actualmente logueado
                $ticket->setCreator($this->getUser());
                // $this->getUser() saca del token de seguridad actual el usuario que ha hecho login.

                $entityManager->persist($ticket);
                $entityManager->flush();

                // Mensaje flash de éxito
                $this->addFlash('success', 'Ticket creado con éxito');

                // do anything else you need here, like send an email
                return $this->redirectToRoute('app_ticket_list');
            } catch (Exception $e) {
                $this->addFlash('danger', 'No se pudo crear el ticket');
            }
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

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        // Por lo que hay que comprobar si en el ticket de la consulta
        // Coinciden creador y usuario logueado que lo consulta
        if ($ticket->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        // Para que cada user solo pueda acceder a sus tickets

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $comments = $ticket->getComments();

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
            'commentForm' => $form->createView(),
            'comments' => $comments
        ]);
    }

    #[Route('/ticket/{id}/edit', name: 'app_ticket_id_edit')]
    public function edit(TicketRepository $ticketRepo, int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Comprobamos si el id del ticket es del usuario con sesion iniciada
        $ticket = $ticketRepo->find($id);
        if (!$ticket) {
            throw $this->createNotFoundException();
        }
        if ($ticket->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Al crear el form con el objeto ticket rellena los campos con sus datos
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();

                // Mensaje flash de éxito
                $this->addFlash('success', 'Ticket actualizado con éxito');
                return $this->redirectToRoute('app_ticket_list');
            } catch (Exception $e) {
                $this->addFlash('danger', 'No se pudo actualizar el ticket');
            }
        }

        return $this->render('ticket/edit.html.twig', [
            'ticketForm' => $form->createView(),
        ]);
    }

    #[Route('/ticket/{id}/delete', name: 'app_ticket_id_delete', methods: ['POST'])]
    public function delete(Request $request, TicketRepository $ticketRepo, int $id, EntityManagerInterface $entityManager): Response
    {
        // Comprobamos si el id del ticket es del usuario con sesion iniciada
        $ticket = $ticketRepo->find($id);
        // Comprobamos si la consulta devuelve algun ticket
        if (!$ticket) {
            throw $this->createNotFoundException();
        }
        // Comprobamos que id sea propiedad del usuario con sesion iniciada
        if ($ticket->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        /* Comprobamos que el token CSRF enviado por el formulario es válido.
        Esto evita ataques donde otra web intenta enviar peticiones POST
        aprovechando que el usuario tiene una sesión iniciada. */
        if (!$this->isCsrfTokenValid(
            'delete' . $ticket->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException();
        }

        try {
            $entityManager->remove($ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Ticket eliminado con éxito');
        } catch (Exception $e) {
            $this->addFlash('danger', 'No se pudo eliminar el ticket');
        }
        // Redirigir después de borrar
        return $this->redirectToRoute('app_ticket_list');
    }

    #[Route('/ticket/{id}/close', name: 'app_ticket_id_close')]
    public function close(TicketRepository $ticketRepo, int $id, EntityManagerInterface $entityManager): Response
    {
        // Comprobamos si el id del ticket es del usuario con sesion iniciada
        $ticket = $ticketRepo->find($id);
        // Comprobamos si la consulta devuelve algun ticket
        if (!$ticket) {
            throw $this->createNotFoundException();
        }
        // Comprobamos que id sea propiedad del usuario con sesion iniciada
        if ($ticket->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        try {
            $ticket->setStatus(TicketStatus::CLOSED);
            $entityManager->flush();

            $this->addFlash('success', 'Ticket cerrado con éxito');
        } catch (Exception $e) {
            $this->addFlash('danger', 'No se pudo modificar el estado del ticket');
        }
        // Redirigir después de borrar
        return $this->redirectToRoute('app_ticket_id', ['id' => $ticket->getId()]);
    }

    #[Route('/ticket/{id}/reopen', name: 'app_ticket_id_reopen')]
    public function reopen(TicketRepository $ticketRepo, int $id, EntityManagerInterface $entityManager): Response
    {
        // Comprobamos si el id del ticket es del usuario con sesion iniciada
        $ticket = $ticketRepo->find($id);
        // Comprobamos si la consulta devuelve algun ticket
        if (!$ticket) {
            throw $this->createNotFoundException();
        }
        // Comprobamos que id sea propiedad del usuario con sesion iniciada
        if ($ticket->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        try {
            $ticket->setStatus(TicketStatus::OPEN);
            $entityManager->flush();

            $this->addFlash('success', 'Ticket reabierto con éxito');
        } catch (Exception $e) {
            $this->addFlash('danger', 'No se pudo modificar el estado del ticket');
        }
        // Redirigir después de borrar
        return $this->redirectToRoute('app_ticket_id', ['id' => $ticket->getId()]);
    }

    #[Route('/ticket/{id}/comment', name: 'app_ticket_comment', methods: ['POST'])]
    public function addComment(Request $request, EntityManagerInterface $entityManager, Ticket $ticket): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setAuthor($this->getUser());
            $comment->setTicket($ticket);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Comentario creado con éxito');
        }

        return $this->redirectToRoute('app_ticket_id', ['id' => $ticket->getId()]);
    }

    #[Route('/ticket/{id}/delete/comment', name: 'app_ticket_delete_comment', methods: ['POST'])]
    public function deleteComment(Request $request, CommentRepository $commentRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $comment = $commentRepository->find($id);
        // Comprobamos si la consulta devuelve algun ticket
        if (!$comment) {
            throw $this->createNotFoundException();
        }
        // Comprobamos que id sea propiedad del usuario con sesion iniciada
        if ($comment->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        /* Comprobamos que el token CSRF enviado por el formulario es válido.
        Esto evita ataques donde otra web intenta enviar peticiones POST
        aprovechando que el usuario tiene una sesión iniciada. */
        if (!$this->isCsrfTokenValid(
            'delete' . $comment->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException();
        }

        $ticket = $comment->getTicket();

        try {
            $entityManager->remove($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Comentario eliminado con éxito');
        } catch (Exception $e) {
            $this->addFlash('danger', 'No se pudo eliminar el comentario');
        }

        // Redirigir después de borrar
        return $this->redirectToRoute('app_ticket_id', ['id' => $ticket->getId()]);
    }
}
