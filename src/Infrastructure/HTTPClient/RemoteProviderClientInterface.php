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

namespace GitPullRequest\Infrastructure\HTTPClient;

/**
 * Interface.
 */
interface RemoteProviderClientInterface
{
    /**
     * @param int $pullRequestNumber
     *
     * @return array
     */
    public function getPullRequest(int $pullRequestNumber);
}
