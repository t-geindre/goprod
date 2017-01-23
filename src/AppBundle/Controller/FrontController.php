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
                    'site' => $this->container->getParameter('app_bundle.github.urls.site'),
                    'api' => $this->container->getParameter('app_bundle.github.urls.api'),
                    'client_id' => $this->container->getParameter('app_bundle.github.client_id')
                ]
            ]
        );
    }
}
