<?php

namespace ApiBundle\Tests\Units\Manager;

use ApiBundle\Entity\Deploy;
use atoum;

/**
 * Test deploy manager class
 */
class DeployManager extends atoum
{
    /**
     * Test updateStatus method
     */
    public function testUpdateStatus()
    {
        $this->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(),
                $this->getGoliveClientMock(),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // No pull request, no golive deploy
            ->object($this->testedInstance->updateStatus(
                $deploy = $this->getDeployMock()->setStatus(Deploy::STATUS_NEW)
            ))
                ->isIdenticalTo($this->testedInstance)
            ->string($deploy->getStatus())
                ->isEqualTo(Deploy::STATUS_WAITING)
        ->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock([
                    $this->getDeployMock(2)->setStatus(Deploy::STATUS_DEPLOY),
                ]),
                $this->getGithubClientMock(),
                $this->getGoliveClientMock([], ['status' => 'success']),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // Another one running, should be queued
            ->object($this->testedInstance->updateStatus(
                $deploy = $this->getDeployMock(1)->setStatus(Deploy::STATUS_NEW)
            ))
                ->isIdenticalTo($this->testedInstance)
            ->string($deploy->getStatus())
                ->isEqualTo(Deploy::STATUS_QUEUED)
        ->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(false),
                $this->getGoliveClientMock([], ['status' => 'success']),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // PR not merged
            ->object($this->testedInstance->updateStatus(
                $deploy = $this->getDeployMock()
                    ->setStatus(Deploy::STATUS_NEW)
                    ->setPullrequestId(1)
            ))
                ->isIdenticalTo($this->testedInstance)
            ->string($deploy->getStatus())
                ->isEqualTo(Deploy::STATUS_MERGE)
            // Not deployed
            ->object($this->testedInstance->updateStatus(
                $deploy = $this->getDeployMock()
                    ->setStatus(Deploy::STATUS_NEW)
            ))
                ->isIdenticalTo($this->testedInstance)
            ->string($deploy->getStatus())
                ->isEqualTo(Deploy::STATUS_DEPLOY)
            ->object($this->testedInstance->updateStatus(
                $deploy = $this->getDeployMock()
                    ->setStatus(Deploy::STATUS_NEW)
                    ->setGoliveId(10)
            ))
                ->isIdenticalTo($this->testedInstance)
            ->string($deploy->getStatus())
                ->isEqualTo(Deploy::STATUS_WAITING)
        ;
    }

    /**
     * Test canStart method
     */
    public function testCanStart()
    {
        $this->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock([]),
                $this->getGithubClientMock(),
                $this->getGoliveClientMock(),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // Bad status
            ->boolean($this->testedInstance->canStart(
                $this->getDeployMock()
            ))
                ->isFalse()
            // No other deployments
            ->boolean($this->testedInstance->canStart(
                $this->getDeployMock(1)->setStatus(Deploy::STATUS_QUEUED)
            ))
                ->isTrue()
        ->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock([
                    $deploy = $this->getDeployMock(1)->setStatus(Deploy::STATUS_QUEUED),
                ]),
                $this->getGithubClientMock(),
                $this->getGoliveClientMock(),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // Same deploy
            ->boolean($this->testedInstance->canStart($deploy))
                ->isTrue()
            // Another deploy is already running
            ->boolean($this->testedInstance->canStart(
                $this->getDeployMock(2)
            ))
                ->isFalse()
        ;
    }

    /**
     * Test isMerged method
     */
    public function testIsMerged()
    {
        $this->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(false),
                $this->getGoliveClientMock(),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // No PR associated, considered as merged
            ->boolean($this->testedInstance->isMerged(
                $this->getDeployMock()
            ))
                ->isTrue()
            // Not merged on github
            ->boolean($this->testedInstance->isMerged(
                $this->getDeployMock()->setPullrequestId(10)
            ))
                ->isFalse()
        ->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(true),
                $this->getGoliveClientMock(),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // Merged on github
            ->boolean($this->testedInstance->isMerged(
                $this->getDeployMock()->setPullrequestId(10)
            ))
                ->isTrue()
        ;
    }

    /**
     * Test isDeployed method
     */
    public function testIsDeployed()
    {
        $this->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(),
                $this->getGoliveClientMock(),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // No correspondig golive project, considered as deployed
            ->boolean($this->testedInstance->isDeployed(
                $this->getDeployMock()
            ))
                ->isTrue()
        ->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(),
                $this->getGoliveClientMock([], ['status' => 'running']),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // No golive id, has project
            ->boolean($this->testedInstance->isDeployed(
                $this->getDeployMock()
            ))
                ->isFalse()
            // Golive id, deploy running
            ->boolean($this->testedInstance->isDeployed(
                $this->getDeployMock()->setGoliveId(10)
            ))
                ->isFalse()
        ->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(),
                $this->getGoliveClientMock([], ['status' => 'success']),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // Golive id, deploy success
            ->boolean($this->testedInstance->isDeployed(
                $this->getDeployMock()->setGoliveId(10)
            ))
                ->isTrue()
        ;
    }

    /**
     * Test deploy method
     */
    public function testDeploy()
    {
        $this->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(),
                $golive = $this->getGoliveClientMock(),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // Wrong status
            ->object($this->testedInstance->deploy(
                $deploy = $this->getDeployMock()
            ))
                ->isIdenticalTo($deploy)
                ->mock($golive)
                    ->call('createDeployment')->never()
            // Good status, no project associated
            ->object($this->testedInstance->deploy(
                $deploy = $this->getDeployMock()->setStatus(Deploy::STATUS_DEPLOY)
            ))
                ->isIdenticalTo($deploy)
                ->mock($golive)
                    ->call('createDeployment')->never()
        ->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(),
                $golive = $this->getGoliveClientMock([], ['status' => 'running']),
                'prod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // Has golive project
            ->object($this->testedInstance->deploy(
                $deploy = $this->getDeployMock()
                    ->setStatus(Deploy::STATUS_DEPLOY)
                    ->setRepository('foo')
            ))
                ->isIdenticalTo($deploy)
                ->mock($golive)
                    ->call('createDeployment')
                        ->withArguments('foo', 'prod')->once()
            // Has golive project, has golive id
            ->object($this->testedInstance->deploy($deploy))
                ->isIdenticalTo($deploy)
                ->mock($golive)
                    // no call, except previous one
                    ->call('createDeployment')->once()
        ->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(),
                $golive = $this->getGoliveClientMock([], ['status' => 'failure']),
                'preprod',
                $this->getEntityManagerMock()
            )
        )
        ->then
            // Deployment has failed, allowed to start a new one
            ->object($this->testedInstance->deploy(
                $deploy = $this->getDeployMock()
                    ->setStatus(Deploy::STATUS_DEPLOY)
                    ->setRepository('bar')
                    ->setGoliveId(10)
            ))
                ->isIdenticalTo($deploy)
                ->mock($golive)
                    ->call('createDeployment')
                        ->withArguments('bar', 'preprod')->once()
        ;
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $this->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(),
                $golive = $this->getGoliveClientMock(),
                'prod',
                $em = $this->getEntityManagerMock()
            )
        )
        ->then
            ->object($this->testedInstance->save(
                $deploy = $this->getDeployMock()
            ))
                ->isIdenticalTo($this->testedInstance)
                ->mock($em)
                    ->call('persist')->withArguments($deploy)->once()
                    ->call('persist')->once()
                    ->call('flush')->once()
        ;
    }

    /**
     * Test save method
     */
    public function testUpdateQueue()
    {
        $this->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock(),
                $this->getGithubClientMock(),
                $golive = $this->getGoliveClientMock(),
                'prod',
                $em = $this->getEntityManagerMock()
            )
        )
        ->then
            // Empty queue
            ->object($this->testedInstance->updateQueue('foo', 'bar'))
                ->isIdenticalTo($this->testedInstance)
            ->mock($em)
                ->call('persist')->never()
                ->call('flush')->never()
        ->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock($deploys = [
                    $this->getDeployMock()->setStatus(Deploy::STATUS_MERGE),
                    $this->getDeployMock()->setStatus(Deploy::STATUS_QUEUED),
                ]),
                $this->getGithubClientMock(),
                $golive = $this->getGoliveClientMock(),
                'prod',
                $em = $this->getEntityManagerMock()
            )
        )
        ->then
            // Not empty queue, but first deploy already running
            ->object($this->testedInstance->updateQueue('foo', 'bar'))
                ->isIdenticalTo($this->testedInstance)
            ->mock($em)
                ->call('persist')->never()
                ->call('flush')->never()
            ->string($deploys[0]->getStatus())
                ->isEqualTo(Deploy::STATUS_MERGE)
            ->string($deploys[1]->getStatus())
                ->isEqualTo(Deploy::STATUS_QUEUED)
        ->given(
            $this->newTestedInstance(
                $this->getEntityRepositoryMock($deploys = [
                    $this->getDeployMock()->setStatus(Deploy::STATUS_QUEUED),
                    $this->getDeployMock()->setStatus(Deploy::STATUS_QUEUED),
                ]),
                $this->getGithubClientMock(),
                $golive = $this->getGoliveClientMock(),
                'prod',
                $em = $this->getEntityManagerMock()
            )
        )
        ->then
            // Not empty queue, all queued
            ->object($this->testedInstance->updateQueue('foo', 'bar'))
                ->isIdenticalTo($this->testedInstance)
            ->mock($em)
                ->call('persist')->withArguments($deploys[0])->once()
                ->call('flush')->once()
            ->string($deploys[0]->getStatus())
                ->isEqualTo(Deploy::STATUS_WAITING)
            ->string($deploys[1]->getStatus())
                ->isEqualTo(Deploy::STATUS_QUEUED)
        ;
    }

    /**
     * @param array $matching
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepositoryMock($matching = [])
    {
        $this->mockGenerator->orphanize('__construct');
        $mock = new \mock\Doctrine\ORM\EntityRepository();

        $mock->getMockController()->matching = function () use ($matching) {
            return new \Doctrine\Common\Collections\ArrayCollection($matching);
        };

        return $mock;
    }

    /**
     * @param boolean $merged
     *
     * @return ApiBundle\Service\Github\Client
     */
    protected function getGithubClientMock($merged = false)
    {
        $this->mockGenerator->orphanize('__construct');
        $mock = new \mock\ApiBundle\Service\Github\Client();

        $mock->getMockController()->getPullRequest = ['merged' => $merged];

        return $mock;
    }

    /**
     * @return ApiBundle\Service\Golive\Client
     */
    protected function getGoliveClientMock($project = null, $deployment = null)
    {
        $this->mockGenerator->orphanize('__construct');
        $mock = new \mock\ApiBundle\Service\Golive\Client();

        $mock->getMockController()->createDeployment = ['id' => 1];
        $mock->getMockController()->getDeployment = $deployment;
        $mock->getMockController()->getProject = $project;

        return $mock;
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerMock()
    {
        $mock = new \mock\Doctrine\ORM\EntityManager();

        $mock->getMockController()->persist = true;
        $mock->getMockController()->flush = true;

        return $mock;
    }

    /**
     * @param int $id
     *
     * @return Deploy
     */
    protected function getDeployMock(int $id = 1)
    {
        $mock = (new \mock\ApiBundle\Entity\Deploy())
            ->setStatus(Deploy::STATUS_NEW)
            ->setOwner('owner')
            ->setRepository('repository')
        ;

        $mock->getMockController()->getId = $id;

        return $mock;
    }
}
