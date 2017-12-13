<?php
/**
 * Created by PhpStorm.
 * User: Никита
 * Date: 03.12.2017
 * Time: 22:56
 */

namespace App\Controller;


use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{

    public function index()
    {
        $books = $this->getDoctrine()->getRepository(Book::class)->findAllBooks();

        return $this->render('list/index.html.twig', ['books' => $books]);
    }
}