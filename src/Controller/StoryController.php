<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\Story;
use App\Repository\StoryRepository;
use Mael\InterventionImageBundle\MaelInterventionImageManager;

/**
 * @Route("story")
 */

class StoryController extends AbstractController
{
    private $em;
    private $storyRepository;
    private $image;

    public function __construct(EntityManagerInterface $em, StoryRepository $storyRepository, MaelInterventionImageManager $image)
    {
        $this->em = $em;
        $this->storyRepository = $storyRepository;
        $this->image = $image;
    }

    /**
     * @Route("", methods={"OPTIONS"})
     */
    public function options(): Response
    {
        return new Response('');
    }

    /**
     * @Route("/data", name="data_story", methods={"GET"})
     */
    public function readStory(UserRepository $userRepository): Response
    {
        $story = $this->storyRepository->findBy(['published' => true, 'isActive' => true], []);

        $result = [];

        foreach ($story as $story) {
            $result[] = [
                'id' => $story->getId(),
                'title' => $story->getTitle(),
                'content' => $story->getContent(),
                'publicationDate' => $story->getPublicationDate(),
                'User' => $story->getUser()->getNickName(),
                'genre' => $story->getGenre(),
                'published' => $story->getPublished(),
                'coverImage' => $story->getCoverImage()
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/detail/{id}", name="detail_story", methods={"GET"})
     */

    public function detailStory(Request $request, $id): Response
    {
        $story = $this->storyRepository->find($id);

        return new JsonResponse([
            'id' => $story->getId(),
            'title' => $story->getTitle(),
            'content' => $story->getContent(),
            'publicationDate' => $story->getPublicationDate(),
            'User' => $story->getUser()->getNickName(),
            'genre' => $story->getGenre(),
            'published' => $story->getPublished(),
            'coverImage' => $story->getCoverImage()
        ]);
    }

    /**
     * @Route("/publishedFromUser", name="published_story_from_user", methods={"GET"})
     */
    public function publishedFromUser(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $story = $this->storyRepository->findBy(['User' => $user, 'published' => true, 'isActive' => true], []);

        $result = [];

        foreach ($story as $story) {
            $result[] = [
                'id' => $story->getId(),
                'StoryTitle' => $story->getTitle(),
                'StoryGenre' => $story->getGenre(),
                'published' => $story->getPublished(),
                'isActive' => $story->getIsActive(),
                'coverImage' => $story->getCoverImage(),
                'userLogin' => $user->getNickName(),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/draftsFromUser", name="drafts_story_from_user", methods={"GET"})
     */
    public function draftsFromUser(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $story = $this->storyRepository->findBy(['User' => $user, 'published' => false], []);

        $result = [];

        foreach ($story as $story) {
            $result[] = [
                'id' => $story->getId(),
                'StoryTitle' => $story->getTitle(),
                'StoryGenre' => $story->getGenre(),
                'published' => $story->getPublished(),
                'coverImage' => $story->getCoverImage(),
                'userLogin' => $user->getNickName()
            ];
        }

        return new JsonResponse($result);
    }


    /**
     * @Route("/add", name="add_story", methods={"POST"})
     */

    public function addStory(Request $request, UserRepository $userRepository): Response
    {
        $datos = $request->request->all();

        $user = $this->getUser();

        $title = $request->get('title');
        $content = $request->get('content');
        $genre = $request->get('genre');
        $published = $request->get('published');
        $coverImage = $request->files->get('coverImage');
        $isActive = $request->get('isActive');

        $story = new Story();

        $story->setTitle($title);

        $story->setContent($content);

        $story->setPublicationDate(new \DateTime());

        $story->setUser($user);

        $story->setGenre($genre);

        $story->setPublished($published);

        if ($coverImage) {
            $renderedImag = $this->image->make($coverImage);
            $renderedImag->save('/var/www/html/images/' . $coverImage->getClientOriginalName());

            $imag64 = base64_encode($renderedImag);
            $story->setCoverImage($imag64);
        }

        $story->setIsActive($isActive);

        $this->em->persist($story);

        $this->em->flush();

        return new JsonResponse(
            [
                'code' => 200,
                'content' => $content
            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="edit_story", methods={"POST"})
     */
    public function update(Request $request, $id, UserRepository $userRepository): Response
    {
        $user = $this->getUser();


        $story = $this->storyRepository->find($id);

        $title = $request->get('title');
        $story->setTitle($title);

        $content = $request->get('content');
        $story->setContent($content);

        $date = new \DateTime('@' . strtotime('now'));
        $story->setPublicationDate($date);

        $genre = $request->get('genre');
        $story->setGenre($genre);

        $published = $request->get('published');
        $story->setPublished($published);


        $coverImage = $request->files->get('coverImage');

        if ($coverImage) {
            $renderedImag = $this->image->make($coverImage);
            $renderedImag->save('/var/www/html/images/' . $coverImage->getClientOriginalName());

            $imag64 = base64_encode($renderedImag);
            $story->setCoverImage($imag64);
        }



        $this->em->flush();

        return new JsonResponse([
            'code' => 200,
            'content' => $content
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete_story", methods={"DELETE"})
     */

    public function delete($id): Response
    {
        $story = $this->storyRepository->find($id);

        $story->setIsActive(false);

        $this->em->flush();
        return new JsonResponse(['code' => 200]);
    }
}
