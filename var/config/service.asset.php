<?php
/**
 * Asset service configuration
 */

return array(
    // Append version number to asset files to prevent browser cache
    'append_version'    => true,

    // Use symlink for asset publish
    // @FIXME Unidentified issues reported, thus symlink is disabled by default temporarily
    'use_symlink'       => false,

    // Override existent files on copy
    'override'          => true,
);
