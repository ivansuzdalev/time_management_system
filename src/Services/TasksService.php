<?php
namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

class TasksService
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }

    public function getTasksByUserPeriod(string $date_from, string $date_to, string $user_id): ?array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $from = (new \DateTime($date_from))->format("Y-m-d")." 00:00:00";
        $to   = (new \DateTime($date_to))->format("Y-m-d")." 23:59:59";

        $queryBuilder
            ->select('e')
            ->from('App\Entity\Tasks', 'e')
            ->andWhere('e.startFrom BETWEEN :from AND :to')
            ->andWhere('user.id = :user_id')
            ->leftJoin('App\Entity\User', 'user', \Doctrine\ORM\Query\Expr\Join::WITH,'user = user.id')
            ->setParameter('from', $from )
            ->setParameter('to', $to)
            ->setParameter('user_id', $user_id)
        ;

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