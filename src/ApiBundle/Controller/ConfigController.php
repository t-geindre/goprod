<?php

namespace ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Config controller
 */
class ConfigController extends BaseController
{
    /**
     * @param Request $request [description]
     *
     * @return array
     */
    public function configAction(Request $request) : array
    {
        $container = $this->container;

        return [
            'github' => [
                'client_id' => $container->getParameter('api_bundle.github.client_id'),
                'urls' => [
                    'api' => $container->getParameter('api_bundle.github.urls.api'),
                    'site' => $container->getParameter('api_bundle.github.urls.site'),
                    'auth_proxy' => $this->generateUrl('user_auth'),
                ],
            ],
            'golive' => [
                'stage' => $container->getParameter('api_bundle.golive.stage'),
                'urls' => [
                    'api' => $container->getParameter('api_bundle.golive.urls.api'),
                    'site' => $container->getParameter('api_bundle.golive.urls.site'),
                ],
            ],
        ];
    }
}
