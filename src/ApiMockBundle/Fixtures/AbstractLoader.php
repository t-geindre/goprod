<?php
namespace ApiMockBundle\Fixtures;

use Doctrine\ORM\EntityManager;
use ApiMockBundle\Entity\AbstractEntity;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractLoader
{
    protected $manager;
    protected $logger;
    protected $fixturesDir;

    public function __construct(EntityManager $manager, LoggerInterface $logger = null)
    {
        $this->manager = $manager;
        $this->logger = $logger ? $logger : new NullLogger();
        $this->fixturesDir = $root = realpath(__DIR__.'/../Resources/fixtures').'/';
    }

    abstract protected function getEntityClass() : string;

    protected function clear()
    {
        $cmd = $this->manager->getClassMetadata($this->getEntityClass());
        $connection = $this->manager->getConnection();
        $connection->beginTransaction();
        $connection->query('TRUNCATE '.$cmd->getTableName());
        $connection->commit();
    }

    protected function scanFiles(string $dir) : array
    {
        if (!is_dir($dir)) {
            throw new \RuntimeException(sprintf('%s directory not found', $dir));
        }

        return array_values(array_filter(array_map(
            function($item) use ($dir) {
                $file = $dir.'/'.$item;
                if (is_file($file) && substr($item, 0, 1) != '.') {
                    return $file;
                }
                return null;
            },
            scandir($dir)
        )));
    }

    public function load()
    {
        $this->clear();

        $class = $this->getEntityClass();

        $classParts = explode('\\', $class);
        $type = strtolower(array_pop($classParts));

        foreach ($this->scanFiles($this->fixturesDir.$type) as $file) {
            $content = file_get_contents($file);
            $entity = new $class;

            if ($entity instanceof AbstractEntity) {
                $entity->setPayload($content);
            }

            $this->populateEntity($entity, json_decode($content, true));
            $this->manager->persist($entity);
        }

        $this->manager->flush();
    }

    abstract protected function populateEntity($entity, array $payload);
}
