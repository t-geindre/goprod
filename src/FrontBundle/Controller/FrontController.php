<?php

namespace FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Front controller
 */
class FrontController extends Controller
{
    /**
     * index
     *
     * @return Response
     */
    public function indexAction() : Response
    {
        return $this->render('FrontBundle::index.html.twig');
    }
}
