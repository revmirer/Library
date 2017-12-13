<?php
/**
 * Created by PhpStorm.
 * User: Никита
 * Date: 10.12.2017
 * Time: 21:25
 */

namespace App\Controller;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class BookController extends Controller
{
    public function show($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('No se encuentra la tarea con id = '.$id);
        }
        $book = $this->getDoctrine()->getRepository(Book::class)->find($id);

        if (!$book){
            throw $this->createNotFoundException('No se encuentra la tarea con id = '.$id);
        }

        $genres = $em->getRepository(Genre::class)->findAllGenresForBook();
        $authors = $em->getRepository(Author::class)->findAllAuthorsForBook();
        
        $form = $this->createFormBuilder($book)
            ->setAction($this->generateUrl('edit_book', ['id' => $id]))
            ->setMethod('POST')
            ->add('title', TextType::class, ['label' => 'Название'])
            ->add('added_on', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => 'Дата занесения в каталог'
            ])
            ->add('published_on', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => 'Дата выпуска'
            ])
            ->add('author_id', ChoiceType::class, [
                'choices' => $authors,
                'label' => 'Автор'
            ])
            ->add('genre_id', ChoiceType::class, [
                'choices' => $genres,
                'label' => 'Жанр'
            ])
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
                    'Информация о книге успешно обновлена.'
                );
                $em->flush();
            }
        }

        return $this->render('book/show.html.twig', ['form' => $form->createView()]);

    }

    public function remove($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('No se encuentra la tarea con id = '.$id);
        }
        $book = $this->getDoctrine()->getRepository(Book::class)->find($id);

        if (!$book){
            throw $this->createNotFoundException('No se encuentra la tarea con id = '.$id);
        }

        $this->addFlash(
            'success',
            'Книга была успешно удалена'
        );

        return $this->redirectToRoute('index');


    }

}