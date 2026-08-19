#!/bin/bash

# UIKit Theme Builder - UIKit Update Script
# Updates UIKit to a specific compatible version (LESS.php compatible)

set -e  # Exit bei Fehlern

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default version
DEFAULT_VERSION="3.25.15"
TARGET_VERSION="${1:-$DEFAULT_VERSION}"

echo -e "${BLUE}🏨 UIKit Theme Builder - UIKit Update${NC}"
echo -e "${BLUE}======================================${NC}"
echo ""

# Validate version format
if [[ ! $TARGET_VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo -e "${RED}❌ Invalid version format: $TARGET_VERSION${NC}"
    echo -e "${YELLOW}Expected format: X.Y.Z (e.g., 3.23.12)${NC}"
    exit 1
fi

echo -e "${BLUE}📦 Target UIKit Version: ${GREEN}$TARGET_VERSION${NC}"

# Check if version is compatible
MAJOR=$(echo $TARGET_VERSION | cut -d. -f1)
MINOR=$(echo $TARGET_VERSION | cut -d. -f2)
PATCH=$(echo $TARGET_VERSION | cut -d. -f3)

# Version compatibility check
if [ "$MAJOR" -gt 3 ]; then
    echo -e "${YELLOW}⚠️  WARNING: UIKit v$TARGET_VERSION is a major version above 3.x${NC}"
    echo -e "${YELLOW}⚠️  LESS.php compatibility is untested for this version${NC}"
    echo -e "${YELLOW}⚠️  Recommended version: $DEFAULT_VERSION${NC}"
    echo ""
    read -p "Continue anyway? (y/N): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${RED}❌ Update cancelled${NC}"
        exit 1
    fi
fi

# Pfade
ADDON_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
UIKIT_DIR="$ADDON_DIR/assets/uikit"
TEMP_DIR="/tmp/uikit-update-$$"
BACKUP_DIR="$ADDON_DIR/backup/uikit-$(date +%Y%m%d-%H%M%S)"

echo -e "${YELLOW}📁 AddOn Directory: $ADDON_DIR${NC}"
echo -e "${YELLOW}📁 UIKit Directory: $UIKIT_DIR${NC}"

# Backup der aktuellen custom Dateien erstellen
echo -e "\n${BLUE}📦 Creating backup of custom files...${NC}"
mkdir -p "$BACKUP_DIR"
if [ -d "$UIKIT_DIR/custom" ]; then
    cp -r "$UIKIT_DIR/custom" "$BACKUP_DIR/"
    echo -e "${GREEN}✅ Custom files backed up to: $BACKUP_DIR${NC}"
fi

# Backup der wellings.less
if [ -f "$UIKIT_DIR/wellings.less" ]; then
    cp "$UIKIT_DIR/wellings.less" "$BACKUP_DIR/"
    echo -e "${GREEN}✅ wellings.less backed up${NC}"
fi

# Backup der themes.json falls vorhanden
if [ -f "$UIKIT_DIR/themes.json" ]; then
    cp "$UIKIT_DIR/themes.json" "$BACKUP_DIR/"
    echo -e "${GREEN}✅ themes.json backed up${NC}"
fi

# Aktuelle UIKit Version anzeigen
if [ -f "$UIKIT_DIR/package.json" ]; then
    CURRENT_VERSION=$(grep '"version":' "$UIKIT_DIR/package.json" | sed 's/.*"version": "\(.*\)".*/\1/')
    echo -e "\n${YELLOW}📋 Current UIKit Version: $CURRENT_VERSION${NC}"
fi

# Temporäres Verzeichnis erstellen und UIKit clonen
echo -e "\n${BLUE}📥 Downloading UIKit v$TARGET_VERSION from GitHub...${NC}"
rm -rf "$TEMP_DIR"
git clone --depth 1 --branch "v$TARGET_VERSION" https://github.com/uikit/uikit.git "$TEMP_DIR"

# Neue Version anzeigen
NEW_VERSION=$(grep '"version":' "$TEMP_DIR/package.json" | sed 's/.*"version": "\(.*\)".*/\1/')
echo -e "${GREEN}✅ Downloaded UIKit Version: $NEW_VERSION${NC}"

# UIKit Verzeichnis leeren (aber .gitignore behalten)
echo -e "\n${BLUE}🧹 Cleaning UIKit directory...${NC}"
find "$UIKIT_DIR" -type f ! -name '.gitignore' -delete
find "$UIKIT_DIR" -type d ! -path "$UIKIT_DIR" -exec rm -rf {} + 2>/dev/null || true

# Neue UIKit Dateien kopieren
echo -e "${BLUE}📋 Copying new UIKit files...${NC}"
cp -r "$TEMP_DIR"/* "$UIKIT_DIR/"
cp "$TEMP_DIR/.gitignore" "$UIKIT_DIR/" 2>/dev/null || true
cp "$TEMP_DIR/.prettierrc.json" "$UIKIT_DIR/" 2>/dev/null || true
cp "$TEMP_DIR/.prettierignore" "$UIKIT_DIR/" 2>/dev/null || true

# Custom Dateien wiederherstellen
echo -e "\n${BLUE}🔄 Restoring custom files...${NC}"
if [ -d "$BACKUP_DIR/custom" ]; then
    cp -r "$BACKUP_DIR/custom" "$UIKIT_DIR/"
    echo -e "${GREEN}✅ Custom directory restored${NC}"
fi

if [ -f "$BACKUP_DIR/wellings.less" ]; then
    cp "$BACKUP_DIR/wellings.less" "$UIKIT_DIR/"
    echo -e "${GREEN}✅ wellings.less restored${NC}"
fi

if [ -f "$BACKUP_DIR/themes.json" ]; then
    cp "$BACKUP_DIR/themes.json" "$UIKIT_DIR/"
    echo -e "${GREEN}✅ themes.json restored${NC}"
fi

# Zum UIKit Verzeichnis wechseln
cd "$UIKIT_DIR"

# Dependencies installieren
echo -e "\n${BLUE}📦 Installing dependencies with pnpm...${NC}"
if ! command -v pnpm &> /dev/null; then
    echo -e "${RED}❌ pnpm not found. Installing pnpm globally...${NC}"
    npm install -g pnpm
fi

pnpm install

# UIKit kompilieren
echo -e "\n${BLUE}🔨 Compiling UIKit...${NC}"
echo -e "${YELLOW}⏳ This may take a moment...${NC}"
pnpm compile

# Kompilierte Dateien nach compiled_uikit/ kopieren
echo -e "\n${BLUE}📋 Copying compiled files to compiled_uikit/...${NC}"
COMPILED_DIR="$ADDON_DIR/assets/compiled_uikit"
mkdir -p "$COMPILED_DIR/css" "$COMPILED_DIR/js/components"

# CSS kopieren
cp "$UIKIT_DIR/dist/css/"*.css "$COMPILED_DIR/css/"

# JS kopieren
cp "$UIKIT_DIR/dist/js/uikit.js" "$UIKIT_DIR/dist/js/uikit.min.js" \
   "$UIKIT_DIR/dist/js/uikit-core.js" "$UIKIT_DIR/dist/js/uikit-core.min.js" \
   "$UIKIT_DIR/dist/js/uikit-icons.js" "$UIKIT_DIR/dist/js/uikit-icons.min.js" \
   "$COMPILED_DIR/js/"

# Icons-Custom kopieren falls vorhanden
if ls "$UIKIT_DIR/dist/js/uikit-icons-"*.js 1>/dev/null 2>&1; then
    cp "$UIKIT_DIR/dist/js/uikit-icons-"*.js "$COMPILED_DIR/js/"
fi

# JS-Komponenten kopieren
if [ -d "$UIKIT_DIR/dist/js/components" ]; then
    cp "$UIKIT_DIR/dist/js/components/"*.js "$COMPILED_DIR/js/components/"
fi

echo -e "${GREEN}✅ compiled_uikit/ aktualisiert${NC}"

# Cleanup
echo -e "\n${BLUE}🧹 Cleaning up...${NC}"
rm -rf "$TEMP_DIR"

# Success Message
echo -e "\n${GREEN}🎉 UIKit Update Complete!${NC}"
echo -e "${GREEN}=========================${NC}"
echo -e "${GREEN}✅ UIKit updated from $CURRENT_VERSION to $NEW_VERSION${NC}"
echo -e "${GREEN}✅ compiled_uikit/ mit neuen Dateien aktualisiert${NC}"
echo -e "${GREEN}✅ Custom files preserved${NC}"
echo -e "${GREEN}✅ UIKit compiled successfully${NC}"
echo -e "${YELLOW}📁 Backup stored in: $BACKUP_DIR${NC}"

echo -e "\n${BLUE}📋 What's next?${NC}"
echo -e "${YELLOW}1. Check if your custom themes still work correctly${NC}"
echo -e "${YELLOW}2. Test the UIKit Theme Builder functionality${NC}"
echo -e "${YELLOW}3. Update any custom LESS files if needed${NC}"

echo -e "\n${BLUE}🔗 Useful commands:${NC}"
echo -e "${YELLOW}   cd $UIKIT_DIR${NC}"
echo -e "${YELLOW}   pnpm watch    # Watch for changes${NC}"
echo -e "${YELLOW}   pnpm compile  # Recompile UIKit${NC}"