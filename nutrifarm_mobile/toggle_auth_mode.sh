#!/bin/bash

# Development Mode Toggle Script
# This script helps you toggle between development (bypass auth) and production (normal auth flow) modes

case "$1" in
    "dev"|"development")
        echo "🚀 Switching to DEVELOPMENT mode (bypassing authentication)..."
        
        # Create backup if it doesn't exist
        if [ ! -f "lib/main_production.dart" ]; then
            echo "📦 Creating backup of production main.dart..."
            cp lib/main.dart lib/main_production.dart
        fi
        
        # Apply development changes
        sed -i '' "s|initialRoute: '/home'|initialRoute: '/home'|g" lib/main.dart
        sed -i '' "s|'/': (context) => const OnboardingScreen()|'/': (context) => const MainNavigator()|g" lib/main.dart
        
        echo "✅ DEVELOPMENT mode activated!"
        echo "   - Authentication bypassed"
        echo "   - App will start directly on home page"
        echo "   - Use './toggle_auth_mode.sh prod' to restore authentication"
        ;;
    "prod"|"production")
        echo "🔐 Switching to PRODUCTION mode (normal authentication flow)..."
        
        if [ -f "lib/main_production.dart" ]; then
            cp lib/main_production.dart lib/main.dart
            echo "✅ PRODUCTION mode restored!"
            echo "   - Normal authentication flow"
            echo "   - App will show onboarding/login screens"
        else
            # Manual restoration
            sed -i '' "s|initialRoute: '/home'|initialRoute: '/'|g" lib/main.dart
            sed -i '' "s|'/': (context) => const MainNavigator()|'/': (context) => const OnboardingScreen()|g" lib/main.dart
            echo "✅ PRODUCTION mode activated!"
            echo "   - Normal authentication flow restored"
        fi
        ;;
    "status")
        echo "📊 Current Mode Status:"
        if grep -q "initialRoute: '/home'" lib/main.dart; then
            echo "   🚀 DEVELOPMENT mode (auth bypassed)"
        else
            echo "   🔐 PRODUCTION mode (normal auth)"
        fi
        
        if [ -f "lib/main_production.dart" ]; then
            echo "   📦 Production backup available"
        else
            echo "   ⚠️  No production backup found"
        fi
        ;;
    *)
        echo "🔧 Authentication Mode Toggle"
        echo ""
        echo "Usage: $0 [option]"
        echo ""
        echo "Options:"
        echo "  dev, development    Switch to development mode (bypass auth)"
        echo "  prod, production    Switch to production mode (normal auth)"
        echo "  status              Show current mode status"
        echo ""
        echo "Examples:"
        echo "  $0 dev              # Enable development mode"
        echo "  $0 prod             # Enable production mode"
        echo "  $0 status           # Check current mode"
        ;;
esac
