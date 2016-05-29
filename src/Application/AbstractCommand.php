<?php

/*
 * This file is part of git-pull-request/git-pull-request.
 *
 * (c) Julien Dufresne <https://github.com/git-pull-request/git-pull-request>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GitPullRequest\Application;

use GitPullRequest\DomainModel\Config\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Base Command to specify common tasks.
 */
abstract class AbstractCommand extends Command
{
    /** @var Config */
    private $config;

    /** @var SymfonyStyle */
    private $symfonyStyleIO;

    /**
     * @param null $name
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->config = new Config();
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /** @param string $stepName */
    public function startStep(string $stepName)
    {
        $this->getIo()->write(' > '.$stepName.'... ');
    }

    public function stepSucceeded()
    {
        $this->writeColorText('OK', 'green');
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initIO($input, $output);
    }

    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->initIO($input, $output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initIO($input, $output);

        return 1; // Command will fail if this method is not override.
    }

    /**
     * @return SymfonyStyle
     */
    protected function getIo()
    {
        return $this->symfonyStyleIO;
    }

    /** @param string $waningMessage */
    protected function stepWarning(string $waningMessage = '')
    {
        $this->writeColorText('WARN', 'yellow');
        if ('' !== $waningMessage) {
            $this->getIo()->warning($waningMessage);
        }
    }

    /**
     * @param string $errorMessage
     *
     * @return bool
     */
    protected function stepFailed(string $errorMessage = '') : bool
    {
        $this->writeColorText('FAIL', 'red');
        if ('' !== $errorMessage) {
            $this->getIo()->error($errorMessage);
        }

        return false;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function initIO(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->symfonyStyleIO) {
            $this->symfonyStyleIO = new SymfonyStyle($input, $output);
        }
    }

    /**
     * @param string $text
     * @param string $color
     */
    private function writeColorText(string $text, string $color)
    {
        $this->getIo()->writeln("<fg=$color>$text</>");
    }
}
