<?php
/**
 * Created by PhpStorm.
 * User: Никита
 * Date: 03.12.2017
 * Time: 22:56
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{

    public function index()
    {
        return $this->render('list/index.html.twig');
    }
}