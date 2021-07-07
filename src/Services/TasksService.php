<?php
namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tasks;

class TasksService
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }

    public function writeTask(string $title, string $comment, string $startFrom, string $dateTimeSpent, object $userOb): ? bool
    {

        if($title && $comment && $startFrom && $dateTimeSpent){
        
            $task = new Tasks();
            $task->setTitle($title);
            $task->setComment($comment);
            $task->setDateTimeSpent($dateTimeSpent);
            $task->setUser($userOb);
            $dateFromOb = new \DateTime($startFrom);
            $task->setStartFrom($dateFromOb);
            $task->setDateTimeSpent($dateTimeSpent);
            $this->entityManager->persist($task);
            return $this->entityManager->flush();
        }

        return false;
    
    }
    
    public function getTasksByUserPeriod(string $user_id, string $date_from, string $date_to): ?array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $from = (new \DateTime($date_from))->format("Y-m-d")." 00:00:00";
        $to   = (new \DateTime($date_to))->format("Y-m-d")." 23:59:59";

        $queryBuilder
            ->select('e')
            ->from('App\Entity\Tasks', 'e')
            ->andWhere('e.user = :user_id')
            ->leftJoin('App\Entity\User', 'user', \Doctrine\ORM\Query\Expr\Join::WITH,'user = user.id')
            ->setParameter('user_id', $user_id)
        ;
        if($date_from && $date_to) {
            $queryBuilder
            ->andWhere('e.startFrom BETWEEN :from AND :to')
            ->setParameter('from', $from )
            ->setParameter('to', $to);
        }
        $tasks_arr_ob = $queryBuilder->getQuery()->getResult();

        if($tasks_arr_ob){
            return $tasks_arr_ob;
        } else {
            return array();
        }

    }

    public function convertTasksObToArray(array $tasks_arr_ob): ?array
    {

        $tasks_arr = array();

        foreach($tasks_arr_ob as $record){
            $tasks_arr[] = array(
                'ID' =>$record->getId(),
                'StartFrom' => $record->getStartFrom()->format('Y-m-d H:i:s'),
                'EndDateTime' => $record->getEndDateTime() ? $record->getEndDateTime()->format('Y-m-d H:i:s'): '',
                'Title' => $record->getTitle(),
                'Comment' => $record->getComment(),
                'DateTimeSpent' => $record->getDateTimeSpent(),
            );
        }

        return $tasks_arr;
    }

    public function calculateTasksTotalTime(array $tasks_arr_ob): ?int
    {
        $total_time = 0;
            
        foreach($tasks_arr_ob as $record){
            $date_time_from = $record->getStartFrom()->format('U');
            if($record->getEndDateTime()){
                $date_time_to = $record->getEndDateTime()->format('U');
            } else {
                $date_time_to =(new  \DateTime())->format('U');
            }
            $interval = $date_time_to - $date_time_from;

            $total_time = $total_time + $interval;
        }
        //Return value in minutes from seconds
        return $total_time/60;
    
    }

}