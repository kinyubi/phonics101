<?php


namespace App;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function list()
    {
        $args = [];
        return $this->render('base.html.twig', $args);
    }

}
