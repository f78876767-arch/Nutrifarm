#!/bin/bash

# Kill existing Laravel servers
pkill -f "php artisan serve" 2>/dev/null || true

echo "🚀 Starting Nutrifarm Development Server..."
echo "📡 Server will be available at: http://192.168.1.81:8000"
echo "🔄 Server will auto-restart on file changes"
echo "⏹️  Press Ctrl+C to stop"

# Function to start server
start_server() {
    php artisan serve --host=0.0.0.0 --port=8000 &
    SERVER_PID=$!
    echo "✅ Server started with PID: $SERVER_PID"
}

# Function to restart server
restart_server() {
    echo "🔄 Restarting server..."
    kill $SERVER_PID 2>/dev/null || true
    sleep 1
    start_server
}

# Start initial server
start_server

# Watch for file changes (excluding vendor, node_modules, storage/logs)
fswatch -o \
    --exclude=vendor/ \
    --exclude=node_modules/ \
    --exclude=storage/logs/ \
    --exclude=storage/framework/cache/ \
    --exclude=storage/framework/sessions/ \
    --exclude=storage/framework/views/ \
    --exclude=.git/ \
    app/ config/ routes/ resources/ database/ public/ .env | while read f; do
    echo "📁 File change detected, restarting..."
    restart_server
done
