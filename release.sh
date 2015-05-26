#!/bin/bash

# bundle a release and push to WordPress plugin directory.
# modified from https://github.com/publicmediaplatform/pmp-wordpress/blob/master/release.sh

RELEASE_DIR=release
SVN_PATH=$RELEASE_DIR/svn
SVN_REPO=http://plugins.svn.wordpress.org/doubleclick-for-wp/

# Simple colors
ESC_SEQ="\x1b["
COL_RESET=$ESC_SEQ"39;49;00m"
COL_RED=$ESC_SEQ"31;01m"
COL_GREEN=$ESC_SEQ"32;01m"
COL_YELLOW=$ESC_SEQ"33;01m"
COL_BLUE=$ESC_SEQ"34;01m"
COL_MAGENTA=$ESC_SEQ"35;01m"
COL_CYAN=$ESC_SEQ"36;01m"

# which files go into the release?
GLOBIGNORE=*
WHITELIST=(js/* dfw-*.php dfw.php readme.txt)
BLACKLIST=(docs/* release/* mkdocs.yml readme.md .* *.sh)

# (1) check branch state of this git repo
REMOTES=`git ls-remote --quiet`
CURRENT=`git rev-parse HEAD`
IS_MASTER=`echo "$REMOTES" | grep "refs/heads/master" | grep $CURRENT | awk '{print $2;}'`
IS_TAG=`echo "$REMOTES" | grep "refs/tags" | grep $CURRENT | awk '{print $2;}'`
if [[ $IS_MASTER == "" && $IS_TAG == "" ]]
then
  echo ""
  echo ""
  echo -e "${COL_RED}[Error]${COL_RESET}: Bad release state for git repo."
  echo "Make sure you've checked out a git-tag or latest-master before releasing."
  echo ""
  echo ""
  exit 1
fi

# (2) check dirty/untracked state of this git repo
IS_DIRTY=`git status --porcelain 2> /dev/null`
if [[ $IS_DIRTY != "" ]]
then
  echo ""
  echo ""
  echo -e "${COL_RED}[Error]${COL_RESET}: This branch is dirty, or has untracked changes."
  echo "Commit or discard changes before release."
  echo ""
  echo ""
  exit 1
fi

# (3) make sure we know what we're doing
WHICH_TEXT="[master] and [$(echo $IS_TAG | sed -e 's/^refs\/tags\///')]"
if [[ $IS_MASTER == "" ]]; then WHICH_TEXT="[$(echo $IS_TAG | sed -e 's/^refs\/tags\///')]"; fi
if [[ $IS_TAG == "" ]]; then WHICH_TEXT="[master]"; fi
read -p "Really release plugin from $WHICH_TEXT? " -n 1 -r
echo ""
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
  echo "(no changes made)"
  echo ""
  exit 0
fi

# (4) init and update svn repo
if [[ ! -d $SVN_PATH ]]
then
  echo " - checking out svn repo"
  OUT=`mkdir -p $SVN_PATH && svn checkout $SVN_REPO $SVN_PATH`
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
else
  echo " - updating svn repo"
  OUT=`cd $SVN_PATH && svn update`
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
fi

# (5) build zip
echo " - zipping up release/wp-release.zip"
OUT=`rm -f release/wp-release.zip`
OUT=`zip -r release/wp-release.zip . --include ${WHITELIST[@]} --exclude ${BLACKLIST[@]} -q`
if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

# (6) optionally write to /svn/trunk
if [[ $IS_MASTER != "" ]]
then
  TRUNK_PATH=$SVN_PATH/trunk

  # overwrite with unzip
  echo " - writing to $TRUNK_PATH"
  OUT=`rm -rf $TRUNK_PATH && unzip -o release/wp-release.zip -d $TRUNK_PATH`
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

  # stage all changes (adds and removes)
  OUT=`cd $TRUNK_PATH && svn st | grep '^\?' | awk '{print \$2}' | xargs svn add` # add all
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  OUT=`cd $TRUNK_PATH && svn st | grep '^\!' | awk '{print \$2}' | xargs svn rm` # remove all
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

  # make sure something has changed besides the autoloader hashes
  CHANGES=`cd $TRUNK_PATH && svn st | grep -v 'autoload\(_real\)\?\.php'`
  if [[ $CHANGES == "" ]]
  then
    echo "   no changes to commit for trunk"
    OUT=`cd $TRUNK_PATH && svn revert --recursive .`
    if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  else
    echo " - committing $TRUNK_PATH (slow) ..."
    OUT=`cd $TRUNK_PATH && svn commit -m "update trunk to git $CURRENT"`
    if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  fi
fi

# (7) optionally write to /svn/tags/0.0.0
if [[ $IS_TAG != "" ]]
then
  WP_TAG=`echo $IS_TAG | sed -e 's/^refs\/tags\///' -e 's/^v//'`
  TAG_PATH=$SVN_PATH/tags/$WP_TAG

  # overwrite with unzip
  echo " - writing to $TAG_PATH"
  OUT=`rm -rf $TAG_PATH && unzip -o release/wp-release.zip -d $TAG_PATH`
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

  # set the version
  echo " - updating dfw.php version to $WP_TAG"
  OUT=`sed "s/\* Version:.*$/* Version: $WP_TAG/" $TAG_PATH/dfw.php > dfw.php.new && mv dfw.php.new $TAG_PATH/dfw.php`
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

  # stage all changes (adds and removes)
  OUT=`cd $SVN_PATH/tags && svn st | grep '^\?' | awk '{print \$2}' | xargs svn add` # add all
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  OUT=`cd $SVN_PATH/tags && svn st | grep '^\!' | awk '{print \$2}' | xargs svn rm` # remove all
  if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi

  # make sure something has changed besides the autoloader hashes
  CHANGES=`cd $SVN_PATH/tags && svn st | grep -v 'autoload\(_real\)\?\.php'`
  if [[ $CHANGES == "" ]]
  then
    echo "   no changes to commit for $TAG_PATH"
    OUT=`cd $SVN_PATH/tags && svn revert --recursive .`
    if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  else
    echo " - committing $TAG_PATH (slow) ..."
    OUT=`cd $SVN_PATH/tags && svn commit -m "update $WP_TAG to git $CURRENT"`
    if [[ $? -ne 0 ]]; then echo "$OUT" && exit 1; fi
  fi
fi

# success
echo ""
echo -e "${COL_GREEN}all done!${COL_RESET}"  