<?php

namespace dpodium\filemanager\components;

use Yii;
use yii\web\UploadedFile;

/**
 * FileSecurityHelper - Provides file validation and sanitization to prevent XSS attacks
 * 
 * This helper class implements comprehensive security checks for uploaded files,
 * including detection and removal of malicious content in PDFs and other file types.
 */
class FileSecurityHelper
{
    /**
     * Dangerous file extensions that should never be allowed
     */
    const DANGEROUS_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'exe', 'com', 'bat', 'cmd', 'sh', 'bash',
        'js', 'jsx', 'vbs', 'jar', 'app',
        'scr', 'msi', 'dmg', 'apk'
    ];
    
    /**
     * Validate uploaded file for security threats
     * 
     * @param UploadedFile $file
     * @param array $config Module configuration
     * @return array ['isValid' => bool, 'error' => string|null]
     */
    public static function validateFile($file, $config = [])
    {
        $enableSecurity = isset($config['enableSecurityValidation']) ? $config['enableSecurityValidation'] : true;
        
        if (!$enableSecurity) {
            return ['isValid' => true, 'error' => null];
        }
        
        // Check if file is empty
        if ($file->size === 0) {
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'File is empty')
            ];
        }
        
        // Check for dangerous extensions
        $extension = strtolower($file->extension);
        if (in_array($extension, self::DANGEROUS_EXTENSIONS)) {
            self::logSecurityEvent('DANGEROUS_EXTENSION_BLOCKED', [
                'filename' => $file->name,
                'extension' => $extension
            ]);
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'File type is not allowed for security reasons')
            ];
        }
        
        // Check for null bytes in filename (path traversal attack)
        if (strpos($file->name, "\0") !== false) {
            self::logSecurityEvent('NULL_BYTE_DETECTED', ['filename' => $file->name]);
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'Invalid filename detected')
            ];
        }
        
        // Check for path traversal attempts in filename
        if (preg_match('/\.\.\/|\.\.\\\\/', $file->name)) {
            self::logSecurityEvent('PATH_TRAVERSAL_DETECTED', ['filename' => $file->name]);
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'Invalid filename detected')
            ];
        }
        
        // Special validation for PDFs
        if ($extension === 'pdf') {
            $pdfValidation = self::validatePdfSecurity($file->tempName);
            if (!$pdfValidation['isValid']) {
                self::logSecurityEvent('MALICIOUS_PDF_BLOCKED', [
                    'filename' => $file->name,
                    'reason' => $pdfValidation['error']
                ]);
                return $pdfValidation;
            }
        }
        
        // Special validation for images
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
            $imageValidation = self::validateImageSecurity($file->tempName);
            if (!$imageValidation['isValid']) {
                self::logSecurityEvent('MALICIOUS_IMAGE_BLOCKED', [
                    'filename' => $file->name,
                    'reason' => $imageValidation['error']
                ]);
                return $imageValidation;
            }
        }
        
        return [
            'isValid' => true,
            'error' => null
        ];
    }
    
    /**
     * Validate PDF file for embedded JavaScript and malicious content
     * 
     * @param string $filePath
     * @return array ['isValid' => bool, 'error' => string|null]
     */
    public static function validatePdfSecurity($filePath)
    {
        if (!file_exists($filePath)) {
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'File not found')
            ];
        }
        
        // Read file content (first 1MB for performance)
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'Unable to read file')
            ];
        }
        
        $content = fread($handle, 1048576); // Read first 1MB
        fclose($handle);
        
        if ($content === false) {
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'Unable to read file')
            ];
        }
        
        // Check if it's actually a PDF
        if (substr($content, 0, 4) !== '%PDF') {
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'Invalid PDF file format')
            ];
        }
        
        // Check for embedded JavaScript - Common XSS vectors in PDFs
        $jsPatterns = [
            '/\/JavaScript/i',
            '/\/JS\s*\(/i',
            '/\/JS\s*</i',
            '/app\.alert/i',
            '/this\.submitForm/i',
            '/this\.getURL/i',
            '/eval\s*\(/i',
            '/<script/i',
            '/javascript:/i',
        ];
        
        foreach ($jsPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Yii::warning('PDF with embedded JavaScript detected: ' . $filePath, __METHOD__);
                return [
                    'isValid' => false,
                    'error' => Yii::t('filemanager', 'PDF contains embedded scripts which are not allowed for security reasons')
                ];
            }
        }
        
        // Check for form actions
        if (preg_match('/\/AA\s*<<|\/OpenAction/i', $content)) {
            Yii::warning('PDF with automatic actions detected: ' . $filePath, __METHOD__);
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'PDF contains automatic actions which are not allowed for security reasons')
            ];
        }
        
        // Check for URI actions with javascript protocol
        if (preg_match('/\/URI\s*\([^)]*javascript:/i', $content)) {
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'PDF contains malicious URI actions')
            ];
        }
        
        return [
            'isValid' => true,
            'error' => null
        ];
    }
    
    /**
     * Validate image file for embedded malicious content
     * 
     * @param string $filePath
     * @return array ['isValid' => bool, 'error' => string|null]
     */
    protected static function validateImageSecurity($filePath)
    {
        // Verify image is valid using getimagesize
        $imageInfo = @getimagesize($filePath);
        
        if ($imageInfo === false) {
            return [
                'isValid' => false,
                'error' => Yii::t('filemanager', 'Invalid image file')
            ];
        }
        
        // Read first 2KB to check for script tags or PHP code
        $handle = fopen($filePath, 'rb');
        if ($handle) {
            $chunk = fread($handle, 2048);
            fclose($handle);
            
            // Check for script tags or PHP code
            if (preg_match('/<\?php|<script|javascript:/i', $chunk)) {
                return [
                    'isValid' => false,
                    'error' => Yii::t('filemanager', 'Image contains malicious code')
                ];
            }
        }
        
        return [
            'isValid' => true,
            'error' => null
        ];
    }
    
    /**
     * Sanitize filename to prevent path traversal and other attacks
     * 
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename($filename)
    {
        // Remove any path components
        $filename = basename($filename);
        
        // Remove null bytes
        $filename = str_replace("\0", '', $filename);
        
        // Remove directory traversal attempts
        $filename = str_replace(['../', '..\\'], '', $filename);
        
        // Remove control characters
        $filename = preg_replace('/[\x00-\x1F\x7F]/u', '', $filename);
        
        // Ensure filename is not empty after sanitization
        if (empty($filename)) {
            $filename = 'file_' . uniqid();
        }
        
        return $filename;
    }
    
    /**
     * Log security event
     * 
     * @param string $event
     * @param array $details
     */
    public static function logSecurityEvent($event, $details = [])
    {
        $logData = [
            'event' => $event,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => Yii::$app->user->id ? Yii::$app->user->id : null,
            'ip_address' => Yii::$app->request->userIP ? Yii::$app->request->userIP : null,
            'details' => $details
        ];
        
        Yii::warning('File Upload Security Event: ' . json_encode($logData), 'filemanager.security');
    }
}