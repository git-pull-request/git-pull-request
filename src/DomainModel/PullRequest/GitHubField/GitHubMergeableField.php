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

namespace GitPullRequest\DomainModel\PullRequest\GitHubField;

/**
 * Possible values for GitHub's "mergeable" field.
 */
final class GitHubMergeableField extends AbstractGitHubPullRequestField
{
    const UNKNOWN       = null;
    const MERGEABLE     = true;
    const NOT_MERGEABLE = false;
}
