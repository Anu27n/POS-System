<?php
/**
 * POS System - Storage Link Creator
 * 
 * This script creates the storage symlink required for images to work.
 * Access this file via browser if images/logos are not displaying.
 * 
 * DELETE THIS FILE after the symlink is created for security!
 */

// Prevent direct access in production after link is created
$publicStoragePath = __DIR__ . '/storage';
$targetPath = dirname(__DIR__) . '/storage/app/public';

echo "<!DOCTYPE html><html><head><title>Storage Link Creator</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:600px;margin:50px auto;padding:20px;background:#f5f5f5;}";
echo ".box{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
echo ".success{color:#28a745;}.error{color:#dc3545;}.warning{color:#ffc107;}.info{color:#17a2b8;}";
echo "code{background:#eee;padding:2px 6px;border-radius:3px;}</style></head><body><div class='box'>";
echo "<h2>🔧 POS System - Storage Link Creator</h2>";

// Check current status
echo "<h3>Current Status:</h3>";
echo "<p><strong>Public Storage Path:</strong> <code>$publicStoragePath</code></p>";
echo "<p><strong>Target Path:</strong> <code>$targetPath</code></p>";

// Check if target exists
if (!is_dir($targetPath)) {
    echo "<p class='error'>❌ Target directory does not exist: $targetPath</p>";
    echo "<p>Please make sure the <code>storage/app/public</code> folder exists.</p>";
    echo "</div></body></html>";
    exit;
}

// Check if symlink already exists and works
if (is_link($publicStoragePath) && is_dir($publicStoragePath)) {
    echo "<p class='success'>✅ Storage symlink already exists and is working!</p>";
    echo "<p class='warning'>⚠️ <strong>Security Notice:</strong> Please delete this file (<code>create-storage-link.php</code>) now.</p>";
    echo "</div></body></html>";
    exit;
}

// Try to create the symlink
echo "<h3>Creating Storage Link...</h3>";

$success = false;
$method = '';

// Remove existing file/directory if it's blocking
if (file_exists($publicStoragePath)) {
    if (is_dir($publicStoragePath) && !is_link($publicStoragePath)) {
        $files = array_diff(scandir($publicStoragePath), ['.', '..', '.gitkeep']);
        if (empty($files)) {
            rmdir($publicStoragePath);
            echo "<p class='info'>ℹ️ Removed empty public/storage directory</p>";
        } else {
            echo "<p class='error'>❌ public/storage directory exists and is not empty. Cannot create symlink.</p>";
            echo "<p>Please manually delete or rename the <code>public/storage</code> folder.</p>";
            echo "</div></body></html>";
            exit;
        }
    } elseif (!is_link($publicStoragePath)) {
        unlink($publicStoragePath);
        echo "<p class='info'>ℹ️ Removed blocking file at public/storage</p>";
    }
}

// Method 1: Try absolute symlink
if (!$success) {
    echo "<p>Trying Method 1: Absolute symlink...</p>";
    if (@symlink($targetPath, $publicStoragePath)) {
        $success = true;
        $method = 'absolute symlink';
        echo "<p class='success'>✅ Success with absolute symlink!</p>";
    } else {
        echo "<p class='warning'>⚠️ Absolute symlink failed</p>";
    }
}

// Method 2: Try relative symlink
if (!$success) {
    echo "<p>Trying Method 2: Relative symlink...</p>";
    $originalDir = getcwd();
    chdir(__DIR__);
    if (@symlink('../storage/app/public', 'storage')) {
        $success = true;
        $method = 'relative symlink';
        echo "<p class='success'>✅ Success with relative symlink!</p>";
    } else {
        echo "<p class='warning'>⚠️ Relative symlink failed</p>";
    }
    chdir($originalDir);
}

// Check result
if ($success && is_link($publicStoragePath) && is_dir($publicStoragePath)) {
    echo "<h3 class='success'>✅ Storage Link Created Successfully!</h3>";
    echo "<p>Your product images, logos, and other uploaded files should now display correctly.</p>";
    echo "<p class='warning'><strong>⚠️ IMPORTANT:</strong> Delete this file (<code>create-storage-link.php</code>) immediately for security!</p>";
} else {
    // Method 3: Copy files as fallback
    echo "<p>Trying Method 3: Copying files (fallback)...</p>";
    
    if (!file_exists($publicStoragePath)) {
        @mkdir($publicStoragePath, 0755, true);
    }
    
    // Recursive copy function
    function copyDirectory($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst, 0755, true);
        $copied = 0;
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcPath = $src . '/' . $file;
                $dstPath = $dst . '/' . $file;
                if (is_dir($srcPath)) {
                    $copied += copyDirectory($srcPath, $dstPath);
                } else {
                    if (copy($srcPath, $dstPath)) {
                        $copied++;
                    }
                }
            }
        }
        closedir($dir);
        return $copied;
    }
    
    $copiedCount = copyDirectory($targetPath, $publicStoragePath);
    
    if ($copiedCount > 0 || is_dir($publicStoragePath)) {
        $success = true;
        echo "<h3 class='success'>✅ Files Copied Successfully!</h3>";
        echo "<p>Copied $copiedCount files from storage to public folder.</p>";
        echo "<p class='info'>ℹ️ <strong>Note:</strong> Since symlinks failed, files were copied instead. After uploading new images, you may need to run this script again or use the sync option below.</p>";
        echo "<p><a href='?action=sync' style='display:inline-block;padding:10px 20px;background:#007bff;color:#fff;text-decoration:none;border-radius:5px;'>🔄 Sync Files Now</a></p>";
        echo "<p class='warning'><strong>⚠️ IMPORTANT:</strong> Keep this file for syncing, but protect it with a password or delete after setup.</p>";
    }
    
    if (!$success) {
        echo "<h3 class='error'>❌ Could Not Create Storage Link or Copy Files</h3>";
        echo "<p>Your hosting may have strict file permissions. Please try:</p>";
        echo "<h4>Option 1: Via cPanel File Manager</h4>";
        echo "<ol>";
        echo "<li>Open cPanel → File Manager</li>";
        echo "<li>Navigate to your <code>public_html/pos/public</code> folder (or wherever your site is)</li>";
        echo "<li>If a <code>storage</code> folder exists there, rename or delete it</li>";
        echo "<li>Look for a 'Create Symlink' or 'Link' option</li>";
        echo "<li>Create a link named <code>storage</code> pointing to <code>../storage/app/public</code></li>";
        echo "</ol>";
        
        echo "<h4>Option 2: Manual File Copy</h4>";
        echo "<ol>";
        echo "<li>Download the contents of <code>storage/app/public</code> folder</li>";
        echo "<li>Upload them to <code>public/storage</code> folder</li>";
        echo "</ol>";
        
        echo "<h4>Option 3: Contact Hosting Support</h4>";
        echo "<p>Ask them to create a symbolic link:</p>";
        echo "<pre>From: /path/to/site/public/storage\nTo: /path/to/site/storage/app/public</pre>";
    }
}

// Handle sync action
if (isset($_GET['action']) && $_GET['action'] === 'sync') {
    echo "<hr><h3>🔄 Syncing Files...</h3>";
    if (function_exists('copyDirectory')) {
        $synced = copyDirectory($targetPath, $publicStoragePath);
        echo "<p class='success'>Synced $synced files!</p>";
    } else {
        // Re-define if needed
        $dir = opendir($targetPath);
        $synced = 0;
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcPath = $targetPath . '/' . $file;
                $dstPath = $publicStoragePath . '/' . $file;
                if (is_file($srcPath)) {
                    if (@copy($srcPath, $dstPath)) $synced++;
                }
            }
        }
        closedir($dir);
        echo "<p class='success'>Synced $synced files from root!</p>";
        echo "<p>For subfolders, please run again or manually copy.</p>";
    }
}

echo "</div></body></html>";

