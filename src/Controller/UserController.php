<?php
 
namespace App\Controller;
 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Psr\Log\LoggerInterface;


/**
 * @Route("/api", name="api")
 */
 
class UserController extends AbstractFOSRestController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
    * @Rest\View(serializerGroups={"list"})
    * @Rest\Get("/users", name="api-users-list")
    */
    public function getUsers(UserRepository $userRepository)
    {
        $this->logger->debug('Users index');

        return $userRepository->findAll();
    }
 
    /**
    * @Rest\View(serializerGroups={"get"})
    * @Rest\Get("/users/{id}", name="api-users-show")
    */
    public function findUser(User $user)
    {
        $this->logger->debug('User show: ' . $user->getId());

        return $user;
    }

    /**
    * @Rest\View(serializerGroups={"list"})
    * @Rest\Post("/users", name="api-users-new")
    */
    public function createUser(EntityManagerInterface $em, Request $request)
    { 
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        
        $form->submit($request->request->all());
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new BadRequestException('Form is not valid');
        }
  
        try {
            $em->persist($user);
            $em->flush();
            $this->logger->debug('New user: ' . $user->getId());
 
            return $user;
        } catch (UniqueConstraintViolationException $th) {
            throw new BadRequestException('User is not unique');
        }
    }
  
    /**
     * @Rest\View(serializerGroups={"list"})
     * @Rest\Patch("/users/{id}", name="api-users-edit")
     */
    public function updateUser(User $user, EntityManagerInterface $em, Request $request)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->submit($request->request->all(), false);
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new BadRequestException('Form is not valid');
        }
  
        try {
            $em->flush();
            $this->logger->debug('Edit user: ' . $user->getId());
 
            return $user;
        } catch (UniqueConstraintViolationException $th) {
            throw new BadRequestException('User is not unique');
        }
    }
  
    /**
     * @Rest\View(serializerGroups={"list"})
     * @Rest\Delete("/users/{id}", name="api-user-delete")
     */
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $userId = $user->getId();

        $em->remove($user);
        $em->flush();
  
        $this->logger->debug('Deleted user: ' . $userId);

        return $this->json('Deleted a user successfully with id ' . $userId);
    }
}