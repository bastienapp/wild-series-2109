<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Form\CommentType;
use App\Form\EpisodeType;
use App\Repository\EpisodeRepository;
use App\Service\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/episode") */
class EpisodeController extends AbstractController
{
    /** @Route("/", name="episode_index", methods={"GET"}) */
    public function index(EpisodeRepository $episodeRepository): Response
    {
        return $this->render("episode/index.html.twig", [
            "episodes" => $episodeRepository->findAll(),
        ]);
    }

    /** @Route("/new", name="episode_new", methods={"GET", "POST"}) */
    public function new(Request $request, EntityManagerInterface $entityManager, Slugify $slugify): Response
    {
        $episode = new Episode();
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugify->generate($episode->getTitle());
            $episode->setSlug($slug);
            $entityManager->persist($episode);
            $entityManager->flush();

            return $this->redirectToRoute("episode_index", [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm("episode/new.html.twig", [
            "episode" => $episode,
            "form" => $form,
        ]);
    }

    /** @Route("/{slug}", name="episode_show", methods={"GET", "POST"}) */
    public function show(Request $request, EntityManagerInterface $entityManager, Episode $episode): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $user = $this->getUser();
        if ($form->isSubmitted() && $form->isValid() && $user) {
            $comment->setEpisode($episode);
            $comment->setAuthor($user);
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('episode_show', ['slug' => $episode->getSlug()]);
        }
        return $this->render("episode/show.html.twig", [
            "episode" => $episode,
            "comments" => $episode->getComments(),
            "form" => $form->createView()
        ]);
    }

    /** @Route("/{slug}/edit", name="episode_edit", methods={"GET", "POST"}) */
    public function edit(Request $request, Episode $episode, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute("episode_index", [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm("episode/edit.html.twig", [
            "episode" => $episode,
            "form" => $form,
        ]);
    }

    /** @Route("/{slug}", name="episode_delete", methods={"POST"}) */
    public function delete(Request $request, Episode $episode, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid("delete".$episode->getId(), $request->request->get("_token"))) {
            $entityManager->remove($episode);
            $entityManager->flush();
        }

        return $this->redirectToRoute("episode_index", [], Response::HTTP_SEE_OTHER);
    }

    /** @Route("/comment/{id}", name="episode_comment_delete", methods={"POST"}) */
    public function deleteComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        // Check wether the logged in user is the owner of the program
        if ($this->getUser() !== $comment->getAuthor() && !in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Only the owner can delete the comment!');
        }
        $episode = $comment->getEpisode();
        $slug = $episode->getSlug();
        if ($this->isCsrfTokenValid("delete" . $comment->getId(), $request->request->get("_token"))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute("episode_show", ['slug' => $slug], Response::HTTP_SEE_OTHER);
    }
}
