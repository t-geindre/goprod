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
     * @param int     $id
     * @param Request $request
     *
     * @return Response|StreamedResponse
     */
    public function getLiveDeploymentAction(int $id, Request $request) : Response
    {
        if (!$deployment = $this->get('api_mock.repository.deployment')->find($id)) {
            return $this->response(['message' => 'Not Found'], 404);
        }

        $finalStatus = 'success';
        if (!is_null(
            $pullrequestNumber = $this
                ->get('api_bundle.repository.deploy')
                ->findOneBy(['goliveId' => $id])
                ->getPullRequestId()
        )) {
            array_map(
                function ($label) use (&$finalStatus) {
                    if ($label['name'] == 'deploy will fail') {
                        $finalStatus = 'failure';
                    }
                },
                $this
                    ->get('api_mock.repository.issue')
                    ->findOneBy([
                        'number' => $pullrequestNumber,
                    ])
                    ->getPayload()['labels']
            );
        }

        $wait = $this->container->getParameter('kernel.environment') != 'test';
        if (in_array($deployment->getStatus(), ['success', 'failure'])) {
            $wait = false;
        } else {
            $deployment->setStatus($finalStatus);
            $em = $this->get('doctrine')->getManager();
            $em->persist($deployment);
            $em->flush();
        }

        $events = [
            [
                'message' => null,
                'status' => 'pending',
            ],
            [
                'message' => 'Doing some admin stuff',
                'status' => 'running',
            ],
            [
                'message' => 'Cleaning some strange directories',
                'status' => 'running',
            ],
            [
                'message' => 'Moving something',
                'status' => 'running',
            ],
            [
                'message' => 'Forgot to clean smothing, admin stuff, you know...',
                'status' => 'running',
            ],
            [
                'message' => 'Calling an API, juste for fun',
                'status' => 'running',
            ],
            [
                'message' => 'Installing some dependencies',
                'status' => 'running',
            ],
            [
                'message' => 'Running smoke checks',
                'status' => 'running',
            ],
            [
                'message' => 'Doing a bit more strange admin stuff',
                'status' => 'running',
            ],
            [
                'message' => 'Wait a few more seconds',
                'status' => 'running',
            ],
            [
                'message' => '...a bit more',
                'status' => 'running',
            ],
            [
                'message' => $finalStatus.'!',
                'status' => $finalStatus,
            ],
        ];

        if ($request->headers->get('Accept') == 'text/event-stream') {
            return StreamedResponse::create(
                function () use ($events, $wait) {
                    echo "\n\n";
                    foreach ($events as $id => $event) {
                        echo sprintf("id:%d\ndata: %s\n\n", $id, json_encode($event));
                        flush();
                        ob_flush();
                        usleep($wait ? mt_rand(100000, 1000000) : 0);
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
