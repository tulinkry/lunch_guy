<?php
namespace Net\TomasKadlec\LunchGuy\BaseBundle\Command;

use Net\TomasKadlec\LunchGuy\BaseBundle\Service\ApplicationInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunCommand
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Command
 */
class RunCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('lunch_guy:run')
            ->setDescription('Return menus ...')
            ->addOption('no-cache', 'N', InputOption::VALUE_NONE, 'Do not use cached data')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Select an application output', 'stdout')
            ->addOption('slack-channel', 's', InputOption::VALUE_REQUIRED, 'Select a channel', null)
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Run on all configured restaurants')
            ->addArgument('restaurants', InputArgument::IS_ARRAY, 'Restaurant(s) to process', []);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ApplicationInterface $application */
        $application = $this->getContainer()->get('net_tomas_kadlec_lunch_guy_base.service.application');
        if ($input->getOption('no-cache')) {
            $application = $this->getContainer()->get('net_tomas_kadlec_lunch_guy_base.service_application.application');
        }

        $outputFormat = $input->getOption('output');
        if (!$application->isOutput($outputFormat))
            throw new \RuntimeException('Supported output formats: ' . join(', ', $application->getOutputs()));

        // output options (prefixed with $outputFormat)
        $options = [];
        foreach($input->getOptions() as $option => $value) {
            if (preg_match("/^{$outputFormat}-/", $option)) {
                $option = preg_replace("/^{$outputFormat}-/", '', $option);
                if (!empty($value))
                    $options[$option] = $value;
            }
        }

        if ($input->getOption('all')) {
            $restaurantIds  = $application->getRestaurants();
        } else {
            $restaurantIds = $input->getArgument('restaurants');
            if (empty($restaurantIds))
                throw new \RuntimeException('Provide one restaurant ID at least or use --all option');
        }

        foreach ($restaurantIds as $restaurantId) {
            if (!$application->isRestaurant($restaurantId))
                continue;
            $application->output($restaurantId, $outputFormat, $options);
        }
    }
}