# Theme JSON Auto Export

A WordPress plugin that automatically exports your theme's `theme.json` file to a shared directory for use with headless/hybrid setups like Nuxt.js.

## 📋 Overview

This plugin bridges WordPress block themes with external frontend frameworks by automatically syncing your `theme.json` design tokens (colors, typography, spacing) to a shared directory that your frontend can read.

**Perfect for:**
- Headless WordPress setups
- WordPress + Nuxt.js hybrid projects
- WordPress + Next.js hybrid projects
- Any setup where you want to sync WordPress design tokens with your frontend

## ✨ Features

- ✅ **Automatic Export** - Exports theme.json when theme changes
- ✅ **Manual Export** - Quick toolbar button for manual exports
- ✅ **Status Notifications** - Clear admin notices about export status
- ✅ **Error Handling** - Detailed error messages for troubleshooting
- ✅ **Performance Optimized** - Checks only every 5 minutes to avoid overhead
- ✅ **Docker-Ready** - Works seamlessly with Docker volumes

## 🚀 Installation

### Standard Installation

1. Download the plugin file `theme-json-auto-export.php`
2. Upload to `/wp-content/plugins/theme-json-auto-export/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Ensure `/shared/` directory exists and is writable

### Docker Installation

If using Docker, add this to your `docker-compose.yml`:

```yaml
services:
  wordpress:
    volumes:
      - ./shared:/shared  # Create shared directory for theme.json
```

Then:
```bash
docker compose up -d
```

## 📁 Directory Structure

Your project should look like this:

```
your-project/
├── shared/
│   └── theme.json          ← Plugin exports here
├── nuxt/                   ← Your Nuxt app reads from here
│   ├── scripts/
│   │   └── syncTheme.js   ← Converts to tailwind.config.js
│   └── tailwind.config.js
└── wordpress/
    └── wp-content/
        ├── plugins/
        │   └── theme-json-auto-export/
        │       └── theme-json-auto-export.php
        └── themes/
            └── your-theme/
                └── theme.json  ← Source file
```

## 🔧 Configuration

### Required Setup

1. **Create `/shared` directory:**
   ```bash
   mkdir shared
   chmod 755 shared
   ```

2. **Ensure your theme has a `theme.json` file:**
   ```
   /wp-content/themes/your-theme/theme.json
   ```

3. **Check permissions:**
   ```bash
   # Make sure WordPress can write to /shared
   chown -R www-data:www-data shared/
   ```

### Docker Setup

Add the volume mount to your `docker-compose.yml`:

```yaml
services:
  wordpress:
    image: wordpress:latest
    volumes:
      - ./wordpress:/var/www/html
      - ./shared:/shared  # ← Add this line
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_NAME: wordpress
```

## 🎯 Usage

### Automatic Export

The plugin automatically exports your `theme.json` in these scenarios:

1. **Theme Switch** - When you activate a different theme
2. **Admin Load** - Checks every 5 minutes while in admin
3. **Plugin Activation** - Immediately on plugin activation

### Manual Export

Click the **🔄 Export theme.json** button in the WordPress admin toolbar (top right) to manually trigger an export.

## 🔍 Status Messages

The plugin shows clear status messages in the WordPress admin:

### Success
```
Theme JSON Export: Successfully exported at 2025-11-13 11:48:04
```

### Errors

**theme.json not found:**
```
Theme JSON Export ERROR: theme.json file not found in your active theme directory.
```
**Solution:** Make sure your active theme has a `theme.json` file.

**Shared directory not found:**
```
Theme JSON Export ERROR: /shared directory not found. Check your docker-compose.yml volumes.
```
**Solution:** Create the `/shared` directory or add the volume mount.

**Not writable:**
```
Theme JSON Export ERROR: /shared directory is not writable. Check permissions.
```
**Solution:** Run `chmod 755 shared` or `chown www-data:www-data shared/`

**Copy failed:**
```
Theme JSON Export ERROR: Failed to copy theme.json.
```
**Solution:** Check file permissions and disk space.

## 🐛 Troubleshooting

### Export Not Working?

1. **Check the logs:**
   ```bash
   tail -f wp-content/debug.log
   ```

2. **Enable WordPress debug mode** in `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

3. **Verify directory exists:**
   ```bash
   ls -la /shared
   ```

4. **Check permissions:**
   ```bash
   # From inside WordPress container:
   docker exec -it wordpress-container bash
   ls -la /shared
   touch /shared/test.txt  # Test if writable
   ```

### Common Issues

**Issue:** "theme.json not found"
- **Cause:** Your theme doesn't have a `theme.json` file
- **Fix:** Add a `theme.json` file to your theme root

**Issue:** "/shared directory not found"
- **Cause:** Volume not mounted in Docker
- **Fix:** Add volume to docker-compose.yml and restart containers

**Issue:** "directory is not writable"
- **Cause:** Permission issue
- **Fix:** `chmod 755 shared` or `chown www-data:www-data shared/`

## 🔗 Integration with Nuxt.js

After the plugin exports to `/shared/theme.json`, use it in your Nuxt app:

### 1. Create Sync Script

```javascript
// nuxt/scripts/syncTheme.js
import fs from 'fs'
import path from 'path'

const themeJsonPath = path.join(__dirname, '../../shared/theme.json')
const theme = JSON.parse(fs.readFileSync(themeJsonPath, 'utf-8'))

// Convert to Tailwind config
const tailwindConfig = convertThemeToTailwind(theme)
fs.writeFileSync('./tailwind.config.js', generateConfig(tailwindConfig))

console.log('✅ Theme synced!')
```

### 2. Add NPM Script

```json
{
  "scripts": {
    "sync-theme": "node scripts/syncTheme.js",
    "dev": "npm run sync-theme && nuxt dev"
  }
}
```

### 3. Run Sync

```bash
npm run sync-theme
```

Now your Nuxt app uses the same design tokens as WordPress! 🎨

## 📊 How It Works

```
WordPress Theme
     ↓
  theme.json
     ↓
[Plugin Exports]
     ↓
/shared/theme.json
     ↓
[Nuxt Script Reads]
     ↓
tailwind.config.js
     ↓
Your Nuxt App ✨
```

## 🤝 Use Cases

### Headless WordPress + Nuxt
1. Design in WordPress Gutenberg
2. Plugin exports theme.json
3. Nuxt reads and converts to Tailwind
4. Frontend matches WordPress exactly

### Hybrid Setup
1. WordPress manages content pages
2. Nuxt handles custom pages
3. Both use same design tokens
4. Consistent styling across entire site

## 📝 Requirements

- WordPress 5.8+ (for theme.json support)
- PHP 7.4+
- Write access to `/shared` directory
- Active block theme with theme.json

## 🔐 Security

- Only administrators can manually export
- File operations use WordPress core functions
- No user input accepted
- Directory traversal prevented

## 📄 License

This plugin is open source and free to use in your projects.

## 👨‍💻 Author

**Ryan Grace**

## 🐞 Bug Reports

Found a bug? Check the WordPress debug log at `wp-content/debug.log` and look for entries starting with `[Theme JSON Export]`.

## 💡 Tips

- **Performance:** The plugin checks only every 5 minutes in admin to avoid performance issues
- **Docker:** Make sure to mount `/shared` as a volume in your docker-compose.yml
- **Testing:** Use the manual export button to test the sync immediately
- **Monitoring:** Keep an eye on the admin notices to ensure exports are working

## 🚀 Related Tools

Works great with:
- [Nuxt.js](https://nuxt.com) - Vue.js framework
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS
- [WordPress Block Themes](https://developer.wordpress.org/block-editor/how-to-guides/themes/block-theme-overview/) - FSE themes

## 📚 Further Reading

- [WordPress theme.json documentation](https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-json/)
- [Headless WordPress guide](https://developer.wordpress.org/rest-api/)
- [Nuxt.js documentation](https://nuxt.com/docs)

---

**Made with ❤️ for headless WordPress projects**
