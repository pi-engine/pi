Pi Engine Github Usage
======================

Notes:
* ```upstream```: Pi Engine Repo at ```https://github.com/pi-engine/pi```
* ```origin``` or ```your repo```: the repo you create by forking Pi Engine at ```https://github.com/<your-account>/pi```
* ```local repo```: the working repo you clone from your repo 

Checkout from Pi Engine project (readonly)
------------------------------------------
* ```git clone git://github.com/pig-engine/pi```

Make a new fork
---------------
* Fork from [Pi Engine Repo](https://github.com/pi-engine/pi) following ![Image guide](https://raw.github.com/pi-asset/image/master/git-fork.png)


Working with forked repo
------------------------
* Checkout code to local computer as working repo: `git clone https://github.com/<your-account>/pi`
* Working with commits
  * Synchronize code from your repo: `git pull` or `git fetch`
  * Add local changes: `git add --all`
  * Commit local changes: `git commit -a -m 'Commit log message.'`
  * Push commits to your repo: `git push`
  * Revert the last commit before push: `git reset --soft HEAD`
  * Merge one specific commit from another branch: `git cherry-pick A`
  * Merge specific commits after A to B: `git cherry-pick A..B`
  * Merge specific commits from A through B: `git cherry-pick A^..B`
  * Quit merges: `git quit --merge`
* Working with branches
  * Check local branches: `git branch`
  * Create a local branch: `git branch -a <new-branch>`
  * Push a local branch to your repo: `git push`
  * Swtich to a branch: `git checkout <another-branch>`
  * Merge code from another branch: `git merge <another-branch>`
  * Delete a local branch: `git branch -d <old-branch>`
  * Delete a branch from your repo: `git push origin :<old-branch>`
* Working with tags
  * Check local branches: `git tag`
  * Create a local branch: `git tag -a <new-tag>`
  * Push local tags to your repo: `git push --tags`
  * Delete a local branch: `git tag -d <old-tag>`
  * Delete a tag from your repo: `git push origin :<old-tag>`

Working with upstream repo
--------------------------
* Add Pi Engine Repo as upstream: `git remote add upstream https://github.com/pi-engine/pi.git`
* Fetch changes from Pi Engine Repo: `git fetch upstream`
* Merge Pi Engine changes into local repo: `git merge upstream/<branch-name>`
* Synchronize your repo with Pi Engine Repo: `git merge upstream/<branch-name>` + `git push origin <branch-name>`


Pi Engine Github Skeleton
=========================

Pi Engine Core
----------------
* [pi-engine/pi](https://github.com/pi-engine/pi): Pi Engine core repo
  * [branch/master] (https://github.com/pi-engine/pi): Stable code in development
  * [branch/develop] (https://github.com/pi-engine/pi/tree/develop): Code in active development
  * [tag/release-{20130314}] (https://github.com/pi-engine/pi/tree/release-pi-day): Release tags
* [pi-engine/pi/wiki](https://github.com/pi-engine/pi/wiki): Pi Engine documents


Pi Engine Module
----------------
* [pi-module](https://github.com/pi-module): repos for modules
* Eeach module has its own repo, for instance [pi-module/tag](https://github.com/pi-module/tag) for module tag

Pi Engine Theme
---------------
* [pi-theme](https://github.com/pi-theme): repos for themes
* Each theme has its ownrepo, for instance [pi-theme/pi](https://github.com/pi-theme/pi) for theme pi

Pi Engine Asset
---------------
* [pi-asset](https://github.com/pi-asset): repos for assets: [images](https://github.com/pi-asset/image), [pdfs/files](https://github.com/pi-asset/file), videos, etc.
 
Pi Engine Extras
----------------
* [pi-extra](https://github.com/pi-extra): repos for extra components
