<?php

namespace App\Controller;

use App\Entity\Contact;
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
use App\Repository\ContactRepository;
use App\Repository\EntradaRepository;
use App\Repository\StoryRepository;
use DateTimeInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @Route("contact")
 */

class ContactController extends AbstractController
{
    private $em;
    private $contactRepository;

    public function __construct(EntityManagerInterface $em, ContactRepository $contactRepository)
    {
        $this->em = $em;
        $this->contactRepository = $contactRepository;
    }

    /**
     * @Route("/add", name="add_contact", methods={"POST"})
     */

    public function addStory(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);

        $contact = new Contact();

        $contact->setName($content['name']);
        $contact->setEmail($content['email']);
        $contact->setMessage($content['message']);


        $this->em->persist($contact);

        $this->em->flush();

        return new JsonResponse(
            [
                'result' => 'ok',
                'code' => 200,
                'content' => $content

            ]
        );
    }
}
