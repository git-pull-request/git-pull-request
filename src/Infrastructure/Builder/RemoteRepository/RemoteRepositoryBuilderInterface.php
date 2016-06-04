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

namespace GitPullRequest\Infrastructure\Builder\RemoteRepository;

/**
 * Required methods to create a repository model.
 */
interface RemoteRepositoryBuilderInterface
{
    /** @return string */
    public function buildCloneUrl() : string;

    /** @return string */
    public function buildDefaultBranch() : string;

    /** @return string */
    public function buildHtmlURL() : string;

    /** @return string */
    public function buildName() : string;

    /** @return string */
    public function buildSshURL() : string;
}
