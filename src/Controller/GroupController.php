<?php
 
namespace App\Controller;
 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Group;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;


 
/**
 * @Route("/api", name="api")
 */
 
class GroupController extends AbstractFOSRestController
{

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
    * @Rest\View(serializerGroups={"list"})
    * @Rest\Get("/groups", name="api-groups-list")
    */
    public function listGroups(GroupRepository $groupRepository)
    {
        $this->logger->debug('Groups index');

        return $groupRepository->findAll();
    }
  
    /**
    * @Rest\View(serializerGroups={"get"})
    * @Rest\Get("/groups/{id}", name="api-groups-show")
    */
    public function getGroup(Group $group)
    {
        $this->logger->debug('Group show: ' . $group->getId());

        return $group;
    }

    /**
    * @Rest\View(serializerGroups={"get"})
    * @Rest\Post("/groups", name="api-groups-new")                      
    */
    public function newGroup(EntityManagerInterface $em, Request $request)
    { 
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        
        $form->submit($request->request->all());
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new BadRequestException('Form is not valid');
        }
  
        try {
            $em->persist($group);
            $em->flush();
            $this->logger->debug('New group: ' . $group->getId()); 

            return $group;      
        } catch (UniqueConstraintViolationException $th) {
            throw new BadRequestException('User is not unique');
        }
    }

    /**
     * @Rest\View(serializerGroups={"get"})
     * @Rest\Patch("/groups/{id}", name="api-groups-edit")
     */
    public function editGroup(Group $group, EntityManagerInterface $em, Request $request)
    {
        $form = $this->createForm(GroupType::class, $group);
        
        $form->submit($request->request->all(), false);
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new BadRequestException('Form is not valid');
        }
  
        try {
            $em->persist($group);
            $em->flush();
            $this->logger->debug('Edit group: ' . $group->getId());

            return $group;      
        } catch (UniqueConstraintViolationException $th) {
            throw new BadRequestException('User is not unique');
        }
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/groups/{id}", name="api-group-delete")
     */
    public function deleteGroup(Group $group, EntityManagerInterface $em): Response
    {
        if ($group->getUsers()->count() !== 0) {
            throw new BadRequestException('Group has users and cannot be deleted');
        }

        $groupId = $group->getId();

        $em->remove($group);
        $em->flush();

        $this->logger->debug('Deleted group: ' . $groupId);
  
        return $this->json('Deleted a group successfully with id ' . $groupId);
    }
}