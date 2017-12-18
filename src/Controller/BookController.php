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
use App\Entity\Rating;
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
                $em->flush();
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

        if ($this->getUser()) {
            if ($this->getUser()->getId()) {
                $isFavorite = count($em->getRepository(Favorite::class)->findBy(['user_id' => $this->getUser()->getId(), 'book_id' => $id, 'active' => '1']));
            }
        } else {
            $isFavorite = '';
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
        $books = $this->getDoctrine()->getRepository(Book::class)->findMainPageBooks();

        $ratings = [];
        if ($this->getUser())
        {
            $ratings = $this->getDoctrine()->getRepository(Rating::class)->findBooksRatingsOfUser($this->getUser()->getId(), $books);
        }

        return $this->render('list/index.html.twig', ['books' => $books, 'ratings' => $ratings]);
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
        return $this->render('book/edit.html.twig', ['form' => $form->createView(), 'image' => $book->getImage() ]);

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

        $em->remove($book);
        $em->flush();
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

    public function search(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if (strlen($request->request->get('search_query')) > 3) {
            $books = $em->getRepository(Book::class)->findBooksByQuery($request->request->get('search_query'));
            return $this->render('book/search_results.html.twig', ['books' => $books, 'query' => $request->request->get('search_query')]);    
        } else {
            $this->addFlash(
                'danger',
                'Слишком короткий поисковый запрос'
            );

            return $this->redirect(
                $request
                    ->headers
                    ->get('referer')
            );
        }
    }

    public function addPlus($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('');
        }

        $book = $this->getDoctrine()->getRepository(Book::class)->find($id);

        if (!$book){
            throw $this->createNotFoundException('');
        }

        $rating = $em->getRepository(Rating::class)->findBy(['user_id' => $this->getUser()->getId(), 'book_id' => $id]);

        if ($rating) {
            $rating = $rating[0];
            if ($rating->getScore() == 1) {
                $this->addFlash(
                    'success',
                    'Вы уже поставили плюс этой книге'
                );
            } elseif ($rating->getScore() == -1) {
                $rating->setActive('0');
                $rating->setScore('0');
                $this->addFlash(
                    'success',
                    'Предыдущая негативная оценка этой книги отменена'
                );
                $em->flush();
            } elseif ($rating->getScore() == 0) {
                $rating->setActive('1');
                $rating->setScore('1');
                $this->addFlash(
                    'success',
                    'Положительная оценка этой книги сохранена'
                );
                $em->flush();
            }
        } else {
            $rating = new Rating();
            $rating->setUserId($this->getUser()->getId());
            $rating->setBookId($id);
            $rating->setScore(1);
            $rating->setActive(1);
            $em->persist($rating);
            $em->flush();
            $this->addFlash(
                'success',
                'Положительная оценка этой книги сохранена'
            );

        }

        return $this->redirect(
            $request
                ->headers
                ->get('referer')
        );

    }

    public function addMinus($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('');
        }

        $book = $this->getDoctrine()->getRepository(Book::class)->find($id);

        if (!$book){
            throw $this->createNotFoundException('');
        }

        $rating = $em->getRepository(Rating::class)->findBy(['user_id' => $this->getUser()->getId(), 'book_id' => $id]);

        if ($rating) {
            $rating = $rating[0];
            if ($rating->getScore() == -1) {
                $this->addFlash(
                    'success',
                    'Вы уже поставили минус этой книге'
                );
            } elseif ($rating->getScore() == 1) {
                $rating->setActive('0');
                $rating->setScore('0');
                $this->addFlash(
                    'success',
                    'Предыдущая положительная оценка этой книги отменена'
                );
                $em->flush();
            } elseif ($rating->getScore() == 0) {
                $rating->setActive('1');
                $rating->setScore('-1');
                $this->addFlash(
                    'success',
                    'Негативная оценка этой книги сохранена'
                );
                $em->flush();
            }
        } else {
            $rating = new Rating();
            $rating->setUserId($this->getUser()->getId());
            $rating->setBookId($id);
            $rating->setScore(-1);
            $rating->setActive(1);
            $em->persist($rating);
            $em->flush();
            $this->addFlash(
                'success',
                'Отрицательная оценка этой книги сохранена'
            );

        }

        return $this->redirect(
            $request
                ->headers
                ->get('referer')
        );
    }

}