<?php

namespace FrontBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FrontController extends Controller
{
    public function indexAction()
    {
        return $this->render(
            'FrontBundle::index.html.twig',
            [
                'github' => [
                    'site' => $this->container->getParameter('api_bundle.github.urls.site'),
                    'api' => $this->container->getParameter('api_bundle.github.urls.api'),
                    'client_id' => $this->container->getParameter('api_bundle.github.client_id')
                ]
            ]
        );
    }
}
