<?php

namespace ApiMockBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Response;
use ApiBundle\Entity\User;
use ApiMockBundle\Entity\Deployment;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Github controller
 */
class GoliveController extends AbstractController
{
    /**
     * @return Response
     */
    public function siteAction() : Response
    {
        return $this->render(
            'ApiMockBundle:golive:index.html.twig',
            [
                'users' =>  $this->getPayloads(
                    $this->get('api_mock.repository.user')->findAll()
                ),
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getWhoAmIAction(Request $request) : Response
    {
        if (!$user = $this->getUser()) {
            return $this->response(['message' => 'Unauthorized'], 401);
        }

        return $this->response([
            'id' => $user->getId(),
            'name' => $user->getLogin(),
            'admin' => false,
            'metadata' => null,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getProjectsAction(Request $request) : Response
    {
        $criteria = Criteria::create();
        if ($request->query->has('name')) {
            $criteria
                ->andWhere(
                    $criteria->expr()->eq(
                        'name',
                        $request->query->get('name')
                    )
                );
        }

        return $this->response(
            array_map(
                function ($item) {
                    return [
                        'id' => $item['id'],
                        'metadata' => null,
                        'name' => $item['name'],
                        'queue' => null,
                    ];
                },
                $this->getPayloads(
                    $this
                        ->get('api_mock.repository.repository')
                        ->matching($criteria)
                        ->toArray()
                )
            )
        );
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function getDeploymentAction(int $id) : Response
    {
        if (!$deployment = $this->get('api_mock.repository.deployment')->find($id)) {
            return $this->response(['message' => 'Not Found'], 404);
        }

        return $this->response($this->formatDeployment($deployment));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function postDeploymentAction(Request $request) : Response
    {
        if (!$user = $this->getUser()) {
            return $this->response(['message' => 'Unauthorized'], 401);
        }

        $deployment = (new Deployment())
            ->setProject($request->get('project'))
            ->setStage($request->get('stage'))
            ->setInstance('default')
            ->setUser($user->getLogin())
            ->setAction($request->get('action'))
            ->setRef('HEAD')
            ->setStatus('pending')
            ->setRevision(uniqid())
            ->setDescription('Mock deployment')
            ->setClientIp('127.0.0.1');

        $em = $this->get('doctrine')->getManager();
        $em->persist($deployment);
        $em->flush();

        return $this->response($this->formatDeployment($deployment));
    }

    /**
     * @param Request $request
     *
     * @return Response|StreamedResponse
     */
    public function getLiveDeploymentAction(int $id, Request $request) : Response
    {
        if (!$deployment = $this->get('api_mock.repository.deployment')->find($id)) {
            return $this->response(['message' => 'Not Found'], 404);
        }

        $wait = true;
        if ($deployment->getStatus() != 'pending') {
            $wait = false;
        } else {
            $deployment->setStatus('success');
            $em = $this->get('doctrine')->getManager();
            $em->persist($deployment);
            $em->flush();
        }

        $events = [
            ['message' => null,                                            'status' => 'pending'],
            ['message' => null,                                            'status' => 'pending'],
            ['message' => 'Checking disk space usage',                     'status' => 'running'],
            ['message' => 'Configuring Git credentials',                   'status' => 'running'],
            ['message' => 'Pruning Git remote cached copy',                'status' => 'running'],
            ['message' => 'Updating code base with remote_cache strategy', 'status' => 'running'],
            ['message' => 'Configuring Composer',                          'status' => 'running'],
            ['message' => 'Installing configuration files',                'status' => 'running'],
            ['message' => 'Installing Composer dependencies',              'status' => 'running'],
            ['message' => 'Cleaning .git directories in vendors',          'status' => 'running'],
            ['message' => 'Setting permissions',                           'status' => 'running'],
            ['message' => 'Creating www symlink',                          'status' => 'running'],
            ['message' => 'Success!',                                      'status' => 'success'],
        ];

        if ($request->headers->get('Accept') == 'text/event-stream') {
            return StreamedResponse::create(
                function () use ($events, $wait) {
                    echo "\n\n";
                    foreach ($events as $id => $event) {
                        echo sprintf("id:%d\ndata: %s\n\n", $id, json_encode($event));
                        flush(); ob_flush();
                        usleep($wait ? mt_rand(50, 1000)*1000 : 0);
                    }
                },
                200,
                ['Content-type' => 'text/event-stream']
            );
        }

        return $this->response($events);
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        $header = $this->get('request_stack')->getCurrentRequest()->headers->get('Authorization');
        if ($header) {
            $authorization = explode(' ', $header);
            if (count($authorization) == 2
                && $authorization[0] == 'token'
                && $user = $this
                    ->get('doctrine')
                    ->getRepository('ApiBundle\\Entity\\User')
                    ->findOneBy(['accessToken' => $authorization[1]])
            ) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @param Deployment $deployment
     *
     * @return array
     */
    protected function formatDeployment(Deployment $deployment) : array
    {
        return [
            'id' =>  $deployment->getId(),
            'project' => ['id'=> 1, 'name'=> $deployment->getProject(), 'queue' => null],
            'stage' =>  ['id' => 1, 'name' => $deployment->getStage()],
            'instance' => ['id' => 1, $deployment->getInstance()],
            'user' =>  ['id' => 1, 'name' => $deployment->getUser(), 'admin' => false],
            'action' =>  $deployment->getAction(),
            'ref' =>  $deployment->getRef(),
            'status' =>  $deployment->getStatus(),
            'revision' =>  $deployment->getRevision(),
            'description' => $deployment->getDescription(),
            'client_ip' =>  $deployment->getClientIp(),
        ];
    }
}
