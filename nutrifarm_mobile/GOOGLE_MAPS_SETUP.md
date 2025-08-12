# Google Maps Setup Guide

## Getting Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing project
3. Enable the following APIs:
   - Maps SDK for Android
   - Maps SDK for iOS
   - Places API (for address search)
   - Geocoding API (for address lookup)

4. Go to "Credentials" and create a new API Key
5. Restrict the API key by:
   - Application restrictions (add your app's bundle ID)
   - API restrictions (enable only the APIs you need)

## Adding API Key to the App

### Android
1. Open `android/app/src/main/AndroidManifest.xml`
2. Replace `YOUR_GOOGLE_MAPS_API_KEY_HERE` with your actual API key:
```xml
<meta-data android:name="com.google.android.geo.API_KEY"
           android:value="YOUR_ACTUAL_API_KEY_HERE"/>
```

### iOS
1. Open `ios/Runner/AppDelegate.swift`
2. Add the following import at the top:
```swift
import GoogleMaps
```
3. Add this line in the `application` method:
```swift
GMSServices.provideAPIKey("YOUR_ACTUAL_API_KEY_HERE")
```

## Current Status

✅ **Dependencies Added**: All necessary packages are in pubspec.yaml
- google_maps_flutter: ^2.12.3
- geolocator: ^13.0.2
- permission_handler: ^11.3.1
- shared_preferences: ^2.3.5

✅ **Permissions Configured**: 
- Android: Location permissions added to AndroidManifest.xml
- iOS: Location usage descriptions added to Info.plist

⚠️ **API Key Required**: You need to add your Google Maps API key to complete the setup

## Features Implemented

The address management system includes:

1. **Address List Page**: View and manage all saved addresses
2. **Add/Edit Address Page**: Add new addresses with Google Maps integration
3. **Location Services**: Get current location and search for addresses
4. **Local Storage**: Addresses are saved locally using SharedPreferences
5. **Search functionality**: Search through saved addresses
6. **Google Maps Integration**: Pick locations directly from the map

## Usage

Navigate to Profile → Alamat to access the address management system.
