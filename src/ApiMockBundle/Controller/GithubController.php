<?php

namespace ApiMockBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class GithubController extends AbstractController
{
    public function oauthAction()
    {
        return $this->render(
            'ApiMockBundle:github:users.html.twig',
            [
                'users' =>  $this->getData('github', 'user')
            ]
        );

    }

    public function accessTokenAction(Request $request)
    {
        $code = $request->get('code');
        return $this->response('github', 'user', function ($users) use ($code) {
            foreach ($users as $user) {
                if ($user['oauth_code'] == $code) {
                    return $user;
                }
            }
            return [];
        });
    }

    public function getCurrentUserAction(Request $request)
    {
        $token = $request->get('access_token');
        return $this->response('github', 'user', function ($users) use ($token) {
            foreach ($users as $user) {
                if ($user['access_token'] == $token) {
                    return $user;
                }
            }
            return [];
        });
    }

    public function searchIssuesAction(Request $request)
    {
        return $this->response('github', 'issue', function ($issues) {
            return [
                'total_count' => count($issues),
                'items' => $issues
            ];
        });
    }

    public function getIssueAction(string $owner, string $repo, int $number)
    {
        return $this->response('github', 'issue',
            function ($issues) use ($owner, $repo, $number) {
                foreach ($issues as $issue) {
                    if ($issue['number'] == $number) {
                        $parts = explode('/', $issue["repository_url"]);
                        if (array_pop($parts) == $repo && array_pop($parts) == $owner) {
                            return $issue;
                        }
                    }
                }
                return [];
            }
        );
    }

    public function getPullRequestAction(string $owner, string $repo, int $number)
    {
        return $this->response('github', 'pullrequest',
            function ($pullrequests) use ($owner, $repo, $number) {
                foreach ($pullrequests as $pullrequest) {
                    if ($pullrequest['number'] == $number
                        && $pullrequest['head']['repo']['owner']['login'] == $owner
                        && $pullrequest['head']['repo']['name'] == $repo
                    ) {
                        return $pullrequest;
                    }
                }
                return [];
            }
        );
    }
}
