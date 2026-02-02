#!/bin/bash

# Logger Function
log() {
  local message="$1"
  local type="$2"
  local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
  local color
  local endcolor="\033[0m"

  case "$type" in
    "info") color="\033[38;5;79m" ;;
    "success") color="\033[1;32m" ;;
    "error") color="\033[1;31m" ;;
    *) color="\033[1;34m" ;;
  esac

  echo -e "${color}${timestamp} - ${message}${endcolor}"
}

# Error handler function
handle_error() {
  local exit_code=$1
  local error_message="$2"
  log "Error: $error_message (Exit Code: $exit_code)" "error"
  exit $exit_code
}

# unzip function
unzip_file() {
  local zip_path="$1"
  local dest_path="$2"
  if [ ! -f "$zip_path" ]; then
    handle_error 1 "Zip file not found: $zip_path"
  fi

    if [ -f "$dest_path" ]; then
      rm -Rf "$dest_path"
    fi

  mkdir -p "$dest_path"
  unzip -o "$zip_path" -d "$dest_path" || handle_error $? "Failed to unzip $zip_path"
  log "Unzipped $zip_path to $dest_path" "success"
}

move_files() {
  local src_path="$1"
  local dest_path="$2"
  if [ ! -d "$src_path" ]; then
    handle_error 1 "Source directory not found: $src_path"
  fi
  mv "$src_path" "$dest_path" || handle_error $? "Failed to move $src_path to $dest_path"
  log "Moved $src_path to $dest_path" "success"
}

setup_laravel() {
  local project_path="$1"
  if [ ! -d "$project_path" ]; then
    handle_error 1 "Project directory not found: $project_path"
  fi
  cd "$project_path" || handle_error $? "Failed to change directory to $project_path"

  # Install Composer dependencies
  composer install --no-interaction --prefer-dist || handle_error $? "Composer install failed"

  # Copy .env.example to .env
  cp .env.example .env || handle_error $? "Failed to copy .env.example to .env"

  # Set permissions
  chmod -R 775 storage bootstrap/cache || handle_error $? "Failed to set permissions"

  # Generate application key
  php artisan key:generate || handle_error $? "Failed to generate application key"

  php artisan migrate --force || handle_error $? "Database migration failed"

  php artisan db:seed --force || handle_error $? "Database seeding failed"

  #add_local_composer_repository_on_composer_json_seed "/home/rotaz/rotaz-projects/filament-accounts"

  composer require rotaz/filament-accounts:* || handle_error $? "Failed to require filament-accounts package"

  log "Laravel setup completed successfully in $project_path" "success"

  php artisan filament-accounts:install || handle_error $? "Failed to install Filament Accounts"

  php artisan migrate:refresh --seed || handle_error $? "Database refresh and seeding failed"

  npm install && npm run build || handle_error $? "NPM install or build failed"

  log "Filament Accounts installed and database refreshed with seeding." "success"

}

add_local_composer_repository_on_composer_json_seed() {
  local repo_path="$1"
  if [ ! -f "composer.json" ]; then
    handle_error 1 "composer.json not found in the current directory"
  fi
  jq --arg path "$repo_path" '.repositories += [{"type": "path", "url": $path}]' composer.json > composer.tmp.json && mv composer.tmp.json composer.json || handle_error $? "Failed to add local repository to composer.json"
  log "Added local Composer repository: $repo_path" "success"
}

add_composer_repository() {
  local repo_url="$1"
  composer config repositories.custom-repo "$repo_url" || handle_error $? "Failed to add Composer repository $repo_url"
  log "Added Composer repository: $repo_url" "success"
}


# Define experimental features
LARAMENT_PATH="/media/rotaz/projects/rotaz/workspace/larament-3.zip"
EXPERIMENTAL_PATH="/media/rotaz/projects/rotaz/workspace/build"
PROJECT_PATH="/media/rotaz/projects/rotaz/workspace/"
PROJECT_NAME="$1"
#PROJECT_NAME="OK"

log "Starting the runner script..." "info"
unzip_file "$LARAMENT_PATH" "$EXPERIMENTAL_PATH"
move_files "$EXPERIMENTAL_PATH/larament" "$PROJECT_PATH/$PROJECT_NAME"
setup_laravel "$PROJECT_PATH/$PROJECT_NAME"

log "Runner script completed successfully." "success"
