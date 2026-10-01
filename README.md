# MataKo

MataKo is a digital eye strain self-assessment app for students and working professionals. The mobile client is now React Native with Expo. The Laravel API remains in `backend-app/`. The previous Flutter client is retained in `mobile/` as a reference.

## Run the app

Start the Laravel API in one PowerShell terminal:

```powershell
Set-Location D:\MataKO\MataKO\backend-app
php artisan serve --host 0.0.0.0
```

Start an Android emulator in Android Studio. In a second terminal, install the JavaScript dependencies once, then launch MataKo:

```powershell
Set-Location D:\MataKO\MataKO\mobile-react-native
npm install
npm run android
```

`npm run android` starts Expo and opens the app on the available Android emulator. On iOS, run `npm start` and open its QR code in Expo Go; keep the phone and computer on the same Wi-Fi. The app stores the sign-in token and selected language on the device. Android emulators connect to `http://10.0.2.2:8000/api`; iOS uses the host address from Expo's development server. For another backend address, set `EXPO_PUBLIC_API_URL` before starting Expo.

## Included app flows

- English, Filipino, and Cebuano language selection
- Student and professional profiles
- Account registration, sign-in, and sign-out through Laravel Sanctum
- Eye strain assessment, saved results, and assessment history
- Eye care tips, profile settings, and foreground 20-minute reminders

The assessment is for general guidance and is not a medical diagnosis. Password reset and Google/Apple sign-in are not configured in this prototype.
