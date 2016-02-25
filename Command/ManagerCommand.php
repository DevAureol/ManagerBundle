<?php

namespace Aureol\ManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;


class ManagerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('aureol_manager:generate:manager')
            ->setDescription('Generate Manager')
            ->addArgument(
                'BundleName',
                InputArgument::REQUIRED,
                'In which Bundle ?'
            )->addArgument(
                'EntityName',
                InputArgument::OPTIONAL,
                'With which entity to generate Manager ?'
            )->addOption(
                'base',
                null,
                InputOption::VALUE_NONE,
                'Create Manager file for all Entity'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $text = null;
        $fs = new Filesystem();
        $name = $input->getArgument('BundleName');
        $entity = $input->getArgument('EntityName');

        if($entity) {
            if ($fs->exists('src/' . $name)) {
                if ($fs->exists('src/' . $name . '/Manager')) {
                    if ($fs->exists('src/' . $name . '/Manager/BaseManager.php')) {

                        try {
                            $fs->dumpFile('src/' . $name . '/Manager/' . ucfirst($entity) . 'Manager.php', $this->fillManagerWithEntity($name, $entity, true));
                        } catch (IOExceptionInterface $e) {
                            $output->writeln("An error occurred while creating your directory at " . $e->getPath());
                        }
                    } else {
                        try {
                            $fs->dumpFile('src/' . $name . '/Manager/' . ucfirst($entity) . 'Manager.php', $this->fillManagerWithEntity($name, $entity, false));
                        } catch (IOExceptionInterface $e) {
                            $output->writeln("An error occurred while creating your directory at " . $e->getPath());
                        }
                    }
                    $text .= "Manager file created \n";
                } else {
                    try {
                        $fs->mkdir('src/' . $name . '/Manager');
                    } catch (IOExceptionInterface $e) {
                        $output->writeln("An error occurred while creating your directory at " . $e->getPath());
                    }

                    $text .= "Manager folder created \n";

                    try {
                        $fs->dumpFile('src/' . $name . '/Manager/' . ucfirst($entity) . 'Manager.php', $this->fillManagerWithEntity($name, $entity, false));
                    } catch (IOExceptionInterface $e) {
                        $output->writeln("An error occurred while creating your directory at " . $e->getPath());
                    }

                    $text .= "Manager file created \n";
                }
            } else {
                $text = $name . " doesn't exist \n";
            }
        }else{
            if($fs->exists('src/' . $name . '/Entity')){
                $finder = new Finder();
                $finder->files()->in('src/' . $name . '/Entity');

                if ($input->getOption('base')) {
                    try {
                        $fs->dumpFile('src/' . $name . '/Manager/BaseManager.php', $this->fillBaseManager($name));
                    } catch (IOExceptionInterface $e) {
                        $output->writeln("An error occurred while creating your directory at " . $e->getPath());
                    }
                    $text .= "BaseManager file created \n";
                }

                if ($fs->exists('src/' . $name . '/Manager/BaseManager.php')) {
                    foreach ($finder as $file) {
                        $entity = str_replace('.php', '',$file->getRelativePathname());
                        try {
                            $fs->dumpFile('src/' . $name . '/Manager/' . ucfirst($entity) . 'Manager.php', $this->fillManagerWithEntity($name, $entity, true));
                        } catch (IOExceptionInterface $e) {
                            $output->writeln("An error occurred while creating your directory at " . $e->getPath());
                        }
                        $text .= ucfirst($entity) . 'Manager created' . "\n";
                    }
                } else {
                    foreach ($finder as $file) {
                        $entity = str_replace('.php', '',$file->getRelativePathname());
                        try {
                            $fs->dumpFile('src/' . $name . '/Manager/' . ucfirst($entity) . 'Manager.php', $this->fillManagerWithEntity($name, $entity, false));
                        } catch (IOExceptionInterface $e) {
                            $output->writeln("An error occurred while creating your directory at " . $e->getPath());
                        }
                        $text .= ucfirst($entity) . 'Manager created' . "\n";
                    }

                }
            }else{
                $text .= "No entity";
            }
        }

        $output->writeln($text);
    }

    private function fillManagerWithEntity($name, $entity, $basemanager){
        if($basemanager){
            $content = '<?php
namespace ' . $name . '\Manager;

use Doctrine\ORM\EntityManager;
use ' . $name . '\Entity\\' . $entity . ';

class ' . $entity . 'Manager extends BaseManager
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getRepository()
    {
        return $this->em->getRepository("' . $name . ':' . $entity . '");
    }

}';
            return $content;
        }
    else {
        $content = '<?php
namespace ' . $name . '\Manager;

use Doctrine\ORM\EntityManager;
use ' . $name . '\Entity\\' . $entity . ';

class ' . $entity . 'Manager
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getRepository()
    {
        return $this->em->getRepository("' . $name . ':' . $entity . '");
    }

}';
        return $content;
    }

}
    private function fillBaseManager($name){
        $content = '<?php

namespace ' . $name . '\\Manager;

abstract class BaseManager
{
    protected function persistAndFlush($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return $this->getRepository()->find($id, $lockMode, $lockVersion);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }


}';

        return $content;
    }
}
