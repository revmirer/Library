<?php
/**
 * Created by PhpStorm.
 * User: Никита
 * Date: 14.12.2017
 * Time: 4:12
 */

namespace App\Controller;


use App\Entity\Genre;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genre = $this->getDoctrine()->getRepository(Genre::class)->findAll();

        return $this->render('genre/index.html.twig', ['genres' => $genre]);
    }

    public function edit($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('No se encuentra la tarea con id = '.$id);
        }
        $genre = $this->getDoctrine()->getRepository(Genre::class)->find($id);

        if (!$genre){
            throw $this->createNotFoundException('No se encuentra la tarea con id = '.$id);
        }

        $form = $this->createFormBuilder($genre)
            ->setAction($this->generateUrl('edit_genre', ['id' => $id]))
            ->setMethod('POST')
            ->add('genre', TextType::class, ['label' => 'Название'])
            ->add('id', HiddenType::class)
            ->add('Сохранить', SubmitType::class, array('label' => 'Cохранить'))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if (!$form->isValid()){
                $this->addFlash(
                    'alert',
                    'Введенные данные невалидны'
                );
            } else {
                $this->addFlash(
                    'success',
                    'Информация о жанре успешно обновлена.'
                );
                $em->flush();
            }
        }

        return $this->render('genre/edit.html.twig', ['form' => $form->createView()]);

    }
}