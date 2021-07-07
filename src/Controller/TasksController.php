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
use App\Entity\Tasks;
use App\Services\Csv;
use App\Services\TasksService;
use Symfony\Component\HttpFoundation\Session\Session;


class TasksController extends AbstractController
{

    /**
     * @Route("/user-tasks", name="user_tasks")
     */
    public function userTasks(Request $request, Security $security, EntityManagerInterface $entityManager, Session $session)
    {
            $error = '';
            $userOb = $security->getUser();
            if(!$userOb) {
                return new RedirectResponse('/');
            } else {

                $recordsCount = count($entityManager->getRepository(Tasks::class)->findByUser(['user_id' => $userOb->getUserId()], $orderBy = null));

                $rowsCount = $request->get('rowsCount');
                $page = $request->get('page');

                if(!$rowsCount && !$session->get('rowsCount')) $rowsCount = 5;
                if(!$page && !$session->get('page')) $page = 1;

                if($page) {
                    $session->set('page', $page);
                } else {
                    $page = $session->get('page');
                }

                if($rowsCount) {
                    $session->set('rowsCount', $rowsCount);
                } else {
                    $rowsCount = $session->get('rowsCount');
                }
                
                $paginationArr = array();
                $from = 1;
                $to = 1;
                
                $pagesCount = (int)($recordsCount/$rowsCount)+1;
                
                if($recordsCount > 0 && $recordsCount % $rowsCount == 0) $pagesCount = $pagesCount - 1;

                if ($page < 10) $from = 1;
                else $from = $page - 10;
        
                if ($page > $pagesCount - 10) $to = $pagesCount;
                else $to = $page + 10;
                
                for ($i=$from;$i<=$to;$i++){
                    $paginationArr[$i]=$i;
                }
                

                $tasksArrOb = $entityManager->getRepository(Tasks::class)->findByUser(['user_id' => $userOb->getUserId()], $orderBy = null, $limit = $rowsCount, $offset = $rowsCount*($page-1));

                return $this->render('tasks/user-tasks.html.twig', ['pagesCount'=>$pagesCount, 'rowsCount'=>$rowsCount, 'page'=>$page, 'paginationArr'=>$paginationArr, 'tasksArrOb' => $tasksArrOb, 'error' => $error, 'username' => $userOb->getUserName()]); 
        
            }
            
    }

    /**
     * @Route("/complete-task", name="complete_task")
     */
    public function completeTask(Request $request, Security $security, EntityManagerInterface $entityManager, Session $session)
    {
            $error = '';
            $userOb = $security->getUser();
            if(!$userOb) {
                return new RedirectResponse('/');
            } else {

                $id = $request->get('id');
                $page = $request->get('page');
                $rowsCount = $request->get('rowsCount');
                
                if($page) {
                    $session->set('page', $page);
                } else {
                    $page = $session->get('page');
                }

                if($rowsCount) {
                    $session->set('rowsCount', $rowsCount);
                } else {
                    $rowsCount = $session->get('rowsCount');
                }
                

                
                if($id){
                    
                    $task = $entityManager->getRepository(Tasks::class)->findOneBy(['id' => $id]);
         
                    if($task){
                        $taskEndDateTime = new \DateTime();
                        $task->setEndDateTime($taskEndDateTime);
                        $entityManager->persist($task);
                        $entityManager->flush();
                    }
                }
                return new RedirectResponse('/user-tasks?page='.$page.'&rowsCount='.$rowsCount);
            }
    }


    /**
     * @Route("/create-task", name="create_task")
     */
    public function createTasks(Request $request, Security $security, EntityManagerInterface $entityManager)
    {
            $error = '';
            $userOb = $security->getUser();
            if(!$userOb) {
                return new RedirectResponse('/');
            } else {

                $title = $request->get('title');
                $comment = $request->get('comment');
                $startFrom = $request->get('startFrom');
                $dateTimeSpent = $request->get('dateTimeSpent');

                if($title && $comment && $startFrom && $dateTimeSpent){
                    $task = new Tasks();
                    $task->setTitle($title);
                    $task->setComment($comment);
                    $task->setDateTimeSpent($dateTimeSpent);
                    $task->setUser($userOb);
                    $dateFromOb = new \DateTime($startFrom);
                    $task->setStartFrom($dateFromOb);
                    $task->setDateTimeSpent($dateTimeSpent);
                    $entityManager->persist($task);
                    $entityManager->flush();
                    return new RedirectResponse('/user-tasks');
                }

                return new RedirectResponse('/');

            }
            
    }

    
    /**
     * @Route("/export-tasks")
     */
    public function exportTasks(Request $request, Security $security, EntityManagerInterface $entityManager)
    {
        $error = '';
        $userOb = $security->getUser();
        if(!$userOb) {
            return new RedirectResponse('/');
        } else {
      
            $dataFrom = $request->get('dataFrom');
            $dataTo = $request->get('dataTo');

            $tasksServiceOb = (new TasksService($entityManager));

            $tasksArrOb = $tasksServiceOb->getTasksByUserPeriod($userOb->getId(), $dataFrom, $dataTo);

            $tasksArr = $tasksServiceOb->convertTasksObToArray($tasksArrOb);
            
            $totalTime = $tasksServiceOb->calculateTasksTotalTime($tasksArrOb);

            Csv::outputCSV($tasksArr);
            
            $totalTimeArr = array(
                ['Name'=>'Total Time', 'total time'=>$totalTime]
            );
            Csv::outputCSV($totalTimeArr, false);

            $response = new Response();
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="taks-export.csv"');
            return $response;
        }
    }

    /**
     * @Route("/user-task-create", name="user_task_create")
     */
    public function userTaskCreate(Request $request, Security $security, EntityManagerInterface $entityManager)
    {
            $error = '';
            $userOb = $security->getUser();
            if(!$userOb) {
                return new RedirectResponse('/');
            } else {

                $title = $request->get('title');
                $comment = $request->get('comment');
                $startFrom = $request->get('startFrom');
                $dateTimeSpent = $request->get('dateTimeSpent');

                if($title && $comment && $startFrom && $dateTimeSpent){
                    $task = new Tasks();
                    $task->setTitle($title);
                    $task->setComment($comment);
                    $task->setDateTimeSpent($dateTimeSpent);
                    $task->setUser($userOb);
                    $dateFromOb = new \DateTime($startFrom);
                    $task->setStartFrom($dateFromOb);
                    $task->setDateTimeSpent($dateTimeSpent);
                    $entityManager->persist($task);
                    $entityManager->flush();
                }
    
                return $this->render('tasks/user-task-create.html.twig', ['error'=>$error, 'username' => $userOb->getUserName()]); 
            }
            
    }

    /**
     * @Route("/user-tasks-export", name="user_tasks_export")
     */
    public function userTaskExport(Request $request, Security $security, EntityManagerInterface $entityManager)
    {
            $error = '';
            $userOb = $security->getUser();
            if(!$userOb) {
                return new RedirectResponse('/');
            } else {
    
                return $this->render('tasks/user-tasks-export.html.twig', ['error'=>$error, 'username' => $userOb->getUserName()]); 
            }
            
    }
}
