<?php
namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TasksService
{
    private $entityManager;
    private $tasks;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        $containerBuilder = new ContainerBuilder();
        $containerBuilder
            ->register('tasks_repository', 'App\Entity\Tasks');
        $this->tasks = $containerBuilder->get('tasks_repository');
        
    }

    public function writeTask(string $title, string $comment, string $startFrom, string $dateTimeSpent, object $userOb): ? bool
    {

        if($title && $comment && $startFrom && $dateTimeSpent){
        
            $this->tasks->setTitle($title);
            $this->tasks->setComment($comment);
            $this->tasks->setDateTimeSpent($dateTimeSpent);
            $this->tasks->setUser($userOb);
            $dateFromOb = new \DateTime($startFrom);
            $this->tasks->setStartFrom($dateFromOb);
            $this->tasks->setDateTimeSpent($dateTimeSpent);
            $this->entityManager->persist($this->tasks);
            return $this->entityManager->flush();
        }

        return false;
    
    }
    
    public function getTasksByUserPeriod(string $userId, string $dateFrom, string $dateTo): ?array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $from = (new \DateTime($dateFrom))->format("Y-m-d")." 00:00:00";
        $to   = (new \DateTime($dateTo))->format("Y-m-d")." 23:59:59";

        $queryBuilder
            ->select('e')
            ->from('App\Entity\Tasks', 'e')
            ->andWhere('e.user = :userId')
            ->leftJoin('App\Entity\User', 'user', \Doctrine\ORM\Query\Expr\Join::WITH,'user = user.id')
            ->setParameter('userId', $userId)
        ;
        if($dateFrom && $dateTo) {
            $queryBuilder
            ->andWhere('e.startFrom BETWEEN :from AND :to')
            ->setParameter('from', $from )
            ->setParameter('to', $to);
        }
        $tasksArrOb = $queryBuilder->getQuery()->getResult();

        if($tasksArrOb){
            return $tasksArrOb;
        } else {
            return array();
        }

    }

    public function convertTasksObToArray(array $tasksArrOb): ?array
    {

        $tasks_arr = array();

        foreach($tasksArrOb as $record){
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

    public function calculateTasksTotalTime(array $tasksArrOb): ?int
    {
        $totalTime = 0;
            
        foreach($tasksArrOb as $record){
            $dateTimeFrom = $record->getStartFrom()->format('U');
            if($record->getEndDateTime()){
                $dateTimeTo = $record->getEndDateTime()->format('U');
            } else {
                $dateTimeTo =(new  \DateTime())->format('U');
            }
            $interval = $dateTimeTo - $dateTimeFrom;

            $totalTime = $totalTime + $interval;
        }
        //Return value in minutes from seconds
        return $totalTime/60;
    
    }

}