<?php

namespace App\Controller;

use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Entity\Entrada;
use App\Entity\Story;
use App\Repository\CategoriaRepository;
use App\Repository\CommentRepository;
use App\Repository\EntradaRepository;
use App\Repository\StoryRepository;
use DateTimeInterface;

/**
 * @Route("comment")
 */

class CommentController extends AbstractController
{

    private $em;
    private $commentRepository;

    public function __construct(EntityManagerInterface $em, CommentRepository $commentRepository)
    {
        $this->em = $em;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @Route("/data", name="data_comment", methods={"GET"})
     */
    public function readComment(): Response
    {
        $comment = $this->commentRepository->findBy([], []);

        $result = [];

        foreach ($comment as $comment) {
            $result[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'publicationDate' => $comment->getPublicationDate(),
                'score' => $comment->getScore(),
                'User' => $comment->getUser()->getNickName(),
                'Story ' => $comment->getStory()->getTitle(),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/detail/{id}", name="detail_comment", methods={"GET"})
     */

    public function detailStory(Request $request, $id): Response
    {
        $comment = $this->commentRepository->findBy(['Story' => $id], []);

        $result = [];

        foreach ($comment as $comment) {
            $result[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'publicationDate' => $comment->getPublicationDate(),
                'score' => $comment->getScore(),
                'User' => $comment->getUser()->getNickName(),
                'Story ' => $comment->getStory()->getId(),
            ];
        }
        return new JsonResponse($result);
    }


    /**
     * @Route("/add", name="add_comment", methods={"POST"})
     */

    public function addComment(Request $request, UserRepository $userRepository, StoryRepository $storyRepository): Response
    {
        $content = json_decode($request->getContent(), true);

        $user = $this->getUser();

        $story = $storyRepository->findOneBy(['id' => $content['story']]);

        $comment = new Comment();

        $comment->setContent($content['content']);

        $date = new \DateTime('@' . strtotime('now'));
        $comment->setPublicationDate($date);

        $comment->setScore($content['score']);

        $comment->setUser($user);

        $comment->setStory($story);

        $this->em->persist($comment);

        $this->em->flush();

        return new JsonResponse(
            [
                'result' => 'ok',
                'code' => 200,
                'content' => $content

            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="edit_comment", methods={"PUT"})
     */
    public function update(Request $request, $id, UserRepository $userRepository, StoryRepository $storyRepository): Response
    {
        $content = json_decode($request->getContent(), true);
        $comment = $this->commentRepository->find($id);


        if (isset($content['content'])) {
            $comment->setContent($content['content']);
        }

        if (isset($content['date'])) {
            $date = new \DateTime('@' . strtotime('now'));
            $comment->setPublicationDate($date);
        }

        if (isset($content['score'])) {
            $comment->setScore($content['score']);
        }

        if (isset($content['user'])) {
            $user = $userRepository->findOneBy(['nickName' => $content['user']]);
            $comment->setUser($user);
        }

        if (isset($content['story'])) {
            $story = $storyRepository->findOneBy(['title' => $content['story']]);
            $comment->setStory($story);
        }


        $this->em->flush();

        return new JsonResponse(['respuesta' => 'ok']);
    }

    /**
     * @Route("/delete/{id}", name="delete_comment", methods={"DELETE"})
     */
    public function delete($id): Response
    {
        $comment = $this->commentRepository->find($id);
        $this->em->remove($comment);
        $this->em->flush();
        return new JsonResponse(['respuesta' => 'ok']);
    }
}
