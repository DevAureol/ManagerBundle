<?php

namespace Aureol\ManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

class BaseManagerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('aureol_manager:generate:basemanager')
            ->setDescription('Generate BaseManager')
            ->addArgument(
                'BundleName',
                InputArgument::REQUIRED,
                'In which Bundle ? '
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

        if($fs->exists('src/' . $name)){
            if($fs->exists('src/' . $name . '/Manager')) {
                try {
                    $fs->dumpFile('src/' . $name . '/Manager/BaseManager.php', $this->fillBaseManager($name));
                } catch (IOExceptionInterface $e) {
                    $output->writeln("An error occurred while creating your directory at " . $e->getPath());
                }
                $text .= "BaseManager file created\n";
            }else{
                try {
                    $fs->mkdir('src/' . $name . '/Manager');
                } catch (IOExceptionInterface $e) {
                    $output->writeln("An error occurred while creating your directory at " . $e->getPath());
                }

                $text .= "Manager folder created\n";

                try {
                    $fs->dumpFile('src/' . $name . '/Manager/BaseManager.php', $this->fillBaseManager($name));
                } catch (IOExceptionInterface $e) {
                    $output->writeln("An error occurred while creating your directory at " . $e->getPath());
                }

                $text .= "BaseManager file created \n";
            }
        }else{
            $text = $name . " doesn't exist\n";
        }
        $output->writeln($text);
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