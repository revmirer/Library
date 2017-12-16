<?php
/**
 * Created by PhpStorm.
 * User: Никита
 * Date: 14.12.2017
 * Time: 9:11
 */

namespace App\Controller;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Gender;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = $this->getDoctrine()->getRepository(Author::class)->findAll();

        return $this->render('author/index.html.twig', ['authors' => $authors]);
    }

    public function create(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $genders = $em->getRepository(Gender::class)->findAllGenders();
        $author = new Author();

        $form = $this->createFormBuilder($author)
            ->setAction($this->generateUrl('create_author'))
            ->setMethod('POST')
            ->add('name', TextType::class, ['label' => 'Имя'])
            ->add('birthday', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => 'День рождения'
            ])
            ->add('gender_id', ChoiceType::class, [
                'choices' => $genders,
                'label' => 'Пол'
            ])
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
                $em->persist($author);
                $this->addFlash(
                    'success',
                    'Автор успешно добавлен.'
                );
                $em->flush();
                return $this->redirectToRoute('author_list');
            }
        }

        return $this->render('author/show.html.twig', ['form' => $form->createView()]);

    }

    public function edit($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('No se encuentra la tarea con id = '.$id);
        }
        $author = $this->getDoctrine()->getRepository(Author::class)->find($id);

        if (!$author){
            throw $this->createNotFoundException('No se encuentra la tarea con id = '.$id);
        }

        $genders = $em->getRepository(Gender::class)->findAllGenders();

        $form = $this->createFormBuilder($author)
            ->setAction($this->generateUrl('edit_author', ['id' => $id]))
            ->setMethod('POST')
            ->add('name', TextType::class, ['label' => 'Имя'])
            ->add('birthday', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'label' => 'День рождения'
            ])
            ->add('gender_id', ChoiceType::class, [
                'choices' => $genders,
                'label' => 'Пол'
            ])
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
                    'Информация об авторе успешно обновлена.'
                );
                $em->flush();
            }
        }

        return $this->render('author/show.html.twig', ['form' => $form->createView()]);
    }

    public function remove($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('No se encuentra la tarea con id = '.$id);
        }

        $author = $em->getRepository(Author::class)->find($id);

        if (!$author){
            throw $this->createNotFoundException('No se encuentra la tarea con id = '.$id);
        }

        $linkedBooksCount = count($em->getRepository(Book::class)->findBooksByAuthorFilter($id));
        if ($linkedBooksCount) {
            $this->addFlash(
                'danger',
                'Невозможно удалить автора, пока к нему привязана хотя бы одна книга. '
            );
        } else {
            $this->addFlash(
                'success',
                'Автор успешно удален.'
            );
        }

        return $this->redirectToRoute('author_list');
    }
}