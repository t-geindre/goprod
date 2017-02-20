<?php

namespace ApiMockBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class GithubController extends AbstractController
{
    public function oauthAction()
    {
        return $this->render(
            'ApiMockBundle:github:users.html.twig',
            ['users' => $this->getData('github', 'user')]
        );

    }

    public function accessTokenAction(Request $request)
    {
        $code = $request->get('code');
        $users = $this->getData('github', 'user');
        foreach ($users as $user) {
            if ($user['oauth_code'] == $code) {
                return $user;
            }
        }
        return [];
    }

    public function getCurrentUserAction(Request $request)
    {
        $code = $request->get('access_token');
        $users = $this->getData('github', 'user');
        foreach ($users as $user) {
            if ($user['access_token'] == $code) {
                return $user;
            }
        }
        return [];
    }

    public function searchIssuesAction(Request $request)
    {
        $issues = $users = $this->getData('github', 'issue');
        $total = count($issues);

        return [
            'total_count' => $total,
            'items' => $issues
        ];
    }

    public function getIssueAction(string $owner, string $repo, int $number)
    {
        $issues = $users = $this->getData('github', 'issue');
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

    public function getPullRequestAction(string $owner, string $repo, int $number)
    {
        $pullrequests = $this->getData('github', 'pullrequest');
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
}
