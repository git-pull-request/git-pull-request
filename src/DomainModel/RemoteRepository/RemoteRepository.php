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

namespace GitPullRequest\DomainModel\RemoteRepository;

use InvalidArgumentException;

/**
 * Representation of a remote repository.
 */
final class RemoteRepository implements RemoteRepositoryInterface
{
    /** @var string */
    private $cloneURL;
    /** @var string */
    private $defaultBranch;
    /** @var string */
    private $htmlURL;
    /** @var string */
    private $name;
    /** @var string */
    private $sshURL;

    /**
     * RemoteRepository constructor.
     *
     * @param string $cloneURL
     * @param string $defaultBranch
     * @param string $htmlURL
     * @param string $name
     * @param string $sshURL
     */
    public function __construct(string $cloneURL, string $defaultBranch, string $htmlURL, string $name, string $sshURL)
    {
        $this->cloneURL      = $cloneURL;
        $this->defaultBranch = $defaultBranch;
        $this->htmlURL       = $htmlURL;
        $this->name          = $name;
        $this->sshURL        = $sshURL;
    }

    /** @return string */
    public function getHtmlURL() : string
    {
        return $this->htmlURL;
    }

    /**
     * @param string $cloneMethod
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getCloneURLForMethod(string $cloneMethod) : string
    {
        if ('ssh' === $cloneMethod) {
            return $this->sshURL;
        }
        if ('https' === $cloneMethod) {
            return $this->cloneURL;
        }

        throw new InvalidArgumentException('Unknown clone method. Must be one of ssh or https.');
    }
}
