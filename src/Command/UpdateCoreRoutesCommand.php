<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Command;

use Sonata\PageBundle\Route\RoutePageGenerator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update core routes by reading routing information.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/page-bundle 3.26
 */
class UpdateCoreRoutesCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('sonata:page:update-core-routes');
        $this->setDescription('Update core routes, from routing files to page manager');
        // NEXT_MAJOR: Remove the "all" option.
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Create snapshots for all sites');
        $this->addOption('clean', null, InputOption::VALUE_NONE, 'Removes all unused routes');
        $this->addOption('site', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Site id', null);
        $this->addOption('base-command', null, InputOption::VALUE_OPTIONAL, 'Site id', 'php app/console');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all')) {
            @trigger_error(
                'Using the "all" option is deprecated since 3.4 and will be removed in 4.0.',
                \E_USER_DEPRECATED
            );
        }

        if (!$input->getOption('site')) {
            $output->writeln('Please provide an <info>--site=SITE_ID</info> option or the <info>--site=all</info> directive');
            $output->writeln('');

            $output->writeln(sprintf(' % 5s - % -30s - %s', 'ID', 'Name', 'Url'));

            foreach ($this->getSiteManager()->findBy([]) as $site) {
                $output->writeln(sprintf(' % 5s - % -30s - %s', $site->getId(), $site->getName(), $site->getUrl()));
            }

            return 0;
        }

        foreach ($this->getSites($input) as $site) {
            if ('all' !== $input->getOption('site')) {
                $this->getRoutePageGenerator()->update($site, $output, $input->getOption('clean'));
                $output->writeln('');
            } else {
                $arguments = [
                    'env' => $input->getOption('env'),
                    'site' => $site->getId(),
                ];

                if ($input->getOption('no-debug')) {
                    $arguments['--no-debug'] = true;
                }

                if ($input->getOption('clean')) {
                    $arguments['--clean'] = true;
                }

                $this->run(new ArrayInput($arguments), $output);
            }
        }

        $output->writeln('<info>done!</info>');

        return 0;
    }

    /**
     * Returns Sonata route page generator service.
     *
     * @return RoutePageGenerator
     */
    private function getRoutePageGenerator()
    {
        return $this->getContainer()->get('sonata.page.route.page.generator');
    }
}
