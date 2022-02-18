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
     * @Route("/comment", name="comment")
     */
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }
}
