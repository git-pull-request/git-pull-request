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

namespace GitPullRequest\DomainModel\User;

/**
 * Representation of a remote repository.
 */
final class User implements UserInterface
{
    /** @var string */
    private $avatarURL;

    /** @var string */
    private $name;

    /** @var string */
    private $profileURL;

    /**
     * @param string $avatarURL
     * @param string $name
     * @param string $profileURL
     */
    public function __construct(string $avatarURL, string $name, string $profileURL)
    {
        $this->avatarURL  = $avatarURL;
        $this->name       = $name;
        $this->profileURL = $profileURL;
    }

    /** @return string */
    public function getAvatarURL() : string
    {
        return $this->avatarURL;
    }

    /** @return string */
    public function getName() : string
    {
        return $this->name;
    }

    /** @return string */
    public function getProfileURL() : string
    {
        return $this->profileURL;
    }
}
