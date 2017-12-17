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
use App\Entity\Favorite;
use App\Entity\Genre;
use Eventviva\ImageResize;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class BookController extends Controller
{
    public function create(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $genres = $em->getRepository(Genre::class)->findAllGenresForBook();
        $authors = $em->getRepository(Author::class)->findAllAuthorsForBook();
        $book = new Book();

        $form = $this->createFormBuilder($book)
            ->setAction($this->generateUrl('create_book'))
            ->setMethod('POST')
            ->add('title', TextType::class, ['label' => 'Название'])
            ->add('image', FileType::class, array('label' => 'Картинка'))
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
            ->add('Создать', SubmitType::class, array('label' => 'Создать'))
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
                $image = $book->getImage();
                $imageName = md5(uniqid()).'.'.$image->guessExtension();
                $image->move(
                    $this->getParameter('book_image_directory'),
                    $imageName
                );

                $book->setImage($imageName);
                $em->persist($book);
                $this->addFlash(
                    'success',
                    'Книга добавлена.'
                );
//                $em->flush();
                return $this->redirectToRoute('index');
            }
        }

        return $this->render('book/edit.html.twig', ['form' => $form->createView()]);
    }

    public function show($id)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('');
        }
        $book = $em->getRepository(Book::class)->findBooksById($id);
        if (!$book) {
            throw $this->createNotFoundException();
        }
        if ($this->getUser()->getId()) {
            $isFavorite = count($em->getRepository(Favorite::class)->findBy(['user_id' => $this->getUser()->getId(), 'book_id' => $id, 'active' => '1']));
        }

        $similarBooks = $em->getRepository(Book::class)->findSimilarBooks($book);


        return $this->render('book/show.html.twig', ['book' => $book[0], 'isFavorite' => $isFavorite, 'similarBooks' => $similarBooks]);
    }

    public function showFilteredByAuthorBooks($filterId)
    {
        $em = $this->getDoctrine()->getManager();
        $books = $em->getRepository(Book::class)->findBooksByAuthorFilter($filterId);

        return $this->render('author/booklist.html.twig', ['books' => $books]);
    }

    public function showFilteredByGenreBooks($filterId)
    {
        $em = $this->getDoctrine()->getManager();
        $books = $em->getRepository(Book::class)->findBooksByGenreFilter($filterId);

        return $this->render('genre/booklist.html.twig', ['books' => $books]);
    }

    public function showAll()
    {
        $books = $this->getDoctrine()->getRepository(Book::class)->findAllBooks();

        return $this->render('list/index.html.twig', ['books' => $books]);
    }
    
    public function edit($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('');
        }
        $book = $this->getDoctrine()->getRepository(Book::class)->find($id);
        $previeousImage = $book->getImage();

        if (!$book){
            throw $this->createNotFoundException('');
        }

        $genres = $em->getRepository(Genre::class)->findAllGenresForBook();
        $authors = $em->getRepository(Author::class)->findAllAuthorsForBook();
        
        $form = $this->createFormBuilder($book)
            ->setAction($this->generateUrl('edit_book', ['id' => $id]))
            ->setMethod('POST')
            ->add('image', FileType::class, ['label' => 'Картинка', 'required' => false, 'data_class' => null])
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
            ->add('Сохранить', SubmitType::class, ['label' => 'Cохранить'])
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if (!$form->isValid()) {
                $this->addFlash(
                    'danger',
                    'Введенные данные невалидны'
                );
            } else {
                $image = $book->getImage();
                if ($image) {
                    if (getimagesize($image)) {
                        $imageName = md5(uniqid()).'.'.$image->guessExtension();
                        $image->move(
                            $this->getParameter('book_image_directory'),
                            $imageName
                        );
                        $image = new ImageResize($this->getParameter('book_image_directory').$imageName);
                        $image->resizeToBestFit($this->getParameter('book_image_x'), $this->getParameter('book_image_y'));
                        $image->save($this->getParameter('book_image_directory').$imageName);
                        $book->setImage($imageName);
                    } else {
                        $this->addFlash(
                            'danger',
                            'Добавленный вами файл не является картинкой.'
                        );
                        return $this->render('book/edit.html.twig', ['form' => $form->createView(), 'image' => $previeousImage]);
                    }
                }

                $this->addFlash(
                    'success',
                    'Информация о книге успешно обновлена.'
                );
                $em->flush();
            }
        }
        return $this->render('book/edit.html.twig', ['form' => $form->createView(), 'image' => $book->getImage()]);

    }

    public function remove($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('');
        }
        $book = $this->getDoctrine()->getRepository(Book::class)->find($id);

        if (!$book){
            throw $this->createNotFoundException('');
        }

        $this->addFlash(
            'success',
            'Книга была успешно удалена'
        );

        return $this->redirectToRoute('index');
        
    }
    
    public function removePicture($id)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('');
        }
        $book = $this->getDoctrine()->getRepository(Book::class)->find($id);

        if (!$book){
            throw $this->createNotFoundException('');
        }
        $book->setImage('');

        $em->flush();

        $this->addFlash(
            'success',
            'Картинка была успешно удалена'
        );

        return $this->redirectToRoute('edit_book', ['id' => $id]);
    }

}