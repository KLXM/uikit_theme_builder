# LESS.php Bugfix - PHP 8.1+ Kompatibilität

## Problem
LESS.php v5.4.0 erzeugte viele Deprecated Warnings unter PHP 8.1+:
```
Deprecated: file_exists(): Passing null to parameter #1 ($filename) of type string is deprecated
```

## Ursache
Die `Less_FileManager::getFilePath()` Funktion kann `null` zurückgeben, wenn keine gültige Datei gefunden wird. In `Functions.php` wurde das Ergebnis jedoch direkt destructured:

```php
[ $filePath ] = Less_FileManager::getFilePath( $filePath, $this->currentFileInfo );
```

Dies führte dazu, dass `$filePath` zu `null` wurde und anschließend an `file_exists()` übergeben wurde.

## Lösung
Zwei Fixes in `vendor/wikimedia/less.php/lib/Less/Functions.php`:

### 1. data-uri() Funktion (Zeile ~1001)
```php
$filePathResult = Less_FileManager::getFilePath( $filePath, $this->currentFileInfo );
if ( !$filePathResult ) {
    $fallback = new Less_Tree_Url( ( $filePathNode ?: $mimetypeNode ), $this->currentFileInfo );
    return $fallback->compile( $this->env );
}
[ $filePath ] = $filePathResult;
```

### 2. getImageSize() Funktion (Zeile ~1145)
```php
$filePathResult = Less_FileManager::getFilePath( $filePath, $this->currentFileInfo );
if ( !$filePathResult ) {
    return [ "width" => 0, "height" => 0 ];
}
[ $filePath ] = $filePathResult;

if ( !$filePath || !file_exists( $filePath ) ) {
    return [ "width" => 0, "height" => 0 ];
}
```

## Validierung
Der Test `test_less_fix.php` validiert beide Fixes und bestätigt, dass keine Deprecated Warnings mehr auftreten.

## Status
- ✅ Alle PHP 8.1+ Deprecated Warnings behoben
- ✅ Funktionalität bleibt vollständig erhalten
- ✅ Graceful Fallbacks für ungültige Dateipfade
- ✅ Test-Validierung erfolgreich