<?php

namespace App\Controller\Front;

use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("update/comment/{id}", name="comment_update")
     */
    public function updateComment(
        CommentRepository $commentRepository,
        $id,
        EntityManagerInterface $entityManagerInterface,
        Request $request
    ) {
        $comment = $commentRepository->find($id);
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {

            $entityManagerInterface->persist($comment);
            $entityManagerInterface->flush();

            return $this->redirectToRoute('app_login');
        }


        return $this->render("front/commentform.html.twig", ['commentForm' => $commentForm->createView()]);
    }

    /**
     * @Route("delete/comment/{id}", name="comment_delete")
     */
    public function deleteComment(
        $id,
        CommentRepository $commentRepository,
        EntityManagerInterface $entityManagerInterface
    ) {
        $comment = $commentRepository->find($id);
        $entityManagerInterface->remove($comment);
        $entityManagerInterface->flush();

        return $this->redirectToRoute('app_login');
    }
}