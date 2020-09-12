<?php


namespace App;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class Controller extends AbstractController
{

    /**
     * Matches /blog exactly
     *
     * @Route("/", name="main")
     */
    public function main()
    {
        // ...
    }

}
