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
use App\Repository\CommentRepository;
use App\Repository\StoryRepository;

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
     * @Route("/detail/{id}", name="detail_comment", methods={"GET"})
     */

    public function detailStory(Request $request, $id): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $comment = $this->commentRepository->findBy(['Story' => $id], []);

        $result = [];

        foreach ($comment as $comment) {
            $result[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'publicationDate' => $comment->getPublicationDate(),
                'score' => $comment->getScore(),
                'User' => $comment->getUser()->getNickName(),
                'UserIdComment' => $comment->getUser()->getId(),
                'UserIdLogin' => $user->getId(),
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
                'code' => 200,
                'content' => $content
            ]
        );
    }

    /**
     * @Route("/delete/{id}", name="delete_comment", methods={"DELETE"})
     */
    public function delete($id): Response
    {
        $comment = $this->commentRepository->find($id);
        $this->em->remove($comment);
        $this->em->flush();
        return new JsonResponse([
            'code' => 200
        ]);
    }
}
