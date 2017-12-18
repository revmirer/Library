<?php
/**
 * Created by PhpStorm.
 * User: Никита
 * Date: 14.12.2017
 * Time: 4:12
 */

namespace App\Controller;


use App\Entity\Book;
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

    public function create(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $genre = new Genre();
        $form = $this->createFormBuilder($genre)
            ->setAction($this->generateUrl('create_genre'))
            ->setMethod('POST')
            ->add('genre', TextType::class, ['label' => 'Название'])
            ->add('id', HiddenType::class)
            ->add('Создать', SubmitType::class, array('label' => 'Создать'))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if (!$form->isValid()){
                $this->addFlash(
                    'danger',
                    'Введенные данные невалидны'
                );
            } else {
                $em->persist($genre);
                $this->addFlash(
                    'danger',
                    'Жанр успешно создан.'
                );
                $em->flush();
                return $this->redirectToRoute('genres_list');
            }
        }

        return $this->render('genre/edit.html.twig', ['form' => $form->createView()]);
    }
    public function edit($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('');
        }
        $genre = $this->getDoctrine()->getRepository(Genre::class)->find($id);

        if (!$genre){
            throw $this->createNotFoundException('');
        }

        $form = $this->createFormBuilder($genre)
            ->setAction($this->generateUrl('edit_genre', ['id' => $id]))
            ->setMethod('POST')
            ->add('genre', TextType::class, ['label' => 'Название'])
            ->add('id', HiddenType::class)
            ->add('Сохранить', SubmitType::class, array('label' => 'Cохранить'))
            ->getForm()
        ;

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if (!$form->isValid()){
                $this->addFlash(
                    'danger',
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

    public function remove($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('');
        }

        $genre = $em->getRepository(Genre::class)->find($id);

        if (!$genre){
            throw $this->createNotFoundException('');
        }

        $linkedBooksCount = count($em->getRepository(Book::class)->findBooksByGenreFilter($id));
        if ($linkedBooksCount) {
            $this->addFlash(
                'danger',
                'Невозможно удалить жанр, пока к нему привязана хотя бы одна книга. '
            );
        } else {
            $em->remove($genre);
            $em->flush();
            $this->addFlash(
                'success',
                'Жанр успешно удален.'
            );
        }

        return $this->redirectToRoute('genres_list');
    }
}