<?php

namespace ApiMockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ApiMockBundle\Entity\AbstractEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Base mock controller
 */
abstract class AbstractController extends Controller
{
    /**
     * @param array $entities
     *
     * @return array
     */
    protected function getPayloads(array $entities)
    {
        return array_map(
            function (AbstractEntity $entity) {
                return $entity->getPayload();
            },
            $entities
        );
    }

    /**
     * @param array $data
     * @param int   $status
     *
     * @return Response
     */
    protected function response(array $data, int $status = 200)
    {
        $response = new Response(json_encode($data), $status);
        $response->headers->add(['Content-type' => 'application/json']);

        return $response;
    }

    /**
     * @param string          $message
     * @param \Exception|null $previous
     */
    protected function createBadRequestException($message = 'Bad request', \Exception $previous = null)
    {
        return new BadRequestHttpException($message, $previous);
    }
}
