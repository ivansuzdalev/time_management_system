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

 /**
     * @Route("/complete-task", name="complete_task")
     */
    public function completeTask(Request $request, Security $security, EntityManagerInterface $entityManager)
    {
            $error = '';
            $userOb = $security->getUser();
            if(!$userOb) {
                return new RedirectResponse('/');
            } else {
                $id = $request->get('id');
                $page = $request->get('page');
                if($id){
                    
                    $task = $entityManager->getRepository(Tasks::class)->findOneBy(['id' => $id]);
         
                    if($task){
                        $task_end_date_time = new \DateTime();
                        $task->setEndDateTime($task_end_date_time);
                        $entityManager->persist($task);
                        $entityManager->flush();
                    }
                }
                return new RedirectResponse('user-tasks?page='.$page);
            }
    }


    /**
     * @Route("/user-tasks", name="user_tasks")
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
                }

                $records_count = count($entityManager->getRepository(Tasks::class)->findByUser(['user_id' => $userOb->getUserId()], $orderBy = null));

                $rows_count = $request->get('rows_count') ? $request->get('rows_count') : 5;
                $page = $request->get('page') ? $request->get('page') : 0;

                $pagination_arr = array();
                $from = 0;
                $to = 0;
                
                $pages_count = (int)($records_count/$rows_count);
                if ($page < 10) $from = 0;
                else $from = $page - 10;
        
                if ($page > $pages_count - 10) $to = $pages_count;
                else $to = $page + 10;
                for ($i=$from;$i<=$to;$i++){
                    $pagination_arr[$i]=$i;
                }
    
                $tasks_arr_ob = $entityManager->getRepository(Tasks::class)->findByUser(['user_id' => $userOb->getUserId()], $orderBy = null, $limit = $rows_count, $offset = $rows_count*$page);

                return $this->render('tasks/user-tasks.html.twig', ['pages_count'=>$pages_count, 'page'=>$page, 'pagination_arr'=>$pagination_arr, 'tasks_arr_ob' => $tasks_arr_ob, 'error' => $error, 'username' => $userOb->getUserName()]); 
            }
            
    }

    /**
     * @Route("/export-user-tasks")
     */
    public function exportUserTasks(Request $request, Security $security, EntityManagerInterface $entityManager)
    {
        $error = '';
        $userOb = $security->getUser();
        if(!$userOb) {
            return new RedirectResponse('/');
        } else {
      
            $date_from = $request->get('date_from');
            $date_to = $request->get('date_to');

            $tasks_service_ob = (new TasksService($entityManager));

            $tasks_arr_ob = $tasks_service_ob->getTasksByUserPeriod($date_from, $date_to, $userOb->getId());

            $tasks_arr = $tasks_service_ob->convertTasksObToArray($tasks_arr_ob);
            
            $total_time = $tasks_service_ob->calculateTasksTotalTime($tasks_arr_ob);

            Csv::outputCSV($tasks_arr);
            
            $total_time_arr = array(
                ['Name'=>'', 'total time'=>''],
                ['Name'=>'Total Time', 'total time'=>$total_time]
            );
            Csv::outputCSV($total_time_arr, false);

            $response = new Response();
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="operations-export.csv"');
            return $response;
        }
    }

    

}
