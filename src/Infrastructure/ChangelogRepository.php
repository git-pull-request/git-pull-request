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

namespace GitPullRequest\Infrastructure;

use GitPullRequest\DomainModel\PullRequest\PullRequestInterface;
use GitPullRequest\Git\Exception\RuntimeException;
use GitPullRequest\Git\Git;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Handle CHANGELOG.md files.
 */
final class ChangelogRepository
{
    /** @var string[] */
    private static $changelogUrlFormat = [
        'bitbucket' => '{repository_html_url}/branches/compare/{base}%0D{head}',
        'github'    => '{repository_html_url}/compare/{base}...{head}',
        'gitlab'    => '{repository_html_url}/compare/{base}...{head}',
    ];
    /** @var string[] */
    private static $tagUrlFormat = [
        'bitbucket' => '{repository_html_url}/src?at={tag}',
        'github'    => '{repository_html_url}/tags/{tag}',
        'gitlab'    => '{repository_html_url}/tags/{tag}',
    ];

    /** @var string */
    private $file;
    /** @var Git */
    private $git;
    /** @var string */
    private $projectDirectory;
    /** @var string */
    private $provider;

    /**
     * @param string $projectDirectory
     * @param string $provider
     */
    public function __construct(string $projectDirectory, string $provider)
    {
        $this->projectDirectory = $projectDirectory;
        $this->file             = $projectDirectory.DIRECTORY_SEPARATOR.'CHANGELOG.md';
        $this->git              = new Git();
        $this->provider         = $provider;
    }

    /** @return string */
    public function getFile() : string
    {
        return $this->file;
    }

    /**
     * This method should not be located in the repository.
     *
     *
     * @param PullRequestInterface $pullRequest
     *
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function addLine(PullRequestInterface $pullRequest)
    {
        $tags        = $this->getTags();
        $previousTag = $pullRequest->getPreviousTag($tags);
        $tag         = $pullRequest->getTag($tags);
        $tagUrl      = str_replace(
            ['{repository_html_url}', '{tag}'],
            [
                $pullRequest->getBase()->getRemoteRepository()->getHtmlURL(),
                (string) $tag,
            ],
            self::$tagUrlFormat[$this->provider]
        );

        $changelog = '-';
        if (null !== $previousTag) {
            $changelog = str_replace(
                ['{repository_html_url}', '{base}', '{head}'],
                [
                    $pullRequest->getBase()->getRemoteRepository()->getHtmlURL(),
                    (string) $previousTag,
                    (string) $tag,
                ],
                '[Changelog]('.self::$changelogUrlFormat[$this->provider].')'
            );
        }
        $columns = [
            'Tag'          => sprintf('[%s](%s)', (string) $tag, $tagUrl),
            'Release date' => date('Y-m-d H:i:s'),
            'PR'           => sprintf('[#%d](%s)', $pullRequest->getNumber(), $pullRequest->getHtmlURL()),
            'Contributor'  => sprintf(
                '[![%1$s](%2$s)](%3$s)[%1$s](%3$s)',
                $pullRequest->getAuthor()->getName(),
                $pullRequest->getAuthor()->getAvatarURL(),
                $pullRequest->getAuthor()->getProfileURL()
            ),
            'Message'   => $pullRequest->getTitle(),
            'Changelog' => $changelog,
        ];

        $this->addContent('|'.implode('|', $columns).'|'.PHP_EOL);
    }

    /**
     * @param string $content
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    private function addContent(string $content)
    {
        if (!is_file($this->file)) {
            $content = $this->header().$content;
        }

        (new Filesystem())->dumpFile($this->file, $content);
    }

    /** @return string */
    private function header() : string
    {
        return <<<'EOF'
# Change Log

|   Tag   | Release date | PR | Contributor | Message | Diff |
|:-------:| ------------ | --:|:-----------:| ------- |:----:|
EOF;
    }

    /**
     * @return \SemVer\SemVer\Version[]
     */
    private function getTags() : array
    {
        $currentDirectory = getcwd();
        chdir($this->projectDirectory);
        try {
            $tags = $this->git->getTags();
        } catch (RuntimeException $exception) {
            $tags = [];
        }
        chdir($currentDirectory);

        return $tags;
    }
}
