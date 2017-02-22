<?php
namespace ApiMockBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ApiMockBundle\Fixtures\AbstractLoader;

/**
 * Load fixtures
 */
class LoadFixturesCommand extends ContainerAwareCommand
{
    /**
     * @var array
     */
    protected $fixturesLoaders = [];

    /**
     * @param AbstractLoader $loader
     */
    public function addFixturesLoader(AbstractLoader $loader)
    {
        $this->fixturesLoaders[] = $loader;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('apimock:fixtures:load');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->fixturesLoaders as $loader) {
            $loader->load();
        }
    }
}
