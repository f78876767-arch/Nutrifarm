#!/bin/bash

# Profile Page Style Switcher
# This script helps you switch between different profile page styles

case "$1" in
    "cart")
        echo "🔄 Switching to Cart-Style Profile Page..."
        cp lib/pages/profile_page.dart lib/pages/profile_page_current_backup.dart
        cp lib/pages/profile_page_cart_style.dart lib/pages/profile_page.dart
        echo "✅ Switched to Cart-Style Profile Page"
        echo "💾 Your previous version is backed up as profile_page_current_backup.dart"
        ;;
    "original")
        echo "🔄 Switching to Original Profile Page..."
        cp lib/pages/profile_page.dart lib/pages/profile_page_current_backup.dart
        cp lib/pages/profile_page_original.dart lib/pages/profile_page.dart
        echo "✅ Switched to Original Profile Page"
        echo "💾 Your previous version is backed up as profile_page_current_backup.dart"
        ;;
    "list")
        echo "📋 Available Profile Page Styles:"
        echo "   - original: Your original profile page design"
        echo "   - cart: Cart-inspired profile page design"
        echo ""
        echo "🔍 Current files:"
        ls -la lib/pages/profile_page*.dart
        ;;
    *)
        echo "🔧 Profile Page Style Switcher"
        echo ""
        echo "Usage: $0 [option]"
        echo ""
        echo "Options:"
        echo "  cart        Switch to cart-style profile page"
        echo "  original    Switch back to original profile page"
        echo "  list        List available styles and current files"
        echo ""
        echo "Examples:"
        echo "  $0 cart      # Switch to cart style"
        echo "  $0 original  # Switch back to original"
        echo "  $0 list      # See available options"
        ;;
esac
