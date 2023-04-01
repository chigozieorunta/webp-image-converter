#!/bin/bash

# Set the name of your plugin file
PLUGIN_FILE="webp-image-converter.php"

# Set the name of the build directory
BUILD_DIR="build"

# Set the source directories you want to copy
SRC_DIRS="assets languages src"

# Create the build directory if it doesn't exist
if [ ! -d "$BUILD_DIR" ]; then
  mkdir "$BUILD_DIR"
else
  rm -r "$BUILD_DIR"
	mkdir "$BUILD_DIR"
fi

# Copy the source directories and plugin files to the build directory
for dir in $SRC_DIRS
do
  cp -r "$dir" "$BUILD_DIR/$dir"
done
cp "$PLUGIN_FILE" "$BUILD_DIR/$PLUGIN_FILE"
cp "readme.txt" "$BUILD_DIR/readme.txt"
cp "composer.json" "$BUILD_DIR/composer.json"

# Navigate to the build directory
cd "$BUILD_DIR"

# Remove the dev dependencies
composer install --no-dev

# Remove the composer.json and lock files
rm "composer.json"
rm "composer.lock"

# Confirm that the files have been copied successfully
echo "Build complete!"
