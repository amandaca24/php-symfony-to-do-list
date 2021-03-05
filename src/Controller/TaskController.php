<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks", name="tasks_index")
     */
    public function index(TaskRepository $taskRespository): Response
    {
        return $this->render('task/index.html.twig', [
            'tasks' => $taskRespository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="task_new")
     */
    public function createTask(ValidatorInterface $validator, Request $request): Response
    {
        if($request->isMethod('post')){
            
            $task = new Task();

            $task->setTitle($request->request->get('title'));
            $task->setDate($request->request->get('date'));
            $task->setDone($request->request->get('done'));

            $errors = $validator->validate($task);

            if (count($errors) > 0) {
                return $this->render('task/form.html.twig', [
                    'errors' => $errors,
                ]);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush(); 
            
            return $this->redirectToRoute('tasks_index');


        }

        return $this->render('task/form.html.twig', [
            'errors' => '',
        ]);
    } 

    /**
     * @Route("/task/edit/{id}", name="task_edit")
     */
    public function updateTask(ValidatorInterface $validator, Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $task = $entityManager->getRepository(Task::class)->find($id);


        if (!$task) {
            throw $this->addFlash('error', 'There is no task with ' .$id);
        }

        if($request->isMethod('post')){

            $errors = $validator->validate($task);

            if (count($errors) > 0) {
                return $this->render('task/form.html.twig', [
                    'errors' => $errors,
                ]);
            }

            $task->setTitle($request->request->get('title'));
            $task->setDate($request->request->get('date'));
            $task->setDone($request->request->get('done'));
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('sucess', 'Task saved!');

            return $this->redirectToRoute('tasks_index');

        }

        return $this->render('task/edit.html.twig', [
            'id' => $id,
            'task' => $task,
            'errors' => ''
        ]);  
    }

    /**
     * @Route("/task/remove/{id}", name="task_delete")
     */
    public function deleteTask(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $task = $entityManager->getRepository(Task::class)->find($id);


        if (!$task) {
            throw $this->addFlash('delete', 'There is no task with ' .$id);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash('delete', 'Task deleted!');

        return $this->redirectToRoute('tasks_index');

    }


}
