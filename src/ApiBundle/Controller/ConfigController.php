<?php

namespace ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ConfigController extends BaseController
{
    public function configAction(Request $request)
    {
        $container = $this->container;
        return [
            'github' => [
                'client_id' => $container->getParameter('api_bundle.github.client_id'),
                'urls' => [
                    'api' => $container->getParameter('api_bundle.github.urls.api'),
                    'site' => $container->getParameter('api_bundle.github.urls.site'),
                    'auth_proxy' => $this->generateUrl('user_auth'),
                ]
            ]
        ];
    }
}

