<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateMetricCommand extends Command
{
    private const DATA_FILE = '/../../data/Jira.csv';
    private const COLUMN_LOGGED = 'Log Work';
    private const COLUMN_ESTIMATE = 'Original Estimate';
    private const COLUMN_WORK_RATIO = 'Work Ratio';
    private const COLUMN_ISSUE_KEY = 'Issue key';

    protected static $defaultName = 'app:generate-metric';

    protected function configure()
    {
        $this
            ->setDescription('Generates Estimation Accuracy Metric for L10')
            ->setHelp('This command generates estimation accuracy metric for L10 based on CSV file exported from Jira.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = __DIR__.self::DATA_FILE;
        $handle = fopen($filePath, 'rb');
        if (false === $handle) {
            $output->writeln('Failed to open data file at "'.self::DATA_FILE.'".');
            exit();
        }

        $rowCount = 0;
        $columnsWithTimeLogged = [];
        $columnWithEstimate = null;
        $columnWithWorkRatio = null;
        $columnWithIssueKey = null;
        $outliers = [];
        $sumOfTimeLogged = 0;
        $sumOfEstimates = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if ($rowCount === 0) {
                foreach ($row as $index => $cell) {
                    if ($cell === self::COLUMN_LOGGED) {
                        $columnsWithTimeLogged[] = $index;
                    } elseif ($cell === self::COLUMN_ESTIMATE) {
                        $columnWithEstimate = $index;
                    } elseif ($cell === self::COLUMN_WORK_RATIO) {
                        $columnWithWorkRatio = $index;
                    } elseif ($cell === self::COLUMN_ISSUE_KEY) {
                        $columnWithIssueKey = $index;
                    }
                }
                if (empty($columnsWithTimeLogged)) {
                    $output->writeln('No "'.self::COLUMN_LOGGED.'" columns found.');
                    exit();
                }

                if (null === $columnWithEstimate) {
                    $output->writeln('No "'.self::COLUMN_ESTIMATE.'" column found.');
                    exit();
                }

                if (null === $columnWithWorkRatio) {
                    $output->writeln('No "'.self::COLUMN_WORK_RATIO.'" column found.');
                    exit();
                }
            } else {
                $issueKey = null;
                foreach ($row as $index => $cell) {
                    if ($index === $columnWithIssueKey) {
                        $issueKey = $cell;
                    }

                    if (! empty($cell) && in_array($index, $columnsWithTimeLogged, true)) {
                        [, , , $time] = explode(';', $cell);
                        $sumOfTimeLogged += (int) $time;
                    }

                    if ($index === $columnWithEstimate) {
                        $sumOfEstimates += (int) $cell;
                    }

                    if ($index === $columnWithWorkRatio && strlen($cell) > 4) {
                        $outliers[] = $issueKey;
                    }
                }
            }

            $rowCount++;
        }

        $output->writeln('TOTAL ISSUES:   '.($rowCount - 1));
        $output->writeln('TOTAL ESTIMATE: '.$sumOfEstimates);
        $output->writeln('TOTAL LOGGED:   '.$sumOfTimeLogged);

        $rate = round(($sumOfEstimates / $sumOfTimeLogged - 1) * 100, 2);

        $output->writeln('RATE (est/log): '.$rate.'%');

        if ($outliers) {
            $output->writeln("\n-----------------------------------------\n");
            $output->writeln('OUTLIERS:');
            foreach ($outliers as $outlier) {
                $output->writeln($outlier);
            }
        }

        return 0;
    }
}
