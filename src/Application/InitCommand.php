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

use GitPullRequest\Git\Git;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper to generate config files.
 */
final class InitCommand extends AbstractCommand
{
    /**
     * @var array
     */
    private static $defaultHosts = [
        'bitbucket' => [
            'name'         => 'bitbucket.org',
            'paas'         => 'https://api.bitbucket.org',
            'self_hosted'  => 'http(s)://yourserver.tld',
            'api_base_uri' => '/rest/api',
        ],
        'github'    => [
            'name'         => 'github.com',
            'paas'         => 'https://api.github.com',
            'self_hosted'  => 'http(s)://yourserver.tld',
            'api_base_uri' => '/api/v3',
        ],
        'gitlab'    => [
            'self_hosted'  => 'http(s)://yourserver.tld',
            'api_base_uri' => '/api/v3',
        ],
    ];
    /** @var string[] */
    private $options;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('init')
            ->setAliases(['config', 'configure', 'setup'])
            ->setDescription('Generates/Updates a configuration file')
            ->addArgument('file-type', InputArgument::OPTIONAL, 'Type of file to generate')
            ->setHelp(file_get_contents(__DIR__.'/.help/init'));
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     * @throws \GitPullRequest\Git\Exception\RuntimeException
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        switch ($input->getArgument('file-type')) {
            case 'global':
                $this->getConfig()->saveGlobalOptions($this->options);
                break;
            case 'shared':
                $this->getConfig()->saveSharedOptions($this->options);
                break;
            case 'local':
                $this->getConfig()->saveLocalOptions($this->options);
                break;
            default:
                return 1;
        }

        return 0;
    }

    /**
     * Interacts with the user.
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @throws \GitPullRequest\Git\Exception\RuntimeException
     *
     * @return int
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);
        if (null === $input->getArgument('file-type')) {
            $input->setArgument('file-type', $this->askWhichFileToGenerate());
        }
        switch ($input->getArgument('file-type')) {
            case 'global':
                $this->options = $this->askGlobalOptions();
                break;
            case 'shared':
                $this->options = $this->askSharedOptions();
                break;
            case 'local':
                $this->options = $this->askLocalOptions();
                break;
            default:
                return 1;
        }

        return 0;
    }

    /**
     * @throws \GitPullRequest\Git\Exception\RuntimeException
     *
     * @return string
     */
    private function askWhichFileToGenerate() : string
    {
        $home             = getenv('HOME');
        $globalFilePath   = $home.'/.git-pull-request.yml';
        $git              = new Git();
        $isInsideWorkTree = $git->isInsideWorkTree();
        $help             = str_replace(
            '<HOME>',
            $home,
            <<<'EOT'
The <info>init</info> command may generate 3 files:
  * <info><HOME>/.git-pull-request.yml</info> stores global information such as the default credentials
  * <info><WORKING_DIR>/.git-pull-request.yml</info> stores generic information about your repository
  * <info><WORKING_DIR>/.git/.git-pull-request.yml</info> store your credentials to connect to the current repository's provider.
EOT
        );
        if (!$isInsideWorkTree) {
            $this->getIo()->note(
                [
                    'You are not inside a git working tree.',
                    sprintf('This means you can only generate the global %s file', $globalFilePath),
                ]
            );

            return $this->getIo()->confirm('Do you want to generate the global file ?') ? 'global' : '';
        }

        $rootGitWorkingTree = $git->getProjectRootDir();
        $help               = str_replace('<WORKING_DIR>', $rootGitWorkingTree, $help);
        $defaultChoice      = $rootGitWorkingTree.'/.git/.git-pull-request.yml';
        $choices            = [
            'global' => $globalFilePath,
            'shared' => $rootGitWorkingTree.'/.git-pull-request.yml',
            'local'  => $defaultChoice,
        ];

        $this->getIo()->text($help);

        return $this->getIo()->choice('Which file do you want to generate/update ?', $choices, $defaultChoice);
    }

    /**
     * @throws \GitPullRequest\Git\Exception\RuntimeException
     *
     * @return array
     */
    private function askGlobalOptions() : array
    {
        $definedOptions = $this->getConfig()->getGlobalOptions();
        $providers      = ['bitbucket', 'github', 'gitlab'];
        $options        = [];
        foreach ($providers as $provider) {
            if ($this->getIo()->confirm(sprintf('Do you want to configure a global %s account ?', $provider))) {
                $current                         = $definedOptions['providers'][$provider] ?? [];
                $options['providers'][$provider] = [
                    'user'         => $this->getIo()->ask($provider.' user', $current['user'] ?? null),
                    'password'     => $this->getIo()->ask($provider.' password or token', $current['password'] ?? null),
                    'clone_method' => $this->askCloneMethod($current['clone_method'] ?? ''),
                ];
            }
        }

        $options = array_merge(
            $options,
            $this->askCommitterOptions($definedOptions['committer'] ?? []),
            $this->askLabelOptions($definedOptions['labels'] ?? [], true)
        );

        return $options;
    }

    /**
     * @throws \GitPullRequest\Git\Exception\RuntimeException
     *
     * @return array
     */
    private function askSharedOptions() : array
    {
        $definedOptions = $this->getConfig()->getSharedOptions();
        $options        = [];

        $options = array_merge(
            $options,
            $this->askProviderOption($definedOptions['provider'] ?? []),
            $this->askLabelOptions($definedOptions['labels'] ?? [])
        );

        return $options;
    }

    /**
     * @throws \GitPullRequest\Git\Exception\RuntimeException
     *
     * @return array
     */
    private function askLocalOptions() : array
    {
        $definedOptions = $this->getConfig()->getLocalOptions();
        $options        = [];
        $this->getIo()->text(
            [
                '<info>git pr</info> needs to access your remote provider to get information about pull requests.',
                'It is recommended to specify your credentials in this file.',
            ]
        );

        if ($this->getIo()->confirm('Do you want to configure your credentials ?')) {
            $options = [
                'auth' => [
                    'user'     => $this->getIo()->ask('user', $definedOptions['auth']['user'] ?? null),
                    'password' => $this->getIo()->ask('password or token', $definedOptions['auth']['password'] ?? null),
                ],
            ];
        }

        $options = array_merge(
            $options,
            $this->askCommitterOptions($definedOptions['committer'] ?? []),
            $this->askProviderOption($definedOptions['provider'] ?? [], true),
            $this->askLabelOptions($definedOptions['labels'] ?? [], true)
        );

        return $options;
    }

    /**
     * @param array $default
     *
     * @return array
     */
    private function askCommitterOptions(array $default) : array
    {
        $this->getIo()->text(
            [
                'Some <info>git pr</info> commands may need to add some commits.',
                'In such a case we will set your user.name and user.email using <info>git config</info> command.',
            ]
        );

        return [
            'committer' => [
                'name'  => $this->getIo()->ask('user.name', $default['name'] ?? null),
                'email' => $this->getIo()->ask('user.email', $default['email'] ?? null),
            ],
        ];
    }

    /**
     * @param array $default
     * @param bool  $askConfirmation
     *
     * @return array
     */
    private function askLabelOptions(array $default, bool $askConfirmation = false) : array
    {
        $this->getIo()->text(
            [
                'The <info>git pr merge</info> command allows you to tag the merge commit.',
                'The tag will be guessed according to the information contained in the pull request.',
                'For example, if for example the pull request contains an <info>enhancement</info> label, we will update the MINOR version of the previous tag.',
                'If no label are defined, it will search in the pull request title.',
                '<comment>The best place to store this information is in the shared file.</comment>',
            ]
        );

        if ($askConfirmation && !$this->getIo()->confirm('Do you want to define those label ?', false)) {
            return [];
        }

        return [
            'labels' => [
                'patch' => $this->getIo()->ask('patch', $default['patch'] ?? 'bug'),
                'minor' => $this->getIo()->ask('minor', $default['minor'] ?? 'enhancement'),
                'major' => $this->getIo()->ask('major', $default['major'] ?? 'bc-break'),
            ],
        ];
    }

    /**
     * @param string[] $default
     * @param bool     $askConfirmation
     *
     * @return array
     */
    private function askProviderOption(array $default, bool $askConfirmation = false) : array
    {
        $this->getIo()->text(
            [
                'The <info>provider</info> config contains every information about your remote repository.',
                'Specifying it will help guessing how to retrieve pull request information.',
                '<comment>The best place to store this information is in the shared file.</comment>',
            ]
        );

        if ($askConfirmation && !$this->getIo()->confirm('Do you want to define the provider ?', false)) {
            return [];
        }

        $choices       = array_keys(self::$defaultHosts);
        $defaultChoice = in_array($default['name'], $choices, true) ? $default['name'] : null;
        $provider      = $this->getIo()->choice('provider', $choices, $defaultChoice);

        $apiBaseUrl = null;
        $host       = self::$defaultHosts[$provider];
        if (array_key_exists('name', $host)
            && $this->getIo()->confirm(sprintf('Is your remote repository located on %s ?', $host['name']))
        ) {
            $apiBaseUrl = $host['paas'];
        }

        if (null === $apiBaseUrl) {
            $question   = 'What is your remote repository base url ?';
            $apiBaseUrl = $this->getIo()->ask($question, $host['self_hosted']).$host['api_base_uri'];
        }

        return [
            'provider' => [
                'name'         => $provider,
                'api'          => $apiBaseUrl,
                'clone_method' => $this->askCloneMethod($default['clone_method'] ?? ''),
                'repository'   => $this->getIo()->ask(
                    'What is your repository name ?',
                    $default['repository'] ?? 'organization/repo'
                ),
            ],
        ];
    }

    /**
     * @param string $default
     *
     * @return string
     */
    private function askCloneMethod(string $default) : string
    {
        return $this->getIo()->choice('Clone method', ['https', 'ssh'], '' === $default ? null : $default);
    }
}
