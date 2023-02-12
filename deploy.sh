#!/usr/bin/env bash

# Directory to self https://stackoverflow.com/a/246128/933065
SELF_PATH="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"

# make sure the github-action has the required packages.
composer install

if [[ -z "${STOCKROOM_URL}" ]]; then
  echo "Set the STOCKROOM_URL in your github secret"
  exit 1
fi
if [[ -z "${STOCKROOM_USER}" ]]; then
  echo "Set the STOCKROOM_USER in your github secret"
  exit 1
fi
if [[ -z "${STOCKROOM_PASS}" ]]; then
  echo "Set the STOCKROOM_PASS in your github secret"
  exit 1
fi

# Allow some ENV variables to be customized
if [[ -z "${SLUG}" ]]; then
  SLUG=$(basename $(pwd))
fi

if [[ -z "${BUILD_DIR}" ]]; then
  BUILD_DIR=$(pwd)
fi
BUILD_DIR=${BUILD_DIR%/} # Strip trailing slash, always.


echo "BUILD_DIR is ${BUILD_DIR}"
echo "SLUG is ${SLUG}"

# Determine the version.
if [[ -f "${BUILD_DIR}/style.css" ]]; then
  VERSION=$(grep  'Version:.*' "${BUILD_DIR}/style.css" | sed -E "s/.* ([.0-9])/\\1/")
  echo "Theme version is ${VERSION}"
else
  VERSION=$(grep  'Version:.*' "${BUILD_DIR}/${SLUG}.php" | sed -E "s/.* ([.0-9])/\\1/")
  echo "Plugin version is ${VERSION}"
fi
if [[ -z "${VERSION}" ]]; then
  echo "Cannot determine version. It should be in ${SLUG}.php for plugins or style.css for themes."
  exit 1
fi

# Check for readme file.
if [[ ! -f "${BUILD_DIR}/readme.txt" ]]; then
  echo "Cannot find readme.txt file. It is required."
  exit 1
fi
README_FILE="${BUILD_DIR}/readme.txt"

# Full path to the zip file.
ZIP_FILE="${BUILD_DIR}/${SLUG}.${VERSION}.zip"

# Build the list of excludes.

if [[ -f ${BUILD_DIR}/.distignore ]]; then
  ZIP_EXCLUDES=() # will hold the ignore files.
  while IFS= read -r IGNORE_FILE; do
    if [[ ${IGNORE_FILE} == \#* ]]; then
      continue # ignore lines with a comment.
    fi
    if [[ -d "${BUILD_DIR}/${IGNORE_FILE}" ]]; then
      ZIP_EXCLUDES+="${SLUG}/${IGNORE_FILE}/* " # directories
    fi
    # Match files and patterns.
    ZIP_EXCLUDES+="${SLUG}/${IGNORE_FILE} "
  done < ${BUILD_DIR}/.distignore

  ZIP_EXCLUDES="-x ${ZIP_EXCLUDES[@]}"
else
  ZIP_EXCLUDES=''
  echo "No .distignore file found. Including everything in the zip."
fi

# Go up one dir. Only way to proper to make sure ZIP uses the correct directory.
cd "${BUILD_DIR}/../"

# Finally zip it up.
set -o noglob #https://stackoverflow.com/a/11456496/933065
zip -r -q ${ZIP_FILE} ./$( basename ${BUILD_DIR}) ${ZIP_EXCLUDES}
set +o noglob # the `*` at the end of directories kept expanding.
echo "Created zip file in ${ZIP_FILE}"

$SELF_PATH/deploy.php "${STOCKROOM_URL}" "${STOCKROOM_USER}" "${STOCKROOM_PASS}" "${VERSION}" -s "${SLUG}" -z "${ZIP_FILE}" -r "${README_FILE}"
