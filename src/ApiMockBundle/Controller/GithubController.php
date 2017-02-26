<?php

namespace ApiMockBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Response;

/**
 * Github controller
 */
class GithubController extends AbstractController
{
    /**
     * @return Response
     */
    public function oauthAction() : Response
    {
        return $this->render(
            'ApiMockBundle:github:users.html.twig',
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
    public function getUserAction(Request $request) : Response
    {
        if (count($criteria = array_filter([
            'oauthCode' => $code = $request->get('code'),
            'accessToken' => $code = $request->get('access_token'),
        ])) == 0) {
            throw $this->createBadRequestException('You must specified "access_token" or "code" value.');
        };

        if (is_null(
            $user = $this
                ->get('api_mock.repository.user')
                ->findOneBy($criteria)
        )) {
            throw $this->createNotFoundException('User not found.');
        }

        return $this->response($user->getPayload());
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function searchIssuesAction(Request $request) : Response
    {
        $criteria = Criteria::create()
            ->orderBy(['id' => $request->get('order', 'desc')])
            ->setMaxResults($perPage = $request->query->getInt('per_page', 10))
            ->setFirstResult(($request->query->getInt('page', 1) - 1) * $perPage)
        ;

        if (!is_null($q = $request->get('q'))) {
            $parts = explode(' ', $q);
            foreach ($parts as $part) {
                $field = explode(':', $part);
                if (count($field) !== 2) {
                    continue;
                }
                list($key, $value) = $field;
                if ($key == 'is' && in_array($value, ['open', 'closed'])) {
                    $criteria->andWhere($criteria->expr()->eq('open', $value == 'open'));
                }
                if (in_array($key, ['author', 'assignee', 'mentions'])) {
                    $criteria->andWhere($criteria->expr()->eq('author', $value));
                }
            }
        }

        $issues = $this->get('api_mock.repository.issue')->matching($criteria);

        return $this->response([
            'total_count' => $issues->count(),
            'items' => $this->getPayloads($issues->toArray()),
        ]);
    }

    /**
     * @param string $owner
     * @param string $repo
     * @param int    $number
     *
     * @return Response
     */
    public function getIssueAction(string $owner, string $repo, int $number) : Response
    {
        if (is_null($issue = $this->get('api_mock.repository.issue')->findOneBy([
            'owner' => $owner, 'repo' => $repo, 'number' => $number,
        ]))) {
            throw $this->createNotFoundException('Issue not found');
        }

        return $this->response($issue->getPayload());
    }

    /**
     * @param string $owner
     * @param string $repo
     * @param int    $number
     *
     * @return Response
     */
    public function getPullRequestAction(string $owner, string $repo, int $number) : Response
    {
        if (is_null($pullrequest = $this->get('api_mock.repository.pullrequest')->findOneBy([
            'owner' => $owner, 'repo' => $repo, 'number' => $number,
        ]))) {
            throw $this->createNotFoundException('Pullrequest not found');
        }

        return $this->response($pullrequest->getPayload());
    }

    /**
     * @param string $owner
     * @param string $repo
     * @param int    $number
     *
     * @return Response
     */
    public function mergePullRequestAction(string $owner, string $repo, int $number) : Response
    {
        $em = $this->get('doctrine')->getManager();
        foreach (['pullrequest', 'issue'] as $type) {
            if (is_null($entity = $this->get(sprintf('api_mock.repository.%s', $type))->findOneBy([
                'owner' => $owner, 'repo' => $repo, 'number' => $number,
            ]))) {
                throw $this->createNotFoundException(sprintf('%s not found', ucfirst($type)));
            }

            $em->persist(
                $entity
                    ->setPayload(array_merge(
                        $entity->getPayload(),
                        array_filter([
                            'merged' => $type == 'pullrequest' ? true : null,
                            'state' => 'closed',
                        ])
                    ))
                    ->setOpen(false)
            );
        }
        $em->flush();

        return $this->response([]);
    }

    /**
     * @return Response
     */
    public function getOrganizationsAction() : Response
    {
        return $this->response(
            $this->getPayloads(
                $this
                    ->get('api_mock.repository.organization')
                    ->findAll()
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function searchRepositoriesAction(Request $request) : Response
    {
        $criteria = Criteria::create()->orderBy(['name' => 'asc']);

        if (!is_null($q = $request->get('q'))) {
            $parts = explode(' ', $q);
            foreach ($parts as $part) {
                $field = explode(':', $part);
                if (count($field) !== 2) {
                    continue;
                }
                list($key, $value) = $field;
                if ($key == 'user') {
                    $criteria->andWhere($criteria->expr()->eq('owner', $value));
                }
                $q = str_replace($part, '', $q);
            }
        }

        $q = trim($q);
        if (!empty($q)) {
            $criteria->andWhere(
                $criteria->expr()->orX(
                    $criteria->expr()->contains('name', $q),
                    $criteria->expr()->contains('owner', $q)
                )
            );
        }

        $repositories = $this->get('api_mock.repository.repository')->matching($criteria);

        return $this->response([
            'total_count' => $repositories->count(),
            'items' => $this->getPayloads($repositories->toArray()),
        ]);
    }
}
