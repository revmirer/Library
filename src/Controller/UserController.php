<?php
/**
 * Created by PhpStorm.
 * User: Никита
 * Date: 16.12.2017
 * Time: 7:15
 */

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Favorite;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends Controller
{

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('index');
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'Пользователь успешно создан. Теперь вы можете войти.'
            );

            return $this->redirectToRoute('login_user');
        }

        return $this->render(
            'user/register.html.twig',
            ['form' => $form->createView()]
        );
    }

    public function login(Request $request, AuthenticationUtils $authUtils)
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('index');
        }
        $error = $authUtils->getLastAuthenticationError();
        
        $lastUsername = $authUtils->getLastUsername();
        

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
    
    public function editLogin(Request $request, UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($user->getId());

        $form = $this->createFormBuilder($user)
            ->setAction($this->generateUrl('edit_user'))
            ->setMethod('POST')
            ->add('username', TextType::class, ['label' => 'Логин'])
            ->add('id', HiddenType::class)
            ->add('Сохранить', SubmitType::class, array('label' => 'Cохранить'))
            ->getForm()
        ;
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $user = $em->getRepository(User::class)->findBy(['username' => $request->request->get('form')['username']]);
            if (count($user) > 0) {
                $this->addFlash(
                    'danger',
                    'Пользователь с таким логином уже существует'
                );
            } elseif (!$form->isValid()){
                $this->addFlash(
                    'danger',
                    'Введенные данные невалидны'
                );
            } else {
                $this->addFlash(
                    'success',
                    'Логин успешно изменен.'
                );
                $em->flush();
            }
        } else {
            $favoriteBooks = $em->getRepository(Book::class)->findFavoriteBooksOfUser($this->getUser()->getId());
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'favoriteBooks' => $favoriteBooks]);
    }

    public function addToFavorites($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('');
        }

        $book = $this->getDoctrine()->getRepository(Book::class)->find($id);

        if (!$book){
            throw $this->createNotFoundException('');
        } else {
            $favorite = $em->getRepository(Favorite::class)->findBy(['user_id' => $this->getUser()->getId(), 'book_id' => $id]);
            if (!$favorite) {
                $favorite = new Favorite();
                $favorite->setBookId($id);
                $favorite->setUserId($this->getUser()->getId());
            } else {
                $favorite = $favorite[0];
            }
            $favorite->setActive(true);
            $this->addFlash(
                'success',
                'Книга успешно добавлена в избранное.'
            );
            $em->persist($favorite);
            $em->flush();

            return $this->redirect(
                $request
                    ->headers
                    ->get('referer')
            );
        }
    }

    public function removeFromFavorites($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$id) {
            throw $this->createNotFoundException('');
        }

        $book = $this->getDoctrine()->getRepository(Book::class)->find($id);

        if (!$book){
            throw $this->createNotFoundException('');
        } else {
            $favorite = $em->getRepository(Favorite::class)->findBy(['user_id' => $this->getUser()->getId(), 'book_id' => $id])[0];
            if (!$favorite) {
                $this->addFlash(
                    'success',
                    'Книга отсутсвует у вас в избранном.'
                );
            } else {
                $this->addFlash(
                    'success',
                    'Книга успешно удалена из избранного'
                );
                $favorite->setActive(false);
                $em->persist($favorite);
                $em->flush();
            }
            return $this->redirect(
                $request
                    ->headers
                    ->get('referer')
            );

        }
    }

}