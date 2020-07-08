<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("home", name="main")
     */
    public function index()
    {
        return $this->render('main/index.html.twig');
    }
    /**
     * @Route('')
     */
    public function admin()
    {
        return $this->render('main/admin/');
    }
}
