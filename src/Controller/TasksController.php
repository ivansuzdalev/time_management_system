<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


class TasksController extends AbstractController
{
    private $ob_manager;

    /**
     * @Route("/user-tasks", name="user_tasks")
     */
    public function userTasks(Request $request, Security $security)
    {
            $error = '';
            $userOb = $security->getUser();
            if(!$userOb) {
                return new RedirectResponse('/');
            } else {
                return $this->render('tasks/user-tasks.html.twig', [ 'error' => $error, 'username' => $userOb->getUserName()]); 
            }
            
    }

    

}
