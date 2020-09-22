<?php

namespace App\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StyleTestCommand extends Command{
    private const TEST_CONST_ONE = 'test constant';
    private const TEST_CONST_TWO    = 1000;
    private const TEST_CONST_THREE         = true;
    const TEST_CONST_FOUR = array(1,2,3,4,5);

    protected static $defaultName = 'app:test-styles';

    protected function configure()
    {
        $this
            ->setDescription('Dummy code to test code style automation')
            ->setHelp('This command is used to test code style automation tool (StyleCI).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $a = 1;
        $aaa = 2;

        if ($this->PrintVars($a, $aaa))
        {
            print('printed');
            echo "\n";
        };;;

        return 0;
    }

    function PrintVars($a, $b) {
        print $a.PHP_EOL;
        print $b.PHP_EOL;
        return true;
    }
}