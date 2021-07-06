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
    private $ob_manager;

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

                $records_count = count($entityManager->getRepository(Tasks::class)->findByUser(['user_id' => $userOb->getUserId()], $orderBy = null));

                $rows_count = $request->get('rows_count');
                $page = $request->get('page');

                if(!$rows_count && !$session->get('rows_count')) $rows_count = 5;
                if(!$page && !$session->get('page')) $page = 1;

                if($page) {
                    $session->set('page', $page);
                } else {
                    $page = $session->get('page');
                }

                if($rows_count) {
                    $session->set('rows_count', $rows_count);
                } else {
                    $rows_count = $session->get('rows_count');
                }
                
                $pagination_arr = array();
                $from = 1;
                $to = 1;
                
                $pages_count = (int)($records_count/$rows_count)+1;
                
                if($records_count > 0 && $records_count % $rows_count == 0) $pages_count = $pages_count - 1;

                if ($page < 10) $from = 1;
                else $from = $page - 10;
        
                if ($page > $pages_count - 10) $to = $pages_count;
                else $to = $page + 10;
                
                for ($i=$from;$i<=$to;$i++){
                    $pagination_arr[$i]=$i;
                }
                

                $tasks_arr_ob = $entityManager->getRepository(Tasks::class)->findByUser(['user_id' => $userOb->getUserId()], $orderBy = null, $limit = $rows_count, $offset = $rows_count*($page-1));

                return $this->render('tasks/user-tasks.html.twig', ['pages_count'=>$pages_count, 'rows_count'=>$rows_count, 'page'=>$page, 'pagination_arr'=>$pagination_arr, 'tasks_arr_ob' => $tasks_arr_ob, 'error' => $error, 'username' => $userOb->getUserName()]); 
        
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
                $rows_count = $request->get('rows_count');
                
                if($page) {
                    $session->set('page', $page);
                } else {
                    $page = $session->get('page');
                }

                if($rows_count) {
                    $session->set('rows_count', $rows_count);
                } else {
                    $rows_count = $session->get('rows_count');
                }
                

                
                if($id){
                    
                    $task = $entityManager->getRepository(Tasks::class)->findOneBy(['id' => $id]);
         
                    if($task){
                        $task_end_date_time = new \DateTime();
                        $task->setEndDateTime($task_end_date_time);
                        $entityManager->persist($task);
                        $entityManager->flush();
                    }
                }
                return new RedirectResponse('/user-tasks?page='.$page.'&rows_count='.$rows_count);
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
                $start_from = $request->get('start_from');
                $date_time_spent = $request->get('date_time_spent');

                if($title && $comment && $start_from && $date_time_spent){
                    $task = new Tasks();
                    $task->setTitle($title);
                    $task->setComment($comment);
                    $task->setDateTimeSpent($date_time_spent);
                    $task->setUser($userOb);
                    $date_from_ob = new \DateTime($start_from);
                    $task->setStartFrom($date_from_ob);
                    $task->setDateTimeSpent($date_time_spent);
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
      
            $date_from = $request->get('date_from');
            $date_to = $request->get('date_to');

            $tasks_service_ob = (new TasksService($entityManager));

            $tasks_arr_ob = $tasks_service_ob->getTasksByUserPeriod($userOb->getId(), $date_from, $date_to);

            $tasks_arr = $tasks_service_ob->convertTasksObToArray($tasks_arr_ob);
            
            $total_time = $tasks_service_ob->calculateTasksTotalTime($tasks_arr_ob);

            Csv::outputCSV($tasks_arr);
            
            $total_time_arr = array(
                ['Name'=>'Total Time', 'total time'=>$total_time]
            );
            Csv::outputCSV($total_time_arr, false);

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
                $start_from = $request->get('start_from');
                $date_time_spent = $request->get('date_time_spent');

                if($title && $comment && $start_from && $date_time_spent){
                    $task = new Tasks();
                    $task->setTitle($title);
                    $task->setComment($comment);
                    $task->setDateTimeSpent($date_time_spent);
                    $task->setUser($userOb);
                    $date_from_ob = new \DateTime($start_from);
                    $task->setStartFrom($date_from_ob);
                    $task->setDateTimeSpent($date_time_spent);
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
