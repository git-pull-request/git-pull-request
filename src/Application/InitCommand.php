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
use GitPullRequest\Git\Git;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper to generate config files.
 */
final class InitCommand extends AbstractCommand
{
    /** @var Config */
    private $config;
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

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('init')
            ->setAliases(['config', 'configure', 'setup'])
            ->setDescription(
                'Generates/Updates a configuration file'
            )
            ->addArgument('file-type', InputArgument::OPTIONAL, 'Type of file to generate')
            ->setHelp(
                <<<'EOT'
The <info>git pr %command.name%</info> command creates/updates a configuration
file (e.g <comment>.git-pull-request.yml</comment>).

It can generate 1 out of 3 configuration files:
* <info><HOME>/.git-pull-request.yml</info>
        can store global information such as the default credentials
        for all your provider (Bitbucket, GitHub, GitLab)
* <info><WORKING_DIR>/.git-pull-request.yml</info>
        can store generic information about your repository:
        - its full-name
        - the provider to use
        - the way we can detect if a pull request is a PATCH, MINOR or MAJOR version
        - ...
        This file should be shared across the team
* <info><WORKING_DIR>/.git/.git-pull-request.yml</info>
        can store your credentials to connect to the current repository's provider.
        It may also override information stored in the <comment>shared</comment> file but it is not recommended.
EOT
            );
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
                $this->config->saveGlobalOptions($this->options);
                break;
            case 'shared':
                $this->config->saveSharedOptions($this->options);
                break;
            case 'local':
                $this->config->saveLocalOptions($this->options);
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
        $definedOptions = $this->config->getGlobalOptions();
        $providers      = ['bitbucket', 'github', 'gitlab'];
        $options        = [];
        foreach ($providers as $provider) {
            if ($this->getIo()->confirm(sprintf('Do you want to configure a global %s account ?', $provider))) {
                $current                         = $definedOptions['providers'][$provider];
                $options['providers'][$provider] = [
                    'user'     => $this->getIo()->ask($provider.' user', $current['user'] ?? null),
                    'password' => $this->getIo()->ask($provider.' password or token', $current['password'] ?? null),
                ];
            }
        }

        $options = array_merge(
            $options,
            $this->askCommitOptions($definedOptions['commits'] ?? [], true),
            $this->askLabelOptions($definedOptions['labels'] ?? [], false)
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
        $definedOptions = $this->config->getSharedOptions();
        $options        = [];

        $options = array_merge(
            $options,
            $this->askProviderOption($definedOptions['provider'] ?? [], true),
            $this->askLabelOptions($definedOptions['labels'] ?? [], true)
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
        $definedOptions = $this->config->getLocalOptions();
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
            $this->askCommitOptions($definedOptions['commits'] ?? [], true),
            $this->askProviderOption($definedOptions['provider'] ?? [], false),
            $this->askLabelOptions($definedOptions['labels'] ?? [], false)
        );

        return $options;
    }

    /**
     * @param array $previouslyDefinedOption
     * @param bool  $confirmDefaultValue
     *
     * @return array
     */
    private function askCommitOptions(array $previouslyDefinedOption, bool $confirmDefaultValue) : array
    {
        $this->getIo()->text(
            [
                'Some <info>git pr</info> commands may need to add some commits.',
                'In such a case we will set your user.name and user.email using <info>git config</info> command.',
            ]
        );

        if (!$this->getIo()->confirm('Do you want to define those information ?', $confirmDefaultValue)) {
            return [];
        }

        return [
            'commits' => [
                'name'  => $this->getIo()->ask('user.name', $previouslyDefinedOption['name'] ?? null),
                'email' => $this->getIo()->ask('user.email', $previouslyDefinedOption['email'] ?? null),
            ],
        ];
    }

    /**
     * @param array $previouslyDefinedOption
     * @param bool  $confirmDefaultValue
     *
     * @return array
     */
    private function askLabelOptions(array $previouslyDefinedOption, bool $confirmDefaultValue) : array
    {
        $this->getIo()->text(
            [
                'The <info>git pr merge</info> command allows you to tag the merge commit.',
                'The tag will be guessed according to the information contained in the pull request.',
                'For example, if for example the pull request contains an <info>enhancement</info> label, we will update the MINOR version of the previous tag.',
                'If no label are defined, it will search in the pull request title.',
            ]
        );

        if (!$this->getIo()->confirm('Do you want to define those label ?', $confirmDefaultValue)) {
            return [];
        }

        return [
            'labels' => [
                'patch' => $this->getIo()->ask('patch', $previouslyDefinedOption['patch'] ?? 'bug'),
                'minor' => $this->getIo()->ask('minor', $previouslyDefinedOption['minor'] ?? 'enhancement'),
                'major' => $this->getIo()->ask('major', $previouslyDefinedOption['major'] ?? 'bc-break'),
            ],
        ];
    }

    /**
     * @param string|null $previouslyDefinedOption
     * @param bool        $confirmDefaultValue
     *
     * @return array
     */
    private function askProviderOption($previouslyDefinedOption, bool $confirmDefaultValue = true) : array
    {
        $this->getIo()->text(
            [
                'The <info>provider</info> config contains every information about your remote repository.',
                'Specifying it will help guessing how to retrieve pull request information.',
            ]
        );

        if (!$this->getIo()->confirm('Do you want to define the provider ?', $confirmDefaultValue)) {
            return [];
        }

        $provider = $this->getIo()->choice(
            'provider',
            array_keys(self::$defaultHosts),
            $previouslyDefinedOption['name'] ?? null
        );

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
                'name'       => $provider,
                'api'        => $apiBaseUrl,
                'repository' => $this->getIo()->ask('What is your repository name ?', 'organization/repo'),
            ],
        ];
    }
}
