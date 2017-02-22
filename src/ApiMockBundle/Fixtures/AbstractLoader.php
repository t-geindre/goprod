<?php
namespace ApiMockBundle\Fixtures;

use Doctrine\ORM\EntityManager;
use ApiMockBundle\Entity\AbstractEntity;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Base fixtures loader
 */
abstract class AbstractLoader
{
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $fixturesDir;

    /**
     * @var integer
     */
    protected $count = 0;

    /**
     * @param EntityManager        $manager
     * @param LoggerInterface|null $logger
     */
    public function __construct(EntityManager $manager, LoggerInterface $logger = null)
    {
        $this->manager = $manager;
        $this->logger = $logger ? $logger : new NullLogger();
        $this->fixturesDir = $root = realpath(__DIR__.'/../Resources/fixtures').'/';
    }

    /**
     * Load fixtures
     */
    public function load()
    {
        $this->clear();
        $this->count = 0;
        $class = $this->getEntityClass();
        $type = $this->getType();

        foreach ($this->scanFiles($this->fixturesDir.$type) as $file) {
            $payload = json_decode(file_get_contents($file), true);
            $entity = new $class();

            if ($entity instanceof AbstractEntity) {
                $entity->setPayload($payload);
            }

            $this->populateEntity($entity, $payload);
            $this->save($entity);
        }

        $this->manager->flush();
        $this->logger->info(sprintf('Created %d %s entries.', $this->count, $this->getType()));
    }


    protected function save(AbstractEntity $entity)
    {
        $this->manager->persist($entity);
        $this->count++;
    }

    protected function clear()
    {
        $cmd = $this->manager->getClassMetadata($this->getEntityClass());
        $connection = $this->manager->getConnection();
        $connection->beginTransaction();
        $connection->query('TRUNCATE '.$cmd->getTableName());
        $connection->commit();
        $this->logger->info(sprintf('%s table cleared.', $this->getType()));
    }

    protected function scanFiles(string $dir) : array
    {
        if (!is_dir($dir)) {
            throw new \RuntimeException(sprintf('%s directory not found', $dir));
        }

        return array_values(array_filter(array_map(
            function ($item) use ($dir) {
                $file = $dir.'/'.$item;
                if (is_file($file) && substr($item, 0, 1) != '.') {
                    return $file;
                }

                return null;
            },
            scandir($dir)
        )));
    }

    protected function getType()
    {
        $classParts = explode('\\', $this->getEntityClass());

        return array_pop($classParts);
    }

    /**
     * @return string
     */
    abstract protected function getEntityClass() : string;

    /**
     * Populate entity
     *
     * @param object $entity
     * @param array  $payload
     */
    abstract protected function populateEntity($entity, array $payload);
}
