# File Manager Security Guide

## Overview

This filemanager includes comprehensive security features to prevent XSS attacks and malicious file uploads.

## Security Features

### 1. PDF JavaScript Detection

Automatically detects and blocks PDFs containing:
- Embedded JavaScript (`/JavaScript`, `/JS`, `app.alert()`, `eval()`)
- Automatic actions (`/OpenAction`, `/AA`)
- Malicious URI actions with `javascript:` protocol

### 2. Image Malware Detection

Scans uploaded images for:
- Embedded PHP code
- Script tags
- Invalid image format

### 3. Filename Sanitization

- Removes path traversal attempts (`../`, `..\`)
- Strips null bytes
- Removes control characters

### 4. Extension Blocking

Dangerous extensions are automatically blocked:
`php`, `exe`, `sh`, `js`, `bat`, `cmd`, `vbs`, `jar`, `phar`, etc.

## Configuration

### Enable/Disable Security Validation

'filemanager' => [
    'class' => 'dpodium\filemanager\Module',
    'enableSecurityValidation' => true, // Set to false to disable (not recommended)
    // ... other config
]### Custom Blocked Extensions

'filemanager' => [
    'class' => 'dpodium\filemanager\Module',
    'blockedExtensions' => [
        'php', 'exe', 'sh', 'custom-ext'
    ],
    // ... other config
]## Testing

### Test Malicious PDF Upload

Upload a PDF with embedded JavaScript - it should be blocked with the message:
> "PDF contains embedded scripts which are not allowed for security reasons"

### Monitor Security Events

Check application logs for security events:
grep "File Upload Security Event" @runtime/logs/app.log## Bypassing Security (Not Recommended)

For development/testing only:

'filemanager' => [
    'class' => 'dpodium\filemanager\Module',
    'enableSecurityValidation' => false, // DANGEROUS - only for dev/testing
]## Vulnerability Reports

If you discover a security vulnerability, please email: security@dpodium.com

## Compliance

These security features address:
- **OWASP A03:2021** - Injection
- **OWASP A08:2021** - Software and Data Integrity Failures
- **CWE-79** - Cross-site Scripting (XSS)

---

**Last Updated**: 2025-11-19