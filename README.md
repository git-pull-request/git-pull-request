git-pull-request
================

The missing `git pr` commands written in PHP.

Installation
------------

Download the `git-pull-request.phar` (we will provide it soon), rename it to `git-pr` and put it somewhere accessible by
your `PATH`.

Setup your repository to use `git pr`
-------------------------------------

**Provides shared information**

Some information required by `git pr` may be shared across your team.  
Use the `git pr init shared` command and follow the instructions.  
It will produce a `.git-pull-request.yml` file in your project directory which you can commit and share.

**Defines private information**

`git pr` needs to authorizations to access your git provider API.  
Use the `git pr init local` command and follow the instructions.  
It will produce a `.git-pull-request.yml` file inside the `.git` subdirectory. That way you can be sure it will never be 
shared with others

Setup global information
------------------------

If you have a lot of repositories, it may be painful to specify your credentials in every repository.  
Moreover, some commands may be run outside of a git working tree.  
For all those cases, you can use the `git pr init global` command and follow the instructions.  
It will produce a `.git-pull-request.yml` file inside your `HOME` directory.
