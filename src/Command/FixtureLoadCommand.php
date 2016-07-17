<?php

namespace Butterfly\Component\Fixtures\Command;

use Butterfly\Component\Fixtures\FixtureLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixtureLoadCommand extends Command
{
    /**
     * @var FixtureLoader
     */
    protected $loader;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param FixtureLoader $loader
     */
    public function setLoader($loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    protected function configure()
    {
        $this
            ->setName('bfy:fixtures:load')
            ->setDescription('Load fixtures to DB');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loader->load($this->data);

        $output->writeln(sprintf('Fixtures is loaded'));
    }
}
