<?php
namespace ApiMockBundle\Fixtures;

use Doctrine\ORM\EntityManager;
use ApiMockBundle\Entity\Mock;

class Loader
{
    protected $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function load()
    {
        $cmd = $this->manager->getClassMetadata('ApiMockBundle:Mock');
        $connection = $this->manager->getConnection();
        $connection->beginTransaction();
        $connection->query('TRUNCATE '.$cmd->getTableName());
        $connection->commit();

        $root = realpath(__DIR__.'/../Resources/fixtures').'/';
        foreach ($this->scanDirectories($root) as $api) {
            foreach ($this->scanDirectories($root.$api) as $type) {
                foreach ($this->scanFiles($root.$api.'/'.$type) as $file) {
                    $this->manager->persist(
                        (new Mock())
                            ->setApi($api)
                            ->setType($type)
                            ->setPayload(file_get_contents($root.$api.'/'.$type.'/'.$file))
                    );
                }
            }
        }

        $this->manager->flush();
    }

    protected function scanDirectories(string $dir) : array
    {
        return array_values(array_filter(array_map(
            function($item) use ($dir) {
                if (is_dir($dir.'/'.$item) && substr($item, 0, 1) != '.') {
                    return $item;
                }
                return null;
            },
            scandir($dir)
        )));
    }

    protected function scanFiles(string $dir) : array
    {
        return array_values(array_filter(array_map(
            function($item) use ($dir) {
                if (is_file($dir.'/'.$item) && substr($item, 0, 1) != '.') {
                    return $item;
                }
                return null;
            },
            scandir($dir)
        )));
    }
}
