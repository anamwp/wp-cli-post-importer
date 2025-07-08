# WP CLI Post Importer #
**Contributors:** anamwp  
**Donate link:** https://anam.rocks  
**Tags:** wp-cli, import, posts, api, command-line  
**Requires at least:** 4.5  
**Tested up to:** 6.7.1  
**Requires PHP:** 5.6  
**Stable tag:** 0.1.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Import and manage posts from external APIs using WP-CLI commands. Fetches sample posts with featured images for testing and development.

## Description ##

WP CLI Post Importer is a WordPress plugin that provides WP-CLI commands to import and manage posts from external APIs. It's designed for developers who need to quickly populate their WordPress sites with sample content for testing and development purposes.

**Key Features:**

* Import posts from external APIs (currently supports DummyJSON Posts API)
* Automatic featured image generation and assignment using Picsum.photos
* Bulk import with pagination support
* Duplicate post detection and prevention
* Complete post cleanup including media and taxonomy terms
* Support for categories and tags assignment
* WP-CLI integration for command-line usage

**Data Source:**
The plugin fetches sample posts from https://dummyjson.com/posts, which provides realistic dummy content including titles, body text, and metadata perfect for testing WordPress themes and functionality.

**Perfect for:**
* Theme developers testing their designs
* Plugin developers needing sample content
* Site builders creating demo content
* Development and staging environments

## Installation ##

**Requirements:**
* WordPress 4.5 or higher
* PHP 5.6 or higher
* WP-CLI installed on your server

**Installation Steps:**

1. Upload the plugin files to the `/wp-content/plugins/wp-cli-post-importer/` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. The plugin will automatically register WP-CLI commands - no additional configuration needed

**Verify Installation:**
After activation, you can verify the commands are available by running:
```
wp help start
```

This should list the available import and delete commands provided by the plugin.

## WP-CLI Commands ##

The plugin provides four main WP-CLI commands for importing and managing posts:

**Import Commands:**

`wp start import-posts`
- Imports the first batch of posts from the API
- Includes featured image generation
- Checks for duplicate posts before importing

`wp start import-all-posts`
- Imports ALL available posts from the API using pagination
- Processes posts in batches of 30
- Recommended for bulk content generation

**Delete Commands:**

`wp start delete-posts`
- Deletes imported posts (first batch)
- Removes associated media files
- Cleans up unused categories and tags

`wp start delete-all-posts`
- Deletes ALL imported posts using pagination
- Complete cleanup including media and taxonomy terms
- Recommended for clearing all imported content

**Usage Examples:**

```bash
# Import a batch of sample posts
wp start import-posts

# Import all available posts for extensive testing
wp start import-all-posts

# Clean up by deleting imported posts
wp start delete-posts

# Remove all imported content completely
wp start delete-all-posts
```

**What Gets Imported:**
* Post title and content
* Randomly generated featured images (1200x800px from Picsum.photos)
* Tags and categories
* Proper post metadata
* Published status posts ready for viewing

## Frequently Asked Questions ##

### Do I need WP-CLI installed to use this plugin? ###

Yes, this plugin requires WP-CLI to be installed on your server. The plugin provides WP-CLI commands and cannot function without it. You can install WP-CLI by following the instructions at https://wp-cli.org/

### Where do the posts come from? ###

Posts are fetched from https://dummyjson.com/posts, which provides realistic dummy content including titles, body text, and metadata. This is perfect for testing and development purposes.

### Will this plugin create duplicate posts? ###

No, the plugin checks for existing posts with the same title before importing. If a post with the same title already exists, it will skip importing that post and show a warning message.

### What happens to the featured images when I delete posts? ###

When you delete posts using the delete commands, the plugin automatically removes all associated media files including featured images to keep your media library clean.

### Can I customize which posts get imported? ###

Currently, the plugin imports posts from the DummyJSON API as-is. For custom import functionality, you would need to modify the plugin code or create your own custom implementation.

### Is this plugin safe to use on production sites? ###

This plugin is designed for development and testing environments. While it includes safeguards against duplicate content, we recommend using it primarily on staging or development sites.

## Changelog ##

### 0.1.0 ###
* Initial release
* Added `wp start import-posts` command for importing posts from API
* Added `wp start delete-posts` command for cleaning up imported posts
* Added `wp start import-all-posts` command for bulk importing with pagination
* Added `wp start delete-all-posts` command for bulk deletion with pagination
* Featured image generation from Picsum.photos
* Automatic category and tag assignment
* Duplicate post detection and prevention
* Complete media cleanup on post deletion

## Upgrade Notice ##

### 0.1.0 ###
Initial release of WP CLI Post Importer. Install to get WP-CLI commands for importing sample posts with featured images.

## Technical Details ##

**API Endpoint:** https://dummyjson.com/posts
**Image Source:** https://picsum.photos/ (for featured images)
**Image Dimensions:** 1200x800 pixels
**Batch Size:** 30 posts per batch (for pagination commands)

**Post Structure:**
- Title: Imported from API
- Content: Full post body from API  
- Status: Published
- Author: Admin user (ID: 1)
- Featured Image: Randomly generated from Picsum.photos
- Categories/Tags: Assigned from API data

**File Structure:**
```
wp-cli-post-importer/
├── cli/
│   ├── class-wp-cli-post-importer-manage-post.php
│   └── class-wp-cli-post-importer-manage-posts.php
├── wp-cli-post-importer.php
└── readme.txt
```
