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

namespace GitPullRequest\Application\Merge;

use GitPullRequest\DomainModel\Branch\BranchInterface;
use GitPullRequest\DomainModel\Config\Config;
use GitPullRequest\DomainModel\PullRequest\PullRequestInterface;
use GitPullRequest\Git\Git;
use GitPullRequest\Infrastructure\ChangelogRepository;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class.
 */
final class MergePullRequestService
{
    /** @var Config */
    private $config;
    /** @var Git */
    private $git;
    /** @var string */
    private $projectDirectory;
    /** @var PullRequestInterface */
    private $pullRequest;

    /**
     * @param PullRequestInterface $pullRequest
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function __construct(PullRequestInterface $pullRequest)
    {
        $this->config           = new Config();
        $this->git              = new Git();
        $this->projectDirectory = $this->initProjectDirectory();
        $this->pullRequest      = $pullRequest;
    }

    public function prepare()
    {
        $base = $this->pullRequest->getBase();
        $head = $this->pullRequest->getHead();
        $this->cloneRepository($base);
        $currentDirectory = getcwd();
        $email            = $this->config->mustGet('committer.email');
        $name             = $this->config->mustGet('committer.name');
        $provider         = $this->config->mustGet('provider.name');
        $cloneMethod      = $this->config->mustGet(['provider.clone_method', "providers.$provider.clone_method"]);
        $headCloneURL     = $head->getRemoteRepository()->getCloneURLForMethod($cloneMethod);

        chdir($this->projectDirectory);
        $this->git->setUserEmail($email);
        $this->git->setUserName($name);
        $this->git->createBranch($head->getName(), $base->getName());
        $this->git->checkout($head->getName());
        $this->git->pull($headCloneURL, $head->getName());
        chdir($currentDirectory);
    }

    /**
     * @throws \GitPullRequest\Git\Exception\RuntimeException
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function addChangelog()
    {
        $changelog = new ChangelogRepository($this->projectDirectory, $this->config->mustGet('provider.name'));
        $changelog->addLine($this->pullRequest);

        $currentDirectory = getcwd();
        chdir($this->projectDirectory);
        $this->git->add($changelog->getFile());
        $this->git->commit('Add changelog');
        chdir($currentDirectory);
    }

    public function merge()
    {
        $currentDirectory = getcwd();
        chdir($this->projectDirectory);
        $this->git->checkout($this->pullRequest->getBase()->getName());
        $this->git->merge($this->pullRequest->getHead()->getName(), ['--squash']);
        $this->git->commit(sprintf('%s (#%d)', $this->pullRequest->getTitle(), $this->pullRequest->getNumber()));
        chdir($currentDirectory);
    }

    public function tag()
    {
        $currentDirectory = getcwd();
        chdir($this->projectDirectory);
        $tags = $this->git->getTags();
        $tag  = $this->pullRequest->getTag($tags);

        $this->git->createAnnotatedTag($tag, $this->pullRequest->getTitle());
        chdir($currentDirectory);
    }

    /**
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     *
     * @return string
     */
    private function initProjectDirectory() : string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'pr_');
        if (false === $tmpFile) {
            throw new IOException('Unable to create a directory inside your system\'s temporary directory.');
        }
        $fs = new Filesystem();
        if ($fs->exists($tmpFile)) {
            $fs->remove($tmpFile);
        }

        return $tmpFile;
    }

    /**
     * @param BranchInterface $base
     *
     * @throws \GitPullRequest\Git\Exception\RuntimeException
     * @throws \GitPullRequest\DomainModel\Exception\UndefinedConfigKeyException
     */
    private function cloneRepository(BranchInterface $base)
    {
        $provider    = $this->config->mustGet('provider.name');
        $cloneMethod = $this->config->mustGet(['provider.clone_method', "providers.$provider.clone_method"]);
        $this->git->cloneRepository(
            $base->getRemoteRepository()->getCloneURLForMethod($cloneMethod),
            $this->projectDirectory,
            '-q --single-branch --branch '.$base->getName()
        );
    }
}
