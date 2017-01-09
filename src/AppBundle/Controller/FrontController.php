<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FrontController extends Controller
{
    public function indexAction()
    {
        return $this->render(
            'AppBundle::index.html.twig',
            [
                'github' => [
                    'url' => $this->container->getParameter('app_bundle.github.url'),
                    'client_id' => $this->container->getParameter('app_bundle.github.client_id')
                ]
            ]
        );
    }
}
